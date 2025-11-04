<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../connection/main_connection.php';

$user_id = intval($_SESSION['user_id']);
$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;

if ($request_id <= 0 || $service_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid request or service id']);
    exit;
}

// Verify ownership
$stmt = $conn->prepare('SELECT request_id FROM services_requests WHERE request_id = ? AND user_id = ?');
$stmt->bind_param('ii', $request_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Access denied']);
    exit;
}
$stmt->close();

// Check can_update flag; disable updating when false/0
$stmtCU = $conn->prepare('SELECT can_update FROM services_requests WHERE request_id = ? AND user_id = ?');
$stmtCU->bind_param('ii', $request_id, $user_id);
$stmtCU->execute();
$resCU = $stmtCU->get_result();
$rowCU = $resCU->fetch_assoc();
$stmtCU->close();

if (!$rowCU || intval($rowCU['can_update']) !== 1) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Updates are locked for this request']);
    exit;
}

// Load fields for the service
$fields = [];
$stmtF = $conn->prepare('SELECT field_id, field_type, is_required FROM services_fields WHERE service_id = ?');
$stmtF->bind_param('i', $service_id);
$stmtF->execute();
$resF = $stmtF->get_result();
while ($row = $resF->fetch_assoc()) {
    $fields[intval($row['field_id'])] = $row;
}
$stmtF->close();

// Helper to save file
function saveUploadedFile($fieldId, $fileInfo, $baseUrl)
{
    $uploadDir = __DIR__ . '/../uploads/service_requests/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }
    $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
    $unique = 'req_' . date('Ymd_His') . '_' . $fieldId . '_' . bin2hex(random_bytes(4));
    $filename = $unique . ($ext ? ('.' . $ext) : '');
    $destPath = $uploadDir . $filename;
    if (!move_uploaded_file($fileInfo['tmp_name'], $destPath)) {
        return [false, 'Failed to move uploaded file'];
    }
    $relativePath = 'uploads/service_requests/' . $filename; // web-accessible path
    $absoluteUrl = rtrim($baseUrl, '/') . '/' . $relativePath; // match submit-service-request.php
    return [true, $absoluteUrl];
}

$conn->begin_transaction();
try {
    // Build base URL consistent with submit-service-request.php
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $project_root = str_replace('/api', '', dirname($_SERVER['PHP_SELF']));
    $base_url = "$protocol://$host$project_root";
    foreach ($fields as $fid => $f) {
        $type = $f['field_type'];
        $name = 'field-' . $fid;
        $value = null;

        if ($type === 'file') {
            if (isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK) {
                [$ok, $urlOrError] = saveUploadedFile($fid, $_FILES[$name], $base_url);
                if (!$ok) {
                    throw new Exception($urlOrError);
                }
                $value = $urlOrError;
            } else {
                // Keep existing value if no new file uploaded
                $stmtE = $conn->prepare('SELECT answer_value FROM services_answers WHERE request_id = ? AND field_id = ?');
                $stmtE->bind_param('ii', $request_id, $fid);
                $stmtE->execute();
                $resE = $stmtE->get_result();
                $value = ($resE->num_rows ? $resE->fetch_assoc()['answer_value'] : '');
                $stmtE->close();
            }
        } elseif ($type === 'checkbox') {
            $arr = isset($_POST[$name]) && is_array($_POST[$name]) ? $_POST[$name] : [];
            // Use a ", " delimiter to match original submission format
            $value = implode(', ', array_map('trim', $arr));
        } else {
            $value = isset($_POST[$name]) ? trim($_POST[$name]) : '';
        }

        // Skip writing empty values to avoid creating duplicate blank rows
        if ($value === '' || $value === null) {
            continue;
        }

        // Update-first to avoid duplicates; insert if no existing row
        $stmtU = $conn->prepare('UPDATE services_answers SET answer_value = ? WHERE request_id = ? AND field_id = ?');
        $stmtU->bind_param('sii', $value, $request_id, $fid);
        $stmtU->execute();
        $affected = $stmtU->affected_rows;
        $stmtU->close();

        if ($affected === 0) {
            // If no rows were affected it's either because the row doesn't exist
            // or because the value is identical. Check existence first to
            // avoid inserting duplicates when the row already exists.
            $stmtCheck = $conn->prepare('SELECT 1 FROM services_answers WHERE request_id = ? AND field_id = ? LIMIT 1');
            $stmtCheck->bind_param('ii', $request_id, $fid);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            $exists = ($resCheck && $resCheck->num_rows > 0);
            $stmtCheck->close();

            if (!$exists) {
                $stmtI = $conn->prepare('INSERT INTO services_answers (request_id, field_id, answer_value) VALUES (?, ?, ?)');
                $stmtI->bind_param('iis', $request_id, $fid, $value);
                if (!$stmtI->execute()) {
                    throw new Exception('Failed saving answer for field ' . $fid);
                }
                $stmtI->close();
            }
        }
    }

    // After successful field updates, lock further edits
    $stmtLock = $conn->prepare('UPDATE services_requests SET can_update = 0 WHERE request_id = ?');
    $stmtLock->bind_param('i', $request_id);
    if (!$stmtLock->execute()) {
        throw new Exception('Failed to lock request after update');
    }
    $stmtLock->close();

    $conn->commit();
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

<?php
session_start();

// --- AUTHENTICATION GUARD ---
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

include '../connection/main_connection.php';
require_once __DIR__ . '/../functions/send_email.php';

header('Content-Type: application/json');

// This makes the try/catch block work for database errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Define Upload Directory (absolute path to project root uploads)
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/service_requests/');

try {
    // Get service_id from POST data
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    
    if ($service_id <= 0) {
        throw new Exception('Invalid service ID');
    }

    // Verify service exists and is active
    $stmt = $conn->prepare("SELECT name FROM services_list WHERE service_id = ? AND is_active = 1");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Service not found or inactive');
    }
    
    $service = $result->fetch_assoc();
    $stmt->close();

    // Get user_id from session
    $user_id = intval($_SESSION['user_id']);

    // Get base URL for file uploads
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    // Get the project root directory (remove /api from the path)
    $project_root = str_replace('/api', '', dirname($_SERVER['PHP_SELF']));
    $base_url = "$protocol://$host$project_root";

    // Start transaction
    $conn->begin_transaction();

    // Create a new service request
    $default_status_id = 1; // Default to first status (usually "Pending")
    $stmt = $conn->prepare("INSERT INTO services_requests (service_id, user_id, status_id, requested_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $service_id, $user_id, $default_status_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create service request');
    }
    
    $request_id = $conn->insert_id;
    $stmt->close();

    // Get service fields to know what answers to save
    $stmt = $conn->prepare("SELECT field_id, field_type FROM services_fields WHERE service_id = ? ORDER BY display_order");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $fields_result = $stmt->get_result();
    $fields = $fields_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Collect all answers (text and files)
    $answers = [];

    // Create upload directory if it doesn't exist
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    // Process file uploads first
    if (!empty($_FILES)) {
        foreach ($_FILES as $field_key => $file_info) {
            if (isset($file_info['error']) && $file_info['error'] === UPLOAD_ERR_OK) {
                $field_id = str_replace('field-', '', $field_key);
                
                $filename = $file_info['name'];
                $tmp_name = $file_info['tmp_name'];
                
                // Create safe filename
                $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
                $safe_filename = uniqid() . '-' . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($filename, '.' . $file_extension)) . '.' . $file_extension;
                
                $absolute_destination = UPLOAD_DIR . $safe_filename;
                
                if (move_uploaded_file($tmp_name, $absolute_destination)) {
                    // Build the full URL using relative path for web access
                    $relative_url_path = 'uploads/service_requests/' . $safe_filename;
                    $absolute_url = $base_url . '/' . $relative_url_path;
                    $answers[$field_id] = $absolute_url;
                } else {
                    throw new Exception("Could not move uploaded file to destination.");
                }
            }
        }
    }

    // Process text fields
    foreach ($fields as $field) {
        $field_id = $field['field_id'];
        $field_type = $field['field_type'];
        $field_key = "field-{$field_id}";
        
        // Skip if this is a file field (already processed above)
        if ($field_type === 'file') {
            continue;
        }
        
        $answer_value = null;
        
        // Handle different field types
        switch ($field_type) {
            case 'checkbox':
                // For checkboxes, combine multiple values
                if (isset($_POST[$field_key]) && is_array($_POST[$field_key])) {
                    $answer_value = implode(', ', $_POST[$field_key]);
                }
                break;
                
            default:
                // For text, textarea, date, select, radio
                if (isset($_POST[$field_key])) {
                    $answer_value = trim($_POST[$field_key]);
                }
                break;
        }
        
        // Only save if there's a value
        if ($answer_value !== null && $answer_value !== '') {
            $answers[$field_id] = $answer_value;
        }
    }

    // Save all answers to database
    if (!empty($answers)) {
        $answer_stmt = $conn->prepare("INSERT INTO services_answers (request_id, field_id, answer_value) VALUES (?, ?, ?)");
        
        foreach ($answers as $field_id => $answer_value) {
            $answer_stmt->bind_param("iis", $request_id, $field_id, $answer_value);
            if (!$answer_stmt->execute()) {
                throw new Exception('Failed to save answer for field ' . $field_id);
            }
        }
        
        $answer_stmt->close();
    }

    // Commit transaction
    $conn->commit();

    // Prepare and send confirmation email to logged-in user using template
    try {
        // Fetch user email and fullname parts
        $stmtU = $conn->prepare('SELECT email, first_name, middle_name, last_name, suffix FROM services_users WHERE id = ? LIMIT 1');
        $stmtU->bind_param('i', $user_id);
        $stmtU->execute();
        $resU = $stmtU->get_result();
        $userEmail = null;
        $registeredFullname = 'N/A';
        if ($resU && $rowU = $resU->fetch_assoc()) {
            $userEmail = (string)($rowU['email'] ?? '');
            $fn = trim((string)($rowU['first_name'] ?? ''));
            $mn = trim((string)($rowU['middle_name'] ?? ''));
            $ln = trim((string)($rowU['last_name'] ?? ''));
            $sf = trim((string)($rowU['suffix'] ?? ''));
            if ($fn && $ln) {
                $registeredFullname = trim($fn . ($mn ? ' ' . $mn : '') . ' ' . $ln . ($sf ? ' ' . $sf : ''));
            }
        }
        $stmtU->close();

        // Resolve status name for default status
        $statusName = 'Pending';
        $stmtS = $conn->prepare('SELECT status_name FROM services_request_statuses WHERE status_id = ? LIMIT 1');
        $stmtS->bind_param('i', $default_status_id);
        $stmtS->execute();
        $resS = $stmtS->get_result();
        if ($resS && $rowS = $resS->fetch_assoc()) {
            $statusName = (string)($rowS['status_name'] ?? 'Pending');
        }
        $stmtS->close();

        $remarksText = 'N/A';

        // Build email subject/body using template if available
        $subject = !empty($SUBJECT_SERVICE_REQUEST) ? $SUBJECT_SERVICE_REQUEST : 'Service Request Submitted';
        if (!empty($HTML_CODE_SERVICE_REQUEST)) {
            $body = str_replace(
                ['{{registered_fullname}}', '{{service_name}}', '{{request_id}}', '{{status}}', '{{remarks}}'],
                [htmlspecialchars($registeredFullname), htmlspecialchars($service['name']), htmlspecialchars((string)$request_id), htmlspecialchars($statusName), htmlspecialchars($remarksText)],
                $HTML_CODE_SERVICE_REQUEST
            );
        } else {
            // Fallback simple HTML body
            $body = '<p>Hello ' . htmlspecialchars($registeredFullname) . ',</p>' .
                '<p>Your service request has been submitted.</p>' .
                '<ul>' .
                    '<li><strong>Request ID:</strong> ' . htmlspecialchars((string)$request_id) . '</li>' .
                    '<li><strong>Service:</strong> ' . htmlspecialchars($service['name']) . '</li>' .
                    '<li><strong>Status:</strong> ' . htmlspecialchars($statusName) . '</li>' .
                    '<li><strong>Remarks:</strong> ' . htmlspecialchars($remarksText) . '</li>' .
                '</ul>' .
                '<p>— Pamantasan ng Lungsod ng Pasig - Student Success Office</p>';
        }

        if (!empty($userEmail)) {
            try { sendEmail($userEmail, $subject, $body); } catch (Throwable $e) { /* ignore email errors */ }
        }
    } catch (Throwable $e) {
        // Do not block success if email fails
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Service request submitted successfully',
        'request_id' => $request_id,
        'service_name' => $service['name']
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Delete any files that were successfully uploaded before the DB error
    if (isset($answers)) {
        foreach ($answers as $field_id => $answer_value) {
            if (strpos($answer_value, UPLOAD_DIR) !== false && file_exists($answer_value)) {
                unlink($answer_value); // Delete the file
            }
        }
    }
    
    error_log("Service request submission error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../connection/main_connection.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) { $input = $_POST; }

$current = isset($input['current_password']) ? (string)$input['current_password'] : '';
$new = isset($input['new_password']) ? (string)$input['new_password'] : '';
$userId = $_SESSION['user_id'];

// Validate requirements
$hasMinLen = strlen($new) >= 8;
$hasUpper = preg_match('/[A-Z]/', $new);
$hasLower = preg_match('/[a-z]/', $new);
$hasDigit = preg_match('/\d/', $new);
$hasSpecial = preg_match('/[^A-Za-z0-9]/', $new);
if (!$hasMinLen || !$hasUpper || !$hasLower || !$hasDigit || !$hasSpecial) {
    echo json_encode(['success' => false, 'message' => 'Password does not meet requirements.']);
    exit;
}

try {
    // Fetch current hash
    $stmt = $conn->prepare('SELECT password_hash FROM services_users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows !== 1) {
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
        exit;
    }
    $row = $res->fetch_assoc();
    $hash = $row['password_hash'];
    if (!password_verify($current, $hash)) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit;
    }

    $newHash = password_hash($new, PASSWORD_BCRYPT);
    $stmtUpd = $conn->prepare('UPDATE services_users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
    $stmtUpd->bind_param('si', $newHash, $userId);
    $stmtUpd->execute();

    echo json_encode(['success' => true]);
    exit;
} catch (Throwable $t) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $t->getMessage()]);
    exit;
}
?>
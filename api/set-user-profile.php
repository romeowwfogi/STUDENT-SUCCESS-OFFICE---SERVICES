<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../connection/main_connection.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) { $input = $_POST; }

$firstName = isset($input['first_name']) ? trim($input['first_name']) : '';
$middleName = isset($input['middle_name']) ? trim($input['middle_name']) : '';
$lastName = isset($input['last_name']) ? trim($input['last_name']) : '';
$suffix = isset($input['suffix']) ? trim($input['suffix']) : '';
$userId = $_SESSION['user_id'];

if ($firstName === '') {
    echo json_encode(['success' => false, 'message' => 'INVALID_FIRST_NAME']);
    exit;
}
if ($lastName === '') {
    echo json_encode(['success' => false, 'message' => 'INVALID_LAST_NAME']);
    exit;
}

try {
    $stmt = $conn->prepare('UPDATE services_users SET first_name = ?, middle_name = ?, last_name = ?, suffix = ?, updated_at = NOW() WHERE id = ?');
    $stmt->bind_param('ssssi', $firstName, $middleName, $lastName, $suffix, $userId);
    $ok = $stmt->execute();
    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'SET_PROFILE_FAILED']);
        exit;
    }
    echo json_encode(['success' => true, 'message' => 'SET_PROFILE_SUCCESS']);
    exit;
} catch (Throwable $t) {
    echo json_encode(['success' => false, 'message' => 'SET_PROFILE_FAILED']);
    exit;
}
?>
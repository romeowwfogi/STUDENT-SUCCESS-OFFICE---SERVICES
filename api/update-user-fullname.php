<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../connection/main_connection.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$firstName = isset($input['first_name']) ? trim($input['first_name']) : '';
$middleName = isset($input['middle_name']) ? trim($input['middle_name']) : '';
$lastName = isset($input['last_name']) ? trim($input['last_name']) : '';
$suffix = isset($input['suffix']) ? trim($input['suffix']) : '';
$currentPassword = isset($input['current_password']) ? (string)$input['current_password'] : '';
$userId = $_SESSION['user_id'];

if ($firstName === '' || $lastName === '') {
    echo json_encode(['success' => false, 'message' => 'Please provide first and last name.']);
    exit;
}
if ($currentPassword === '') {
    echo json_encode(['success' => false, 'message' => 'Please enter your password to confirm.']);
    exit;
}

try {
    // Get current user info
    $stmtU = $conn->prepare('SELECT email, password_hash FROM services_users WHERE id = ? LIMIT 1');
    $stmtU->bind_param('i', $userId);
    $stmtU->execute();
    $resU = $stmtU->get_result();
    if (!$resU || $resU->num_rows !== 1) {
        throw new Exception('Account not found.');
    }
    $userRow = $resU->fetch_assoc();
    $hash = $userRow['password_hash'] ?? null;
    if (!$hash || !password_verify($currentPassword, $hash)) {
        echo json_encode(['success' => false, 'message' => 'Password confirmation failed.']);
        exit;
    }

    $stmtUpd = $conn->prepare('UPDATE services_users SET first_name = ?, middle_name = ?, last_name = ?, suffix = ?, updated_at = NOW() WHERE id = ?');
    $stmtUpd->bind_param('ssssi', $firstName, $middleName, $lastName, $suffix, $userId);
    $stmtUpd->execute();

    echo json_encode(['success' => true]);
    exit;
} catch (Throwable $t) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $t->getMessage()]);
    exit;
}

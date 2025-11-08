<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../connection/main_connection.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) { $input = $_POST; }

$newEmail = isset($input['new_email']) ? trim($input['new_email']) : '';
$currentPassword = isset($input['current_password']) ? (string)$input['current_password'] : '';
$userId = $_SESSION['user_id'];

if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Enter a valid new email address.']);
    exit;
}
if ($currentPassword === '') {
    echo json_encode(['success' => false, 'message' => 'Please enter your password to confirm.']);
    exit;
}

try {
    // Verify current password and fetch current email
    $stmtU = $conn->prepare('SELECT email, password_hash FROM services_users WHERE id = ? LIMIT 1');
    $stmtU->bind_param('i', $userId);
    $stmtU->execute();
    $resU = $stmtU->get_result();
    if (!$resU || $resU->num_rows !== 1) {
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
        exit;
    }
    $userRow = $resU->fetch_assoc();
    $currentEmail = $userRow['email'];
    $hash = $userRow['password_hash'] ?? null;
    if (!$hash || !password_verify($currentPassword, $hash)) {
        echo json_encode(['success' => false, 'message' => 'Password confirmation failed.']);
        exit;
    }

    if (strcasecmp($newEmail, $currentEmail) === 0) {
        echo json_encode(['success' => false, 'message' => 'New email must be different from current email.']);
        exit;
    }

    // Ensure new email not already used by another account
    $stmtCheck = $conn->prepare('SELECT id FROM services_users WHERE email = ? AND id <> ? LIMIT 1');
    $stmtCheck->bind_param('si', $newEmail, $userId);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if ($resCheck && $resCheck->num_rows) {
        echo json_encode(['success' => false, 'message' => 'This email is already associated with another account.']);
        exit;
    }

    $stmtUpd = $conn->prepare('UPDATE services_users SET email = ?, email_verified = 1, email_verified_at = NOW(), updated_at = NOW() WHERE id = ?');
    $stmtUpd->bind_param('si', $newEmail, $userId);
    $stmtUpd->execute();

    // Update session email
    $_SESSION['email'] = $newEmail;

    echo json_encode(['success' => true]);
    exit;
} catch (Throwable $t) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $t->getMessage()]);
    exit;
}
?>
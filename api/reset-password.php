<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../connection/main_connection.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Parse JSON or form data
$input = null;
if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
}
if (!is_array($input)) {
    $input = $_POST;
}

$email = isset($input['email']) ? trim($input['email']) : (isset($input['email_address']) ? trim($input['email_address']) : '');
$code = isset($input['code']) ? preg_replace('/\D/', '', $input['code']) : (isset($input['otp']) ? preg_replace('/\D/', '', $input['otp']) : '');
$newPassword = isset($input['new_password']) ? (string)$input['new_password'] : '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'A valid email is required.']);
    exit;
}
if (strlen($code) !== 6) {
    echo json_encode(['success' => false, 'message' => 'Enter a valid 6-digit OTP code.']);
    exit;
}
// Server-side password requirements: min 8 chars, upper, lower, digit
$hasMin = strlen($newPassword) >= 8;
$hasUpper = preg_match('/[A-Z]/', $newPassword);
$hasLower = preg_match('/[a-z]/', $newPassword);
$hasDigit = preg_match('/\d/', $newPassword);
if (!($hasMin && $hasUpper && $hasLower && $hasDigit)) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters and include uppercase, lowercase, and a number.']);
    exit;
}

try {
    // Resolve user
    $stmtUser = $conn->prepare('SELECT id FROM services_users WHERE email = ? LIMIT 1');
    $stmtUser->bind_param('s', $email);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    if (!$resUser || $resUser->num_rows !== 1) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }
    $userRow = $resUser->fetch_assoc();
    $userId = (int)$userRow['id'];

    // Latest reset_password OTP
    $purpose = 'reset_password';
    $stmtOtp = $conn->prepare('SELECT id, six_digit, expires_at, consumed_at, attempts, max_attempts FROM services_email_otp_codes WHERE user_id = ? AND purpose = ? AND sent_to = ? ORDER BY created_at DESC LIMIT 1');
    $stmtOtp->bind_param('iss', $userId, $purpose, $email);
    $stmtOtp->execute();
    $resOtp = $stmtOtp->get_result();
    if (!$resOtp || $resOtp->num_rows !== 1) {
        echo json_encode(['success' => false, 'message' => 'No OTP code found. Please resend a new code.']);
        exit;
    }
    $otpRow = $resOtp->fetch_assoc();
    $otpId = (int)$otpRow['id'];
    $sixDigit = (string)$otpRow['six_digit'];
    $expiresAtStr = $otpRow['expires_at'];
    $consumedAt = $otpRow['consumed_at'];
    $attempts = (int)$otpRow['attempts'];
    $maxAttempts = (int)$otpRow['max_attempts'];

    $tz = new DateTimeZone('Asia/Manila');
    $now = new DateTime('now', $tz);
    $expired = true;
    if (!empty($expiresAtStr)) {
        try {
            $expiresAt = new DateTime($expiresAtStr, $tz);
            $expired = $now > $expiresAt;
        } catch (Exception $e) {
            $expired = true;
        }
    }

    if (!empty($consumedAt)) {
        echo json_encode(['success' => false, 'message' => 'This OTP has already been used.']);
        exit;
    }
    if ($expired) {
        echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
        exit;
    }
    if ($attempts >= $maxAttempts) {
        echo json_encode(['success' => false, 'message' => 'You’ve reached the maximum verification attempts. Please resend a new code.']);
        exit;
    }

    if ($code !== $sixDigit) {
        if ($attempts < $maxAttempts) {
            $stmtInc = $conn->prepare('UPDATE services_email_otp_codes SET attempts = attempts + 1 WHERE id = ?');
            $stmtInc->bind_param('i', $otpId);
            $stmtInc->execute();
        }
        echo json_encode(['success' => false, 'message' => 'Incorrect OTP code. Please try again.']);
        exit;
    }

    // OTP is valid: mark consumed and update password
    $stmtConsume = $conn->prepare('UPDATE services_email_otp_codes SET consumed_at = NOW() WHERE id = ?');
    $stmtConsume->bind_param('i', $otpId);
    $stmtConsume->execute();

    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmtUpd = $conn->prepare('UPDATE services_users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
    $stmtUpd->bind_param('si', $hash, $userId);
    $stmtUpd->execute();

    echo json_encode(['success' => true, 'message' => 'Your password has been updated successfully.']);
    exit;
} catch (Throwable $t) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $t->getMessage()]);
    exit;
}

<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/main_connection.php';

// Read JSON input
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

$email = isset($input['email']) ? trim($input['email']) : '';
$code = isset($input['otp']) ? preg_replace('/\D/', '', $input['otp']) : '';
if ($code === '' && isset($input['code'])) {
    $code = preg_replace('/\D/', '', $input['code']);
}
$purpose = isset($input['purpose']) ? trim($input['purpose']) : 'register';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email provided.']);
    exit;
}
if (strlen($code) !== 6) {
    echo json_encode(['success' => false, 'message' => 'Enter a valid 6-digit OTP code.']);
    exit;
}

try {
    // Resolve user by email
    $stmtUser = $conn->prepare('SELECT id, email_verified FROM services_users WHERE email = ? LIMIT 1');
    $stmtUser->bind_param('s', $email);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    if (!$resUser || $resUser->num_rows !== 1) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }
    $userRow = $resUser->fetch_assoc();
    $userId = (int)$userRow['id'];

    // Latest OTP for user/purpose/email
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

    // Timezone handling
    $tz = new DateTimeZone('Asia/Manila');
    $now = new DateTime('now', $tz);
    $expired = false;
    if (!empty($expiresAtStr)) {
        try {
            $expiresAt = new DateTime($expiresAtStr, $tz);
            $expired = $now > $expiresAt;
        } catch (Exception $e) {
            // If parsing fails, treat as expired for safety
            $expired = true;
        }
    } else {
        $expired = true;
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

    // Compare code
    if ($code === $sixDigit) {
        // Mark OTP consumed and verify email
        $stmtConsume = $conn->prepare('UPDATE services_email_otp_codes SET consumed_at = NOW() WHERE id = ?');
        $stmtConsume->bind_param('i', $otpId);
        $stmtConsume->execute();

        $stmtVerify = $conn->prepare('UPDATE services_users SET email_verified = 1, email_verified_at = NOW() WHERE id = ?');
        $stmtVerify->bind_param('i', $userId);
        $stmtVerify->execute();

        echo json_encode(['success' => true, 'message' => 'Your account has been verified.']);
        exit;
    } else {
        // Increment attempts on failure (up to max)
        if ($attempts < $maxAttempts) {
            $stmtInc = $conn->prepare('UPDATE services_email_otp_codes SET attempts = attempts + 1 WHERE id = ?');
            $stmtInc->bind_param('i', $otpId);
            $stmtInc->execute();
        }
        echo json_encode(['success' => false, 'message' => 'Incorrect OTP code. Please try again.']);
        exit;
    }
} catch (Throwable $t) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $t->getMessage()]);
    exit;
}
?>
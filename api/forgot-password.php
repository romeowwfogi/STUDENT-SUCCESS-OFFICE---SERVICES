<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../connection/main_connection.php';
require_once __DIR__ . '/../functions/send_email.php';

// Only allow POST
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Read JSON body or form data
$input = null;
if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
}
if (!is_array($input)) {
    $input = $_POST;
}

$email = '';
if (isset($input['email'])) {
    $email = trim($input['email']);
} elseif (isset($input['email_address'])) {
    $email = trim($input['email_address']);
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'A valid email is required.']);
    exit;
}

try {
    // Verify email exists
    $stmtUser = $conn->prepare('SELECT id, is_active FROM services_users WHERE email = ? LIMIT 1');
    $stmtUser->bind_param('s', $email);
    $stmtUser->execute();
    $userRes = $stmtUser->get_result();
    if (!$userRes || $userRes->num_rows !== 1) {
        echo json_encode(['status' => 'error', 'message' => 'We couldn\'t find an account with that email.']);
        exit;
    }
    $userRow = $userRes->fetch_assoc();
    $userId = (int)$userRow['id'];

    // Generate 6-digit OTP
    try {
        $otpCode = (string)random_int(100000, 999999);
    } catch (Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to generate verification code. Please try again.']);
        exit;
    }

    // Derive expires_at from expiration_config for password_reset
    $stmtTTL = $conn->prepare('SELECT interval_value, interval_unit FROM expiration_config WHERE type = ? LIMIT 1');
    $expType = 'password_reset';
    $stmtTTL->bind_param('s', $expType);
    $stmtTTL->execute();
    $ttlRes = $stmtTTL->get_result();

    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    if ($ttlRes && ($row = $ttlRes->fetch_assoc())) {
        $value = (int)$row['interval_value'];
        $unit = strtoupper((string)$row['interval_unit']);
        switch ($unit) {
            case 'MINUTE':
                $now->modify("+{$value} minutes");
                break;
            case 'HOUR':
                $now->modify("+{$value} hours");
                break;
            case 'DAY':
                $now->modify("+{$value} days");
                break;
            case 'MONTH':
                $now->modify("+{$value} months");
                break;
            default:
                $now->modify('+1 day');
                break;
        }
    } else {
        // Fallback to 1 day
        $now->modify('+1 day');
    }
    $expiresAt = $now->format('Y-m-d H:i:s');

    // Upsert into services_email_otp_codes with purpose=reset_password
    $purpose = 'reset_password';
    $stmtFind = $conn->prepare('SELECT id FROM services_email_otp_codes WHERE user_id = ? AND purpose = ? AND sent_to = ? ORDER BY id DESC LIMIT 1');
    $stmtFind->bind_param('iss', $userId, $purpose, $email);
    $stmtFind->execute();
    $findRes = $stmtFind->get_result();
    if ($findRes && $findRes->num_rows > 0) {
        $row = $findRes->fetch_assoc();
        $existingId = (int)$row['id'];
        $stmtUpd = $conn->prepare('UPDATE services_email_otp_codes SET six_digit = ?, expires_at = ?, attempts = 0, consumed_at = NULL WHERE id = ?');
        $stmtUpd->bind_param('ssi', $otpCode, $expiresAt, $existingId);
        $ok = $stmtUpd->execute();
        if (!$ok) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update verification code.']);
            exit;
        }
    } else {
        $stmtIns = $conn->prepare('INSERT INTO services_email_otp_codes (user_id, six_digit, purpose, sent_to, expires_at, attempts) VALUES (?, ?, ?, ?, ?, 0)');
        $stmtIns->bind_param('issss', $userId, $otpCode, $purpose, $email, $expiresAt);
        $ok = $stmtIns->execute();
        if (!$ok) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create verification code.']);
            exit;
        }
    }

    // Send email with the verification code using template from connection
    $subject = isset($SUBJECT_RESET_PASSWORD) && $SUBJECT_RESET_PASSWORD
        ? $SUBJECT_RESET_PASSWORD
        : 'Student Support Services - Reset Password';

    if (isset($HTML_CODE_RESET_PASSWORD) && $HTML_CODE_RESET_PASSWORD) {
        // Replace placeholders in HTML template with formatted date/time
        $expiresAtDisplay = (new DateTime($expiresAt, new DateTimeZone('Asia/Manila')))->format('F j, Y - h:i A');
        $body = str_replace(
            ['{{otp_code}}', '{{expire_at}}'],
            [$otpCode, $expiresAtDisplay],
            $HTML_CODE_RESET_PASSWORD
        );
    } else {
        // Fallback body if template is not available
        $expiresAtDisplay = (new DateTime($expiresAt, new DateTimeZone('Asia/Manila')))->format('F j, Y - h:i A');
        $body = '<p>Hello,</p>' .
            '<p>Your password reset verification code is <strong>' . htmlspecialchars($otpCode) . '</strong>.</p>' .
            '<p>This code expires at <strong>' . htmlspecialchars($expiresAtDisplay) . '</strong>.</p>' .
            '<p>If you did not request a password reset, you can ignore this email.</p>' .
            '<p>— Pamantasan ng Lungsod ng Pasig - Student Success Office</p>';
    }
    try {
        sendEmail($email, $subject, $body);
    } catch (Throwable $e) {
        // Do not fail the request if email sending throws; code is created
    }

    echo json_encode(['status' => 'success', 'message' => 'We\'ve sent a 6-digit password reset code to your email.']);
    exit;
} catch (Throwable $t) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $t->getMessage()]);
    exit;
}

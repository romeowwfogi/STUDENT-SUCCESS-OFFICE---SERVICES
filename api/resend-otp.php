<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../connection/main_connection.php';
require_once __DIR__ . '/../functions/send_email.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Parse input JSON or form
$input = null;
if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
}
if (!is_array($input)) {
    $input = $_POST;
}

$email = isset($input['email']) ? trim($input['email']) : '';
$purpose = isset($input['purpose']) ? strtolower(trim($input['purpose'])) : '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'A valid email is required.']);
    exit;
}

$allowedPurposes = ['register', 'login'];
if (!in_array($purpose, $allowedPurposes, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid purpose.']);
    exit;
}

// Look up user by email
$stmtUser = $conn->prepare('SELECT id, email_verified, is_active FROM services_users WHERE email = ? LIMIT 1');
$stmtUser->bind_param('s', $email);
$stmtUser->execute();
$userRes = $stmtUser->get_result();
if (!$userRes || !$userRes->num_rows) {
    echo json_encode(['success' => false, 'message' => 'No account found for that email.']);
    exit;
}
$userRow = $userRes->fetch_assoc();
$userId = (int)$userRow['id'];

// Generate new code
try {
    $otpCode = (string)random_int(100000, 999999);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to generate code. Try again.']);
    exit;
}

// TTL from expiration_config
$expType = ($purpose === 'register') ? 'activation_account' : 'login_account';
$stmtTTL = $conn->prepare('SELECT interval_value, interval_unit FROM expiration_config WHERE type = ? LIMIT 1');
$stmtTTL->bind_param('s', $expType);
$stmtTTL->execute();
$ttlRes = $stmtTTL->get_result();
$expiresAt = null;
if ($ttlRes && $row = $ttlRes->fetch_assoc()) {
    $value = (int)$row['interval_value'];
    $unit = strtoupper($row['interval_unit']);
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
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
    $expiresAt = $now->format('Y-m-d H:i:s');
} else {
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $now->modify('+1 day');
    $expiresAt = $now->format('Y-m-d H:i:s');
}

// Update existing OTP row for this user/email/purpose; create if none exists
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
        echo json_encode(['success' => false, 'message' => 'Failed to update the code.']);
        exit;
    }
} else {
    $stmtIns = $conn->prepare('INSERT INTO services_email_otp_codes (user_id, six_digit, purpose, sent_to, expires_at, attempts) VALUES (?, ?, ?, ?, ?, 0)');
    $stmtIns->bind_param('issss', $userId, $otpCode, $purpose, $email, $expiresAt);
    $ok = $stmtIns->execute();
    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'Failed to create a new code.']);
        exit;
    }
}

// Send email (use template for register purpose if available)
$expireAtFormatted = isset($expiresAt)
    ? (new DateTime($expiresAt, new DateTimeZone('Asia/Manila')))->format('F j, Y - h:i A')
    : '';

if ($purpose === 'register' && !empty($HTML_CODE_REGISTER_ACCOUNT)) {
    $subject = !empty($SUBJECT_REGISTER_ACCOUNT) ? $SUBJECT_REGISTER_ACCOUNT : 'Your PLP SSO verification code';
    $body = str_replace(
        ['{{otp_code}}', '{{expire_at}}'],
        [htmlspecialchars($otpCode), htmlspecialchars($expireAtFormatted)],
        $HTML_CODE_REGISTER_ACCOUNT
    );
} else {
    // Fallback simple email for login or if template unavailable
    $subject = ($purpose === 'register') ? 'Your PLP SSO verification code' : 'Your PLP SSO login code';
    $body = '<p>Hello,</p>' .
        '<p>Your ' . ($purpose === 'register' ? 'verification' : 'login') . ' code is <strong>' . htmlspecialchars($otpCode) . '</strong>.</p>' .
        ($expireAtFormatted ? '<p>This code expires at <strong>' . htmlspecialchars($expireAtFormatted) . ' (Asia/Manila)</strong>.</p>' : '') .
        '<p>If you did not request this, you can ignore this email.</p>' .
        '<p>— Pamantasan ng Lungsod ng Pasig - Student Success Office</p>';
}

try {
    sendEmail($email, $subject, $body);
} catch (Throwable $e) {
    // Even if email fails, we consider code created successfully
}

echo json_encode(['success' => true, 'message' => 'A new code was sent to your email.']);
exit;

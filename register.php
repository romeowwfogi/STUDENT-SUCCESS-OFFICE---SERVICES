<?php
include "connection/main_connection.php";
include "functions/generalUploads.php";
require_once "functions/send_email.php";
?>
<!DOCTYPE html>
<html lang="en">

<?php if (isset($otpPending) && $otpPending): ?>
    <script>
        // Show modal prompting for OTP and submit form on confirm
        (function() {
            const run = () => {
                const emailMasked = '<?php echo htmlspecialchars($emailValue); ?>';
                messageModalV1Show({
                    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><path d="m4 7 8 5 8-5"/><path d="M20 19a2 2 0 0 0 2-2V7"/><path d="M2 7v10a2 2 0 0 0 2 2h16"/></svg>`,
                    iconBg: '#2e7d327a',
                    actionBtnBg: '#2E7D32',
                    showCancelBtn: true,
                    title: 'Verify Your Email',
                    message: `
                    <div style="margin-top:8px;">We sent a 6-digit code to <strong>${emailMasked}</strong>. Enter it below to verify your account.</div>
                    <div style="margin-top:12px;">
                        <input type="text" id="message-modalv1-input-otp" placeholder="Enter 6-digit code" inputmode="numeric" pattern="[0-9]*" maxlength="6" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; margin-top:6px;" />
                        <div id="resendStatus" style="margin-top:8px; color:#16a34a; display:none;"></div>
                    </div>
                `,
                    cancelText: 'Resend Code',
                    actionText: 'Verify',
                    dismissOnConfirm: false,
                    onCancel: () => {
                        const statusEl = document.getElementById('resendStatus');
                        if (statusEl) {
                            statusEl.style.display = 'block';
                            statusEl.style.color = '#6b7280';
                            statusEl.textContent = 'Resending code...';
                        }
                        if (typeof showLoader === 'function') showLoader();
                        fetch('api/resend-otp.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                email: '<?php echo htmlspecialchars($emailValue); ?>',
                                purpose: 'register'
                            })
                        }).then(r => r.json()).then(data => {
                            if (statusEl) {
                                if (data && data.success) {
                                    statusEl.style.color = '#16a34a';
                                    statusEl.textContent = 'A new code was sent to your email.';
                                } else {
                                    statusEl.style.color = '#b91c1c';
                                    statusEl.textContent = (data && data.message) ? data.message : 'Unable to resend code.';
                                }
                            }
                        }).catch(() => {
                            if (statusEl) {
                                statusEl.style.color = '#b91c1c';
                                statusEl.textContent = 'Network error. Please try again.';
                            }
                        }).finally(() => {
                            if (typeof hideLoader === 'function') hideLoader();
                        });
                    },
                    onConfirm: () => {
                        const inputEl = document.getElementById('message-modalv1-input-otp');
                        const code = (inputEl?.value || '').replace(/\D/g, '').slice(0, 6);
                        if (code.length !== 6) {
                            alert('Enter a valid 6-digit OTP code.');
                            return;
                        }
                        const hiddenOtp = document.getElementById('otp_code_hidden');
                        if (hiddenOtp) hiddenOtp.value = code;
                        const stageEl = document.querySelector('input[name="stage"]');
                        if (stageEl) stageEl.value = 'verify_otp';
                        document.getElementById('signupForm').submit();
                    }
                });

                const inputEl = document.getElementById('message-modalv1-input-otp');
                if (inputEl) {
                    inputEl.addEventListener('input', () => {
                        inputEl.value = inputEl.value.replace(/\D/g, '').slice(0, 6);
                    });
                    inputEl.addEventListener('keypress', (e) => {
                        if (!/[0-9]/.test(e.key)) {
                            e.preventDefault();
                        }
                    });
                    inputEl.addEventListener('paste', (e) => {
                        e.preventDefault();
                        const pasted = (e.clipboardData || window.clipboardData).getData('text')
                            .replace(/\D/g, '')
                            .slice(0, 6);
                        inputEl.value = pasted;
                    });
                    // autofocus
                    inputEl.focus();
                }
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', run);
            } else {
                run();
            }
        })();
    </script>
<?php endif; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pages/src/css/signup.css">
</head>

<body>
    <div class="container">
        <!-- Background -->
        <div class="background"></div>

        <!-- Main content -->
        <div class="main-content">
            <!-- Welcome section -->
            <div class="welcome-section">
                <h1>Create Account</h1>
                <p>Register now to apply for admission and track your application online.</p>
            </div>

            <!-- Signup form -->
            <div class="signup-section">
                <?php
                $return_href = 'email-otp';
                $title = 'Return to Home';
                include "includes/auth_return.php";
                ?>

                <?php
                $auth_header_class = 'signup-header';
                $auth_subtitle = 'Admission | Sign up';
                include "includes/auth_header.php";
                ?>

                <form class="signup-form" id="signupForm" method="POST" action="register.php">
                    <?php
                    // Server-side registration and OTP verification
                    $otpPending = false;
                    $serverMessage = null;
                    $emailValue = '';
                    $maxAttemptsReached = false;

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $stage = $_POST['stage'] ?? 'register';
                        if ($stage === 'register') {
                            $emailValue = trim($_POST['email'] ?? '');
                            $passwordVal = $_POST['password'] ?? '';
                            $confirmVal = $_POST['confirmPassword'] ?? '';

                            if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
                                $serverMessage = 'Please provide a valid email address.';
                            } elseif (strlen($passwordVal) < 8 || strlen($passwordVal) > 16) {
                                $serverMessage = 'Password must be 8-16 characters.';
                            } elseif ($passwordVal !== $confirmVal) {
                                $serverMessage = 'Passwords do not match.';
                            } else {
                                // Check if user exists
                                $stmtCheck = $conn->prepare('SELECT id FROM services_users WHERE email = ? LIMIT 1');
                                $stmtCheck->bind_param('s', $emailValue);
                                $stmtCheck->execute();
                                $resCheck = $stmtCheck->get_result();
                                if ($resCheck && $resCheck->num_rows > 0) {
                                    $serverMessage = 'Email is already registered.';
                                } else {
                                    $hash = password_hash($passwordVal, PASSWORD_BCRYPT);
                                    $stmtIns = $conn->prepare('INSERT INTO services_users (email, password_hash, email_verified, is_active) VALUES (?, ?, 0, 1)');
                                    $stmtIns->bind_param('ss', $emailValue, $hash);
                                    $okUser = $stmtIns->execute();

                                    if ($okUser) {
                                        $userId = $conn->insert_id;
                                        $otp_code = (string)random_int(100000, 999999);

                                        // Derive expires_at from expiration_config
                                        $expType = 'activation_account';
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
                                            // Default to 1 day expiry if config not present
                                            $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
                                            $now->modify('+1 day');
                                            $expiresAt = $now->format('Y-m-d H:i:s');
                                        }

                                        $stmtOtp = $conn->prepare('INSERT INTO services_email_otp_codes (user_id, six_digit, purpose, sent_to, expires_at) VALUES (?, ?, "register", ?, ?)');
                                        $stmtOtp->bind_param('isss', $userId, $otp_code, $emailValue, $expiresAt);
                                        $okOtp = $stmtOtp->execute();

                                        if ($okOtp) {
                                            // attempt to send OTP email
                                            $subject = 'Your PLP SSO verification code';
                                            $ttlText = isset($expiresAt) ? (new DateTime($expiresAt, new DateTimeZone('Asia/Manila')))->format('M d, Y h:i A') : '';
                                            $body = '<p>Hello,</p>' .
                                                '<p>Your verification code is <strong>' . htmlspecialchars($otp_code) . '</strong>.</p>' .
                                                ($ttlText ? '<p>This code expires at <strong>' . $ttlText . ' (Asia/Manila)</strong>.</p>' : '') .
                                                '<p>If you did not request this, you can ignore this email.</p>' .
                                                '<p>— Pamantasan ng Lungsod ng Pasig - Student Success Office</p>';
                                            try {
                                                sendEmail($emailValue, $subject, $body);
                                                $serverMessage = 'We sent a 6-digit code to your email. Enter it below to verify.';
                                            } catch (Throwable $e) {
                                                // Even if email fails, allow manual entry if user obtains code otherwise.
                                                $serverMessage = 'We generated an OTP code, but the email failed to send. Please check your email settings or try again later.';
                                            }
                                            $otpPending = true;
                                        } else {
                                            $serverMessage = 'Registration failed while generating OTP. Please try again.';
                                        }
                                    } else {
                                        $serverMessage = 'Registration failed. Please try again.';
                                    }
                                }
                            }
                        } elseif ($stage === 'verify_otp') {
                            $emailValue = trim($_POST['email'] ?? '');
                            $otpInput = preg_replace('/\D/', '', $_POST['otp_code'] ?? '');
                            if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
                                $serverMessage = 'Invalid email provided.';
                                $otpPending = true;
                            } elseif (strlen($otpInput) !== 6) {
                                $serverMessage = 'Enter a valid 6-digit OTP code.';
                                $otpPending = true;
                            } else {
                                // Resolve user_id by email
                                $stmtUser = $conn->prepare('SELECT id FROM services_users WHERE email = ? LIMIT 1');
                                $stmtUser->bind_param('s', $emailValue);
                                $stmtUser->execute();
                                $userRes = $stmtUser->get_result();
                                if (!$userRes || $userRes->num_rows !== 1) {
                                    $serverMessage = 'User not found.';
                                    $otpPending = true;
                                } else {
                                    $userRow = $userRes->fetch_assoc();
                                    $userId = (int)$userRow['id'];

                                    // Get latest OTP for register purpose and current email address
                                    $stmtGet = $conn->prepare('SELECT id, attempts, max_attempts, expires_at, consumed_at FROM services_email_otp_codes WHERE user_id = ? AND six_digit = ? AND purpose = "register" AND sent_to = ? ORDER BY created_at DESC LIMIT 1');
                                    $stmtGet->bind_param('iss', $userId, $otpInput, $emailValue);
                                    $stmtGet->execute();
                                    $resGet = $stmtGet->get_result();
                                    if ($resGet && $resGet->num_rows === 1) {
                                        $otpRow = $resGet->fetch_assoc();
                                        $otpId = (int)$otpRow['id'];
                                        $attempts = (int)$otpRow['attempts'];
                                        $maxAttempts = (int)$otpRow['max_attempts'];
                                        $expiresAtRow = $otpRow['expires_at'];
                                        $consumedAt = $otpRow['consumed_at'];

                                        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
                                        $expired = $expiresAtRow && ($now > new DateTime($expiresAtRow, new DateTimeZone('Asia/Manila')));
                                        $alreadyUsed = !empty($consumedAt);

                                        if ($alreadyUsed) {
                                            $serverMessage = 'This OTP has already been used.';
                                            $otpPending = true;
                                        } elseif ($expired) {
                                            $serverMessage = 'OTP has expired. Please request a new one.';
                                            $otpPending = true;
                                        } elseif ($attempts >= $maxAttempts) {
                                            $serverMessage = 'You’ve reached the maximum verification attempts. Please resend a new code to continue verifying your account.';
                                            $otpPending = true;
                                            $maxAttemptsReached = true;
                                        } else {
                                            // Success: mark OTP consumed and verify email
                                            $stmtConsume = $conn->prepare('UPDATE services_email_otp_codes SET consumed_at = NOW() WHERE id = ?');
                                            $stmtConsume->bind_param('i', $otpId);
                                            $stmtConsume->execute();

                                            $stmtVerify = $conn->prepare('UPDATE services_users SET email_verified = 1, email_verified_at = NOW() WHERE id = ?');
                                            $stmtVerify->bind_param('i', $userId);
                                            $stmtVerify->execute();

                                            $serverMessage = 'Your account has been verified. You can now login.';
                                            $otpPending = false;
                                        }
                                    } else {
                                        // Failed attempt: increment attempts where latest register OTP exists for user and current email
                                        $stmtLatest = $conn->prepare('SELECT id, attempts, max_attempts FROM services_email_otp_codes WHERE user_id = ? AND purpose = "register" AND sent_to = ? ORDER BY created_at DESC LIMIT 1');
                                        $stmtLatest->bind_param('is', $userId, $emailValue);
                                        $stmtLatest->execute();
                                        $latestRes = $stmtLatest->get_result();
                                        if ($latestRes && $latestRes->num_rows === 1) {
                                            $latestRow = $latestRes->fetch_assoc();
                                            $latestId = (int)$latestRow['id'];
                                            $latestAttempts = (int)$latestRow['attempts'];
                                            $latestMax = (int)$latestRow['max_attempts'];
                                            if ($latestAttempts < $latestMax) {
                                                $stmtInc = $conn->prepare('UPDATE services_email_otp_codes SET attempts = attempts + 1 WHERE id = ?');
                                                $stmtInc->bind_param('i', $latestId);
                                                $stmtInc->execute();
                                            }
                                        }
                                        $serverMessage = 'Incorrect OTP code. Please try again.';
                                        $otpPending = true;
                                    }
                                }
                            }
                        }
                    }
                    ?>
                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" placeholder="Email Address" <?php echo $otpPending ? '' : 'required'; ?> value="<?php echo htmlspecialchars($emailValue); ?>">
                            <img src="pages/src/media/mail.png" alt="Email" class="input-icon">
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" placeholder="Password" <?php echo $otpPending ? '' : 'required'; ?> value="<?php echo isset($passwordVal) ? htmlspecialchars($passwordVal) : ''; ?>">
                            <img src="pages/src/media/key-round.png" alt="Password" class="input-icon" id="passwordIcon" onclick="showHidePassword('password', 'passwordIcon')">
                        </div>
                        <div class="password-requirements">
                            <div class="requirement" id="req-length">
                                <span class="check-icon">✓</span>
                                <span class="text">8-16 Characters</span>
                            </div>
                            <div class="requirement" id="req-uppercase">
                                <span class="check-icon">✓</span>
                                <span class="text">At least one uppercase letter</span>
                            </div>
                            <div class="requirement" id="req-number">
                                <span class="check-icon">✓</span>
                                <span class="text">At least one number</span>
                            </div>
                            <div class="requirement" id="req-special">
                                <span class="check-icon">✓</span>
                                <span class="text">At least one special character</span>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" <?php echo $otpPending ? '' : 'required'; ?> value="<?php echo isset($confirmVal) ? htmlspecialchars($confirmVal) : ''; ?>">
                            <img src="pages/src/media/key-round.png" alt="Confirm Password" class="input-icon" id="confirmPasswordIcon" onclick="showHidePassword('confirmPassword', 'confirmPasswordIcon')">
                        </div>
                        <div class="password-match" id="password-match">
                            <span class="error-text">Password don't match</span>
                        </div>
                    </div>

                    <!-- Use modal for OTP; keep hidden field to carry code on submit -->
                    <?php if ($otpPending): ?>
                        <input type="hidden" name="otp_code" id="otp_code_hidden" />
                    <?php endif; ?>

                    <?php if ($otpPending): ?>
                        <input type="hidden" name="stage" value="verify_otp">
                    <?php else: ?>
                        <input type="hidden" name="stage" value="register">
                    <?php endif; ?>

                    <button type="submit" class="create-button" id="createButton"><?php echo $otpPending ? 'VERIFY OTP' : 'CREATE ACCOUNT'; ?></button>

                    <p class="signin-text">
                        Already have an account? <a href="login" class="signin-link">Sign in</a>
                    </p>
                    <?php if (isset($serverMessage) && !$otpPending && (!isset($stage) || $stage !== 'verify_otp')): ?>
                        <script>
                            (function() {
                                const run = () => {
                                    if (typeof messageModalV1Show !== 'function') return;
                                    const msg = <?php echo json_encode($serverMessage); ?>;
                                    const lower = (msg || '').toLowerCase();
                                    let type = 'error';
                                    if (/success|verified|account created|created successfully|otp sent|code sent/i.test(msg)) {
                                        type = 'success';
                                    } else if (/failed|fail|already|invalid|mismatch|do not match|error/i.test(lower)) {
                                        type = 'failed';
                                    }

                                    const icons = {
                                        success: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>`,
                                        failed: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>`,
                                        error: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-octagon"><path d="M7.86 2h8.28a2 2 0 0 1 1.41.59l4.86 4.86a2 2 0 0 1 .59 1.41v8.28a2 2 0 0 1-.59 1.41l-4.86 4.86a2 2 0 0 1-1.41.59H7.86a2 2 0 0 1-1.41-.59L1.59 18.55A2 2 0 0 1 1 17.14V8.86a2 2 0 0 1 .59-1.41l4.86-4.86A2 2 0 0 1 7.86 2Z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>`
                                    };
                                    const colors = {
                                        success: { title: 'Success', iconBg: '#dcfce7', btnBg: '#16a34a' },
                                        failed: { title: 'Failed', iconBg: '#fee2e2', btnBg: '#b91c1c' },
                                        error: { title: 'Error', iconBg: '#fef3c7', btnBg: '#f59e0b' }
                                    };

                                    const cfg = colors[type];
                                    messageModalV1Show({
                                        icon: icons[type],
                                        iconBg: cfg.iconBg,
                                        actionBtnBg: cfg.btnBg,
                                        showCancelBtn: false,
                                        title: cfg.title,
                                        message: msg,
                                        cancelText: 'Close',
                                        actionText: 'OK',
                                        onConfirm: () => { messageModalV1Dismiss(); }
                                    });
                                };
                                if (document.readyState === 'loading') {
                                    document.addEventListener('DOMContentLoaded', run);
                                } else {
                                    run();
                                }
                            })();
                        </script>
                    <?php endif; ?>
                </form>
                <script>
                    // Show loader on form submit for account creation
                    (function() {
                        const form = document.getElementById('signupForm');
                        if (!form) return;
                        form.addEventListener('submit', function(e) {
                            const stageEl = document.querySelector('input[name="stage"]');
                            const stage = stageEl ? stageEl.value : 'register';
                            if (stage === 'register') {
                                if (typeof showLoader === 'function') showLoader();
                            }
                        });
                    })();
                </script>
            </div>
        </div>

        <!-- Message Modal -->
        <?php include "includes/modal.php"; ?>
        <!-- Global Loader Overlay -->
        <?php include "includes/loader.php"; ?>

        <!-- Terms & Conditions Modal -->
        <div class="modal-overlay" id="termsModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>TERMS & CONDITIONS</h2>
                    <p class="last-updated">Last Updated: September XX, XXXX</p>
                </div>

                <div class="modal-body">
                    <p class="intro-text">
                        By creating an account with Pamantasan ng Lungsod ng Pasig, you agree to the following terms and conditions:
                    </p>

                    <div class="terms-section">
                        <h3>1. Account Eligibility</h3>
                        <ul>
                            <li>You must provide accurate and truthful information when creating your account.</li>
                            <li>Accounts are personal and non-transferable.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>2. Use of Account</h3>
                        <ul>
                            <li>Your account is intended solely for admission, enrollment, and other school-related transactions.</li>
                            <li>Unauthorized use, sharing, or misuse of accounts is strictly prohibited.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>3. User Responsibility</h3>
                        <ul>
                            <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                            <li>Any actions taken through your account will be considered your responsibility.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>4. Institution Rights</h3>
                        <ul>
                            <li>Pamantasan ng Lungsod ng Pasig reserves the right to suspend or terminate accounts that violate policies, use false information, or engage in unauthorized activities.</li>
                            <li>Account creation does not guarantee admission or enrollment.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>5. Amendments</h3>
                        <ul>
                            <li>Pamantasan ng Lungsod ng Pasig may update these Terms & Conditions at any time. Continued use of the system constitutes agreement to the updated terms.</li>
                        </ul>
                    </div>

                    <p class="consent-text">
                        By clicking agree, you acknowledge that you have read and understood our Terms & Conditions and you consent to the processing of your personal data as described herein.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="disagree-button" onclick="closeModal()">DISAGREE</button>
                    <button type="button" class="agree-button" onclick="agreeToTerms()">AGREE</button>
                </div>
            </div>
        </div>

        <!-- Privacy Policy Modal -->
        <div class="modal-overlay" id="privacyModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>PRIVACY POLICY</h2>
                    <p class="last-updated">Last Updated: September XX, XXXX</p>
                </div>

                <div class="modal-body">
                    <p class="intro-text">
                        Pamantasan ng Lungsod ng Pasig is committed to protecting your personal data in accordance with the Data Privacy Act of 2012 (RA 10173).
                    </p>

                    <div class="terms-section">
                        <h3>1. Information Collected</h3>
                        <ul>
                            <li>Personal details (name, contact information, birthdate, etc.)</li>
                            <li>Academic records or documents you submit for admission purposes</li>
                            <li>Login credentials (username, password)</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>2. Purpose of Data Collection</h3>
                        <ul>
                            <li>Account creation and management</li>
                            <li>Admission and enrollment processing</li>
                            <li>Communication regarding school services</li>
                            <li>Compliance with CHED, DepEd, TESDA, and other government regulations</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>3. Data Sharing & Disclosure</h3>
                        <ul>
                            <li>Your data will only be shared with authorized school personnel and government agencies as required by law.</li>
                            <li>We will not sell, rent, or disclose your personal data to unauthorized third parties.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>4. Data Protection</h3>
                        <ul>
                            <li>We implement organizational, physical, and technical safeguards to protect your personal information.</li>
                            <li>Only authorized personnel have access to your records/activities.</li>
                            <li>Account creation does not guarantee admission or enrollment.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>5. User Rights</h3>
                        <p style="margin-bottom: 8px; color: rgba(255, 255, 255, 0.9);">Under the Data Privacy Act, you have the right to:</p>
                        <ul>
                            <li>Access your personal data</li>
                            <li>Request correction of inaccurate or outdated information</li>
                            <li>Withdraw consent (subject to legal and institutional obligations)</li>
                            <li>File complaints with the National Privacy Commission (NPC) if your rights are violated</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>6. Retention & Disposal</h3>
                        <ul>
                            <li>Your personal data will be retained only as long as necessary for academic and legal purposes.</li>
                            <li>Once no longer needed, data will be securely disposed of <em>as described herein</em>.</li>
                        </ul>
                    </div>

                    <p class="consent-text">
                        By creating an account, you acknowledge that you have read and understood our Privacy Policy, and you consent to the processing of your personal data as described herein.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="disagree-button" onclick="closePrivacyModal()">DISAGREE</button>
                    <button type="button" class="agree-button" onclick="agreeToPrivacy()">AGREE</button>
                </div>
            </div>
        </div>
    </div>

    <script src="pages/src/js/showHidePass.js"></script>
    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const createButton = document.getElementById('createButton');
        const passwordMatch = document.getElementById('password-match');
        const signupForm = document.getElementById('signupForm');
        const termsModal = document.getElementById('termsModal');
        const privacyModal = document.getElementById('privacyModal');
        const emailInput = document.getElementById('email');
        const signinLink = document.querySelector('.signin-link');
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');

        let agreedToTerms = false;
        let agreedToPrivacy = false;

        // === Password Validation ===
        function validatePassword() {
            const password = passwordInput.value;

            reqLength.classList.toggle('valid', password.length >= 8 && password.length <= 16);
            reqUppercase.classList.toggle('valid', /[A-Z]/.test(password));
            reqNumber.classList.toggle('valid', /[0-9]/.test(password));
            reqSpecial.classList.toggle('valid', /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password));
        }

        function validateConfirmPassword() {
            if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                passwordMatch.style.display = 'block';
            } else {
                passwordMatch.style.display = 'none';
            }
        }

        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);

        // === Utility ===
        function openModal(modal) {
            modal.style.display = 'flex';
        }

        function closeModal(modal) {
            modal.style.display = 'none';
        }

        // Allow native form submission; client-side validation remains via UI hints

        // === Close Modals on Overlay Click ===
        termsModal.addEventListener('click', (e) => {
            if (e.target === termsModal) closeModal(termsModal);
        });

        privacyModal.addEventListener('click', (e) => {
            if (e.target === privacyModal) closeModal(privacyModal);
        });

        // === Key icon changes to eye when focused or has value ===
        const passwordIcon = document.getElementById('passwordIcon');
        const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');

        function updateIconForInput(input, iconEl) {
            const focusedOrFilled = document.activeElement === input || input.value.trim() !== '';
            iconEl.src = focusedOrFilled ? 'pages/src/media/eye.svg' : 'pages/src/media/key-round.png';
        }

        function attachIconBehavior(input, iconEl) {
            const handler = () => updateIconForInput(input, iconEl);
            input.addEventListener('focus', handler);
            input.addEventListener('blur', handler);
            input.addEventListener('input', handler);
            handler(); // initialize
        }

        attachIconBehavior(passwordInput, passwordIcon);
        attachIconBehavior(confirmPasswordInput, confirmPasswordIcon);

        // === OTP single-input restrictions: digits-only, max 6 ===
        const otpInput = document.getElementById('otp_code');
        if (otpInput) {
            otpInput.addEventListener('input', () => {
                otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6);
            });
            otpInput.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
            otpInput.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text')
                    .replace(/\D/g, '')
                    .slice(0, 6);
                otpInput.value = pasted;
            });
        }
    </script>

    <?php if ($otpPending): ?>
        <script>
            (function() {
                const emailMasked = '<?php echo htmlspecialchars($emailValue); ?>';
                const show = () => {
                    if (typeof messageModalV1Show !== 'function') return;
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><path d="m4 7 8 5 8-5"/><path d="M20 19a2 2 0 0 0 2-2V7"/><path d="M2 7v10a2 2 0 0 0 2 2h16"/></svg>`,
                        iconBg: '#2e7d327a',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: true,
                        title: 'Verify Your Email',
                        message: `
                        <div style="margin-top:8px;">We sent a 6-digit code to <strong>${emailMasked}</strong>. Enter it below to verify your account.</div>
                    <div style="margin-top:12px;">
                        <input type="text" id="message-modalv1-input-otp" placeholder="Enter 6-digit code" inputmode="numeric" pattern="[0-9]*" maxlength="6" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; margin-top:6px;" />
                        <div id="resendStatus" style="margin-top:8px; color:#16a34a; display:none;"></div>
                    </div>
                `,
                        cancelText: 'Resend Code',
                        actionText: 'Verify',
                        dismissOnConfirm: false,
                        onCancel: () => {
                            const statusEl = document.getElementById('resendStatus');
                            if (statusEl) {
                                statusEl.style.display = 'block';
                                statusEl.style.color = '#6b7280';
                                statusEl.textContent = 'Resending code...';
                            }
                            fetch('api/resend-otp.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    email: '<?php echo htmlspecialchars($emailValue); ?>',
                                    purpose: 'register'
                                })
                            }).then(r => r.json()).then(data => {
                                if (statusEl) {
                                    if (data && data.success) {
                                        statusEl.style.color = '#16a34a';
                                        statusEl.textContent = 'A new code was sent to your email.';
                                    } else {
                                        statusEl.style.color = '#b91c1c';
                                        statusEl.textContent = (data && data.message) ? data.message : 'Unable to resend code.';
                                    }
                                }
                            }).catch(() => {
                                if (statusEl) {
                                    statusEl.style.color = '#b91c1c';
                                    statusEl.textContent = 'Network error. Please try again.';
                                }
                            });
                        },
                        onConfirm: () => {
                            if (typeof showLoader === 'function') showLoader();
                            const inputEl = document.getElementById('message-modalv1-input-otp');
                            const code = (inputEl?.value || '').replace(/\D/g, '').slice(0, 6);
                            if (code.length !== 6) {
                                alert('Enter a valid 6-digit OTP code.');
                                if (typeof hideLoader === 'function') hideLoader();
                                return;
                            }
                            const hiddenOtp = document.getElementById('otp_code_hidden');
                            if (hiddenOtp) hiddenOtp.value = code;
                            const stageEl = document.querySelector('input[name="stage"]');
                            if (stageEl) stageEl.value = 'verify_otp';
                            document.getElementById('signupForm').submit();
                        }
                    });

                    const inputEl = document.getElementById('message-modalv1-input-otp');
                    if (inputEl) {
                        inputEl.addEventListener('input', () => {
                            inputEl.value = inputEl.value.replace(/\D/g, '').slice(0, 6);
                        });
                        inputEl.addEventListener('keypress', (e) => {
                            if (!/[0-9]/.test(e.key)) {
                                e.preventDefault();
                            }
                        });
                        inputEl.addEventListener('paste', (e) => {
                            e.preventDefault();
                            const pasted = (e.clipboardData || window.clipboardData).getData('text')
                                .replace(/\D/g, '')
                                .slice(0, 6);
                            inputEl.value = pasted;
                        });
                        inputEl.focus();
                    }

                    // Fallback: also open modal when clicking Verify if no code yet
                    const btn = document.getElementById('createButton');
                    const stageEl = document.querySelector('input[name="stage"]');
                    if (btn && stageEl && stageEl.value === 'verify_otp') {
                        btn.addEventListener('click', (e) => {
                            const hiddenOtp = document.getElementById('otp_code_hidden');
                            if (!hiddenOtp || !hiddenOtp.value) {
                                e.preventDefault();
                                show();
                            } else {
                                if (typeof showLoader === 'function') showLoader();
                            }
                        });
                    }
                };
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', show);
                } else {
                    show();
                }
            })();
        </script>
    <?php endif; ?>

    <?php
    // Show a result modal after verification attempt, regardless of success or failure
    if (isset($serverMessage) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($stage) && $stage === 'verify_otp'):
    ?>
        <script>
            (function() {
                const isSuccess = <?php echo json_encode(!$otpPending); ?>;
                const maxed = <?php echo json_encode(isset($maxAttemptsReached) && $maxAttemptsReached); ?>;
                const msg = <?php echo json_encode($serverMessage); ?>;
                const showResult = () => {
                    const iconSuccess = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>`;
                    const iconError = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>`;
                    const title = isSuccess ? 'Account Verified' : (maxed ? 'Verification Limit Reached' : 'Verification Failed');
                    const iconBg = isSuccess ? '#dcfce7' : '#fee2e2';
                    const btnBg = isSuccess ? '#16a34a' : '#b91c1c';

                    messageModalV1Show({
                        icon: isSuccess ? iconSuccess : iconError,
                        iconBg,
                        actionBtnBg: btnBg,
                        showCancelBtn: false,
                        title,
                        message: msg,
                        cancelText: 'Close',
                        actionText: isSuccess ? 'Go to Login' : (maxed ? 'OK' : 'Try Again'),
                        // Allow dismiss here; OTP modal persistence handled separately
                        dismissOnConfirm: true,
                        onConfirm: () => {
                            if (isSuccess) {
                                if (typeof showLoader === 'function') showLoader();
                                window.location.href = 'login.php';
                            } else if (!maxed) {
                                // Re-open OTP entry modal for another attempt
                                const emailMasked = '<?php echo htmlspecialchars($emailValue); ?>';
                                messageModalV1Show({
                                    icon: `<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-mail\"><path d=\"m4 7 8 5 8-5\"/><path d=\"M20 19a2 2 0 0 0 2-2V7\"/><path d=\"M2 7v10a2 2 0 0 0 2 2h16\"/></svg>`,
                                    iconBg: '#2e7d327a',
                                    actionBtnBg: '#2E7D32',
                                    showCancelBtn: true,
                                    title: 'Verify Your Email',
                                    message: `
                                    <div style=\"margin-top:8px;\">We sent a 6-digit code to <strong>${emailMasked}</strong>. Enter it below to verify your account.</div>
                                    <div style=\"margin-top:12px;\"> 
                                        <input type=\"text\" id=\"message-modalv1-input-otp\" placeholder=\"Enter 6-digit code\" inputmode=\"numeric\" pattern=\"[0-9]*\" maxlength=\"6\" style=\"width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; margin-top:6px;\" />
                                        <div id=\"resendStatus\" style=\"margin-top:8px; color:#16a34a; display:none;\"></div>
                                    </div>
                                `,
                                    cancelText: 'Resend Code',
                                    actionText: 'Verify',
                                    dismissOnConfirm: false,
                                    onCancel: () => {
                                        const statusEl = document.getElementById('resendStatus');
                                        if (statusEl) {
                                            statusEl.style.display = 'block';
                                            statusEl.style.color = '#6b7280';
                                            statusEl.textContent = 'Resending code...';
                                        }
                                        if (typeof showLoader === 'function') showLoader();
                                        fetch('api/resend-otp.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                email: '<?php echo htmlspecialchars($emailValue); ?>',
                                                purpose: 'register'
                                            })
                                        }).then(r => r.json()).then(data => {
                                            if (statusEl) {
                                                if (data && data.success) {
                                                    statusEl.style.color = '#16a34a';
                                                    statusEl.textContent = 'A new code was sent to your email.';
                                                } else {
                                                    statusEl.style.color = '#b91c1c';
                                                    statusEl.textContent = (data && data.message) ? data.message : 'Unable to resend code.';
                                                }
                                            }
                                        }).catch(() => {
                                            if (statusEl) {
                                                statusEl.style.color = '#b91c1c';
                                                statusEl.textContent = 'Network error. Please try again.';
                                            }
                                        }).finally(() => {
                                            if (typeof hideLoader === 'function') hideLoader();
                                        });
                                    },
                                    onConfirm: () => {
                                        if (typeof showLoader === 'function') showLoader();
                                        const inputEl = document.getElementById('message-modalv1-input-otp');
                                        const code = (inputEl?.value || '').replace(/\D/g, '').slice(0, 6);
                                        if (code.length !== 6) {
                                            alert('Enter a valid 6-digit OTP code.');
                                            if (typeof hideLoader === 'function') hideLoader();
                                            return;
                                        }
                                        const hiddenOtp = document.getElementById('otp_code_hidden');
                                        if (hiddenOtp) hiddenOtp.value = code;
                                        const stageEl = document.querySelector('input[name=\"stage\"]');
                                        if (stageEl) stageEl.value = 'verify_otp';
                                        document.getElementById('signupForm').submit();
                                    }
                                });
                            }
                        }
                    });
                };
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', showResult);
                } else {
                    showResult();
                }
            })();
        </script>
    <?php endif; ?>

</body>

</html>
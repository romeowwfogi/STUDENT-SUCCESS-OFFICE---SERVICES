<?php
session_start();
include "connection/main_connection.php";
include "functions/generalUploads.php";
// Server-side login and OTP verification
$serverMessage = null;
$otpPending = false;
$emailValue = '';
$stage = isset($_POST['stage']) ? $_POST['stage'] : 'login';
$verificationAttempted = false;
$verificationSuccess = false;
$showOtpImmediately = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($stage === 'verify_otp') {
        $emailValue = trim($_POST['email'] ?? '');
        $otpInput = preg_replace('/\D/', '', $_POST['otp_code'] ?? '');

        if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
            $serverMessage = 'Invalid email provided.';
            $otpPending = true;
            $verificationAttempted = true;
            $verificationSuccess = false;
            $showOtpImmediately = false;
        } elseif (strlen($otpInput) !== 6) {
            $serverMessage = 'Enter a valid 6-digit OTP code.';
            $otpPending = true;
            $verificationAttempted = true;
            $verificationSuccess = false;
            $showOtpImmediately = false;
        } else {
            // Resolve user_id by email
            $stmtUser = $conn->prepare('SELECT id FROM services_users WHERE email = ? LIMIT 1');
            $stmtUser->bind_param('s', $emailValue);
            $stmtUser->execute();
            $userRes = $stmtUser->get_result();
            if (!$userRes || $userRes->num_rows !== 1) {
                $serverMessage = 'User not found.';
                $otpPending = true;
                $verificationAttempted = true;
                $verificationSuccess = false;
                $showOtpImmediately = false;
            } else {
                $userRow = $userRes->fetch_assoc();
                $userId = (int)$userRow['id'];
                // Find latest register OTP
                $stmtOtp = $conn->prepare('SELECT id, six_digit, expires_at, consumed_at, attempts, max_attempts FROM services_email_otp_codes WHERE user_id = ? AND purpose = "register" AND sent_to = ? ORDER BY created_at DESC LIMIT 1');
                $stmtOtp->bind_param('is', $userId, $emailValue);
                $stmtOtp->execute();
                $otpRes = $stmtOtp->get_result();
                if (!$otpRes || $otpRes->num_rows !== 1) {
                    $serverMessage = 'No OTP code found. Please resend a new code.';
                    $otpPending = true;
                    $verificationAttempted = true;
                    $verificationSuccess = false;
                    $showOtpImmediately = false;
                } else {
                    $otpRow = $otpRes->fetch_assoc();
                    $otpId = (int)$otpRow['id'];
                    $sixDigit = (string)$otpRow['six_digit'];
                    $expiresAtRow = $otpRow['expires_at'];
                    $consumedAt = $otpRow['consumed_at'];
                    $attempts = (int)$otpRow['attempts'];
                    $maxAttempts = (int)$otpRow['max_attempts'];

                    $tz = new DateTimeZone('Asia/Manila');
                    $now = new DateTime('now', $tz);
                    $expired = $expiresAtRow && ($now > new DateTime($expiresAtRow, $tz));

                    if (!empty($consumedAt)) {
                        $serverMessage = 'This OTP has already been used.';
                        $otpPending = true;
                        $verificationAttempted = true;
                        $verificationSuccess = false;
                        $showOtpImmediately = false;
                    } elseif ($expired) {
                        $serverMessage = 'OTP has expired. Please request a new one.';
                        $otpPending = true;
                        $verificationAttempted = true;
                        $verificationSuccess = false;
                        $showOtpImmediately = false;
                    } elseif ($attempts >= $maxAttempts) {
                        $serverMessage = 'You’ve reached the maximum verification attempts. Please resend a new code to continue verifying your account.';
                        $otpPending = true;
                        $verificationAttempted = true;
                        $verificationSuccess = false;
                        $showOtpImmediately = false;
                    } elseif ($otpInput === $sixDigit) {
                        // Success: mark OTP consumed and verify email
                        $stmtConsume = $conn->prepare('UPDATE services_email_otp_codes SET consumed_at = NOW() WHERE id = ?');
                        $stmtConsume->bind_param('i', $otpId);
                        $stmtConsume->execute();

                        $stmtVerify = $conn->prepare('UPDATE services_users SET email_verified = 1, email_verified_at = NOW(), last_login_at = NOW() WHERE id = ?');
                        $stmtVerify->bind_param('i', $userId);
                        $stmtVerify->execute();

                        // Flag success and show modal before redirecting
                        $serverMessage = 'Your account has been verified.';
                        $verificationAttempted = true;
                        $verificationSuccess = true;
                        $otpPending = false;
                        $showOtpImmediately = false;
                    } else {
                        // Failed attempt: increment attempts
                        $stmtInc = $conn->prepare('UPDATE services_email_otp_codes SET attempts = attempts + 1 WHERE id = ?');
                        $stmtInc->bind_param('i', $otpId);
                        $stmtInc->execute();

                        $serverMessage = 'Incorrect OTP code. Please try again.';
                        $otpPending = true;
                        $verificationAttempted = true;
                        $verificationSuccess = false;
                        $showOtpImmediately = false;
                    }
                }
            }
        }
    } else {
        // Normal login post
        $emailValue = trim($_POST['email'] ?? '');
        $passwordVal = $_POST['password'] ?? '';

        if (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
            $serverMessage = 'Invalid email address.';
        } elseif ($passwordVal === '') {
            $serverMessage = 'Password is required.';
        } else {
            $stmtUser = $conn->prepare('SELECT id, password_hash, email_verified, is_active FROM services_users WHERE email = ? LIMIT 1');
            $stmtUser->bind_param('s', $emailValue);
            $stmtUser->execute();
            $userRes = $stmtUser->get_result();
            if (!$userRes || $userRes->num_rows !== 1) {
                $serverMessage = 'Invalid credentials.';
            } else {
                $row = $userRes->fetch_assoc();
                $userId = (int)$row['id'];
                $hash = $row['password_hash'] ?? null;
                $verified = (int)$row['email_verified'] === 1;
                $active = (int)$row['is_active'] === 1;

                if (!$active) {
                    $serverMessage = 'Account is deactivated.';
                } elseif (!$hash || !password_verify($passwordVal, $hash)) {
                    $serverMessage = 'Invalid credentials.';
                } elseif ($verified) {
                    // Successful login, update last_login_at and redirect to home
                    $stmtLL = $conn->prepare('UPDATE services_users SET last_login_at = NOW() WHERE id = ?');
                    $stmtLL->bind_param('i', $userId);
                    $stmtLL->execute();
                    
                    // Set session variables
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['email'] = $emailValue;
                    
                    header('Location: home.php');
                    exit;
                } else {
                    // Not verified: show OTP modal
                    $serverMessage = 'Your account is not verified. Please enter the 6-digit code sent to your email to verify.';
                    $otpPending = true;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pages/src/css/signin.css">
</head>

<body>
    <div class="container">
        <div class="background"></div>

        <div class="main-content">
            <div class="welcome-section">
                <h1>Welcome Back!</h1>
                <p>Access your account to track and manage your request.</p>
            </div>
            <div class="login-section">
                <?php
                $return_href = 'email-otp';
                $title = 'Return to Home';
                include "includes/auth_return.php";
                ?>

                <?php
                $auth_header_class = 'login-header';
                $auth_subtitle = 'Admission | Sign in';
                include "includes/auth_header.php";
                ?>

                <div class="login-form">
                    <form id="loginForm" method="POST" action="login.php">
                        <div class="input-group">
                            <div class="input-wrapper">
                                <img src="pages/src/media/mail.png" alt="Email" class="input-icon">
                                <input type="email" placeholder="Email Address" id="email-address" name="email" required value="<?php echo htmlspecialchars($emailValue); ?>">
                            </div>
                        </div>

                        <div class="input-group">
                            <div class="input-wrapper">
                                <img src="pages/src/media/key-round.png" alt="Password" class="input-icon" id="passwordIcon" onclick="showHidePassword('password', 'passwordIcon')">
                                <input type="password" placeholder="Password" id="password" name="password" required>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Remember Me
                            </label>
                            <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                        </div>

                        <input type="hidden" name="stage" value="<?php echo $otpPending ? 'verify_otp' : 'login'; ?>">
                        <input type="hidden" name="otp_code" id="otp_code_hidden" value="">
                        <button type="submit" class="login-button" id="login-button">LOGIN ACCOUNT</button>
                    </form>

                    <a href="login/email-otp" class="otp-button-link">
                        <button type="button" class="otp-button">LOGIN VIA OTP</button>
                    </a>

                    <p class="signup-text">
                        Don't have an account? <a href="register" class="signup-link">Sign up</a>
                    </p>
                </div>
            </div>

            <!-- Message Modal -->
            <?php include "includes/modal.php"; ?>
            <?php include "includes/loader.php"; ?>

        </div>
    </div>
    <script src="pages/src/js/showHidePass.js"></script>
    <script>
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('login-button');
        const signupLink = document.querySelector('.signup-link');

        // Server-side POST: show loader on submit
        loginForm.addEventListener('submit', () => {
            if (typeof showLoader === 'function') showLoader();
        });

        // === Key icon changes to eye when focused or has value ===
        const passwordIcon = document.getElementById('passwordIcon');
        const passwordInput = document.getElementById('password');

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

        // Show OTP modal if server flagged otpPending
        <?php if ($otpPending && $showOtpImmediately): ?>
                (function() {
                    const showOtpModal = () => {
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
                            <input type="text" id="login-otp-input" placeholder="Enter 6-digit code" inputmode="numeric" pattern="[0-9]*" maxlength="6" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; margin-top:6px;" />
                            <div id="loginResendStatus" style="margin-top:8px; color:#16a34a; display:none;"></div>
                        </div>
                    `,
                            cancelText: 'Resend Code',
                            actionText: 'Verify',
                            dismissOnConfirm: false,
                            onCancel: () => {
                                const statusEl = document.getElementById('loginResendStatus');
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
                                const inputEl = document.getElementById('login-otp-input');
                                const code = (inputEl?.value || '').replace(/\D/g, '').slice(0, 6);
                                if (code.length !== 6) {
                                    alert('Enter a valid 6-digit OTP code.');
                                    return;
                                }
                                const hiddenOtp = document.getElementById('otp_code_hidden');
                                if (hiddenOtp) hiddenOtp.value = code;
                                const stageEl = document.querySelector('input[name="stage"]');
                                if (stageEl) stageEl.value = 'verify_otp';
                                if (typeof showLoader === 'function') showLoader();
                                document.getElementById('loginForm').submit();
                            }
                        });

                        const inputEl = document.getElementById('login-otp-input');
                        if (inputEl) {
                            inputEl.addEventListener('input', () => {
                                inputEl.value = inputEl.value.replace(/\D/g, '').slice(0, 6);
                            });
                            inputEl.addEventListener('keypress', (e) => {
                                if (!/[0-9]/.test(e.key)) e.preventDefault();
                            });
                            inputEl.addEventListener('paste', (e) => {
                                e.preventDefault();
                                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                                inputEl.value = pasted;
                            });
                            inputEl.focus();
                        }
                    };
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', showOtpModal);
                    } else {
                        showOtpModal();
                    }
                })();
        <?php endif; ?>

        // Show result modal after verification attempt (success or failure)
        <?php if ($verificationAttempted): ?>
                (function() {
                    const isSuccess = <?php echo $verificationSuccess ? 'true' : 'false'; ?>;
                    const msg = <?php echo json_encode($serverMessage ?? ($verificationSuccess ? 'Your account has been verified.' : 'Verification failed.')); ?>;
                    const iconSuccess = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 12 2 2 4-4"/></svg>`;
                    const iconFail = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>`;
                    const icon = isSuccess ? iconSuccess : iconFail;
                    const iconBg = isSuccess ? '#dcfce7' : '#fee2e2';
                    const btnBg = isSuccess ? '#16a34a' : '#b91c1c';
                    const title = isSuccess ? 'Account Verified' : 'Verification Failed';
                    const onConfirm = () => {
                        if (isSuccess) {
                            if (typeof showLoader === 'function') showLoader();
                            window.location.href = 'index.php';
                        } else {
                            // Reopen OTP entry modal
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
                                <input type=\"text\" id=\"login-otp-input\" placeholder=\"Enter 6-digit code\" inputmode=\"numeric\" pattern=\"[0-9]*\" maxlength=\"6\" style=\"width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; margin-top:6px;\" />
                                <div id=\"loginResendStatus\" style=\"margin-top:8px; color:#16a34a; display:none;\"></div>
                            </div>
                        `,
                                cancelText: 'Resend Code',
                                actionText: 'Verify',
                                dismissOnConfirm: false,
                                onCancel: () => {
                                    const statusEl = document.getElementById('loginResendStatus');
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
                                    const inputEl = document.getElementById('login-otp-input');
                                    const code = (inputEl?.value || '').replace(/\D/g, '').slice(0, 6);
                                    if (code.length !== 6) {
                                        alert('Enter a valid 6-digit OTP code.');
                                        return;
                                    }
                                    const hiddenOtp = document.getElementById('otp_code_hidden');
                                    if (hiddenOtp) hiddenOtp.value = code;
                                    const stageEl = document.querySelector('input[name=\"stage\"]');
                                    if (stageEl) stageEl.value = 'verify_otp';
                                    if (typeof showLoader === 'function') showLoader();
                                    document.getElementById('loginForm').submit();
                                }
                            });
                            const inputEl = document.getElementById('login-otp-input');
                            if (inputEl) {
                                inputEl.addEventListener('input', () => {
                                    inputEl.value = inputEl.value.replace(/\D/g, '').slice(0, 6);
                                });
                                inputEl.addEventListener('keypress', (e) => {
                                    if (!/[0-9]/.test(e.key)) e.preventDefault();
                                });
                                inputEl.addEventListener('paste', (e) => {
                                    e.preventDefault();
                                    const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                                    inputEl.value = pasted;
                                });
                                inputEl.focus();
                            }
                        }
                    };
                    const run = () => messageModalV1Show({
                        icon,
                        iconBg,
                        actionBtnBg: btnBg,
                        showCancelBtn: false,
                        title,
                        message: msg,
                        actionText: isSuccess ? 'Continue' : 'Try Again',
                        dismissOnConfirm: true,
                        onConfirm
                    });
                    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
                    else run();
                })();
        <?php endif; ?>

        // Show server message modal if present and not doing OTP
        <?php if ($serverMessage && !$otpPending): ?>
                (function() {
                    const msg = <?php echo json_encode($serverMessage); ?>;
                    const text = (typeof msg === 'string') ? msg : '';
                    const lower = text.toLowerCase();
                    let title = 'INFO',
                        iconBg = '#fef3c7',
                        btnBg = '#f59e0b',
                        icon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`;
                    if (lower.includes('success')) {
                        title = 'SUCCESS';
                        iconBg = '#dcfce7';
                        btnBg = '#16a34a';
                        icon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`;
                    } else if (lower.includes('error')) {
                        title = 'ERROR';
                        iconBg = '#fee2e2';
                        btnBg = '#b91c1c';
                        icon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`;
                    } else if (lower.includes('deactivat') || lower.includes('invalid') || lower.includes('failed')) {
                        title = 'FAILED';
                        iconBg = '#fee2e2';
                        btnBg = '#b91c1c';
                        icon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>`;
                    }
                    const run = () => messageModalV1Show({
                        icon,
                        iconBg,
                        actionBtnBg: btnBg,
                        showCancelBtn: false,
                        title,
                        message: text,
                        actionText: 'OK',
                        onConfirm: () => {
                            messageModalV1Dismiss();
                        }
                    });
                    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
                    else run();
                })();
        <?php endif; ?>
    </script>
</body>

</html>
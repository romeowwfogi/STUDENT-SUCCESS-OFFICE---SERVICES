<?php
include "connection/main_connection.php";
include "functions/generalUploads.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pages/src/css/signin.css">
    <title>Forgot Password</title>
</head>

<body>
    <div class="container">
        <div class="background"></div>

        <div class="main-content">
            <div class="welcome-section">
                <h1>Forgot Password</h1>
                <p>Enter your email to receive a password reset link.</p>
            </div>

            <div class="login-section">
                <?php
                $return_href = $LANDING_PAGE_URL;
                $title = 'Back to Login';
                include "includes/auth_return.php";
                ?>

                <?php
                $auth_header_class = 'login-header';
                $auth_subtitle = 'Support Services | Reset Password';
                include "includes/auth_header.php";
                ?>

                <div class="login-form">
                    <form id="forgotForm">
                        <div class="input-group">
                            <div class="input-wrapper">
                                <img src="pages/src/media/mail.png" alt="Email" class="input-icon">
                                <input type="email" placeholder="Email Address" id="email-address" required>
                            </div>
                        </div>

                        <button type="submit" class="login-button" id="forgot-button">SEND VERIFICATION CODE</button>
                    </form>

                    <p class="signup-text">
                        Remembered your password? <a href="login.php" class="signup-link">Sign in</a>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <style>
        /* Reset Password Modal */
        .reset-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1200;
            justify-content: center;
            align-items: center;
        }

        .reset-modal-overlay.active {
            display: flex;
        }

        .reset-modal {
            background: #fff;
            width: 100%;
            max-width: 420px;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .reset-modal h3 {
            margin: 0 0 8px 0;
        }

        .reset-modal p {
            margin: 0 0 14px 0;
            color: #6b7280;
            font-size: 14px;
        }

        .reset-input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
        }

        .reset-input:focus {
            outline: none;
            border-color: #2E7D32;
        }

        .reset-input-wrapper {
            position: relative;
        }

        .reset-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .reset-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }

        .btn {
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-cancel {
            background: #e5e7eb;
            color: #111827;
            flex: 1;
        }

        .btn-action {
            background: #2E7D32;
            color: #fff;
            flex: 1;
        }

        .error-text {
            color: #b91c1c;
            font-size: 12px;
            margin-top: 6px;
            min-height: 16px;
        }

        .requirements {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
        }

        .requirements .ok {
            color: #2E7D32;
        }

        .requirements .bad {
            color: #b91c1c;
        }
    </style>

    <div class="reset-modal-overlay" id="resetModal">
        <div class="reset-modal">
            <h3>Reset Password</h3>
            <p>Enter the 6-digit code sent to your email, then set your new password.</p>
            <form id="resetForm">
                <label>Verification Code</label>
                <input type="text" id="otp_code" class="reset-input" maxlength="6" placeholder="6-digit code" inputmode="numeric" pattern="[0-9]*" required />
                <div id="otp_error" class="error-text"></div>

                <label>New Password</label>
                <div class="reset-input-wrapper">
                    <input type="password" id="new_password" class="reset-input" placeholder="Enter new password" required />
                    <img src="pages/src/media/eye.svg" alt="toggle" id="newPwToggleIcon" class="reset-toggle" onclick="showHidePassword('new_password','newPwToggleIcon')" />
                </div>
                <div class="requirements" id="pw_requirements">
                    • Minimum 8 characters
                    • Include uppercase, lowercase, and a number
                </div>
                <div id="pw_error" class="error-text"></div>

                <label>Confirm New Password</label>
                <div class="reset-input-wrapper">
                    <input type="password" id="confirm_password" class="reset-input" placeholder="Re-enter new password" required />
                    <img src="pages/src/media/eye.svg" alt="toggle" id="confirmPwToggleIcon" class="reset-toggle" onclick="showHidePassword('confirm_password','confirmPwToggleIcon')" />
                </div>
                <div id="confirm_error" class="error-text"></div>

                <div class="reset-actions">
                    <button type="button" class="btn btn-cancel" id="resetCancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-action" id="resetSubmitBtn">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <?php include "includes/modal.php"; ?>
    <?php include "includes/loader.php"; ?>

    <script>
        const forgotForm = document.getElementById('forgotForm');
        const forgotButton = document.getElementById('forgot-button');
        const signupLink = document.querySelector('.signup-link');
        const resetModal = document.getElementById('resetModal');
        const resetForm = document.getElementById('resetForm');
        const resetCancelBtn = document.getElementById('resetCancelBtn');
        const otpCodeInput = document.getElementById('otp_code');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const otpError = document.getElementById('otp_error');
        const pwError = document.getElementById('pw_error');
        const confirmError = document.getElementById('confirm_error');
        const pwReq = document.getElementById('pw_requirements');

        function openResetModal() {
            resetModal.classList.add('active');
            otpCodeInput.focus();
        }

        function closeResetModal() {
            resetModal.classList.remove('active');
            otpError.textContent = '';
            pwError.textContent = '';
            confirmError.textContent = '';
            resetForm.reset();
        }
        resetCancelBtn.addEventListener('click', closeResetModal);

        function validatePasswordReq(pw) {
            const hasMin = pw.length >= 8;
            const hasUpper = /[A-Z]/.test(pw);
            const hasLower = /[a-z]/.test(pw);
            const hasDigit = /\d/.test(pw);
            const items = [{
                    ok: hasMin,
                    text: 'Minimum 8 characters'
                },
                {
                    ok: hasUpper,
                    text: 'Uppercase letter'
                },
                {
                    ok: hasLower,
                    text: 'Lowercase letter'
                },
                {
                    ok: hasDigit,
                    text: 'Number'
                }
            ];
            pwReq.innerHTML = items.map(i => `• <span class="${i.ok ? 'ok' : 'bad'}">${i.text}</span>`).join(' ');
            return hasMin && hasUpper && hasLower && hasDigit;
        }

        newPasswordInput.addEventListener('input', () => validatePasswordReq(newPasswordInput.value));

        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email_address = document.getElementById('email-address').value;
            if (email_address) {
                if (typeof showLoader === 'function') showLoader();
                forgotButton.disabled = true;
                forgotButton.style.cursor = 'not-allowed';
                forgotButton.textContent = 'PLEASE WAIT...';

                signupLink.style.pointerEvents = 'none';
                signupLink.style.cursor = 'not-allowed';
                signupLink.style.opacity = '0.6';

                try {
                    const response = await fetch('api/forgot-password.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email_address
                        })
                    });
                    const data = await response.json();
                    if (data.status === 'success' || data.success === true) {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                            iconBg: '#2e7d327a',
                            actionBtnBg: '#2E7D32',
                            showCancelBtn: false,
                            title: 'VERIFICATION CODE SENT',
                            message: data.message || 'Check your email for the password reset code.',
                            cancelText: 'Cancel',
                            actionText: 'Continue',
                            onConfirm: () => {
                                messageModalV1Dismiss();
                                openResetModal();
                            }
                        });
                    } else {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                            iconBg: '#7d2e2e7a',
                            actionBtnBg: '#c42424ff',
                            showCancelBtn: false,
                            title: 'REQUEST FAILED',
                            message: data.message || 'Unable to send verification code. Please try again.',
                            cancelText: 'Cancel',
                            actionText: 'Okay, Try Again',
                            onConfirm: () => {
                                messageModalV1Dismiss();
                            }
                        });
                    }
                } catch (error) {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                        iconBg: '#7d2e2e7a',
                        actionBtnBg: '#c42424ff',
                        showCancelBtn: false,
                        title: 'ERROR',
                        message: error,
                        cancelText: 'Cancel',
                        actionText: 'Okay, Try Again',
                        onConfirm: () => {
                            messageModalV1Dismiss();
                        }
                    });
                } finally {
                    if (typeof hideLoader === 'function') hideLoader();
                    forgotButton.disabled = false;
                    forgotButton.style.cursor = 'pointer';
                    forgotButton.textContent = 'SEND VERIFICATION CODE';

                    signupLink.style.pointerEvents = 'auto';
                    signupLink.style.cursor = 'pointer';
                    signupLink.style.opacity = '1';
                }
            } else {
                messageModalV1Show({
                    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                    iconBg: '#7d2e2e7a',
                    actionBtnBg: '#c42424ff',
                    showCancelBtn: false,
                    title: 'INVALID INPUT',
                    message: 'Please enter your email address to continue.',
                    cancelText: 'Cancel',
                    actionText: 'Okay, Try Again',
                    onConfirm: () => {
                        messageModalV1Dismiss();
                    }
                });
            }
        });

        resetForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            otpError.textContent = '';
            pwError.textContent = '';
            confirmError.textContent = '';

            const email_address = document.getElementById('email-address').value.trim();
            const otp = otpCodeInput.value.trim();
            const newPw = newPasswordInput.value;
            const confirmPw = confirmPasswordInput.value;

            // Client validations
            if (otp.length !== 6 || /\D/.test(otp)) {
                otpError.textContent = 'Enter a valid 6-digit code.';
                return;
            }
            const pwOk = validatePasswordReq(newPw);
            if (!pwOk) {
                pwError.textContent = 'Password doesn\'t meet requirements.';
                return;
            }
            if (newPw !== confirmPw) {
                confirmError.textContent = 'Passwords do not match.';
                return;
            }

            try {
                if (typeof showLoader === 'function') showLoader();
                const res = await fetch('api/reset-password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email_address,
                        code: otp,
                        new_password: newPw
                    })
                });
                const data = await res.json();
                if (data.success === true || data.status === 'success') {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                        iconBg: '#2e7d327a',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: false,
                        title: 'PASSWORD UPDATED',
                        message: data.message || 'Your password has been updated successfully.',
                        cancelText: 'Cancel',
                        actionText: 'Okay',
                        onConfirm: () => {
                            messageModalV1Dismiss();
                            closeResetModal();
                            window.location.href = 'login.php';
                        }
                    });
                } else {
                    // Show specific errors
                    const msg = (data && data.message) ? data.message : 'Unable to update password.';
                    if (/expired/i.test(msg)) {
                        otpError.textContent = 'OTP has expired. Please request a new code.';
                    } else if (/Incorrect OTP/i.test(msg) || /invalid/i.test(msg)) {
                        otpError.textContent = 'Incorrect code. Please try again.';
                    } else {
                        pwError.textContent = msg;
                    }
                }
            } catch (err) {
                pwError.textContent = 'Unexpected error. Please try again.';
            } finally {
                if (typeof hideLoader === 'function') hideLoader();
            }
        });
    </script>
    <script src="pages/src/js/showHidePass.js"></script>
</body>

</html>
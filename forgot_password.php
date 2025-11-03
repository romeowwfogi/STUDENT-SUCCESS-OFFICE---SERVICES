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
                    $return_href = 'login.php';
                    $title = 'Back to Login';
                    include "includes/auth_return.php"; 
                ?>

                <?php 
                    $auth_header_class = 'login-header';
                    $auth_subtitle = 'Admission | Reset Password';
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

                        <button type="submit" class="login-button" id="forgot-button">SEND RESET LINK</button>
                    </form>

                    <p class="signup-text">
                        Remembered your password? <a href="login.php" class="signup-link">Sign in</a>
                    </p>
                </div>
            </div>

            <?php include "includes/modal.php"; ?>
            <?php include "includes/loader.php"; ?>
        </div>
    </div>

    <script>
        const forgotForm = document.getElementById('forgotForm');
        const forgotButton = document.getElementById('forgot-button');
        const signupLink = document.querySelector('.signup-link');

        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email_address = document.getElementById('email-address').value;
            if (email_address) {
                forgotButton.disabled = true;
                forgotButton.style.cursor = 'not-allowed';
                forgotButton.textContent = 'PLEASE WAIT...';

                signupLink.style.pointerEvents = 'none';
                signupLink.style.cursor = 'not-allowed';
                signupLink.style.opacity = '0.6';

                try {
                    const response = await fetch('api/forgot-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ email_address })
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                            iconBg: '#2e7d327a',
                            actionBtnBg: '#2E7D32',
                            showCancelBtn: false,
                            title: 'RESET LINK SENT',
                            message: data.message || 'Check your email for the reset link.',
                            cancelText: 'Cancel',
                            actionText: 'Okay',
                            onConfirm: () => {
                                messageModalV1Dismiss();
                            }
                        });
                    } else {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                            iconBg: '#7d2e2e7a',
                            actionBtnBg: '#c42424ff',
                            showCancelBtn: false,
                            title: 'REQUEST FAILED',
                            message: data.message || 'Unable to send reset link. Please try again.',
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
                    forgotButton.disabled = false;
                    forgotButton.style.cursor = 'pointer';
                    forgotButton.textContent = 'SEND RESET LINK';

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
    </script>
</body>

</html>
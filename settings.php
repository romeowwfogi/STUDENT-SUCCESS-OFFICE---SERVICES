<?php
// Protect page
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/connection/main_connection.php';

// Fetch current user info
$userId = $_SESSION['user_id'];
$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$firstName = '';
$middleName = '';
$lastName = '';
$suffix = '';

try {
    $stmt = $conn->prepare('SELECT email, first_name, middle_name, last_name, suffix FROM services_users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $userEmail = $row['email'];
        $firstName = $row['first_name'] ?? '';
        $middleName = $row['middle_name'] ?? '';
        $lastName = $row['last_name'] ?? '';
        $suffix = $row['suffix'] ?? '';
    }
} catch (Throwable $t) {
    // Leave defaults
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings - Student Success Office</title>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --primary: #3b82f6;
            --sidebar-bg: #1e293b;
            --on-sidebar: #e2e8f0;
            --background: #f8fafc;
            --surface: #ffffff;
            --on-surface: #475569;
            --success: #10b981;
            --error: #ef4444;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            background: var(--background);
            color: var(--on-surface);
        }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 24px 20px;
            color: var(--on-sidebar);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 16px 0;
            margin: 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--on-sidebar);
            text-decoration: none;
            border-radius: 8px;
            opacity: 0.85;
            margin: 2px 12px;
        }

        .sidebar-nav a:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.08);
        }

        .sidebar-nav a.active {
            background: var(--primary);
            color: #fff;
            opacity: 1;
        }

        .sidebar-nav svg {
            width: 20px;
            height: 20px;
        }

        .main-content {
            margin-left: 280px;
            padding: 32px;
            width: 100%;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .dashboard-header {
            margin-bottom: 24px;
        }

        .dashboard-header h1 {
            margin: 0 0 8px 0;
            font-size: 22px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card {
            background: var(--surface);
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.06);
            padding: 20px;
        }

        .card h2 {
            margin: 0 0 12px 0;
            font-size: 18px;
        }

        .field {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .field input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .muted {
            color: #64748b;
            font-size: 13px;
        }

        .btn-row {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .btn.secondary {
            background: #334155;
        }

        .status {
            margin-top: 8px;
            font-size: 13px;
        }

        .status.success {
            color: var(--success);
        }

        .status.error {
            color: var(--error);
        }

        @media (max-width: 980px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .field {
                grid-template-columns: 1fr;
            }
        }

        .req-list {
            list-style: disc;
            padding-left: 20px;
            color: #334155;
        }

        /* Mobile menu button (hamburger) */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--surface);
            border: none;
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            box-shadow: 0 6px 12px rgba(0,0,0,0.08);
            color: var(--on-surface);
        }

        .mobile-menu-btn svg {
            width: 24px;
            height: 24px;
        }

        /* Mobile Responsive Sidebar */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                width: 100vw; /* fallback */
                width: -webkit-fill-available !important; /* iOS full-width */
                height: 100vh; /* fallback */
                height: -webkit-fill-available !important; /* iOS full-height */
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i data-lucide="menu"></i>
    </button>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="user-fullname">Student Success Office</h2>
        </div>
        <nav>
            <ul class="sidebar-nav">
                <li><a href="home.php"><i data-lucide="grid-3x3"></i> Services</a></li>
                <li><a href="requests.php"><i data-lucide="file-text"></i> My Requests</a></li>
                <li><a href="settings.php" class="active"><i data-lucide="settings"></i> Settings</a></li>
                <li><a href="login.php"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="container">
            <header class="dashboard-header">
                <h1>Account Settings</h1>
                <p class="muted">Manage your profile information and security.</p>
            </header>

            <div class="grid">
                <section class="card">
                    <h2>Profile</h2>
                    <p class="muted">Update your name and email. We’ll ask for your password to confirm changes.</p>
                    <div style="margin-top:14px;">
                        <h3 style="font-size:16px;margin:0 0 8px 0;">Change Fullname</h3>
                        <div class="field"><label>First Name</label><input id="firstName" type="text" value="<?php echo htmlspecialchars($firstName); ?>" placeholder="First name" /></div>
                        <div class="field"><label>Middle Name</label><input id="middleName" type="text" value="<?php echo htmlspecialchars($middleName); ?>" placeholder="Middle name (optional)" /></div>
                        <div class="field"><label>Last Name</label><input id="lastName" type="text" value="<?php echo htmlspecialchars($lastName); ?>" placeholder="Last name" /></div>
                        <div class="field"><label>Suffix</label><input id="suffix" type="text" value="<?php echo htmlspecialchars($suffix); ?>" placeholder="Suffix (optional)" /></div>
                        <div class="btn-row">
                            <button class="btn" onclick="beginFullnameUpdate()">Update Fullname</button>
                        </div>
                        <div id="fullnameStatus" class="status"></div>
                    </div>

                    <div style="margin-top:20px;">
                        <h3 style="font-size:16px;margin:0 0 8px 0;">Change Email Address</h3>
                        <div class="field"><label>Current Email</label><input id="currentEmail" type="email" value="<?php echo htmlspecialchars($userEmail); ?>" disabled /></div>
                        <div class="field"><label>New Email</label><input id="newEmail" type="email" placeholder="Enter new email" /></div>
                        <div class="btn-row">
                            <button class="btn" onclick="beginEmailUpdate()">Update Email</button>
                        </div>
                        <div id="emailStatus" class="status"></div>
                    </div>
                </section>

                <section class="card">
                    <h2>Security</h2>
                    <p class="muted">Set a strong password to keep your account safe.</p>
                    <div style="margin-top:14px;">
                        <h3 style="font-size:16px;margin:0 0 8px 0;">Change Password</h3>

                        <div class="field"><label>New Password</label>
                            <div style="position:relative;">
                                <input id="newPassword" type="password" placeholder="Enter new password" style="width:100%; padding:10px 38px 10px 12px; border:1px solid #cbd5e1; border-radius:8px" />
                                <button id="toggleNewPassword" type="button" aria-label="Show password" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:transparent; border:none; padding:4px; cursor:pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="field"><label>Confirm New Password</label>
                            <div style="position:relative;">
                                <input id="confirmPassword" type="password" placeholder="Confirm new password" style="width:100%; padding:10px 38px 10px 12px; border:1px solid #cbd5e1; border-radius:8px" />
                                <button id="toggleConfirmPassword" type="button" aria-label="Show password" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:transparent; border:none; padding:4px; cursor:pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div id="pwMatchStatus" class="muted" style="margin-top:6px;"></div>
                        <p class="muted">Password must meet the following requirements:</p>
                        <ul class="req-list">
                            <li id="pwReqLen">At least 8 characters</li>
                            <li id="pwReqUpperLower">Contains uppercase and lowercase letters</li>
                            <li id="pwReqDigit">Contains at least one number</li>
                            <li id="pwReqSpecial">Contains at least one special character (!@#$%^&* etc.)</li>
                        </ul>
                        <div class="btn-row">
                            <button class="btn" onclick="submitPasswordChange()">Update Password</button>
                        </div>
                        <div id="passwordStatus" class="status"></div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/modal.php'; ?>
    <?php include __DIR__ . '/includes/loader.php'; ?>
    <?php include __DIR__ . '/includes/profile_guard.php'; ?>

    <script>
        lucide.createIcons();

        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            if (
                window.innerWidth <= 768 &&
                sidebar &&
                !sidebar.contains(event.target) &&
                menuBtn &&
                !menuBtn.contains(event.target) &&
                sidebar.classList.contains('active')
            ) {
                sidebar.classList.remove('active');
            }
        });

        function showStatus(id, message, kind) {
            const el = document.getElementById(id);
            el.textContent = message;
            el.className = 'status ' + (kind || '');
        }

        function updatePasswordRequirements() {
            const npEl = document.getElementById('newPassword');
            if (!npEl) return;
            const np = npEl.value;
            const hasMinLen = np.length >= 8;
            const hasUpper = /[A-Z]/.test(np);
            const hasLower = /[a-z]/.test(np);
            const hasDigit = /\d/.test(np);
            const hasSpecial = /[^A-Za-z0-9]/.test(np);

            const setColor = (id, ok) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.style.color = ok ? '#16a34a' : '#6b7280';
            };
            setColor('pwReqLen', hasMinLen);
            setColor('pwReqUpperLower', hasUpper && hasLower);
            setColor('pwReqDigit', hasDigit);
            setColor('pwReqSpecial', hasSpecial);

            const allOk = hasMinLen && hasUpper && hasLower && hasDigit && hasSpecial;
            npEl.style.borderColor = allOk ? '#16a34a' : '#cbd5e1';
        }

        function updatePasswordMatch() {
            const npEl = document.getElementById('newPassword');
            const cpEl = document.getElementById('confirmPassword');
            const statusEl = document.getElementById('pwMatchStatus');
            if (!npEl || !cpEl || !statusEl) return;
            const matches = cpEl.value.length > 0 && cpEl.value === npEl.value;
            if (cpEl.value.length === 0) {
                statusEl.textContent = '';
                statusEl.style.color = '#6b7280';
                cpEl.style.borderColor = '#cbd5e1';
                return;
            }
            statusEl.textContent = matches ? 'Passwords match' : 'Passwords do not match';
            statusEl.style.color = matches ? '#16a34a' : '#b91c1c';
            cpEl.style.borderColor = matches ? '#16a34a' : '#b91c1c';
        }

        // Attach real-time validation listeners
        setTimeout(() => {
            const npEl = document.getElementById('newPassword');
            const cpEl = document.getElementById('confirmPassword');
            const toggleNew = document.getElementById('toggleNewPassword');
            const toggleConfirm = document.getElementById('toggleConfirmPassword');
            if (npEl) {
                npEl.addEventListener('input', () => {
                    updatePasswordRequirements();
                    updatePasswordMatch();
                });
                updatePasswordRequirements();
            }
            if (cpEl) {
                cpEl.addEventListener('input', updatePasswordMatch);
            }

            const eyeSvg = () => `<svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='#64748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7'/><circle cx='12' cy='12' r='3'/></svg>`;
            const eyeOffSvg = () => `<svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='#64748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.84 20.84 0 0 1 5.06-6.88'/><path d='M1 1l22 22'/><path d='M10.58 10.58A3 3 0 0 0 13.42 13.42'/></svg>`;
            const attachToggle = (btn, input) => {
                if (!btn || !input) return;
                let visible = false;
                const render = () => {
                    btn.innerHTML = visible ? eyeOffSvg() : eyeSvg();
                    btn.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
                };
                render();
                btn.addEventListener('click', () => {
                    visible = !visible;
                    input.type = visible ? 'text' : 'password';
                    render();
                    input.focus();
                });
            };
            attachToggle(toggleNew, npEl);
            attachToggle(toggleConfirm, cpEl);
        }, 0);

        function promptPasswordConfirm(statusId, onConfirm) {
            messageModalV1Show({
                icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M16.5 10.5V6.75A2.25 2.25 0 0 0 14.25 4.5h-4.5A2.25 2.25 0 0 0 7.5 6.75v7.5A2.25 2.25 0 0 0 9.75 16.5h4.5A2.25 2.25 0 0 0 16.5 14.25V12' /></svg>`,
                iconBg: '#eef2ff',
                actionBtnBg: '#2E7D32',
                title: 'Confirm with Password',
                message: `Please enter your account password to continue.
                    <div style='margin-top:10px; position:relative;'>
                        <input id='confirmPwdInput' type='password' style='width:100%; padding:10px 38px 10px 12px; border:1px solid #cbd5e1; border-radius:8px' placeholder='Enter password'>
                        <button id='confirmPwdToggle' type='button' aria-label='Show password' style='position:absolute; right:8px; top:50%; transform:translateY(-50%); background:transparent; border:none; padding:4px; cursor:pointer;'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='#64748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7'/><circle cx='12' cy='12' r='3'/></svg>
                        </button>
                    </div>`,
                cancelText: 'Cancel',
                actionText: 'Confirm',
                dismissOnConfirm: false,
                onConfirm: () => {
                    const pwd = (document.getElementById('confirmPwdInput').value || '').trim();
                    if (!pwd) {
                        showStatus(statusId, 'Please enter your password.', 'error');
                        return;
                    }
                    onConfirm(pwd);
                }
            });
            setTimeout(() => {
                const input = document.getElementById('confirmPwdInput');
                const toggleBtn = document.getElementById('confirmPwdToggle');
                if (!input || !toggleBtn) return;
                let visible = false;
                const eyeSvg = () => `<svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='#64748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7'/><circle cx='12' cy='12' r='3'/></svg>`;
                const eyeOffSvg = () => `<svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='#64748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.84 20.84 0 0 1 5.06-6.88'/><path d='M1 1l22 22'/><path d='M10.58 10.58A3 3 0 0 0 13.42 13.42'/></svg>`;
                const render = () => {
                    toggleBtn.innerHTML = visible ? eyeOffSvg() : eyeSvg();
                    toggleBtn.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
                };
                render();
                toggleBtn.addEventListener('click', () => {
                    visible = !visible;
                    input.type = visible ? 'text' : 'password';
                    render();
                    input.focus();
                });
            }, 0);
        }

        function beginFullnameUpdate() {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            if (!firstName || !lastName) {
                showStatus('fullnameStatus', 'Please enter at least first and last name.', 'error');
                return;
            }
            promptPasswordConfirm('fullnameStatus', (pwd) => submitFullnameWithPassword(pwd));
        }

        function submitFullnameWithPassword(pwd) {
            const payload = {
                first_name: document.getElementById('firstName').value.trim(),
                middle_name: document.getElementById('middleName').value.trim(),
                last_name: document.getElementById('lastName').value.trim(),
                suffix: document.getElementById('suffix').value.trim(),
                current_password: pwd
            };
            showLoader('Saving changes...');
            fetch('api/update-user-fullname.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(data => {
                hideLoader();
                if (data.success) {
                    showStatus('fullnameStatus', 'Fullname updated successfully.', 'success');
                    messageModalV1Show({
                        icon: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgb(46, 125, 50)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-user-pen-icon lucide-user-pen'><path d='M11.5 15H7a4 4 0 0 0-4 4v2'/><path d='M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z'/><circle cx='10' cy='7' r='4'/></svg>`,
                        iconBg: '#e8f5e9',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: false,
                        title: 'Profile Saved',
                        message: 'Your name has been updated successfully.',
                        cancelText: 'Close',
                        actionText: 'OK',
                        dismissOnConfirm: true,
                        onConfirm: () => {}
                    });
                } else {
                    showStatus('fullnameStatus', data.message || 'Failed to update fullname.', 'error');
                    messageModalV1Show({
                        icon: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='#f00000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-x-icon lucide-x'><path d='M18 6 6 18'/><path d='m6 6 12 12'/></svg>`,
                        iconBg: '#ffebee',
                        actionBtnBg: '#b71c1c',
                        showCancelBtn: false,
                        title: 'Save Failed',
                        message: data.message || 'We could not save your changes.',
                        actionText: 'OK',
                        dismissOnConfirm: true,
                        onConfirm: () => {}
                    });
                }
            }).catch(() => {
                hideLoader();
                showStatus('fullnameStatus', 'Server error while updating fullname.', 'error');
            });
        }

        function beginEmailUpdate() {
            const newEmail = document.getElementById('newEmail').value.trim();
            if (!newEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
                showStatus('emailStatus', 'Enter a valid new email address.', 'error');
                return;
            }
            promptPasswordConfirm('emailStatus', (pwd) => submitEmailWithPassword(newEmail, pwd));
        }

        function submitEmailWithPassword(newEmail, pwd) {
            const payload = {
                new_email: newEmail,
                current_password: pwd
            };
            showLoader('Saving changes...');
            fetch('api/update-user-email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(data => {
                hideLoader();
                if (data.success) {
                    document.getElementById('currentEmail').value = newEmail;
                    document.getElementById('newEmail').value = '';
                    showStatus('emailStatus', 'Email updated successfully.', 'success');
                    messageModalV1Show({
                        icon: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgb(46, 125, 50)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-mail-icon lucide-mail'><path d='m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7'/><rect x='2' y='4' width='20' height='16' rx='2'/></svg>`,
                        iconBg: '#e8f5e9',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: false,
                        title: 'Profile Saved',
                        message: 'Your email has been updated successfully.',
                        cancelText: 'Close',
                        actionText: 'OK',
                        dismissOnConfirm: true,
                        onConfirm: () => {}
                    });
                } else {
                    showStatus('emailStatus', data.message || 'Failed to update email.', 'error');
                    messageModalV1Show({
                        icon: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='#f00000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-x-icon lucide-x'><path d='M18 6 6 18'/><path d='m6 6 12 12'/></svg>`,
                        iconBg: '#ffebee',
                        actionBtnBg: '#b71c1c',
                        showCancelBtn: false,
                        title: 'Save Failed',
                        message: data.message || 'We could not save your changes.',
                        actionText: 'OK',
                        dismissOnConfirm: true,
                        onConfirm: () => {}
                    });
                }
            }).catch(() => {
                hideLoader();
                showStatus('emailStatus', 'Server error while updating email.', 'error');
            });
        }

        function submitPasswordChange() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Client-side requirements
            const hasMinLen = newPassword.length >= 8;
            const hasUpper = /[A-Z]/.test(newPassword);
            const hasLower = /[a-z]/.test(newPassword);
            const hasDigit = /\d/.test(newPassword);
            const hasSpecial = /[^A-Za-z0-9]/.test(newPassword);
            if (!hasMinLen || !hasUpper || !hasLower || !hasDigit || !hasSpecial) {
                showStatus('passwordStatus', 'Password does not meet requirements.', 'error');
                return;
            }
            if (newPassword !== confirmPassword) {
                showStatus('passwordStatus', 'New passwords do not match.', 'error');
                return;
            }

            promptPasswordConfirm('passwordStatus', (pwd) => {
                showLoader('Updating password...');
                fetch('api/update-password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        current_password: pwd,
                        new_password: newPassword
                    })
                }).then(r => r.json()).then(data => {
                    hideLoader();
                    if (data.success) {
                        document.getElementById('newPassword').value = '';
                        document.getElementById('confirmPassword').value = '';
                        showStatus('passwordStatus', 'Password updated successfully.', 'success');
                        messageModalV1Show({
                            icon: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgb(46, 125, 50)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-key-round-icon lucide-key-round'><path d='M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z'/><circle cx='16.5' cy='7.5' r='.5' fill='currentColor'/></svg>`,
                            iconBg: '#e8f5e9',
                            actionBtnBg: '#2E7D32',
                            showCancelBtn: false,
                            title: 'Password Updated',
                            message: 'Your password has been updated successfully.',
                            cancelText: 'Close',
                            actionText: 'OK',
                            dismissOnConfirm: true,
                            onConfirm: () => {}
                        });
                    } else {
                        showStatus('passwordStatus', data.message || 'Failed to update password.', 'error');
                        messageModalV1Show({
                            icon: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='#f00000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-x-icon lucide-x'><path d='M18 6 6 18'/><path d='m6 6 12 12'/></svg>`,
                            iconBg: '#ffebee',
                            actionBtnBg: '#b71c1c',
                            showCancelBtn: false,
                            title: 'Update Failed',
                            message: data.message || 'We could not update your password.',
                            actionText: 'OK',
                            dismissOnConfirm: true,
                            onConfirm: () => {}
                        });
                    }
                }).catch(() => {
                    hideLoader();
                    showStatus('passwordStatus', 'Server error while updating password.', 'error');
                });
            });
        }
    </script>
</body>

</html>
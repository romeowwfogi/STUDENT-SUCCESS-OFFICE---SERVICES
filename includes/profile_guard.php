<?php
// Enforce user fullname collection across authenticated pages
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    return;
}

require_once __DIR__ . '/../connection/main_connection.php';
include_once __DIR__ . '/modal.php';
include_once __DIR__ . '/loader.php';

$needsProfile = false;
try {
    $uid = intval($_SESSION['user_id']);
    $stmt = $conn->prepare('SELECT first_name, last_name, middle_name, suffix FROM services_users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $fn = trim((string)($row['first_name'] ?? ''));
        $ln = trim((string)($row['last_name'] ?? ''));
        if ($fn === '' || $ln === '') {
            $needsProfile = true;
        }
    }
} catch (Throwable $t) {
    // Fail open: do not block if DB error
}

if ($needsProfile): ?>
    <script>
        // Force non-cancellable profile modal
        (function() {
            function showProfileModal() {
                messageModalV1Show({
                    icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M15.232 5.232a3.75 3.75 0 1 1-6.464 0M4.5 21a7.5 7.5 0 0 1 15 0' /></svg>`,
                    iconBg: '#eef2ff',
                    actionBtnBg: '#2E7D32',
                    showCancelBtn: false,
                    title: 'Complete Your Profile',
                    message: `We need your name to personalize your experience.<br><br>
                    <div style='display:grid;gap:8px'>
                        <input id='sp_first_name' type='text' placeholder='First name' style='width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px' />
                        <input id='sp_middle_name' type='text' placeholder='Middle name (optional)' style='width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px' />
                        <input id='sp_last_name' type='text' placeholder='Last name' style='width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px' />
                        <input id='sp_suffix' type='text' placeholder='Suffix (optional)' style='width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px' />
                        <div id='sp_status' style='font-size:12px;color:#ef4444'></div>
                    </div>`,
                    actionText: 'Save',
                    dismissOnConfirm: false,
                    onConfirm: () => {
                        if (typeof showLoader === 'function') showLoader();
                        const fn = (document.getElementById('sp_first_name').value || '').trim();
                        const ln = (document.getElementById('sp_last_name').value || '').trim();
                        const mn = (document.getElementById('sp_middle_name').value || '').trim();
                        const sf = (document.getElementById('sp_suffix').value || '').trim();
                        if (!fn) {
                            document.getElementById('sp_status').textContent = 'Please enter your first name.';
                            return;
                        }
                        if (!ln) {
                            document.getElementById('sp_status').textContent = 'Please enter your last name.';
                            return;
                        }
                        fetch('api/set-user-profile.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                first_name: fn,
                                middle_name: mn,
                                last_name: ln,
                                suffix: sf
                            })
                        }).then(r => r.json()).then(data => {
                            if (typeof hideLoader === 'function') hideLoader();
                            if (data && data.success) {
                                messageModalV1Dismiss();
                                // Success message modal
                                messageModalV1Show({
                                    icon: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-check'><path d='M20 6 9 17l-5-5'/></svg>`,
                                    iconBg: '#dcfce7',
                                    actionBtnBg: '#16a34a',
                                    showCancelBtn: false,
                                    title: 'Profile Saved',
                                    message: 'Your name has been saved successfully.',
                                    actionText: 'Close',
                                    dismissOnConfirm: true,
                                    onConfirm: () => {}
                                });
                            } else {
                                document.getElementById('sp_status').textContent = (data && data.message) ? data.message : 'Failed to save profile.';
                                // Error message modal
                                messageModalV1Show({
                                    icon: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='#f00000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-x-icon lucide-x'><path d='M18 6 6 18'/><path d='m6 6 12 12'/></svg>`,
                                    iconBg: '#fee2e2',
                                    actionBtnBg: '#ef4444',
                                    showCancelBtn: false,
                                    title: 'Save Failed',
                                    message: (data && data.message) ? data.message : 'Please try again.',
                                    actionText: 'Close',
                                    dismissOnConfirm: true,
                                    onConfirm: () => {}
                                });
                            }
                        }).catch(() => {
                            if (typeof hideLoader === 'function') hideLoader();
                            document.getElementById('sp_status').textContent = 'Network error while saving profile.';
                            messageModalV1Show({
                                icon: `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='#f00000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-x-icon lucide-x'><path d='M18 6 6 18'/><path d='m6 6 12 12'/></svg>`,
                                iconBg: '#fee2e2',
                                actionBtnBg: '#ef4444',
                                showCancelBtn: false,
                                title: 'Network Error',
                                message: 'Please try again.',
                                actionText: 'Close',
                                dismissOnConfirm: true,
                                onConfirm: () => {}
                            });
                        });
                    }
                });
            }
            // Defer to ensure modal DOM is present
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showProfileModal);
            } else {
                showProfileModal();
            }
        })();
    </script>
<?php endif; ?>
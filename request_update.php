<?php
session_start();

// --- AUTH GUARD ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'connection/main_connection.php';

// Validate request_id
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
if ($request_id <= 0) {
    header('Location: error.php?type=invalid_request');
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Fetch request and verify ownership
$request = null;
$service_id = null;
try {
    $stmt = $conn->prepare("SELECT sr.request_id, sr.service_id, sr.requested_at, sr.admin_remarks, sl.name AS service_name, srs.status_name, srs.color_hex
                            FROM services_requests sr
                            JOIN services_list sl ON sr.service_id = sl.service_id
                            JOIN services_request_statuses srs ON sr.status_id = srs.status_id
                            WHERE sr.request_id = ? AND sr.user_id = ?");
    $stmt->bind_param('ii', $request_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        header('Location: error.php?type=not_found');
        exit;
    }
    $request = $res->fetch_assoc();
    $service_id = intval($request['service_id']);
    $stmt->close();

    // Load fields
    $fields = [];
    $stmtF = $conn->prepare("SELECT field_id, label, field_type, is_required, display_order, visible_when_option_id FROM services_fields WHERE service_id = ? ORDER BY display_order ASC");
    $stmtF->bind_param('i', $service_id);
    $stmtF->execute();
    $resF = $stmtF->get_result();
    while ($row = $resF->fetch_assoc()) {
        $fields[] = $row;
    }
    $stmtF->close();

    // Load options
    $options = [];
    $stmtO = $conn->prepare("SELECT option_id, field_id, option_value, option_label FROM services_field_options WHERE field_id IN (SELECT field_id FROM services_fields WHERE service_id = ?)");
    $stmtO->bind_param('i', $service_id);
    $stmtO->execute();
    $resO = $stmtO->get_result();
    while ($row = $resO->fetch_assoc()) {
        $fid = intval($row['field_id']);
        if (!isset($options[$fid])) $options[$fid] = [];
        $options[$fid][] = $row;
    }
    $stmtO->close();

    // Load existing answers
    $answers = [];
    $stmtA = $conn->prepare("SELECT field_id, answer_value FROM services_answers WHERE request_id = ?");
    $stmtA->bind_param('i', $request_id);
    $stmtA->execute();
    $resA = $stmtA->get_result();
    while ($row = $resA->fetch_assoc()) {
        $answers[intval($row['field_id'])] = $row['answer_value'];
    }
    $stmtA->close();
} catch (Exception $e) {
    error_log('Error loading request update: ' . $e->getMessage());
    header('Location: error.php?type=db');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Request #<?php echo htmlspecialchars($request_id); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
       :root {
    --primary: #1E3A8A;
    --on-primary: #fff;
    --background: #f8fafc;
    --surface: #fff;
    --on-surface: #64748b;
    --on-background: #0f172a;
    --elevation-1: 0 4px 12px rgba(0, 0, 0, 0.08);
}

body {
    font-family: 'Poppins', sans-serif;
    background: var(--background);
    margin: 0;
}

/* IDINAGDAG: Para hindi mag-scroll ang page kapag bukas ang mobile menu */
body.nav-open {
    overflow: hidden;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: 280px;
    background: #145317;
    color: #fff;
    box-shadow: var(--elevation-1);
    padding: 24px;
    z-index: 1000; /* IDINAGDAG: Para siguradong nasa ibabaw */
    /* IDINAGDAG: Para sa smooth na transition */
    transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1),
                width 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
}

.sidebar-header h2 {
    margin: 0;
    font-size: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-nav {
    list-style: none;
    padding: 0;
    margin: 24px 0 0 0;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fff;
    text-decoration: none;
    padding: 10px 12px;
    border-radius: 8px;
    opacity: .9;
}

.sidebar-nav a.active {
    background: #148117;
    color: var(--on-primary);
    opacity: 1;
}

.main-content {
    margin-left: 280px;
    padding: 32px;
    min-height: 100vh;
    /* IDINAGDAG: Para sa smooth na transition */
    transition: margin-left 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
}

.container {
    margin: 0 auto;
    padding: 0 50px;
}

.card {
    background: var(--surface);
    border-radius: 16px;
    box-shadow: var(--elevation-1);
    padding: 40px;
}

.header h1 {
    margin: 0;
    font-size: 26px;
    color: var(--on-background);
}

.meta {
    display: flex;
    gap: 16px;
    align-items: center;
    color: var(--on-surface);
    font-size: 14px;
}

.form-row {
    margin-top: 14px;
}

.form-row label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.form-row input[type=text],
.form-row input[type=date],
.form-row textarea,
.form-row select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
}

.button-group {
    margin-top: 18px;
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 14px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: none;
}

.btn-primary {
    background: linear-gradient(145deg, #1d981f, #136515);
    color: var(--on-primary);
}

/* KRITIKAL NA AYOS: Ang :hover rule ay DAPAT laging pagkatapos ng base rule */
.btn-primary:hover{
    /* Ang background ay kapareho lang, kaya hindi na kailangan ulitin */
    transform: translateY(-1px); /* lifts the button slightly */
    filter: drop-shadow(-2px 9px 5px #000000);
}

.btn-secondary {
    background: #e5e7eb;
    color: #111827;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
}

.remarks {
    color: var(--on-surface);
    font-size: 14px;
    line-height: 1.5;
}

.empty {
    color: var(--on-surface);
    font-style: italic;
}

/* ================================================= */
/* IDINAGDAG: Mobile Menu Button at Overlay Styles   */
/* ================================================= */
.mobile-menu-btn {
    display: none; /* Itago by default */
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001; /* Nasa ibabaw ng lahat maliban sa sidebar */
    background: var(--surface);
    border: none;
    border-radius: 12px;
    padding: 12px;
    cursor: pointer;
    box-shadow: var(--elevation-1);
    color: var(--on-background);
}

.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    z-index: 999; /* Nasa ilalim ng sidebar, ibabaw ng content */
    display: none; /* Itago by default */
}
.sidebar-overlay.active {
    display: block; /* Ipakita kapag active */
}


/* ================================================= */
/* BAGO: Tablet Breakpoint                           */
/* (769px - 1024px)                                  */
/* ================================================= */
@media (max-width: 1024px) {
    .sidebar {
        width: 240px; /* Paliitin ang sidebar */
    }
    .main-content {
        margin-left: 240px; /* I-adjust ang content margin */
    }
}


/* ================================================= */
/* INAYOS: Mobile Breakpoint                         */
/* (<= 768px)                                        */
/* ================================================= */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%); /* Itago ang sidebar */
        width: 300px; /* Palakihin ng kaunti para sa mobile view */
    }
    
    /* IDINAGDAG: Class para ipakita ang sidebar */
    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0; /* Alisin ang margin */
        padding: 16px; /* Paliitin ang padding */
    }

    /* IDINAGDAG: Ipakita ang hamburger button */
    .mobile-menu-btn {
        display: block;
    }
    
    /* INAYOS: Bawasan ang padding sa mobile */
    .container {
        padding: 0;
    }

    .card {
        padding: 24px; /* Mas maliit na padding para sa card */
    }
}
    </style>
</head>

<body>
    <?php include 'includes/loader.php'; ?>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="user-fullname"><i data-lucide="graduation-cap"></i> Student Success Office</h2>
        </div>
        <nav>
            <ul class="sidebar-nav">
                <li><a href="home.php"><i data-lucide="grid-3x3"></i> Services</a></li>
                <li><a href="requests.php" class="active"><i data-lucide="file-text"></i> My Requests</a></li>
                <li><a href="settings.php"><i data-lucide="settings"></i> Settings</a></li>
                <li><a href="login.php"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="container">
            <a href="requests.php" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;"> <i data-lucide="arrow-left"></i> Back to Request List</a>
            <div class="card" style="margin-top: 16px;">
                <div class="header" style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <h1>Update Request #<?php echo htmlspecialchars($request['request_id']); ?></h1>
                        <div class="meta">
                            <div><strong>Service:</strong> <?php echo htmlspecialchars($request['service_name']); ?></div>
                            <div><strong>Requested:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($request['requested_at'])); ?></div>
                        </div>
                    </div>
                    <div>
                        <span class="status-badge" style="background: <?php echo htmlspecialchars($request['color_hex']); ?>;"><?php echo htmlspecialchars($request['status_name']); ?></span>
                    </div>
                </div>

                <div style="margin-top: 14px;">
                    <strong>Admin Remarks:</strong>
                    <?php if (!empty($request['admin_remarks'])): ?>
                        <div class="remarks"><?php echo nl2br(htmlspecialchars($request['admin_remarks'])); ?></div>
                    <?php else: ?>
                        <div class="remarks empty">No remarks yet</div>
                    <?php endif; ?>
                </div>

                <form id="updateForm" method="POST" action="api/update-service-request.php" enctype="multipart/form-data" style="margin-top:18px;">
                    <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request_id); ?>" />
                    <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_id); ?>" />
                    <?php foreach ($fields as $f): ?>
                        <?php
                        $fid = intval($f['field_id']);
                        $label = $f['label'];
                        $type = $f['field_type'];
                        $required = intval($f['is_required']) === 1;
                        $name = 'field-' . $fid;
                        $current = isset($answers[$fid]) ? $answers[$fid] : '';
                        $visibleOptionId = isset($f['visible_when_option_id']) ? intval($f['visible_when_option_id']) : null;
                        ?>
                        <div class="form-row" id="field-container-field-<?php echo $fid; ?>" data-visible-when-option-id="<?php echo $visibleOptionId ? htmlspecialchars($visibleOptionId) : ''; ?>" style="position: relative; display: <?php echo $visibleOptionId ? 'none' : 'block'; ?>;">
                            <label><?php echo htmlspecialchars($label); ?><?php if ($required): ?><span style="color:#ef4444; margin-left:4px;">*</span><?php endif; ?></label>
                            <?php if ($type === 'textarea'): ?>
                                <textarea name="<?php echo $name; ?>" rows="3" <?php echo $required ? 'required' : ''; ?>><?php echo htmlspecialchars($current); ?></textarea>
                            <?php elseif ($type === 'date'): ?>
                                <input type="date" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($current); ?>" <?php echo $required ? 'required' : ''; ?> />
                            <?php elseif ($type === 'select'): ?>
                                <select name="<?php echo $name; ?>" <?php echo $required ? 'required' : ''; ?>>
                                    <option value="">-- Select --</option>
                                    <?php foreach (($options[$fid] ?? []) as $opt): ?>
                                        <?php $selected = ($current === $opt['option_value']) ? 'selected' : ''; ?>
                                        <option value="<?php echo htmlspecialchars($opt['option_value']); ?>" data-option-id="<?php echo htmlspecialchars($opt['option_id']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($opt['option_label']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($type === 'radio'): ?>
                                <div>
                                    <?php foreach (($options[$fid] ?? []) as $opt): ?>
                                        <?php $checked = ($current === $opt['option_value']) ? 'checked' : ''; ?>
                                        <label style="display:inline-flex; align-items:center; gap:6px; margin-right:16px;">
                                            <input type="radio" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($opt['option_value']); ?>" data-option-id="<?php echo htmlspecialchars($opt['option_id']); ?>" <?php echo $checked; ?> <?php echo $required ? 'required' : ''; ?> />
                                            <?php echo htmlspecialchars($opt['option_label']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($type === 'checkbox'): ?>
                                <?php $currentVals = array_filter(array_map('trim', explode(',', (string)$current))); ?>
                                <div>
                                    <?php foreach (($options[$fid] ?? []) as $opt): ?>
                                        <?php $checked = in_array($opt['option_value'], $currentVals, true) ? 'checked' : ''; ?>
                                        <label style="display:inline-flex; align-items:center; gap:6px; margin-right:16px;">
                                            <input type="checkbox" name="<?php echo $name; ?>[]" value="<?php echo htmlspecialchars($opt['option_value']); ?>" data-option-id="<?php echo htmlspecialchars($opt['option_id']); ?>" <?php echo $checked; ?> />
                                            <?php echo htmlspecialchars($opt['option_label']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($type === 'file'): ?>
                                <?php if ($current): ?>
                                    <div style="margin-bottom:6px;">Current: <a href="<?php echo htmlspecialchars($current); ?>" target="_blank" rel="noopener" style="color: var(--primary); text-decoration: none;"><?php echo htmlspecialchars(basename($current)); ?></a></div>
                                <?php endif; ?>
                                <input type="file" name="<?php echo $name; ?>" accept="*/*" />
                                <small style="display:block; color:#6b7280; margin-top:6px;">Uploading a new file will replace the current file.</small>
                            <?php else: ?>
                                <input type="text" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($current); ?>" <?php echo $required ? 'required' : ''; ?> />
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="button-group">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='request_view.php?request_id=<?php echo htmlspecialchars($request_id); ?>'">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="updateBtn">Update Request</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/modal.php'; ?>
    <script>
        lucide.createIcons();

        function isControllingOptionActive(optionId) {
            if (!optionId) return true;
            const root = document.getElementById('updateForm');
            if (!root) return true;
            const el = root.querySelector(`[data-option-id="${optionId}"]`);
            if (!el) return false;
            if (el.tagName === 'OPTION') {
                return el.selected === true;
            }
            if (el.type === 'checkbox' || el.type === 'radio') {
                return el.checked === true;
            }
            return false;
        }

        function setupUpdateFieldDependencies() {
            const root = document.getElementById('updateForm');
            if (!root) return;
            const depsMap = {};
            root.querySelectorAll('.form-row[data-visible-when-option-id]').forEach(container => {
                const optId = container.getAttribute('data-visible-when-option-id');
                if (!optId) return;
                if (!depsMap[optId]) depsMap[optId] = [];
                depsMap[optId].push(container);
            });

            const updateDisplayForOption = (optionId) => {
                const active = isControllingOptionActive(optionId);
                const targets = depsMap[optionId] || [];
                targets.forEach(c => {
                    c.style.display = active ? 'block' : 'none';
                });
            };

            Object.keys(depsMap).forEach(optionId => {
                const controls = root.querySelectorAll(`[data-option-id="${optionId}"]`);
                controls.forEach(ctrl => {
                    if (ctrl.tagName === 'OPTION') {
                        const select = ctrl.parentElement;
                        if (select) {
                            select.addEventListener('change', () => updateDisplayForOption(optionId));
                        }
                    } else {
                        // For radios, wire the entire group so deselection also updates
                        if (ctrl.type === 'radio' && ctrl.name) {
                            const groupInputs = root.querySelectorAll(`input[name="${ctrl.name}"]`);
                            groupInputs.forEach(inp => inp.addEventListener('change', () => updateDisplayForOption(optionId)));
                        } else {
                            ctrl.addEventListener('change', () => updateDisplayForOption(optionId));
                        }
                    }
                });
                updateDisplayForOption(optionId);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupUpdateFieldDependencies);
        } else {
            setupUpdateFieldDependencies();
        }
        const form = document.getElementById('updateForm');
        const btn = document.getElementById('updateBtn');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (typeof showLoader === 'function') showLoader();
            btn.disabled = true;
            try {
                const formData = new FormData(form);
                const resp = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });
                const data = await resp.json().catch(() => ({
                    ok: false,
                    error: 'Invalid JSON'
                }));
                if (data.ok) {
                    if (typeof messageModalV1Show === 'function') {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                            iconBg: '#dcfce7',
                            actionBtnBg: '#16a34a',
                            showCancelBtn: false,
                            title: 'Success',
                            message: 'Request updated successfully.',
                            actionText: 'Close',
                            onConfirm: () => {}
                        });
                    }
                } else {
                    if (typeof messageModalV1Show === 'function') {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>`,
                            iconBg: '#fee2e2',
                            actionBtnBg: '#ef4444',
                            showCancelBtn: false,
                            title: 'Update failed',
                            message: data.error || 'An error occurred while updating the request.',
                            actionText: 'Close',
                            onConfirm: () => {}
                        });
                    } else {
                        alert(data.error || 'Update failed');
                    }
                }
            } catch (err) {
                if (typeof messageModalV1Show === 'function') {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wifi-off"><path d="M2 2l20 20"/><path d="M8.5 16.5a3 3 0 0 1 4.24 0"/><path d="M5 12a7 7 0 0 1 9.9 0"/><path d="M2 8a11 11 0 0 1 15.64 0"/></svg>`,
                        iconBg: '#fee2e2',
                        actionBtnBg: '#ef4444',
                        showCancelBtn: false,
                        title: 'Network error',
                        message: err.message || 'Please try again.',
                        actionText: 'Close',
                        onConfirm: () => {}
                    });
                } else {
                    alert(err.message || 'Network error');
                }
            } finally {
                btn.disabled = false;
                if (typeof hideLoader === 'function') hideLoader();
            }
        });
    </script>
</body>
<?php include __DIR__ . '/includes/profile_guard.php'; ?>

</html>
<?php include __DIR__ . '/includes/profile_guard.php'; ?>
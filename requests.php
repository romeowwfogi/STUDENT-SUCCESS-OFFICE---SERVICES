<?php
session_start();

// --- AUTHENTICATION GUARD ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once 'connection/main_connection.php';

// Get user's service requests
$requests = [];
try {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT 
            sr.request_id,
            sl.name as service_name,
            srs.status_name,
            srs.color_hex,
            sr.admin_remarks,
            sr.can_update,
            sr.requested_at
        FROM services_requests sr
        JOIN services_list sl ON sr.service_id = sl.service_id
        JOIN services_request_statuses srs ON sr.status_id = srs.status_id
        WHERE sr.user_id = ?
        ORDER BY sr.requested_at DESC
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching requests: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - Student Success Office</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Material Design Color Palette - Dark Theme */
            --primary: #3b82f6;
            --primary-variant: #1d4ed8;
            --secondary: #10b981;
            --background: #f8fafc;
            --surface: #ffffff;
            --sidebar-bg: #1e293b;
            --sidebar-surface: #334155;
            --error: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
            --on-primary: #ffffff;
            --on-secondary: #ffffff;
            --on-background: #1e293b;
            --on-surface: #475569;
            --on-sidebar: #e2e8f0;
            --on-error: #ffffff;

            /* Material Design Elevations */
            --elevation-1: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            --elevation-2: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
            --elevation-3: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
            --elevation-4: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
            --elevation-5: 0 19px 38px rgba(0, 0, 0, 0.30), 0 15px 12px rgba(0, 0, 0, 0.22);
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--on-background);
            background: var(--background);
            min-height: 100vh;
            display: flex;
            margin: 0;
        }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            box-shadow: var(--elevation-3);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .sidebar-header {
            padding: 24px 20px;
            background: var(--sidebar-bg);
            color: var(--on-sidebar);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 0.15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 16px 0;
        }

        .sidebar-nav li {
            margin-bottom: 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--on-sidebar);
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.1px;
            margin: 2px 12px;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            opacity: 0.8;
        }

        .sidebar-nav a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: opacity 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
            z-index: -1;
        }

        .sidebar-nav a:hover {
            opacity: 1;
        }

        .sidebar-nav a:hover::before {
            opacity: 1;
        }

        .sidebar-nav a.active {
            background: var(--primary);
            color: var(--on-primary);
            opacity: 1;
        }

        .sidebar-nav a.active::before {
            opacity: 0.12;
        }

        .sidebar-nav svg {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }

        .main-content {
            margin-left: 280px;
            padding: 32px;
            flex: 1;
            background: var(--background);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            margin-bottom: 32px;
            padding: 0;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            backdrop-filter: none;
            text-align: left;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            flex-wrap: wrap;
        }

        .header-text h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--on-background);
            margin: 0 0 8px 0;
            letter-spacing: -0.025em;
            text-shadow: none;
        }

        .header-text p {
            font-size: 16px;
            color: var(--on-surface);
            margin: 0;
            opacity: 0.7;
            max-width: none;
        }

        /* Table Styles */
        .requests-table-container {
            background: var(--surface);
            border-radius: 16px;
            box-shadow: var(--elevation-1);
            overflow: hidden;
            margin-top: 24px;
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
        }

        .requests-table th {
            background: var(--primary);
            color: var(--on-primary);
            padding: 16px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.1px;
            text-transform: uppercase;
        }

        .requests-table th:first-child {
            width: 80px;
            text-align: center;
        }

        .requests-table th:nth-child(2) {
            width: 40%;
        }

        .requests-table th:nth-child(3) {
            width: 150px;
            text-align: center;
        }

        .requests-table th:nth-child(4) {
            width: auto;
        }

        .requests-table th:nth-child(5) {
            width: 140px;
            text-align: center;
        }

        .requests-table td {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: top;
        }

        .requests-table tr:hover {
            background: rgba(59, 130, 246, 0.02);
        }

        .requests-table tr:last-child td {
            border-bottom: none;
        }

        .request-number {
            font-weight: 600;
            color: var(--primary);
            text-align: center;
        }

        .service-name {
            font-weight: 500;
            color: var(--on-background);
            margin-bottom: 4px;
        }

        .request-date {
            font-size: 12px;
            color: var(--on-surface);
            opacity: 0.7;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .remarks {
            color: var(--on-surface);
            font-size: 14px;
            line-height: 1.5;
        }

        .no-remarks {
            color: var(--on-surface);
            opacity: 0.5;
            font-style: italic;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--on-surface);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--on-background);
        }

        .empty-state p {
            opacity: 0.7;
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
            box-shadow: var(--elevation-2);
            color: var(--on-surface);
        }

        .mobile-menu-btn svg {
            width: 24px;
            height: 24px;
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 999; /* below sidebar (1000), above content */
            display: none;
        }
        .sidebar-overlay.active {
            display: block;
        }

        /* Prevent body scroll when nav open */
        body.nav-open {
            overflow: hidden;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
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

            /* Vertical card-style table on mobile */
            .requests-table-container {
                overflow-x: hidden;
            }

            .requests-table {
                min-width: initial;
                display: block;
                border-collapse: separate;
            }
            .requests-table thead {
                display: none;
            }
            .requests-table tbody {
                display: block;
            }
            .requests-table tr {
                display: block;
                margin: 12px 0;
                background: var(--surface);
                border-radius: 12px;
                box-shadow: var(--elevation-1);
                overflow: hidden;
            }
            .requests-table td {
                display: grid;
                grid-template-columns: 110px 1fr;
                gap: 10px;
                padding: 12px 16px;
                border: none;
                text-align: left !important;
            }
            .requests-table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--on-surface);
                opacity: 0.85;
            }
            .requests-table tr:hover {
                background: var(--surface);
            }
        }

        /* Ultra-small screens tweaks */
        @media (max-width: 480px) {
            .requests-table {
                min-width: 480px;
            }
            .requests-table th,
            .requests-table td {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/loader.php'; ?>
    <script>
        // Show loader immediately on page load and hide when DOM is ready
        (function() {
            if (typeof showLoader === 'function') showLoader();
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof hideLoader === 'function') hideLoader();
                });
            } else {
                if (typeof hideLoader === 'function') hideLoader();
            }
        })();
    </script>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i data-lucide="menu"></i>
    </button>
    <!-- Overlay shown when sidebar is open on mobile -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="user-fullname">
                <i data-lucide="graduation-cap"></i>
                Student Success Office
            </h2>
        </div>
        <nav>
            <ul class="sidebar-nav">
                <li>
                    <a href="home.php">
                        <i data-lucide="grid-3x3"></i>
                        Services
                    </a>
                </li>
                <li>
                    <a href="requests.php" class="active">
                        <i data-lucide="file-text"></i>
                        My Requests
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <i data-lucide="settings"></i>
                        Settings
                    </a>
                </li>
                <li>
                    <a href="login.php">
                        <i data-lucide="log-out"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-content">
                    <div class="header-text">
                        <h1>My Requests</h1>
                        <p>Track the status of your service requests and view admin remarks</p>
                    </div>
                </div>
            </header>

            <!-- Requests Table -->
            <div class="requests-table-container">
                <?php if (empty($requests)): ?>
                    <div class="empty-state">
                        <i data-lucide="inbox"></i>
                        <h3>No Requests Yet</h3>
                        <p>You haven't submitted any service requests. Visit the Services page to get started.</p>
                    </div>
                <?php else: ?>
                    <!-- Table Toolbar: Search -->
                    <div class="table-toolbar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 16px; border-bottom: 1px solid rgba(0,0,0,0.06);">
                        <div style="flex:1; max-width: 380px; position: relative;">
                            <input id="requestSearch" type="text" placeholder="Search requests..." aria-label="Search requests"
                                style="width:100%; padding:10px 12px 10px 36px; border:1px solid rgba(0,0,0,0.12); border-radius:8px; background: var(--surface); color: var(--on-background); box-shadow: var(--elevation-0);">
                            <span style="position:absolute; left:10px; top:50%; transform: translateY(-50%); color: var(--on-surface); opacity:.6;">
                                <i data-lucide="search" style="width:18px; height:18px;"></i>
                            </span>
                        </div>
                    </div>
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th class="sortable" data-sort-key="id"><span>#</span><span class="sort-icon" aria-hidden="true" style="display:inline-flex; width:14px; height:14px; margin-left:6px;"></span></th>
                                <th class="sortable" data-sort-key="service"><span>Service</span><span class="sort-icon" aria-hidden="true" style="display:inline-flex; width:14px; height:14px; margin-left:6px;"></span></th>
                                <th class="sortable" data-sort-key="status"><span>Status</span><span class="sort-icon" aria-hidden="true" style="display:inline-flex; width:14px; height:14px; margin-left:6px;"></span></th>
                                <th class="sortable" data-sort-key="remarks"><span>Remarks</span><span class="sort-icon" aria-hidden="true" style="display:inline-flex; width:14px; height:14px; margin-left:6px;"></span></th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td class="request-number" data-label="#">
                                        <?php echo htmlspecialchars($request['request_id']); ?>
                                    </td>
                                    <td data-label="Service">
                                        <div class="service-name"><?php echo htmlspecialchars($request['service_name']); ?></div>
                                        <div class="request-date">
                                            Requested on <?php echo date('M j, Y \a\t g:i A', strtotime($request['requested_at'])); ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;" data-label="Status">
                                        <span class="status-badge" style="background-color: <?php echo htmlspecialchars($request['color_hex']); ?>">
                                            <?php echo htmlspecialchars($request['status_name']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Remarks">
                                        <?php if (!empty($request['admin_remarks'])): ?>
                                            <div class="remarks"><?php echo nl2br(htmlspecialchars($request['admin_remarks'])); ?></div>
                                        <?php else: ?>
                                            <div class="no-remarks">No remarks yet</div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;" data-label="Action">
                                        <div style="display:inline-flex; gap:8px;">
                                            <a href="request_view.php?request_id=<?php echo htmlspecialchars($request['request_id']); ?>" style="background: var(--primary); color: var(--on-primary); border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; box-shadow: var(--elevation-1); text-decoration: none; display: inline-block;">
                                                View
                                            </a>
                                            <a href="request_update.php?request_id=<?php echo htmlspecialchars($request['request_id']); ?>" onclick="return handleUpdateClick(<?php echo intval($request['request_id']); ?>, <?php echo intval($request['can_update'] ?? 0); ?>)" style="background: var(--warning); color: #111827; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; box-shadow: var(--elevation-1); text-decoration: none; display: inline-block;">
                                                Update
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- Table Footer: Rows per page, Showing, Pagination -->
                    <div class="table-footer" style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 16px; border-top: 1px solid rgba(0,0,0,0.06);">
                        <div class="rows-per-page" style="display:flex; align-items:center; gap:8px;">
                            <label for="rowsPerPage" style="font-size:14px; color: var(--on-surface); opacity:.8;">Rows per page</label>
                            <select id="rowsPerPage" style="padding:8px 10px; border:1px solid rgba(0,0,0,0.12); border-radius:8px; background: var(--surface); color: var(--on-background);">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div id="showingText" style="font-size:14px; color: var(--on-surface); opacity:.8;">
                            Showing 0–0 of 0
                        </div>
                        <div class="pagination" style="display:flex; align-items:center; gap:8px;">
                            <button id="prevPage" type="button" style="padding:8px 12px; border:1px solid rgba(0,0,0,0.12); border-radius:8px; background: var(--surface); color: var(--on-background); cursor:pointer;">Prev</button>
                            <button id="nextPage" type="button" style="padding:8px 12px; border:1px solid rgba(0,0,0,0.12); border-radius:8px; background: var(--surface); color: var(--on-background); cursor:pointer;">Next</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/modal.php'; ?>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            if (sidebar) {
                const willActivate = !sidebar.classList.contains('active');
                sidebar.classList.toggle('active');
                document.body.classList.toggle('nav-open', willActivate);
                if (overlay) overlay.classList.toggle('active', willActivate);
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            if (sidebar && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                document.body.classList.remove('nav-open');
                if (overlay) overlay.classList.remove('active');
            }
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            const overlay = document.querySelector('.sidebar-overlay');

            if (
                window.innerWidth <= 768 &&
                sidebar &&
                !sidebar.contains(event.target) &&
                menuBtn &&
                !menuBtn.contains(event.target) &&
                sidebar.classList.contains('active')
            ) {
                sidebar.classList.remove('active');
                document.body.classList.remove('nav-open');
                if (overlay) overlay.classList.remove('active');
            }
        });

        function handleUpdateClick(requestId, canUpdate) {
            try {
                if (parseInt(canUpdate, 10) === 1) {
                    return true; // allow navigation
                }
                const iconSvg = `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M9 12h6m2 8H7a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z' /></svg>`;
                messageModalV1Show({
                    icon: iconSvg,
                    iconBg: '#fee2e2',
                    actionBtnBg: '#ef4444',
                    showCancelBtn: false,
                    title: 'Update Not Allowed',
                    message: 'This request cannot be updated at the moment. Please review admin remarks or contact support.',
                    cancelText: 'Close',
                    actionText: 'Close',
                    onConfirm: () => {},
                    dismissOnConfirm: true,
                });
                return false;
            } catch (e) {
                return false;
            }
        }

        // View Answers Modal Logic
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        async function fetchAnswers(requestId) {
            const res = await fetch(`api/get-request-answers.php?request_id=${encodeURIComponent(requestId)}`);
            if (!res.ok) throw new Error('Failed to load answers');
            return res.json();
        }

        function renderAnswersHtml(answers) {
            if (!answers || answers.length === 0) {
                return '<div class="no-remarks">No submitted answers found.</div>';
            }
            const rows = answers.map(a => {
                const label = escapeHtml(a.label || 'Field');
                let valueHtml = '';
                if (a.is_file && a.file_url) {
                    const url = escapeHtml(a.file_url);
                    const fileName = url.split('/').pop();
                    valueHtml = `<a href="${url}" target="_blank" rel="noopener" style="color: var(--primary); text-decoration: none;">${escapeHtml(fileName)}</a>`;
                } else {
                    valueHtml = escapeHtml(a.value || '');
                }
                return `<tr>
                    <td style="font-weight: 600; color: var(--on-background); padding: 8px 12px; border-bottom: 1px solid rgba(0,0,0,0.06);">${label}</td>
                    <td style="color: var(--on-surface); padding: 8px 12px; border-bottom: 1px solid rgba(0,0,0,0.06);">${valueHtml}</td>
                </tr>`;
            }).join('');

            return `<div style="max-height: 60vh; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding: 8px 12px; background: rgba(0,0,0,0.04);">Field</th>
                            <th style="text-align: left; padding: 8px 12px; background: rgba(0,0,0,0.04);">Answer</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            </div>`;
        }

        document.querySelectorAll('.view-answers-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const requestId = btn.getAttribute('data-request-id');
                try {
                    const data = await fetchAnswers(requestId);
                    const html = renderAnswersHtml(data.answers);

                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>`,
                        iconBg: '#eef2ff',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: false,
                        title: `Submitted Answers (Request #${requestId})`,
                        message: html,
                        cancelText: 'Close',
                        actionText: 'Close',
                        onConfirm: () => {}
                    });
                } catch (err) {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
                        iconBg: '#fee2e2',
                        actionBtnBg: '#DC3545',
                        showCancelBtn: false,
                        title: 'Error',
                        message: '<div class="no-remarks">Unable to load answers. Please try again.</div>',
                        actionText: 'Close',
                        onConfirm: () => {}
                    });
                }
            });
        });

        // Search and Pagination Logic
        (function() {
            const table = document.querySelector('.requests-table');
            if (!table) return;
            const tbody = table.querySelector('tbody');
            const initialRows = Array.from(tbody.querySelectorAll('tr'));

            // Build row data for robust sorting/filtering
            const allData = initialRows.map((tr, idx) => {
                const tds = tr.querySelectorAll('td');
                const idText = (tds[0] ? tds[0].textContent.trim() : '').replace(/[^0-9]/g, '');
                const id = parseInt(idText, 10) || 0;
                const serviceName = (tr.querySelector('.service-name')?.textContent || '').trim().toLowerCase();
                const statusText = (tr.querySelector('.status-badge')?.textContent || '').trim().toLowerCase();
                const remarksText = (tr.querySelector('.remarks')?.textContent || tr.querySelector('.no-remarks')?.textContent || '').trim().toLowerCase();
                const fullText = (tr.textContent || '').toLowerCase();
                return {
                    tr,
                    idx,
                    id,
                    service: serviceName,
                    status: statusText,
                    remarks: remarksText,
                    fullText
                };
            });

            const searchInput = document.getElementById('requestSearch');
            const rowsSelect = document.getElementById('rowsPerPage');
            const prevBtn = document.getElementById('prevPage');
            const nextBtn = document.getElementById('nextPage');
            const showingText = document.getElementById('showingText');
            const sortableHeaders = Array.from(table.querySelectorAll('thead th.sortable'));

            let currentPage = 1;
            let rowsPerPage = parseInt(rowsSelect ? rowsSelect.value : '10', 10) || 10;
            let filtered = allData.slice();
            let sortKey = null; // 'id' | 'service' | 'status' | 'remarks'
            let sortDir = 'asc'; // 'asc' | 'desc'

            function textOfRow(tr) {
                return (tr.textContent || '').toLowerCase();
            }

            function applySearch() {
                const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
                if (!q) {
                    filtered = allData.slice();
                } else {
                    filtered = allData.filter(r => r.fullText.includes(q));
                }
                currentPage = 1;
                render();
            }

            function compareValues(a, b) {
                if (typeof a === 'number' && typeof b === 'number') {
                    return a - b;
                }
                const sa = (a ?? '').toString();
                const sb = (b ?? '').toString();
                return sa.localeCompare(sb);
            }

            function getSortIcon(dir) {
                if (dir === 'asc') {
                    return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5l-6 6h12l-6-6z" fill="currentColor"/></svg>`; // up triangle
                } else if (dir === 'desc') {
                    return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 19l6-6H6l6 6z" fill="currentColor"/></svg>`; // down triangle
                }
                return '';
            }

            function updateHeaderIcons() {
                sortableHeaders.forEach(th => {
                    const key = th.getAttribute('data-sort-key');
                    const iconSpan = th.querySelector('.sort-icon');
                    if (!iconSpan) return;
                    if (key === sortKey) {
                        iconSpan.innerHTML = getSortIcon(sortDir);
                        th.style.cursor = 'pointer';
                        th.style.opacity = '1';
                    } else {
                        iconSpan.innerHTML = '';
                        th.style.cursor = 'pointer';
                        th.style.opacity = '0.95';
                    }
                });
            }

            function render() {
                let working = filtered.slice();
                if (sortKey) {
                    const dirMul = sortDir === 'desc' ? -1 : 1;
                    working.sort((a, b) => {
                        const res = compareValues(a[sortKey], b[sortKey]);
                        if (res !== 0) return res * dirMul;
                        // Tie-breaker: sort by id to make changes visible even when values equal
                        const idCmp = (a.id - b.id) * dirMul;
                        if (idCmp !== 0) return idCmp;
                        // Final tie-breaker: original index for stability
                        return a.idx - b.idx;
                    });
                }

                const total = working.length;
                const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
                if (currentPage > totalPages) currentPage = totalPages;

                const startIdx = total === 0 ? 0 : (currentPage - 1) * rowsPerPage;
                const endIdx = total === 0 ? 0 : Math.min(startIdx + rowsPerPage, total);

                // Clear body and render slice
                tbody.innerHTML = '';
                if (total === 0) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 5;
                    td.className = 'no-remarks';
                    td.textContent = 'No matching requests';
                    tr.appendChild(td);
                    tbody.appendChild(tr);
                } else {
                    const slice = working.slice(startIdx, endIdx);
                    slice.forEach(item => tbody.appendChild(item.tr.cloneNode(true)));
                }

                // Update footer text and buttons
                if (showingText) {
                    const showStart = total === 0 ? 0 : (startIdx + 1);
                    const showEnd = endIdx;
                    showingText.textContent = `Showing ${showStart}–${showEnd} of ${total}`;
                }
                if (prevBtn) prevBtn.disabled = currentPage <= 1 || total === 0;
                if (nextBtn) nextBtn.disabled = currentPage >= Math.ceil(total / rowsPerPage) || total === 0;

                updateHeaderIcons();
            }

            // Event wiring
            if (searchInput) searchInput.addEventListener('input', applySearch);
            if (rowsSelect) rowsSelect.addEventListener('change', () => {
                rowsPerPage = parseInt(rowsSelect.value, 10) || 10;
                currentPage = 1;
                render();
            });
            if (prevBtn) prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    render();
                }
            });
            if (nextBtn) nextBtn.addEventListener('click', () => {
                currentPage++;
                render();
            });

            sortableHeaders.forEach(th => {
                th.addEventListener('click', () => {
                    const key = th.getAttribute('data-sort-key');
                    if (!key) return;
                    if (sortKey === key) {
                        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        sortKey = key;
                        sortDir = 'asc';
                    }
                    currentPage = 1;
                    render();
                });
            });

            // Initial render
            render();
        })();
    </script>
</body>
<?php include __DIR__ . '/includes/profile_guard.php'; ?>

</html>
<?php include __DIR__ . '/includes/profile_guard.php'; ?>
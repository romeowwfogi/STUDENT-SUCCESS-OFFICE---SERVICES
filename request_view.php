<?php
session_start();

// --- AUTHENTICATION GUARD ---
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

// Fetch request metadata and verify ownership
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

    // Load answers
    $answers = [];
    $stmt2 = $conn->prepare("SELECT sf.field_id, sf.label AS field_label, sf.field_type, sf.display_order, sa.answer_value
                             FROM services_answers sa
                             JOIN services_fields sf ON sa.field_id = sf.field_id
                             WHERE sa.request_id = ?
                             ORDER BY sf.display_order ASC");
    $stmt2->bind_param('i', $request_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($row = $res2->fetch_assoc()) {
        $answers[] = $row;
    }
    $stmt2->close();

    // Build option label map for select/radio/checkbox
    $optionLabels = [];
    if ($service_id > 0) {
        $opt = $conn->prepare("SELECT sfo.field_id, sfo.option_value, sfo.option_label
                               FROM services_field_options sfo
                               JOIN services_fields sf ON sfo.field_id = sf.field_id
                               WHERE sf.service_id = ?");
        $opt->bind_param('i', $service_id);
        $opt->execute();
        $optRes = $opt->get_result();
        while ($row = $optRes->fetch_assoc()) {
            $fid = intval($row['field_id']);
            $val = $row['option_value'];
            if (!isset($optionLabels[$fid])) $optionLabels[$fid] = [];
            $optionLabels[$fid][$val] = $row['option_label'];
        }
        $opt->close();
    }
} catch (Exception $e) {
    error_log('Error loading request view: ' . $e->getMessage());
    header('Location: error.php?type=db');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request #<?php echo htmlspecialchars($request_id); ?> - Submitted Answers</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #1E3A8A;
            --on-primary: #ffffff;
            --background: #f8fafc;
            --surface: #ffffff;
            --on-surface: #64748b;
            --on-background: #0f172a;
            --elevation-1: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            margin: 0;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: linear-gradient(180deg, #1E293B 0%, #0F172A 100%);
            color: #fff;
            box-shadow: var(--elevation-1);
            padding: 24px;
            z-index: 1000;
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
            background: var(--primary);
            color: var(--on-primary);
            opacity: 1;
        }

        .main-content {
            margin-left: 280px;
            padding: 32px;
            min-height: 100vh;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            color: var(--on-background);
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

        .card {
            background: var(--surface);
            border-radius: 16px;
            box-shadow: var(--elevation-1);
            padding: 20px;
        }

        .meta {
            display: flex;
            gap: 16px;
            align-items: center;
            color: var(--on-surface);
            font-size: 14px;
        }

        .answers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .answers-table th,
        .answers-table td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            text-align: left;
            vertical-align: top;
        }

        .answers-table thead th {
            background: rgba(0, 0, 0, 0.04);
            font-weight: 600;
        }

        .remarks {
            color: var(--on-surface);
            font-size: 14px;
            line-height: 1.5;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
        }

        .empty {
            color: var(--on-surface);
            font-style: italic;
        }


        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: -webkit-fill-available !important;
                height: -webkit-fill-available !important;
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            /* Vertical card-style answers table on mobile */
            .answers-table {
                display: block;
                border-collapse: separate;
                width: 100%;
            }
            .answers-table thead {
                display: none;
            }
            .answers-table tbody {
                display: block;
            }
            .answers-table tr {
                display: block;
                margin: 12px 0;
                background: var(--surface);
                border-radius: 12px;
                box-shadow: var(--elevation-1);
                overflow: hidden;
            }
            .answers-table td {
                display: grid;
                grid-template-columns: 110px 1fr;
                gap: 10px;
                padding: 12px 16px;
                border: none;
                text-align: left !important;
                color: var(--on-surface);
                min-width: 0; /* allow content to shrink inside grid */
                word-break: break-word; /* wrap long strings like emails */
                overflow-wrap: anywhere; /* ensure no horizontal overflow */
            }
            .answers-table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--on-surface);
                opacity: 0.85;
            }
        }

        /* Ultra-small screens tweaks */
        @media (max-width: 480px) {
            .answers-table td {
                grid-template-columns: 100px 1fr;
                padding: 12px;
            }
        }
    </style>
    <script>
        function mapAnswer(fieldType, fieldId, rawValue) {
            if (!rawValue) return '';
            // file
            if (fieldType === 'file') {
                const url = rawValue;
                const name = url.split('/').pop();
                return `<a href="${url}" target="_blank" rel="noopener" style="color: var(--primary); text-decoration: none;">${name}</a>`;
            }
            // checkbox may store comma-separated option_value
            if (fieldType === 'checkbox') {
                const values = rawValue.split(',').map(v => v.trim()).filter(Boolean);
                const labels = values.map(v => window.optionLabelMap[fieldId]?.[v] || v);
                return labels.join(', ');
            }
            // select/radio
            if (fieldType === 'select' || fieldType === 'radio') {
                return window.optionLabelMap[fieldId]?.[rawValue] || rawValue;
            }
            // text/textarea/date
            return rawValue;
        }
    </script>
</head>

<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="user-fullname"><i data-lucide="graduation-cap"></i> Student Success Office</h2>
        </div>
        <nav>
            <ul class="sidebar-nav">
                <li><a href="home.php"><i data-lucide="grid-3x3"></i> Services</a></li>
                <li><a href="requests.php" class="active"><i data-lucide="file-text"></i> My Requests</a></li>
                <li><a href="#"><i data-lucide="settings"></i> Settings</a></li>
                <li><a href="login.php"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="container">
            <a href="requests.php" class="back-link"><i data-lucide="arrow-left"></i> Back to My Requests</a>
            <div class="card" style="margin-top: 16px;">
                <div class="header">
                    <div>
                        <h1>Request #<?php echo htmlspecialchars($request['request_id']); ?></h1>
                        <div class="meta">
                            <div><strong>Service:</strong> <?php echo htmlspecialchars($request['service_name']); ?></div>
                            <div><strong>Requested:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($request['requested_at'])); ?></div>
                        </div>
                    </div>
                    <div>
                        <span class="status-badge" style="background: <?php echo htmlspecialchars($request['color_hex']); ?>;">
                            <?php echo htmlspecialchars($request['status_name']); ?>
                        </span>
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

                <div style="margin-top: 18px;">
                    <h3 style="margin:0 0 8px 0; font-size: 18px; color: var(--on-background);">Submitted Answers</h3>
                    <?php if (empty($answers)): ?>
                        <div class="empty">No submitted answers found.</div>
                    <?php else: ?>
                        <table class="answers-table">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Field</th>
                                    <th>Answer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($answers as $a): ?>
                                    <tr>
                                        <td data-label="Field"><strong><?php echo htmlspecialchars($a['field_label']); ?></strong></td>
                                        <td data-label="Answer">
                                            <?php
                                            $fid = intval($a['field_id']);
                                            $ftype = $a['field_type'];
                                            $raw = $a['answer_value'];
                                            $map = isset($optionLabels[$fid]) ? $optionLabels[$fid] : [];
                                            // Prepare the map for client-side JS
                                            // But render server-side for reliability
                                            if ($ftype === 'file') {
                                                $url = htmlspecialchars($raw);
                                                $name = htmlspecialchars(basename($raw));
                                                echo "<a href='$url' target='_blank' rel='noopener' style='color: var(--primary); text-decoration: none;'>$name</a>";
                                            } elseif ($ftype === 'checkbox') {
                                                $parts = array_filter(array_map('trim', explode(',', $raw)));
                                                $labels = array_map(function ($v) use ($map) {
                                                    return htmlspecialchars(isset($map[$v]) ? $map[$v] : $v);
                                                }, $parts);
                                                echo implode(', ', $labels);
                                            } elseif ($ftype === 'select' || $ftype === 'radio') {
                                                $label = isset($map[$raw]) ? $map[$raw] : $raw;
                                                echo htmlspecialchars($label);
                                            } else {
                                                echo nl2br(htmlspecialchars($raw));
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
<?php include __DIR__ . '/includes/profile_guard.php'; ?>

</html>
<?php include __DIR__ . '/includes/profile_guard.php'; ?>
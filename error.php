<?php
// error.php – Centralized error page for user-friendly messages
// This page is intentionally public; it should not enforce auth.

// Determine error type and code
$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
$code = 0;
$title = 'Something went wrong';
$message = '';

switch ($type) {
    case 'not_found':
    case '404':
        $code = 404;
        $title = 'Not Found';
        $message = 'The requested resource could not be found.';
        break;
    case 'forbidden':
    case '403':
        $code = 403;
        $title = 'Access Denied';
        $message = "You don’t have permission to view this resource.";
        break;
    case 'invalid_request':
    case '400':
        $code = 400;
        $title = 'Invalid Request';
        $message = 'Your request parameters are invalid or missing.';
        break;
    case 'db':
    case '500':
        $code = 500;
        $title = 'Server Error';
        $message = 'We ran into a problem processing your request.';
        break;
    default:
        $code = 500;
        $title = 'Unexpected Error';
        $message = 'An unexpected error occurred.';
        break;
}

http_response_code($code);

// Optional detail message for debugging/user context (sanitized)
$detail = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';

// Decide a reasonable primary action
$primaryHref = 'home.php';
$primaryText = 'Go to Home';

// If the user is likely looking at requests, suggest that
if (in_array($type, ['not_found','forbidden']) || $code === 404 || $code === 403) {
    $primaryHref = 'requests.php';
    $primaryText = 'Back to My Requests';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($title); ?> - Student Success Office</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --primary: #3b82f6;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
            --shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        body { font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: var(--bg); color: var(--text); margin: 0; min-height: 100vh; display: grid; place-items: center; }
        .wrap { max-width: 640px; width: 92%; }
        .card { background: var(--card); border-radius: 16px; box-shadow: var(--shadow); padding: 28px; }
        .icon { width: 56px; height: 56px; border-radius: 50%; display: grid; place-items: center; margin-bottom: 12px; }
        .icon svg { width: 28px; height: 28px; }
        .title { font-size: 22px; font-weight: 600; margin: 4px 0 8px; }
        .desc { color: var(--muted); font-size: 15px; line-height: 1.6; }
        .detail { color: #334155; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px; margin-top: 14px; padding: 10px 12px; font-size: 13px; }
        .actions { margin-top: 18px; display: flex; gap: 10px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 10px; text-decoration: none; font-weight: 500; border: 1px solid transparent; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-outline { background: #fff; color: var(--muted); border-color: #e2e8f0; }
    </style>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
    <?php /* Optional loader/modal includes if desired */ ?>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <?php
                $iconBg = '#94a3b8';
                $iconName = 'alert-triangle';
                if ($code === 404) { $iconBg = '#eab308'; $iconName = 'search'; }
                elseif ($code === 403) { $iconBg = '#ef4444'; $iconName = 'shield-alert'; }
                elseif ($code === 400) { $iconBg = '#f59e0b'; $iconName = 'octagon-alert'; }
                elseif ($code === 500) { $iconBg = '#ef4444'; $iconName = 'server-crash'; }
            ?>
            <div class="icon" style="background: <?php echo $iconBg; ?>20; border: 1px solid <?php echo $iconBg; ?>40; color: <?php echo $iconBg; ?>;">
                <i data-lucide="<?php echo $iconName; ?>"></i>
            </div>
            <div class="title"><?php echo htmlspecialchars($title); ?></div>
            <div class="desc"><?php echo htmlspecialchars($message); ?></div>
            <?php if ($detail) { ?>
                <div class="detail"><?php echo $detail; ?></div>
            <?php } ?>
            <div class="actions">
                <a class="btn btn-primary" href="<?php echo $primaryHref; ?>">
                    <i data-lucide="arrow-left"></i>
                    <?php echo htmlspecialchars($primaryText); ?>
                </a>
                <a class="btn btn-outline" href="home.php">
                    <i data-lucide="home"></i>
                    Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
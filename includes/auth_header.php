<?php
// Usage: set $auth_header_class (e.g., 'login-header' or 'signup-header'),
// $auth_title (optional, default PLP name), $auth_subtitle (e.g., 'Admission | Sign in').
// Requires: $PLP_LOGO_URL, $PLP_LOGO available in scope.

$auth_header_class = isset($auth_header_class) ? $auth_header_class : 'auth-header';
$auth_title = isset($auth_title) ? $auth_title : 'Pamantasan ng Lungsod ng Pasig';
$auth_subtitle = isset($auth_subtitle) ? $auth_subtitle : '';
?>
<div class="<?php echo htmlspecialchars($auth_header_class); ?>">
    <img src="<?php echo $PLP_LOGO_URL; ?>" alt="<?php echo $PLP_LOGO; ?>" class="logo" />
    <div class="header-text">
        <h2><?php echo htmlspecialchars($auth_title); ?></h2>
        <?php if ($auth_subtitle) { ?>
            <p><?php echo htmlspecialchars($auth_subtitle); ?></p>
        <?php } ?>
    </div>
</div>
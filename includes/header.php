<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="manifest" href="<?php echo BASE_URL; ?>manifest.json">
    <meta name="theme-color" content="#191a66">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- فونت فارسی Vazirmatn -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- استایل اصلی -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/Style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>

<script>
// ثبت Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker
            .register('<?php echo BASE_URL; ?>service-worker.js')
            .then(function(registration) {
                console.log('✅ Service Worker ثبت شد:', registration);
            })
            .catch(function(error) {
                console.log('❌ خطا در ثبت Service Worker:', error);
            });
    });
}
</script>

<body>

<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$pages_with_rect = ['index', 'about', 'consultants', 'consultant', 'contact'];
$show_rect = in_array($current_page, $pages_with_rect);
?>

<?php if ($show_rect): ?>
<div class="rect">
<?php endif; ?>

    <!-- ========== هدر ========== -->
    <header class="navbar">
        <div class="logo">
            <button id="themeToggle" class="theme-btn">
                <i class="fas fa-moon"></i>
            </button>
            <span><?php echo SITE_NAME; ?></span>
        </div>

        <!-- دکمه همبرگری -->
        <button class="hamburger" id="hamburgerBtn" aria-label="منو">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- منوی ناوبری (دسکتاپ) -->
        <nav class="nav-menu" id="navMenu">
            <a href="<?php echo BASE_URL; ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active-link' : ''; ?>">
                <i class="fas fa-home"></i> خانه
            </a>
            <a href="<?php echo BASE_URL; ?>about.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active-link' : ''; ?>">
                <i class="fas fa-info-circle"></i> درباره ما
            </a>
            <a href="<?php echo BASE_URL; ?>consultants.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'consultants.php' || basename($_SERVER['PHP_SELF']) == 'consultant.php') ? 'active-link' : ''; ?>">
                <i class="fas fa-user-md"></i> مشاوران
            </a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active-link' : ''; ?>">
                <i class="fas fa-phone"></i> تماس با ما
            </a>
        </nav>

        <!-- بخش کاربری (دسکتاپ) -->
        <div class="user-section">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-name">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'کاربر'); ?>
                </span>
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn-signup">
                    <i class="fas fa-tachometer-alt"></i> پنل کاربری
                </a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>admin/" class="btn-signup admin-btn">
                        <i class="fas fa-cog"></i> مدیریت
                    </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn-signup logout-btn">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>login.php" class="btn-signup">
                    <i class="fas fa-sign-in-alt"></i> ورود/ثبت نام
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- ========== منوی موبایل ========== -->
    <nav class="mobile-menu" id="mobileMenu">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="mobile-user-section">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'کاربر'); ?>
            </div>
        <?php endif; ?>

        <a href="<?php echo BASE_URL; ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active-link' : ''; ?>">
            <i class="fas fa-home"></i> خانه
        </a>
        <a href="<?php echo BASE_URL; ?>about.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active-link' : ''; ?>">
            <i class="fas fa-info-circle"></i> درباره ما
        </a>
        <a href="<?php echo BASE_URL; ?>consultants.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'consultants.php' || basename($_SERVER['PHP_SELF']) == 'consultant.php') ? 'active-link' : ''; ?>">
            <i class="fas fa-user-md"></i> مشاوران
        </a>
        <a href="<?php echo BASE_URL; ?>contact.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active-link' : ''; ?>">
            <i class="fas fa-phone"></i> تماس با ما
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?php echo BASE_URL; ?>dashboard.php">
                <i class="fas fa-tachometer-alt"></i> پنل کاربری
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>admin/">
                    <i class="fas fa-cog"></i> مدیریت
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> خروج
            </a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>login.php">
                <i class="fas fa-sign-in-alt"></i> ورود/ثبت نام
            </a>
        <?php endif; ?>
    </nav>

    <!-- اوورلی -->
    <div class="menu-overlay" id="menuOverlay"></div>

<?php if ($show_rect): ?>
</div> <!-- پایان rect -->
<?php endif; ?>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['fullName'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $student_id = trim($_POST['studentId'] ?? '');
    $mobile     = trim($_POST['mobile'] ?? '');
    $birth_year = intval($_POST['birthYear'] ?? 0);
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirmPassword'] ?? '';

    if (!$full_name || !$email || !$username || !$student_id || !$mobile || !$birth_year || !$password) {
        $error = 'لطفاً تمام فیلدها را پر کنید.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'فرمت ایمیل صحیح نیست.';
    } elseif (!preg_match('/^09\d{9}$/', $mobile)) {
        $error = 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.';
    } elseif (!preg_match('/^\d{8,10}$/', $student_id)) {
        $error = 'شماره دانشجویی معتبر نیست (۸ تا ۱۰ رقم).';
    } elseif ($birth_year < 1300 || $birth_year > 1390) {
        $error = 'سال تولد باید بین ۱۳۰۰ تا ۱۳۹۰ باشد.';
    } elseif (strlen($password) < 6) {
        $error = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    } elseif ($password !== $confirm) {
        $error = 'رمز عبور و تکرار آن یکسان نیستند.';
    } else {
        try {
            // بررسی تکراری نبودن ایمیل و نام کاربری
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ? OR student_id = ?");
            $stmt->execute([$email, $username, $student_id]);
            
            if ($stmt->fetch()) {
                $error = 'ایمیل، نام کاربری یا شماره دانشجویی قبلاً ثبت شده است.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, email, username, student_id, mobile, birth_year, password_hash) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([$full_name, $email, $username, $student_id, $mobile, $birth_year, $hash]);
                
                if ($result) {
                    header('Location: login.php?registered=1');
                    exit;
                } else {
                    $error = 'خطا در ثبت‌نام: عملیات ناموفق بود.';
                }
            }
        } catch (PDOException $e) {
            $error = 'خطا در ثبت‌نام: ' . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- REGISTER SECTION -->
<section class="signup-container">

    <div class="signup-box">
        <h2>ثبت‌نام در سامانه</h2>
        <p class="signup-subtitle">
            برای رزرو نوبت مشاوره، لطفاً اطلاعات خود را وارد کنید.
        </p>

        <?php if ($error): ?>
            <div style="color:red; background:#ffe0e0; padding:10px; border-radius:8px; margin-bottom:15px; text-align:center; border:1px solid #ff4d4d;">
                 <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="color:green; background:#e0ffe0; padding:10px; border-radius:8px; margin-bottom:15px; text-align:center; border:1px solid #4CAF50;">
                 <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form class="signup-form" id="signupForm" method="POST" action="signup.php">

            <div class="form-grid">

                <div class="form-group">
                    <label>نام و نام خانوادگی</label>
                    <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($_POST['fullName'] ?? '') ?>" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>ایمیل</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>نام کاربری</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>شماره دانشجویی</label>
                    <input type="text" id="studentId" name="studentId" value="<?= htmlspecialchars($_POST['studentId'] ?? '') ?>" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>شماره موبایل</label>
                    <input type="text" id="mobile" name="mobile" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>سال تولد</label>
                    <input type="number" id="birthYear" name="birthYear" value="<?= htmlspecialchars($_POST['birthYear'] ?? '') ?>" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>رمز عبور</label>
                    <input type="password" id="password" name="password" required>
                    <small class="error-message"></small>
                </div>

                <div class="form-group">
                    <label>تکرار رمز عبور</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <small class="error-message"></small>
                </div>

            </div>

            <button type="submit" class="btn-signup full"> ثبت نام</button>

        </form>

        <p style="text-align:center; margin-top:15px;">
            قبلاً ثبت‌نام کرده‌اید؟ <a href="login.php">وارد شوید</a>
        </p>
    </div>

    <div class="signup-image">
        <img src="<?php echo BASE_URL; ?>assets/img/fg4-min.png" alt="ثبت‌نام">
    </div>

</section>

<?php include 'includes/footer.php'; ?>
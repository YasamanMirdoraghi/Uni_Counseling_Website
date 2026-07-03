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
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // ذخیره در سشن
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role'];
                
                if ($user['role'] == 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = '! نام کاربری یا رمز عبور اشتباه است.';
            }
        } catch (PDOException $e) {
            $error = '! خطا در ورود: ' . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- LOGIN SECTION -->
<section class="signup-container">

    <div class="signup-box">
        <h2> ورود به سامانه</h2>
        <p class="signup-subtitle">
            با نام کاربری و رمز عبور خود وارد شوید.
        </p>

        <?php if (isset($_GET['registered'])): ?>
            <div style="color:green; background:#e0ffe0; padding:10px; border-radius:8px; margin-bottom:15px; text-align:center; border:1px solid #4CAF50;">
                 ثبت‌نام با موفقیت انجام شد! لطفاً وارد شوید.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="color:red; background:#ffe0e0; padding:10px; border-radius:8px; margin-bottom:15px; text-align:center; border:1px solid #ff4d4d;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form class="signup-form" method="POST" action="login.php">

            <div class="form-grid">

                <div class="form-group" style="grid-column: span 2;">
                    <label> نام کاربری یا ایمیل</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required placeholder="مثلاً ahmad_r">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label> رمز عبور</label>
                    <input type="password" name="password" required placeholder="رمز عبور خود را وارد کنید">
                </div>

            </div>

            <button type="submit" class="btn-signup full"> ورود</button>

        </form>

        <p style="text-align:center; margin-top:15px;">
            حساب کاربری ندارید؟ <a href="signup.php">ثبت‌نام کنید</a>
        </p>
    </div>

    <div class="signup-image">
        <img src="<?php echo BASE_URL; ?>assets/img/fg4-min.png" alt="ورود">
    </div>

</section>

<?php include 'includes/footer.php'; ?>
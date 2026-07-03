<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';
$user = null;

// دریافت اطلاعات کاربر
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: user.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'خطا در دریافت اطلاعات کاربر: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $birth_year = intval($_POST['birth_year'] ?? 0);
    $role = $_POST['role'] ?? 'user';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($full_name) || empty($email) || empty($username) || empty($student_id)) {
        $error = 'لطفاً فیلدهای ضروری را پر کنید.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ? OR student_id = ?) AND id != ?");
            $stmt->execute([$email, $username, $student_id, $id]);
            if ($stmt->fetch()) {
                $error = 'ایمیل، نام کاربری یا شماره دانشجویی قبلاً ثبت شده است.';
            } else {
                $sql = "
                    UPDATE users 
                    SET full_name = ?, email = ?, username = ?, student_id = ?, mobile = ?, birth_year = ?, role = ?
                ";
                $params = [$full_name, $email, $username, $student_id, $mobile, $birth_year, $role];
                
                if (!empty($new_password)) {
                    if (strlen($new_password) < 6) {
                        $error = 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.';
                    } else {
                        $sql .= ", password_hash = ?";
                        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                    }
                }
                
                if (empty($error)) {
                    $sql .= " WHERE id = ?";
                    $params[] = $id;
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    
                    $success = ' اطلاعات کاربر با موفقیت به‌روزرسانی شد.';
                    
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    $user = $stmt->fetch();
                }
            }
        } catch (PDOException $e) {
            $error = 'خطا در به‌روزرسانی: ' . $e->getMessage();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1><i class="fas fa-user-edit"></i> ویرایش کاربر</h1>
            <p class="admin-page-subtitle"><i class="fas fa-info-circle"></i> اطلاعات کاربر را ویرایش کنید.</p>
        </div>
        <a href="user.php" class="btn-back"><i class="fas fa-arrow-right"></i> بازگشت به لیست</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-box error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-box success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($user): ?>
        <div class="form-container">
            
            <div class="user-preview">
                <div class="user-avatar">
                    <?php echo mb_substr($user['full_name'], 0, 1); ?>
                </div>
                <div>
                    <div class="user-preview-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    <div class="user-preview-info">
                        <?php if ($user['role'] == 'admin'): ?>
                            <i class="fas fa-crown"></i> ادمین
                        <?php else: ?>
                            <i class="fas fa-user"></i> کاربر عادی
                        <?php endif; ?>
                        | <i class="fas fa-calendar-alt"></i> ثبت‌نام: <?php echo date('Y/m/d', strtotime($user['created_at'])); ?>
                    </div>
                </div>
            </div>

            <form method="POST" action="users_edit.php?id=<?php echo $id; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name"><i class="fas fa-user"></i> نام و نام خانوادگی <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="username"><i class="fas fa-user-tag"></i> نام کاربری <span class="required">*</span></label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> ایمیل <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="student_id"><i class="fas fa-id-badge"></i> شماره دانشجویی <span class="required">*</span></label>
                        <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($user['student_id']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="mobile"><i class="fas fa-phone"></i> شماره موبایل</label>
                        <input type="text" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="birth_year"><i class="fas fa-calendar-alt"></i> سال تولد</label>
                        <input type="number" id="birth_year" name="birth_year" value="<?php echo htmlspecialchars($user['birth_year']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="role"><i class="fas fa-tag"></i> نقش کاربری</label>
                    <select id="role" name="role">
                        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>><i class="fas fa-user"></i> کاربر عادی</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>><i class="fas fa-crown"></i> ادمین</option>
                    </select>
                </div>

                <div class="form-group password-section">
                    <label for="new_password"><i class="fas fa-lock"></i> رمز عبور جدید (اختیاری)</label>
                    <input type="password" id="new_password" name="new_password" placeholder="برای تغییر رمز عبور، مقدار جدید را وارد کنید">
                    <div class="helper-text"><i class="fas fa-info-circle"></i> حداقل ۶ کاراکتر. در صورت تمایل به تغییر رمز عبور، مقدار جدید را وارد کنید.</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> ذخیره تغییرات</button>
                    <a href="user.php" class="btn-cancel"><i class="fas fa-times"></i> لغو</a>
                </div>

            </form>
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
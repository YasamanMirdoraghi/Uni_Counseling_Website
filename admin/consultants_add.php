<?php
require_once '../includes/config.php';

// بررسی لاگین بودن و ادمین بودن
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $degree = trim($_POST['degree'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image = trim($_POST['image'] ?? 'default.jpg');

    if (empty($name) || empty($degree) || empty($specialty)) {
        $error = 'لطفاً نام، مدرک و تخصص را وارد کنید.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO consultants (name, degree, specialty, description, image) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $degree, $specialty, $description, $image]);
            
            $success = ' مشاور با موفقیت اضافه شد.';
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'خطا در افزودن مشاور: ' . $e->getMessage();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1><i class="fas fa-user-plus"></i> افزودن مشاور جدید</h1>
            <p class="admin-page-subtitle"><i class="fas fa-info-circle"></i> اطلاعات مشاور جدید را وارد کنید.</p>
        </div>
        <a href="consultants.php" class="btn-back"><i class="fas fa-arrow-right"></i> بازگشت به لیست</a>
    </div>

    <?php if ($error): ?>
        <div class="alert-box error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-box success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <div class="alert-actions">
                <a href="consultants.php" class="btn-add small"><i class="fas fa-list"></i> مشاهده لیست</a>
                <a href="consultants_add.php" class="btn-add small secondary"><i class="fas fa-plus-circle"></i> افزودن مشاور دیگر</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <div class="form-container">
            <form method="POST" action="consultants_add.php">
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> نام و نام خانوادگی <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required placeholder="مثلاً دکتر علی محمدی">
                </div>

                <div class="form-group">
                    <label for="degree"><i class="fas fa-graduation-cap"></i> مدرک تحصیلی <span class="required">*</span></label>
                    <input type="text" id="degree" name="degree" value="<?php echo htmlspecialchars($_POST['degree'] ?? ''); ?>" required placeholder="مثلاً دکترای روانشناسی">
                    <div class="helper-text"><i class="fas fa-info-circle"></i> مثال: دکترای روانشناسی، کارشناسی ارشد مشاوره</div>
                </div>

                <div class="form-group">
                    <label for="specialty"><i class="fas fa-tag"></i> حوزه تخصصی <span class="required">*</span></label>
                    <input type="text" id="specialty" name="specialty" value="<?php echo htmlspecialchars($_POST['specialty'] ?? ''); ?>" required placeholder="مثلاً مشاوره خانواده و ازدواج">
                    <div class="helper-text"><i class="fas fa-info-circle"></i> مثال: مشاوره خانواده و ازدواج، درمان اضطراب و افسردگی</div>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> توضیحات</label>
                    <textarea id="description" name="description" placeholder="توضیحات کامل درباره مشاور، سوابق و تخصص‌ها..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image"><i class="fas fa-image"></i> نام تصویر</label>
                    <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($_POST['image'] ?? 'default.jpg'); ?>" placeholder="default.jpg">
                    <div class="helper-text"><i class="fas fa-info-circle"></i> نام فایل تصویر را در پوشه assets/img/ وارد کنید. (پیش‌فرض: default.jpg)</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> افزودن مشاور</button>
                    <a href="consultants.php" class="btn-cancel"><i class="fas fa-times"></i> لغو</a>
                </div>

            </form>
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
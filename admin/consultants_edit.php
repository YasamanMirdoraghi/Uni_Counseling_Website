<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';
$consultant = null;

// دریافت اطلاعات مشاور
try {
    $stmt = $pdo->prepare("SELECT * FROM consultants WHERE id = ?");
    $stmt->execute([$id]);
    $consultant = $stmt->fetch();
    
    if (!$consultant) {
        header('Location: consultants.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'خطا در دریافت اطلاعات مشاور: ' . $e->getMessage();
}

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
                UPDATE consultants 
                SET name = ?, degree = ?, specialty = ?, description = ?, image = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $degree, $specialty, $description, $image, $id]);
            
            $success = ' اطلاعات مشاور با موفقیت به‌روزرسانی شد.';
            
            $stmt = $pdo->prepare("SELECT * FROM consultants WHERE id = ?");
            $stmt->execute([$id]);
            $consultant = $stmt->fetch();
            
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
            <h1><i class="fas fa-edit"></i> ویرایش مشاور</h1>
            <p class="admin-page-subtitle"><i class="fas fa-info-circle"></i> اطلاعات مشاور را ویرایش کنید.</p>
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
        </div>
    <?php endif; ?>

    <?php if ($consultant): ?>
        <div class="form-container">
            
            <div class="consultant-preview">
                <img src="<?php echo BASE_URL; ?>assets/img/<?php echo htmlspecialchars($consultant['image'] ?? 'default.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($consultant['name']); ?>">
                <div>
                    <div class="consultant-preview-name"><?php echo htmlspecialchars($consultant['name']); ?></div>
                    <div class="consultant-preview-rating"><i class="fas fa-star"></i> <?php echo number_format($consultant['avg_rating'], 1); ?> / 5</div>
                </div>
            </div>

            <form method="POST" action="consultants_edit.php?id=<?php echo $id; ?>">
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> نام و نام خانوادگی <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($consultant['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="degree"><i class="fas fa-graduation-cap"></i> مدرک تحصیلی <span class="required">*</span></label>
                    <input type="text" id="degree" name="degree" value="<?php echo htmlspecialchars($consultant['degree']); ?>" required>
                    <div class="helper-text"><i class="fas fa-info-circle"></i> مثال: دکترای روانشناسی، کارشناسی ارشد مشاوره</div>
                </div>

                <div class="form-group">
                    <label for="specialty"><i class="fas fa-tag"></i> حوزه تخصصی <span class="required">*</span></label>
                    <input type="text" id="specialty" name="specialty" value="<?php echo htmlspecialchars($consultant['specialty']); ?>" required>
                    <div class="helper-text"><i class="fas fa-info-circle"></i> مثال: مشاوره خانواده و ازدواج، درمان اضطراب و افسردگی</div>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> توضیحات</label>
                    <textarea id="description" name="description" placeholder="توضیحات کامل درباره مشاور، سوابق و تخصص‌ها..."><?php echo htmlspecialchars($consultant['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image"><i class="fas fa-image"></i> نام تصویر</label>
                    <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($consultant['image'] ?? 'default.jpg'); ?>" placeholder="default.jpg">
                    <div class="helper-text"><i class="fas fa-info-circle"></i> نام فایل تصویر را در پوشه assets/img/ وارد کنید. (پیش‌فرض: default.jpg)</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> ذخیره تغییرات</button>
                    <a href="consultants.php" class="btn-cancel"><i class="fas fa-times"></i> لغو</a>
                </div>

            </form>
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
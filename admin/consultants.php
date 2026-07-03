<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';
$consultants = [];

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM consultants WHERE id = ?");
        $stmt->execute([$id]);
        $success = ' مشاور با موفقیت حذف شد.';
    } catch (PDOException $e) {
        $error = 'خطا در حذف مشاور: ' . $e->getMessage();
    }
}

// دریافت لیست مشاوران
try {
    $stmt = $pdo->query("
        SELECT 
            c.*,
            (SELECT COUNT(*) FROM reservations r 
             JOIN available_slots a ON r.slot_id = a.id 
             WHERE a.consultant_id = c.id) as total_reservations,
            (SELECT COUNT(*) FROM reviews WHERE consultant_id = c.id) as total_reviews
        FROM consultants c
        ORDER BY c.avg_rating DESC, c.name ASC
    ");
    $consultants = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'خطا در دریافت لیست مشاوران: ' . $e->getMessage();
}
?>

<?php include '../includes/header.php'; ?>

<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1><i class="fas fa-user-md"></i> مدیریت مشاوران</h1>
            <p class="admin-page-subtitle"><i class="fas fa-info-circle"></i> مدیریت اطلاعات مشاوران مرکز مشاوره</p>
        </div>
        <div class="admin-page-actions">
            <a href="consultants_add.php" class="btn-add">
                <i class="fas fa-plus-circle"></i> افزودن مشاور جدید
            </a>
            <a href="index.php" class="btn-back"><i class="fas fa-arrow-right"></i> بازگشت</a>
        </div>
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

    <div class="admin-toolbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchConsultant" placeholder="جستجوی مشاور..." onkeyup="filterTable()">
        </div>
        <div class="toolbar-info">
            <span>تعداد مشاوران: <strong><?php echo count($consultants); ?></strong></span>
        </div>
    </div>

    <div class="admin-table-container">
        
        <?php if (count($consultants) > 0): ?>
            <div class="table-wrap">
                <table class="admin-table" id="consultantTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تصویر</th>
                            <th>نام</th>
                            <th>مدرک</th>
                            <th>تخصص</th>
                            <th><i class="fas fa-star"></i> امتیاز</th>
                            <th><i class="fas fa-calendar-check"></i> رزروها</th>
                            <th><i class="fas fa-comment"></i> نظرات</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultants as $index => $consultant): ?>
                            <tr class="consultant-row" data-name="<?php echo strtolower($consultant['name']); ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <img src="<?php echo BASE_URL; ?>assets/img/<?php echo htmlspecialchars($consultant['image'] ?? 'default.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($consultant['name']); ?>"
                                         class="consultant-img">
                                </td>
                                <td><strong><?php echo htmlspecialchars($consultant['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($consultant['degree']); ?></td>
                                <td class="consultant-specialty">
                                    <?php echo htmlspecialchars(mb_substr($consultant['specialty'], 0, 30)); ?>
                                    <?php if (mb_strlen($consultant['specialty']) > 30): ?>...<?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-rating"><i class="fas fa-star"></i> <?php echo number_format($consultant['avg_rating'], 1); ?></span>
                                </td>
                                <td class="text-center"><?php echo number_format($consultant['total_reservations'] ?? 0); ?></td>
                                <td class="text-center"><?php echo number_format($consultant['total_reviews'] ?? 0); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="consultants_edit.php?id=<?php echo $consultant['id']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i> ویرایش
                                        </a>
                                        <a href="consultants.php?delete=<?php echo $consultant['id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('آیا از حذف مشاور «<?php echo addslashes($consultant['name']); ?>» اطمینان دارید؟\nهمه رزروها و نظرات مرتبط نیز حذف می‌شوند.')">
                                            <i class="fas fa-trash-alt"></i> حذف
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <h3>هیچ مشاوری ثبت نشده است</h3>
                <p>برای افزودن مشاور جدید، روی دکمه "افزودن مشاور جدید" کلیک کنید.</p>
                <a href="consultants_add.php" class="btn-add"><i class="fas fa-plus-circle"></i> افزودن مشاور جدید</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function filterTable() {
    const input = document.getElementById('searchConsultant');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.consultant-row');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        if (name.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
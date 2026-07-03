<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';
$users = [];

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user && $user['role'] == 'admin') {
            $error = ' نمی‌توانید کاربر ادمین را حذف کنید.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $success = ' کاربر با موفقیت حذف شد.';
        }
    } catch (PDOException $e) {
        $error = 'خطا در حذف کاربر: ' . $e->getMessage();
    }
}

try {
    $stmt = $pdo->query("
        SELECT 
            u.*,
            (SELECT COUNT(*) FROM reservations WHERE user_id = u.id) as total_reservations,
            (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) as total_reviews
        FROM users u
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'خطا در دریافت لیست کاربران: ' . $e->getMessage();
}
?>

<?php include '../includes/header.php'; ?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1><i class="fas fa-users-cog"></i> مدیریت کاربران</h1>
            <p class="admin-page-subtitle"><i class="fas fa-info-circle"></i> مدیریت اطلاعات کاربران مرکز مشاوره</p>
        </div>
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-right"></i> بازگشت به داشبورد</a>
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
            <input type="text" id="searchUser" placeholder="جستجوی کاربر..." onkeyup="filterTable()">
        </div>
        <div class="filter-box">
            <i class="fas fa-filter"></i>
            <select id="filterRole" onchange="filterTable()">
                <option value="all">همه نقش‌ها</option>
                <option value="admin"><i class="fas fa-crown"></i> ادمین</option>
                <option value="user"><i class="fas fa-user"></i> کاربر عادی</option>
            </select>
        </div>
        <div class="toolbar-info">
            <span>تعداد کاربران: <strong><?php echo count($users); ?></strong></span>
        </div>
    </div>

    <div class="admin-table-container">
        
        <?php if (count($users) > 0): ?>
            <div class="table-wrap">
                <table class="admin-table" id="userTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><i class="fas fa-user"></i> کاربر</th>
                            <th><i class="fas fa-id-card"></i> نام کامل</th>
                            <th><i class="fas fa-envelope"></i> ایمیل</th>
                            <th><i class="fas fa-id-badge"></i> شماره دانشجویی</th>
                            <th><i class="fas fa-phone"></i> موبایل</th>
                            <th><i class="fas fa-tag"></i> نقش</th>
                            <th><i class="fas fa-calendar-check"></i> رزروها</th>
                            <th><i class="fas fa-comment"></i> نظرات</th>
                            <th><i class="fas fa-calendar-plus"></i> تاریخ ثبت‌نام</th>
                            <th><i class="fas fa-tools"></i> عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                            <tr class="user-row" 
                                data-name="<?php echo strtolower($user['full_name']); ?>" 
                                data-email="<?php echo strtolower($user['email']); ?>"
                                data-role="<?php echo $user['role']; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="user-avatar">
                                        <?php echo mb_substr($user['full_name'], 0, 1); ?>
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                <td>
                                    <span class="role-badge <?php echo $user['role'] == 'admin' ? 'role-admin' : 'role-user'; ?>">
                                        <?php if ($user['role'] == 'admin'): ?>
                                            <i class="fas fa-crown"></i> ادمین
                                        <?php else: ?>
                                            <i class="fas fa-user"></i> کاربر
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="text-center"><?php echo number_format($user['total_reservations'] ?? 0); ?></td>
                                <td class="text-center"><?php echo number_format($user['total_reviews'] ?? 0); ?></td>
                                <td class="date-created"><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="users_edit.php?id=<?php echo $user['id']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i> ویرایش
                                        </a>
                                        <?php if ($user['role'] == 'admin'): ?>
                                            <span class="btn-delete disabled" title="نمی‌توان ادمین را حذف کرد">
                                                <i class="fas fa-trash-alt"></i> حذف
                                            </span>
                                        <?php else: ?>
                                            <a href="user.php?delete=<?php echo $user['id']; ?>" 
                                               class="btn-delete" 
                                               onclick="return confirm('آیا از حذف کاربر «<?php echo addslashes($user['full_name']); ?>» اطمینان دارید؟\nهمه رزروها و نظرات مرتبط نیز حذف می‌شوند.')">
                                                <i class="fas fa-trash-alt"></i> حذف
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <h3>هیچ کاربری ثبت نشده است</h3>
                <p>کاربران پس از ثبت‌نام در سایت به این لیست اضافه می‌شوند.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function filterTable() {
    const input = document.getElementById('searchUser');
    const filter = input.value.toLowerCase();
    const roleFilter = document.getElementById('filterRole').value;
    const rows = document.querySelectorAll('.user-row');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const email = row.getAttribute('data-email') || '';
        const role = row.getAttribute('data-role') || '';
        
        const matchSearch = name.includes(filter) || email.includes(filter);
        const matchRole = roleFilter === 'all' || role === roleFilter;
        
        if (matchSearch && matchRole) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
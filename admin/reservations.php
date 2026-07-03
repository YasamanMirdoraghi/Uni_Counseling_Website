<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';
$reservations = [];

// تغییر وضعیت رزرو
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    
    $valid_statuses = ['pending', 'confirmed', 'canceled', 'done'];
    if (!in_array($status, $valid_statuses)) {
        $error = 'وضعیت نامعتبر است.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT slot_id, status FROM reservations WHERE id = ?");
            $stmt->execute([$id]);
            $reservation = $stmt->fetch();
            
            if (!$reservation) {
                $error = 'رزرو مورد نظر یافت نشد.';
            } else {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                
                if ($status == 'canceled' && $reservation['status'] != 'canceled') {
                    $stmt = $pdo->prepare("
                        UPDATE available_slots 
                        SET status = 'available', is_booked = FALSE 
                        WHERE id = ?
                    ");
                    $stmt->execute([$reservation['slot_id']]);
                }
                
                if (($status == 'confirmed' || $status == 'done') && $reservation['status'] == 'pending') {
                    $stmt = $pdo->prepare("
                        UPDATE available_slots 
                        SET status = 'booked', is_booked = TRUE 
                        WHERE id = ?
                    ");
                    $stmt->execute([$reservation['slot_id']]);
                }
                
                $pdo->commit();
                $success = ' وضعیت رزرو با موفقیت به‌روزرسانی شد.';
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'خطا در تغییر وضعیت: ' . $e->getMessage();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'خطا در تغییر وضعیت: ' . $e->getMessage();
        }
    }
}

// دریافت لیست رزروها
try {
    $stmt = $pdo->query("
        SELECT 
            r.*,
            u.full_name as user_name,
            u.email as user_email,
            c.name as consultant_name,
            c.id as consultant_id,
            a.slot_date,
            a.slot_time
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN available_slots a ON r.slot_id = a.id
        JOIN consultants c ON a.consultant_id = c.id
        ORDER BY r.created_at DESC
    ");
    $reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'خطا در دریافت لیست رزروها: ' . $e->getMessage();
}

$status_counts = [
    'pending' => 0,
    'confirmed' => 0,
    'canceled' => 0,
    'done' => 0
];
foreach ($reservations as $res) {
    if (isset($status_counts[$res['status']])) {
        $status_counts[$res['status']]++;
    }
}
?>

<?php include '../includes/header.php'; ?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1><i class="fas fa-calendar-check"></i> مدیریت رزروها</h1>
            <p class="admin-page-subtitle"><i class="fas fa-info-circle"></i> مدیریت و تغییر وضعیت رزروهای مشاوره</p>
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

    <div class="status-stats">
        <div class="status-stat pending">
            <span class="count"><i class="fas fa-clock"></i> <?php echo $status_counts['pending']; ?></span>
            <span class="label">در انتظار</span>
        </div>
        <div class="status-stat confirmed">
            <span class="count"><i class="fas fa-check-circle"></i> <?php echo $status_counts['confirmed']; ?></span>
            <span class="label">تایید شده</span>
        </div>
        <div class="status-stat done">
            <span class="count"><i class="fas fa-check-double"></i> <?php echo $status_counts['done']; ?></span>
            <span class="label">انجام شده</span>
        </div>
        <div class="status-stat canceled">
            <span class="count"><i class="fas fa-times-circle"></i> <?php echo $status_counts['canceled']; ?></span>
            <span class="label">لغو شده</span>
        </div>
        <div class="status-stat total">
            <span class="count"><i class="fas fa-list-ul"></i> <?php echo count($reservations); ?></span>
            <span class="label">مجموع</span>
        </div>
    </div>

    <div class="admin-toolbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchReservation" placeholder="جستجوی کاربر یا مشاور..." onkeyup="filterTable()">
        </div>
        <div class="filter-box">
            <i class="fas fa-filter"></i>
            <select id="filterStatus" onchange="filterTable()">
                <option value="all">همه وضعیت‌ها</option>
                <option value="pending"><i class="fas fa-clock"></i> در انتظار</option>
                <option value="confirmed"><i class="fas fa-check-circle"></i> تایید شده</option>
                <option value="done"><i class="fas fa-check-double"></i> انجام شده</option>
                <option value="canceled"><i class="fas fa-times-circle"></i> لغو شده</option>
            </select>
        </div>
    </div>

    <div class="admin-table-container">
        
        <?php if (count($reservations) > 0): ?>
            <div class="table-wrap">
                <table class="admin-table" id="reservationTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><i class="fas fa-hashtag"></i> شماره پیگیری</th>
                            <th><i class="fas fa-user"></i> کاربر</th>
                            <th><i class="fas fa-user-md"></i> مشاور</th>
                            <th><i class="fas fa-calendar-day"></i> تاریخ</th>
                            <th><i class="fas fa-clock"></i> ساعت</th>
                            <th><i class="fas fa-info-circle"></i> وضعیت</th>
                            <th><i class="fas fa-calendar-plus"></i> تاریخ ثبت</th>
                            <th><i class="fas fa-tools"></i> عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $index => $res): ?>
                            <tr class="reservation-row" 
                                data-user="<?php echo strtolower($res['user_name']); ?>"
                                data-consultant="<?php echo strtolower($res['consultant_name']); ?>"
                                data-status="<?php echo $res['status']; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <span class="tracking-code"><?php echo htmlspecialchars($res['tracking_code']); ?></span>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-name"><?php echo htmlspecialchars($res['user_name']); ?></div>
                                        <div class="user-email"><?php echo htmlspecialchars($res['user_email']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>consultant.php?id=<?php echo $res['consultant_id']; ?>" 
                                       class="consultant-link" target="_blank">
                                        <?php echo htmlspecialchars($res['consultant_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($res['slot_date']); ?></td>
                                <td><?php echo htmlspecialchars($res['slot_time']); ?></td>
                                <td>
                                    <?php
                                    $status_map = [
                                        'pending' => ['label' => 'در انتظار', 'class' => 'status-pending', 'icon' => 'fa-clock'],
                                        'confirmed' => ['label' => 'تایید شده', 'class' => 'status-confirmed', 'icon' => 'fa-check-circle'],
                                        'canceled' => ['label' => 'لغو شده', 'class' => 'status-canceled', 'icon' => 'fa-times-circle'],
                                        'done' => ['label' => 'انجام شده', 'class' => 'status-done', 'icon' => 'fa-check-double']
                                    ];
                                    $status = $status_map[$res['status']] ?? ['label' => $res['status'], 'class' => '', 'icon' => 'fa-circle'];
                                    ?>
                                    <span class="status-badge <?php echo $status['class']; ?>">
                                        <i class="fas <?php echo $status['icon']; ?>"></i> <?php echo $status['label']; ?>
                                    </span>
                                </td>
                                <td class="date-created"><?php echo date('Y/m/d H:i', strtotime($res['created_at'])); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <?php if ($res['status'] == 'pending'): ?>
                                            <a href="reservations.php?id=<?php echo $res['id']; ?>&status=confirmed" 
                                               class="btn-status confirm" 
                                               onclick="return confirm('تایید رزرو «<?php echo htmlspecialchars($res['tracking_code']); ?>»؟')">
                                                <i class="fas fa-check"></i> تایید
                                            </a>
                                            <a href="reservations.php?id=<?php echo $res['id']; ?>&status=canceled" 
                                               class="btn-status cancel" 
                                               onclick="return confirm('لغو رزرو «<?php echo htmlspecialchars($res['tracking_code']); ?>»؟')">
                                                <i class="fas fa-times"></i> لغو
                                            </a>
                                        <?php elseif ($res['status'] == 'confirmed'): ?>
                                            <a href="reservations.php?id=<?php echo $res['id']; ?>&status=done" 
                                               class="btn-status done" 
                                               onclick="return confirm('علامت‌گذاری رزرو «<?php echo htmlspecialchars($res['tracking_code']); ?>» به عنوان انجام شده؟')">
                                                <i class="fas fa-check-double"></i> انجام شد
                                            </a>
                                            <a href="reservations.php?id=<?php echo $res['id']; ?>&status=canceled" 
                                               class="btn-status cancel" 
                                               onclick="return confirm('لغو رزرو «<?php echo htmlspecialchars($res['tracking_code']); ?>»؟')">
                                                <i class="fas fa-times"></i> لغو
                                            </a>
                                        <?php elseif ($res['status'] == 'done'): ?>
                                            <span class="btn-status disabled"><i class="fas fa-check-double"></i> انجام شده</span>
                                        <?php elseif ($res['status'] == 'canceled'): ?>
                                            <span class="btn-status disabled"><i class="fas fa-times"></i> لغو شده</span>
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
                <i class="fas fa-calendar-times"></i>
                <h3>هیچ رزروی ثبت نشده است</h3>
                <p>رزروها پس از ثبت توسط کاربران در این لیست ظاهر می‌شوند.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function filterTable() {
    const input = document.getElementById('searchReservation');
    const filter = input.value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('.reservation-row');
    
    rows.forEach(row => {
        const user = row.getAttribute('data-user') || '';
        const consultant = row.getAttribute('data-consultant') || '';
        const status = row.getAttribute('data-status') || '';
        
        const matchSearch = user.includes(filter) || consultant.includes(filter);
        const matchStatus = statusFilter === 'all' || status === statusFilter;
        
        if (matchSearch && matchStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
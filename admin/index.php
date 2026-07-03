<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$stats = [];

try {
    // تعداد کل کاربران
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $stmt->fetch()['total'];
    
    // تعداد کل مشاوران
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM consultants");
    $stats['total_consultants'] = $stmt->fetch()['total'];
    
    // تعداد کل رزروها
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations");
    $stats['total_reservations'] = $stmt->fetch()['total'];
    
    // تعداد کل نظرات
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
    $stats['total_reviews'] = $stmt->fetch()['total'];
    
    // رزروهای امروز
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM reservations r
        JOIN available_slots a ON r.slot_id = a.id
        WHERE a.slot_date = CURDATE()
    ");
    $stats['today_reservations'] = $stmt->fetch()['total'];
    
    // رزروهای در انتظار تایید
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM reservations 
        WHERE status = 'pending'
    ");
    $stats['pending_reservations'] = $stmt->fetch()['total'];
    
    // رزروهای تایید شده
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM reservations 
        WHERE status = 'confirmed'
    ");
    $stats['confirmed_reservations'] = $stmt->fetch()['total'];
    
    // رزروهای انجام شده
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM reservations 
        WHERE status = 'done'
    ");
    $stats['done_reservations'] = $stmt->fetch()['total'];
    
    // رزروهای لغو شده
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM reservations 
        WHERE status = 'canceled'
    ");
    $stats['canceled_reservations'] = $stmt->fetch()['total'];
    
    // میانگین امتیازات
    $stmt = $pdo->query("
        SELECT COALESCE(AVG(avg_rating), 0) as average 
        FROM consultants
    ");
    $stats['avg_rating'] = round($stmt->fetch()['average'], 1);
    
    // ۵ مشاور برتر
    $stmt = $pdo->query("
        SELECT id, name, specialty, avg_rating, image 
        FROM consultants 
        ORDER BY avg_rating DESC 
        LIMIT 5
    ");
    $stats['top_consultants'] = $stmt->fetchAll();
    
    // ۵ رزرو اخیر
    $stmt = $pdo->query("
        SELECT 
            r.*,
            u.full_name as user_name,
            c.name as consultant_name,
            a.slot_date,
            a.slot_time
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN available_slots a ON r.slot_id = a.id
        JOIN consultants c ON a.consultant_id = c.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $stats['recent_reservations'] = $stmt->fetchAll();
    
    // تعداد کاربران جدید این ماه
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM users 
        WHERE MONTH(created_at) = MONTH(CURDATE()) 
        AND YEAR(created_at) = YEAR(CURDATE())
    ");
    $stats['new_users_this_month'] = $stmt->fetch()['total'];
    
    // تعداد نظرات این ماه
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM reviews 
        WHERE MONTH(created_at) = MONTH(CURDATE()) 
        AND YEAR(created_at) = YEAR(CURDATE())
    ");
    $stats['new_reviews_this_month'] = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    $error = 'خطا در دریافت آمار: ' . $e->getMessage();
}

// دریافت اطلاعات ادمین
$admin_name = $_SESSION['full_name'] ?? 'مدیر';
?>

<?php include '../includes/header.php'; ?>

<div class="admin-dashboard">

    <!-- عنوان -->
    <div class="admin-header">
        <div>
            <h1><i class="fas fa-cogs"></i> پنل مدیریت</h1>
            <p class="admin-welcome"><i class="fas fa-hand-wave"></i> خوش آمدید <?php echo htmlspecialchars($admin_name); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> خروج
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert-box error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- آمار اصلی -->
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_users'] ?? 0); ?></div>
            <div class="stat-label">کل کاربران</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-md"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_consultants'] ?? 0); ?></div>
            <div class="stat-label">کل مشاوران</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_reservations'] ?? 0); ?></div>
            <div class="stat-label">کل رزروها</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-comment-dots"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_reviews'] ?? 0); ?></div>
            <div class="stat-label">کل نظرات</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-star-half-alt"></i></div>
            <div class="stat-number"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></div>
            <div class="stat-label">میانگین امتیازات</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-number"><?php echo number_format($stats['pending_reservations'] ?? 0); ?></div>
            <div class="stat-label">در انتظار تایید</div>
        </div>
    </div>

    <!-- آمار جزئی -->
    <div class="admin-stats admin-stats-secondary">
        <div class="stat-card stat-confirmed">
            <div class="stat-number"><?php echo number_format($stats['confirmed_reservations'] ?? 0); ?></div>
            <div class="stat-label"><i class="fas fa-check-circle"></i> تایید شده</div>
        </div>
        <div class="stat-card stat-done">
            <div class="stat-number"><?php echo number_format($stats['done_reservations'] ?? 0); ?></div>
            <div class="stat-label"><i class="fas fa-check-double"></i> انجام شده</div>
        </div>
        <div class="stat-card stat-canceled">
            <div class="stat-number"><?php echo number_format($stats['canceled_reservations'] ?? 0); ?></div>
            <div class="stat-label"><i class="fas fa-times-circle"></i> لغو شده</div>
        </div>
        <div class="stat-card stat-today">
            <div class="stat-number"><?php echo number_format($stats['today_reservations'] ?? 0); ?></div>
            <div class="stat-label"><i class="fas fa-calendar-day"></i> رزرو امروز</div>
        </div>
        <div class="stat-card stat-new-users">
            <div class="stat-number"><?php echo number_format($stats['new_users_this_month'] ?? 0); ?></div>
            <div class="stat-label"><i class="fas fa-user-plus"></i> کاربران جدید این ماه</div>
        </div>
        <div class="stat-card stat-new-reviews">
            <div class="stat-number"><?php echo number_format($stats['new_reviews_this_month'] ?? 0); ?></div>
            <div class="stat-label"><i class="fas fa-star"></i> نظرات جدید این ماه</div>
        </div>
    </div>

    <div class="admin-grid">
        <div class="admin-grid-left">
            <div class="admin-card">
                <div class="admin-card-header">
                    <i class="fas fa-list-ul"></i>
                    <h3>آخرین رزروها</h3>
                </div>
                <?php if (!empty($stats['recent_reservations'])): ?>
                    <div class="table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>کاربر</th>
                                    <th>مشاور</th>
                                    <th>تاریخ</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['recent_reservations'] as $res): ?>
                                    <tr>
                                        <td><i class="fas fa-user"></i> <?php echo htmlspecialchars($res['user_name']); ?></td>
                                        <td><i class="fas fa-user-md"></i> <?php echo htmlspecialchars($res['consultant_name']); ?></td>
                                        <td><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($res['slot_date']); ?></td>
                                        <td>
                                            <?php
                                            $status_map = [
                                                'pending' => ['label' => 'در انتظار', 'class' => 'status-pending'],
                                                'confirmed' => ['label' => 'تایید شده', 'class' => 'status-confirmed'],
                                                'canceled' => ['label' => 'لغو شده', 'class' => 'status-canceled'],
                                                'done' => ['label' => 'انجام شده', 'class' => 'status-done']
                                            ];
                                            $status = $status_map[$res['status']] ?? ['label' => $res['status'], 'class' => ''];
                                            ?>
                                            <span class="status-badge <?php echo $status['class']; ?>">
                                                <?php echo $status['label']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="admin-card-footer">
                        <a href="reservations.php" class="admin-link">
                            مشاهده همه رزروها <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>هیچ رزروی ثبت نشده است.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- مشاوران برتر -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <i class="fas fa-trophy"></i>
                    <h3>مشاوران برتر</h3>
                </div>
                <?php if (!empty($stats['top_consultants'])): ?>
                    <div class="top-consultants">
                        <?php foreach ($stats['top_consultants'] as $consultant): ?>
                            <div class="top-consultant-item">
                                <div class="top-consultant-avatar">
                                    <img src="<?php echo BASE_URL; ?>assets/img/<?php echo htmlspecialchars($consultant['image'] ?? 'default.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($consultant['name']); ?>">
                                </div>
                                <div class="top-consultant-info">
                                    <div class="top-consultant-name"><?php echo htmlspecialchars($consultant['name']); ?></div>
                                    <div class="top-consultant-specialty"><?php echo htmlspecialchars($consultant['specialty']); ?></div>
                                </div>
                                <div class="top-consultant-rating">
                                    <i class="fas fa-star"></i> <?php echo number_format($consultant['avg_rating'], 1); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="admin-card-footer">
                        <a href="consultants.php" class="admin-link">
                            مدیریت مشاوران <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>هیچ مشاوری ثبت نشده است.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- ستون راست -->
        <div class="admin-grid-right">

            <!-- لینک‌های سریع -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <i class="fas fa-link"></i>
                    <h3>مدیریت سریع</h3>
                </div>
                <div class="quick-links">
                    <a href="consultants.php" class="quick-link">
                        <span class="quick-link-icon"><i class="fas fa-user-md"></i></span>
                        مدیریت مشاوران
                        <span class="quick-link-count"><?php echo number_format($stats['total_consultants'] ?? 0); ?></span>
                    </a>
                    <a href="user.php" class="quick-link">
                        <span class="quick-link-icon"><i class="fas fa-users"></i></span>
                        مدیریت کاربران
                        <span class="quick-link-count"><?php echo number_format($stats['total_users'] ?? 0); ?></span>
                    </a>
                    <a href="reservations.php" class="quick-link">
                        <span class="quick-link-icon"><i class="fas fa-calendar-check"></i></span>
                        مدیریت رزروها
                        <span class="quick-link-count"><?php echo number_format($stats['total_reservations'] ?? 0); ?></span>
                    </a>
                    <a href="reviews.php" class="quick-link">
                        <span class="quick-link-icon"><i class="fas fa-comment-dots"></i></span>
                        مدیریت نظرات
                        <span class="quick-link-count"><?php echo number_format($stats['total_reviews'] ?? 0); ?></span>
                    </a>
                </div>
            </div>

            <!-- اطلاعات سیستم -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>اطلاعات سیستم</h3>
                </div>
                <div class="system-info">
                    <div class="system-info-item">
                        <span><i class="fas fa-database"></i> تعداد جدول‌ها</span>
                        <span>5</span>
                    </div>
                    <div class="system-info-item">
                        <span><i class="fas fa-layer-group"></i> مجموع رکوردها</span>
                        <span>
                            <?php 
                            $total = ($stats['total_users'] ?? 0) + 
                                     ($stats['total_consultants'] ?? 0) + 
                                     ($stats['total_reservations'] ?? 0) + 
                                     ($stats['total_reviews'] ?? 0);
                            echo number_format($total);
                            ?>
                        </span>
                    </div>
                    <div class="system-info-item">
                        <span><i class="fas fa-check-circle"></i> وضعیت سیستم</span>
                        <span class="status-active"><i class="fas fa-circle"></i> فعال</span>
                    </div>
                </div>
            </div>

            <!-- لینک سایت -->
            <div class="admin-card site-link-card">
                <a href="<?php echo BASE_URL; ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>مشاهده سایت</span>
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>
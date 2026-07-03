<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'خطا در دریافت اطلاعات کاربر: ' . $e->getMessage();
}

// ========== دریافت رزروهای کاربر ==========
try {
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            c.name as consultant_name,
            c.id as consultant_id,
            c.image as consultant_image,
            a.slot_date,
            a.slot_time
        FROM reservations r
        JOIN available_slots a ON r.slot_id = a.id
        JOIN consultants c ON a.consultant_id = c.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    $reservations = [];
}

// ========== دریافت نظرات کاربر ==========
try {
    $stmt = $pdo->prepare("
        SELECT r.*, c.name as consultant_name, c.id as consultant_id
        FROM reviews r
        JOIN consultants c ON r.consultant_id = c.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
}

// ========== دریافت تعداد کل نظرات کاربر ==========
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_reviews_count = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $total_reviews_count = 0;
}

// ========== دریافت آمار جلسات انجام شده به تفکیک مشاور ==========
try {
    $stmt = $pdo->prepare("
        SELECT 
            a.consultant_id,
            c.name as consultant_name,
            COUNT(r.id) as total_sessions,
            (
                SELECT COUNT(*) 
                FROM reviews 
                WHERE user_id = ? AND consultant_id = a.consultant_id
            ) as total_reviews_for_consultant
        FROM reservations r
        JOIN available_slots a ON r.slot_id = a.id
        JOIN consultants c ON a.consultant_id = c.id
        WHERE r.user_id = ? AND r.status IN ('confirmed', 'done')
        GROUP BY a.consultant_id
        ORDER BY total_sessions DESC
    ");
    $stmt->execute([$user_id, $user_id]);
    $session_stats = $stmt->fetchAll();
} catch (PDOException $e) {
    $session_stats = [];
}

// ========== لغو رزرو ==========
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $reservation_id = (int)$_GET['cancel'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, slot_id, status FROM reservations 
            WHERE id = ? AND user_id = ? AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$reservation_id, $user_id]);
        $reservation = $stmt->fetch();
        
        if ($reservation) {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE reservations SET status = 'canceled' WHERE id = ?");
            $stmt->execute([$reservation_id]);
            
            $stmt = $pdo->prepare("
                UPDATE available_slots 
                SET status = 'available', is_booked = FALSE 
                WHERE id = ?
            ");
            $stmt->execute([$reservation['slot_id']]);
            
            $pdo->commit();
            $success = 'رزرو با موفقیت لغو شد.';
            
            header('Location: dashboard.php?msg=canceled');
            exit;
        } else {
            $error = 'رزرو مورد نظر یافت نشد یا قابل لغو نیست.';
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'خطا در لغو رزرو: ' . $e->getMessage();
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'canceled') {
    $success = 'رزرو با موفقیت لغو شد.';
}

// ========== محاسبه آمار ==========
$total_reservations = count($reservations);
$total_reviews = count($reviews);
$pending_reservations = 0;
$confirmed_reservations = 0;
$done_reservations = 0;
$canceled_reservations = 0;

foreach ($reservations as $res) {
    if ($res['status'] == 'pending') $pending_reservations++;
    elseif ($res['status'] == 'confirmed') $confirmed_reservations++;
    elseif ($res['status'] == 'done') $done_reservations++;
    elseif ($res['status'] == 'canceled') $canceled_reservations++;
}
?>

<?php include 'includes/header.php'; ?>

<div class="dashboard-container">

    <!-- ========== HEADER ========== -->
    <div class="dashboard-header">
        <h1><i class="fas fa-user-circle"></i> پنل کاربری</h1>
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>consultants.php" class="btn-signup" style="padding: 10px 20px; font-size: 14px;">
                <i class="fas fa-users"></i> مشاهده مشاوران
            </a>
            <a href="<?php echo BASE_URL; ?>logout.php" class="btn-signup" style="padding: 10px 20px; font-size: 14px; background: linear-gradient(to right, #ff4d4d, #ff6b6b);">
                <i class="fas fa-sign-out-alt"></i> خروج
            </a>
        </div>
    </div>

    <!-- ========== پیام‌ها ========== -->
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

    <!-- ========== کارت‌های اطلاعاتی ========== -->
    <div class="dashboard-cards">
        <!-- کارت اطلاعات شخصی -->
        <div class="dashboard-card">
            <div class="card-title"><i class="fas fa-id-card"></i> اطلاعات شخصی</div>
            <div class="info-grid">
                <div class="info-item">
                    <strong><i class="fas fa-user"></i> نام:</strong> 
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-envelope"></i> ایمیل:</strong> 
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-user-tag"></i> نام کاربری:</strong> 
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-id-badge"></i> شماره دانشجویی:</strong> 
                    <span><?php echo htmlspecialchars($user['student_id']); ?></span>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-phone"></i> موبایل:</strong> 
                    <span><?php echo htmlspecialchars($user['mobile']); ?></span>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-calendar-alt"></i> سال تولد:</strong> 
                    <span><?php echo htmlspecialchars($user['birth_year']); ?></span>
                </div>
                <?php if ($user['role'] == 'admin'): ?>
                    <div class="info-item">
                        <strong><i class="fas fa-crown"></i> نقش:</strong> 
                        <span class="role-badge">مدیر</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- کارت آمار -->
        <div class="dashboard-card">
            <div class="card-title"><i class="fas fa-chart-pie"></i> آمار شما</div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number green">
                        <i class="fas fa-calendar-check"></i> <?php echo $total_reservations; ?>
                    </div>
                    <div class="stat-label">کل رزروها</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number gold">
                        <i class="fas fa-star"></i> <?php echo $total_reviews_count; ?>
                    </div>
                    <div class="stat-label">نظرات ثبت شده</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number yellow">
                        <i class="fas fa-clock"></i> <?php echo $pending_reservations; ?>
                    </div>
                    <div class="stat-label">در انتظار تایید</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number blue">
                        <i class="fas fa-check-double"></i> <?php echo $done_reservations; ?>
                    </div>
                    <div class="stat-label">انجام شده</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== کارت آمار جلسات و نظرات به تفکیک مشاور ========== -->
    <?php if (count($session_stats) > 0): ?>
        <div class="dashboard-card" style="margin-bottom: 30px;">
            <div class="card-title"><i class="fas fa-chart-bar"></i> وضعیت نظرات شما به تفکیک مشاور</div>
            <div class="table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>مشاور</th>
                            <th>جلسات انجام شده</th>
                            <th>نظرات ثبت شده</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($session_stats as $stat): ?>
                            <?php 
                            $can_review = $stat['total_reviews_for_consultant'] < $stat['total_sessions'];
                            $remaining = $stat['total_sessions'] - $stat['total_reviews_for_consultant'];
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>consultant.php?id=<?php echo $stat['consultant_id']; ?>" class="consultant-link">
                                        <?php echo htmlspecialchars($stat['consultant_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo $stat['total_sessions']; ?></td>
                                <td><?php echo $stat['total_reviews_for_consultant']; ?></td>
                                <td>
                                    <?php if ($can_review): ?>
                                        <span class="status-badge status-confirmed" style="background:#4CAF50; color:#fff;">
                                            <i class="fas fa-check-circle"></i> قابل ثبت نظر (<?php echo $remaining; ?> مورد)
                                        </span>
                                    <?php elseif ($stat['total_sessions'] > 0): ?>
                                        <span class="status-badge status-done" style="background:#2196F3; color:#fff;">
                                            <i class="fas fa-check-double"></i> کامل شده
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending" style="background:#ffc107; color:#000;">
                                            <i class="fas fa-clock"></i> در انتظار
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="text-align:center; margin-top:15px; font-size:13px; opacity:0.7;">
                <i class="fas fa-info-circle"></i> 
                برای هر جلسه انجام شده، می‌توانید یک نظر ثبت کنید.
            </div>
        </div>
    <?php endif; ?>

    <!-- ========== لیست رزروها ========== -->
    <div class="dashboard-card" style="margin-bottom: 30px;">
        <div class="card-title"><i class="fas fa-list-ul"></i> لیست رزروهای شما</div>
        
        <?php if (count($reservations) > 0): ?>
            <div class="table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>شماره پیگیری</th>
                            <th>مشاور</th>
                            <th>تاریخ</th>
                            <th>ساعت</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><span class="tracking-code"><?php echo htmlspecialchars($reservation['tracking_code']); ?></span></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>consultant.php?id=<?php echo $reservation['consultant_id']; ?>" class="consultant-link">
                                        <?php echo htmlspecialchars($reservation['consultant_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($reservation['slot_date']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['slot_time']); ?></td>
                                <td>
                                    <?php
                                    $status_map = [
                                        'pending' => ['label' => 'در انتظار', 'class' => 'status-pending'],
                                        'confirmed' => ['label' => 'تایید شده', 'class' => 'status-confirmed'],
                                        'canceled' => ['label' => 'لغو شده', 'class' => 'status-canceled'],
                                        'done' => ['label' => 'انجام شده', 'class' => 'status-done']
                                    ];
                                    $status = $status_map[$reservation['status']] ?? ['label' => $reservation['status'], 'class' => ''];
                                    ?>
                                    <span class="status-badge <?php echo $status['class']; ?>">
                                        <i class="fas <?php 
                                            echo $reservation['status'] == 'pending' ? 'fa-clock' : 
                                                ($reservation['status'] == 'confirmed' ? 'fa-check-circle' : 
                                                ($reservation['status'] == 'done' ? 'fa-check-double' : 'fa-times-circle')); 
                                        ?>"></i>
                                        <?php echo $status['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($reservation['status'] == 'pending' || $reservation['status'] == 'confirmed'): ?>
                                        <a href="dashboard.php?cancel=<?php echo $reservation['id']; ?>" 
                                           onclick="return confirm('آیا از لغو این رزرو اطمینان دارید؟')"
                                           class="btn-cancel">
                                            <i class="fas fa-times-circle"></i> لغو
                                        </a>
                                    <?php else: ?>
                                        <span class="empty-text">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>شما هیچ رزروی ندارید.</p>
                <a href="<?php echo BASE_URL; ?>consultants.php" class="consultant-link">
                    <i class="fas fa-arrow-left"></i> مشاهده مشاوران و رزرو نوبت
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========== لیست نظرات ========== -->
    <div class="dashboard-card">
        <div class="card-title"><i class="fas fa-comments"></i> نظرات ثبت شده شما</div>
        
        <?php if (count($reviews) > 0): ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div>
                                <a href="<?php echo BASE_URL; ?>consultant.php?id=<?php echo $review['consultant_id']; ?>" class="consultant-name">
                                    <i class="fas fa-user-md"></i> <?php echo htmlspecialchars($review['consultant_name']); ?>
                                </a>
                                <span class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <span style="font-size:12px; opacity:0.6;">(<?php echo $review['rating']; ?>/5)</span>
                                </span>
                            </div>
                            <span class="review-date">
                                <i class="far fa-calendar-alt"></i> <?php echo date('Y/m/d', strtotime($review['created_at'])); ?>
                            </span>
                        </div>
                        <div class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-comment-slash"></i>
                <p>شما هیچ نظری ثبت نکرده‌اید.</p>
                <span style="font-size: 13px; opacity: 0.6;">
                    <i class="fas fa-info-circle"></i> 
                    پس از هر جلسه مشاوره می‌توانید یک نظر ثبت کنید.
                </span>
                <br>
                <a href="<?php echo BASE_URL; ?>consultants.php" class="consultant-link" style="margin-top:10px; display:inline-block;">
                    <i class="fas fa-arrow-left"></i> مشاهده مشاوران
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========== لینک‌های سریع ========== -->
    <div class="quick-links">
        <a href="<?php echo BASE_URL; ?>consultants.php" class="btn-link primary">
            <i class="fas fa-users"></i> مشاهده همه مشاوران
        </a>
        <a href="<?php echo BASE_URL; ?>index.php" class="btn-link secondary">
            <i class="fas fa-home"></i> صفحه اصلی
        </a>
        <?php if ($user['role'] == 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>admin/" class="btn-link danger">
                <i class="fas fa-cog"></i> پنل مدیریت
            </a>
        <?php endif; ?>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
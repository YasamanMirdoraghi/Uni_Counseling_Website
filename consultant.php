<?php
require_once 'includes/config.php';

$consultant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($consultant_id == 0) {
    header('Location: consultants.php');
    exit;
}

try {
    // دریافت اطلاعات مشاور
    $stmt = $pdo->prepare("SELECT * FROM consultants WHERE id = ?");
    $stmt->execute([$consultant_id]);
    $consultant = $stmt->fetch();

    if (!$consultant) {
        header('Location: consultants.php');
        exit;
    }

    // دریافت زمان‌های موجود
    $stmt_slots = $pdo->prepare("
        SELECT id, slot_date, slot_time, status 
        FROM available_slots 
        WHERE consultant_id = ? AND status = 'available' AND slot_date >= CURDATE()
        ORDER BY slot_date, slot_time
        LIMIT 10
    ");
    $stmt_slots->execute([$consultant_id]);
    $available_slots = $stmt_slots->fetchAll();

    // ========== نظرات با Pagination ==========
    $per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $per_page;

    // تعداد کل نظرات
    $stmt_count = $pdo->prepare("SELECT COUNT(*) as total FROM reviews WHERE consultant_id = ?");
    $stmt_count->execute([$consultant_id]);
    $total_reviews = $stmt_count->fetch()['total'];
    $total_pages = ceil($total_reviews / $per_page);

    // ========== دریافت نظرات با اصلاح LIMIT و OFFSET ==========
    $stmt_reviews = $pdo->prepare("
        SELECT r.*, u.full_name 
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.consultant_id = ?
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt_reviews->bindValue(1, $consultant_id, PDO::PARAM_INT);
    $stmt_reviews->bindValue(2, $per_page, PDO::PARAM_INT);
    $stmt_reviews->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt_reviews->execute();
    $reviews = $stmt_reviews->fetchAll();

    // ==========  تغییر: کاربر فقط چک میشه که حداقل یک جلسه داشته باشه ==========
    $user_has_reservation = false;
    $can_review = false;
    $total_done_sessions = 0;
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // چند جلسه انجام شده؟
        $stmt_check = $pdo->prepare("
            SELECT COUNT(*) as total_done
            FROM reservations r
            JOIN available_slots a ON r.slot_id = a.id
            WHERE r.user_id = ? AND a.consultant_id = ? AND r.status IN ('confirmed', 'done')
        ");
        $stmt_check->execute([$user_id, $consultant_id]);
        $total_done_sessions = $stmt_check->fetch()['total_done'];
        
        //  اگر حداقل یک جلسه داشته باشه، میتونه نظر بده
        $can_review = ($total_done_sessions > 0);
        $user_has_reservation = ($total_done_sessions > 0);
    }

    $specialties = array_map('trim', explode('،', $consultant['specialty']));

} catch (PDOException $e) {
    die("خطا: " . $e->getMessage());
}
?>

<?php include 'includes/header.php'; ?>

<div class="consultant-profile-container">

    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-image">
                <div class="profile-gradient">
                    <img src="<?php echo BASE_URL; ?>assets/img/<?php echo htmlspecialchars($consultant['image'] ?? 'default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($consultant['name']); ?>">
                </div>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($consultant['name']); ?></h1>
                <span class="consultant-role"><?php echo htmlspecialchars($consultant['degree']); ?></span>
                <p class="consultant-description"><?php echo nl2br(htmlspecialchars($consultant['description'])); ?></p>
                
                <div class="rating-badge">
                    <div class="stars">
                        <?php 
                        $rating = round($consultant['avg_rating']);
                        for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $rating): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span class="rating-number"><?php echo number_format($consultant['avg_rating'], 1); ?></span>
                    </div>
                    <span class="review-count"><i class="fas fa-comment"></i> <?php echo $total_reviews; ?> نظر</span>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn-signup" onclick="window.location.href='<?php echo BASE_URL; ?>reserve.php?consultant_id=<?php echo $consultant_id; ?>'">
                        <i class="fas fa-calendar-check"></i> رزرو وقت مشاوره
                    </button>
                <?php else: ?>
                    <button class="btn-signup outline" onclick="window.location.href='<?php echo BASE_URL; ?>login.php'">
                        <i class="fas fa-sign-in-alt"></i> برای رزرو وارد شوید
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-body">
            <div>
                <h2 class="section-title"><i class="fas fa-tags"></i> تخصص‌ها و حوزه‌های مشاوره</h2>
                <ul class="consultant-expertise">
                    <?php foreach ($specialties as $specialty): ?>
                        <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($specialty); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h2 class="section-title"><i class="fas fa-clock"></i> ساعات قابل رزرو</h2>
                
                <?php if (count($available_slots) > 0): ?>
                    <div class="schedule-table-wrap">
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>تاریخ</th>
                                    <th>ساعت</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($available_slots as $slot): ?>
                                    <tr>
                                        <td><span class="slot-date"><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($slot['slot_date']); ?></span></td>
                                        <td><span class="slot-time"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($slot['slot_time']); ?></span></td>
                                        <td><span class="slot-status"><i class="fas fa-check-circle"></i> آزاد</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="schedule-btn-wrap">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="btn-signup" onclick="window.location.href='<?php echo BASE_URL; ?>reserve.php?consultant_id=<?php echo $consultant_id; ?>'">
                                <i class="fas fa-calendar-plus"></i> رزرو نوبت
                            </button>
                        <?php else: ?>
                            <button class="btn-signup outline" onclick="window.location.href='<?php echo BASE_URL; ?>login.php'">
                                <i class="fas fa-sign-in-alt"></i> برای رزرو وارد شوید
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert-box info">
                        <i class="fas fa-info-circle"></i> در حال حاضر زمانی برای رزرو موجود نیست.
                    </div>
                <?php endif; ?>
            </div>
            
            <div>
                <h2 class="section-title"><i class="fas fa-comments"></i> نظرات دانشجویان</h2>

                <!-- ==========  فرم نظر - بدون محدودیت ========== -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($can_review): ?>
                        <div class="alert-box info" style="margin-bottom:15px;">
                            <i class="fas fa-info-circle"></i> 
                            شما <?php echo $total_done_sessions; ?> جلسه انجام داده‌اید.
                            <br>
                            <strong> می‌توانید هر تعداد نظر که میخواهید ثبت کنید.</strong>
                        </div>
                        <form id="reviewForm" class="review-form" method="POST" action="<?php echo BASE_URL; ?>ajax/submit_review.php">
                            <input type="hidden" name="consultant_id" value="<?php echo $consultant_id; ?>">
                            
                            <div class="form-group">
                                <label for="reviewRating"><i class="fas fa-star"></i> امتیاز شما</label>
                                <select id="reviewRating" name="rating" required>
                                    <option value="">انتخاب کنید</option>
                                    <option value="5">⭐⭐⭐⭐⭐ 5 - عالی</option>
                                    <option value="4">⭐⭐⭐⭐☆ 4 - خوب</option>
                                    <option value="3">⭐⭐⭐☆☆ 3 - متوسط</option>
                                    <option value="2">⭐⭐☆☆☆ 2 - ضعیف</option>
                                    <option value="1">⭐☆☆☆☆ 1 - خیلی ضعیف</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="reviewText"><i class="fas fa-pen"></i> نظر شما</label>
                                <textarea id="reviewText" name="review_text" rows="4" placeholder="نظر خود را بنویسید..." required></textarea>
                            </div>

                            <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> ثبت نظر</button>
                        </form>
                        <div id="reviewMessage" class="review-message"></div>
                    <?php else: ?>
                        <div class="alert-box info">
                            <i class="fas fa-info-circle"></i> برای ثبت نظر و امتیاز، ابتدا باید یک جلسه مشاوره با این مشاور داشته باشید.
                            <br>
                            <a href="<?php echo BASE_URL; ?>reserve.php?consultant_id=<?php echo $consultant_id; ?>"><i class="fas fa-calendar-plus"></i> رزرو نوبت مشاوره</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert-box warning">
                        <i class="fas fa-lock"></i> برای ثبت نظر، ابتدا <a href="<?php echo BASE_URL; ?>login.php">وارد شوید</a>.
                    </div>
                <?php endif; ?>

                <!-- ========== نمایش نظرات ========== -->
                <div id="reviewsContainer" class="reviews-container">
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <p class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></p>
                                <div class="review-meta">
                                    <span class="review-author"><i class="fas fa-user"></i> <?php echo htmlspecialchars($review['full_name']); ?></span>
                                    <span class="review-rating">
                                        <?php 
                                        $r = $review['rating'];
                                        for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $r): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </span>
                                </div>
                                <span class="review-date"><i class="far fa-calendar-alt"></i> <?php echo date('Y/m/d', strtotime($review['created_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-reviews">
                            <i class="far fa-comment-dots"></i>
                            <p>هنوز نظری برای این مشاور ثبت نشده است.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ========== Pagination ========== -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?id=<?php echo $consultant_id; ?>&page=<?php echo $page - 1; ?>" class="pagination-link">
                                <i class="fas fa-chevron-right"></i> قبلی
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?id=<?php echo $consultant_id; ?>&page=<?php echo $i; ?>" 
                               class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?id=<?php echo $consultant_id; ?>&page=<?php echo $page + 1; ?>" class="pagination-link">
                                بعدی <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const form = document.getElementById('reviewForm');
    const messageDiv = document.getElementById('reviewMessage');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('<?php echo BASE_URL; ?>ajax/submit_review.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = `
                        <div class="alert-box success">
                            <i class="fas fa-check-circle"></i> ${data.message}
                        </div>
                    `;
                    form.reset();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    messageDiv.innerHTML = `
                        <div class="alert-box danger">
                            <i class="fas fa-exclamation-circle"></i> ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                messageDiv.innerHTML = `
                    <div class="alert-box danger">
                        <i class="fas fa-times-circle"></i> خطا در ارتباط با سرور
                    </div>
                `;
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
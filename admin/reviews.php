<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';
$reviews = [];

// حذف نظر
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        $success = ' نظر با موفقیت حذف شد.';
    } catch (PDOException $e) {
        $error = 'خطا در حذف نظر: ' . $e->getMessage();
    }
}

// دریافت لیست نظرات
try {
    $stmt = $pdo->query("
        SELECT 
            r.*,
            u.full_name as user_name,
            u.email as user_email,
            c.name as consultant_name,
            c.id as consultant_id
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN consultants c ON r.consultant_id = c.id
        ORDER BY r.created_at DESC
    ");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'خطا در دریافت لیست نظرات: ' . $e->getMessage();
}

// آمار
$total_reviews = count($reviews);
$avg_rating = 0;
$rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

foreach ($reviews as $review) {
    $rating = (int)$review['rating'];
    if (isset($rating_counts[$rating])) {
        $rating_counts[$rating]++;
    }
}

if ($total_reviews > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avg_rating = round($sum / $total_reviews, 1);
}
?>

<?php include '../includes/header.php'; ?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1><i class="fas fa-comment-dots"></i> مدیریت نظرات</h1>
            <p class="admin-page-subtitle"><i class="fas fa-info-circle"></i> مدیریت نظرات و امتیازات ثبت شده توسط کاربران</p>
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

    <!-- آمار امتیازات -->
    <div class="rating-stats">
        <div class="rating-stat total">
            <span class="count"><i class="fas fa-list-ul"></i> <?php echo $total_reviews; ?></span>
            <span class="label">کل نظرات</span>
        </div>
        <div class="rating-stat avg">
            <span class="count"><i class="fas fa-star-half-alt"></i> <?php echo number_format($avg_rating, 1); ?></span>
            <span class="label">میانگین امتیازات</span>
        </div>
        <div class="rating-stat star5">
            <span class="count"><i class="fas fa-star"></i> <?php echo $rating_counts[5] ?? 0; ?></span>
            <span class="label">۵ ستاره</span>
        </div>
        <div class="rating-stat star4">
            <span class="count"><i class="fas fa-star"></i> <?php echo $rating_counts[4] ?? 0; ?></span>
            <span class="label">۴ ستاره</span>
        </div>
        <div class="rating-stat star3">
            <span class="count"><i class="fas fa-star"></i> <?php echo $rating_counts[3] ?? 0; ?></span>
            <span class="label">۳ ستاره</span>
        </div>
        <div class="rating-stat star2">
            <span class="count"><i class="fas fa-star"></i> <?php echo $rating_counts[2] ?? 0; ?></span>
            <span class="label">۲ ستاره</span>
        </div>
        <div class="rating-stat star1">
            <span class="count"><i class="fas fa-star"></i> <?php echo $rating_counts[1] ?? 0; ?></span>
            <span class="label">۱ ستاره</span>
        </div>
    </div>

    <div class="admin-toolbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchReview" placeholder="جستجوی کاربر یا مشاور..." onkeyup="filterTable()">
        </div>
        <div class="filter-box">
            <i class="fas fa-filter"></i>
            <select id="filterRating" onchange="filterTable()">
                <option value="all">همه امتیازات</option>
                <option value="5"><i class="fas fa-star"></i> ۵ ستاره</option>
                <option value="4"><i class="fas fa-star"></i> ۴ ستاره</option>
                <option value="3"><i class="fas fa-star"></i> ۳ ستاره</option>
                <option value="2"><i class="fas fa-star"></i> ۲ ستاره</option>
                <option value="1"><i class="fas fa-star"></i> ۱ ستاره</option>
            </select>
        </div>
        <div class="toolbar-info">
            <span>تعداد نظرات: <strong><?php echo number_format($total_reviews); ?></strong></span>
        </div>
    </div>

    <div class="admin-table-container">
        
        <?php if (count($reviews) > 0): ?>
            <div class="table-wrap">
                <table class="admin-table" id="reviewTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><i class="fas fa-user"></i> کاربر</th>
                            <th><i class="fas fa-user-md"></i> مشاور</th>
                            <th><i class="fas fa-star"></i> امتیاز</th>
                            <th><i class="fas fa-comment"></i> نظر</th>
                            <th><i class="fas fa-calendar-plus"></i> تاریخ ثبت</th>
                            <th><i class="fas fa-tools"></i> عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $index => $review): ?>
                            <tr class="review-row" 
                                data-user="<?php echo strtolower($review['user_name']); ?>"
                                data-consultant="<?php echo strtolower($review['consultant_name']); ?>"
                                data-rating="<?php echo $review['rating']; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo mb_substr($review['user_name'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <div class="user-name"><?php echo htmlspecialchars($review['user_name']); ?></div>
                                            <div class="user-email"><?php echo htmlspecialchars($review['user_email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>consultant.php?id=<?php echo $review['consultant_id']; ?>" 
                                       class="consultant-link" target="_blank">
                                        <?php echo htmlspecialchars($review['consultant_name']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="review-rating star<?php echo $review['rating']; ?>">
                                        <?php 
                                        $full = $review['rating'];
                                        $empty = 5 - $full;
                                        echo str_repeat('<i class="fas fa-star"></i>', $full);
                                        echo str_repeat('<i class="far fa-star"></i>', $empty);
                                        ?>
                                        <span class="rating-number">(<?php echo $review['rating']; ?>)</span>
                                    </span>
                                </td>
                                <td>
                                    <div class="review-text">
                                        <?php 
                                        $text = htmlspecialchars($review['review_text']);
                                        if (mb_strlen($text) > 80) {
                                            echo mb_substr($text, 0, 80) . '...';
                                        } else {
                                            echo $text;
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="date-created"><?php echo date('Y/m/d H:i', strtotime($review['created_at'])); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="reviews.php?delete=<?php echo $review['id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('آیا از حذف این نظر اطمینان دارید؟')">
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
                <i class="fas fa-comment-slash"></i>
                <h3>هیچ نظری ثبت نشده است</h3>
                <p>نظرات پس از ثبت توسط کاربران در این لیست ظاهر می‌شوند.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function filterTable() {
    const input = document.getElementById('searchReview');
    const filter = input.value.toLowerCase();
    const ratingFilter = document.getElementById('filterRating').value;
    const rows = document.querySelectorAll('.review-row');
    
    rows.forEach(row => {
        const user = row.getAttribute('data-user') || '';
        const consultant = row.getAttribute('data-consultant') || '';
        const rating = row.getAttribute('data-rating') || '';
        
        const matchSearch = user.includes(filter) || consultant.includes(filter);
        const matchRating = ratingFilter === 'all' || rating === ratingFilter;
        
        if (matchSearch && matchRating) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
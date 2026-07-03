<?php
require_once 'includes/config.php';

try {
    $stmt = $pdo->query("
        SELECT id, name, degree, specialty, description, image, avg_rating 
        FROM consultants 
        ORDER BY avg_rating DESC, name ASC
    ");
    $consultants = $stmt->fetchAll();
} catch (PDOException $e) {
    $consultants = [];
    $error = 'خطا در دریافت اطلاعات مشاوران: ' . $e->getMessage();
}
?>

<?php include 'includes/header.php'; ?>

<section class="hero flex-container counselors-hero">
    <div class="hero-text counselors-hero-text">
        <h1>مشاوران <span>مرکز مشاوره دانشجویی</span></h1>
        <p>فرصتی بی‌نظیر برای آشنایی عمیق با تیم حرفه‌ای و کارآزموده‌ی مشاوران مرکز ما فراهم شده است. هر یک از مشاوران ما با بهره‌مندی از سال‌ها تجربه‌ی تخصصی در حوزه‌های گوناگون روان‌شناسی، تحصیلی، شغلی، خانواده و مهارت‌های زندگی، آماده‌ی همراهی و یاری‌رسانی به شما عزیزان هستند.

ما کاملاً درک می‌کنیم که هر فردی با دنیای منحصربه‌فرد خود، چالش‌های خاص و اهداف متفاوتی روبه‌روست. به همین دلیل، در این مسیر هیچ رویکرد یکسانی برای همه وجود ندارد. شما می‌توانید با بررسی دقیق پروفایل هر مشاور، شامل زمینه‌های تخصصی، مدرک‌های تحصیلی، گواهی‌های معتبر و رویکردهای درمانی یا آموزشی آن‌ها، بهترین و متناسب‌ترین گزینه را برای نیازهای خود انتخاب کنید. </p>
    </div>
    <img src="<?php echo BASE_URL; ?>assets/img/one.png" alt="مشاوران مرکز">
</section>

</div> 

<section class="consultants consultants-page">
    <h2>لیست مشاوران مرکز</h2>
    <p class="consultants-intro">
        هر کدام از مشاوران زیر در حوزه‌ای مشخص تخصص دارند.<br> روی پروفایل هر مشاور کلیک کنید
        تا توضیحات بیشتر، سوابق و ساعات حضور را مشاهده کنید.
    </p>

    <?php if (isset($error)): ?>
        <div style="color:red; background:#ffe0e0; padding:15px; border-radius:8px; margin-bottom:20px; text-align:center;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="consultants-container">

        <?php if (count($consultants) > 0): ?>
            <?php foreach ($consultants as $consultant): ?>
                <div class="consultant-card">
                    <img src="<?php echo BASE_URL; ?>assets/img/<?php echo htmlspecialchars($consultant['image'] ?? 'default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($consultant['name']); ?>">
                    <h3><?php echo htmlspecialchars($consultant['name']); ?></h3>
                    <span class="consultant-role"><?php echo htmlspecialchars($consultant['degree']); ?></span>
                    <p class="consultant-desc">
                        <?php echo htmlspecialchars(mb_substr($consultant['description'] ?? '', 0, 120)); ?>...
                    </p>
                    
                    <?php
                    $tags = array_slice(explode('،', $consultant['specialty']), 0, 3);
                    ?>
                    <ul class="consultant-tags">
                        <?php foreach ($tags as $tag): ?>
                            <li><?php echo htmlspecialchars(trim($tag)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                
                    <div class="consultant-footer">
                        <div class="star-rating">
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
                        <a href="<?php echo BASE_URL; ?>consultant.php?id=<?php echo $consultant['id']; ?>" 
                           class="consultant-link">
                            مشاهده پروفایل
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1/-1; color: #fff; font-size: 20px; padding: 50px 0;">
                 هیچ مشاوری ثبت نشده است.
            </p>
        <?php endif; ?>

    </div>
</section>

<section class="about-cta">
    <h2>برای رزرو وقت با هر مشاور آماده‌ای؟</h2>
    <p>
        کافی است مشاور مورد نظر خود را انتخاب کنید و از طریق سامانه نوبت‌دهی، زمان مناسب را رزرو کنید.
        در صورت نیاز می‌توانید قبل از رزرو، توضیحات هر مشاور را به دقت مطالعه کنید.
    </p>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <button class="btn-signup" onclick="window.location.href='<?php echo BASE_URL; ?>consultants.php'">
            مشاهده مشاوران
        </button>
    <?php else: ?>
        <button class="btn-signup" onclick="window.location.href='<?php echo BASE_URL; ?>login.php'">
            ابتدا وارد شوید
        </button>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
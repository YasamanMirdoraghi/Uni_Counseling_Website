<?php
require_once 'includes/config.php';

try {
    $stmt = $pdo->query("
        SELECT id, name, specialty, image, avg_rating 
        FROM consultants 
        ORDER BY avg_rating DESC, id DESC 
        LIMIT 6
    ");
    $consultants = $stmt->fetchAll();
} catch (PDOException $e) {
    $consultants = [];
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM consultants");
    $total_consultants = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $total_consultants = 0;
}

?>

<?php include 'includes/header.php'; ?>
<section class="hero flex-container">
    <div class="hero-text">
        <h1>سلامت روان شما <br><span>اولویت </span> ماست</h1>
        <p>
            مرکز مشاوره دانشجویی دانشگاه با همراهی مشاوران متخصص <br>
            به شما کمک می‌کند با استرس‌ها، چالش‌های تحصیلی<br>
            و مسائل فردی بهتر مواجه شوید.
        </p>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="<?php echo BASE_URL; ?>signup.php" class="btn-signup" style="display: inline-block; margin-top: 20px; padding: 15px 40px; font-size: 18px;">
                شروع کنید
            </a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>consultants.php" class="btn-signup" style="display: inline-block; margin-top: 20px; padding: 15px 40px; font-size: 18px;">
                مشاهده مشاوران
            </a>
        <?php endif; ?>
    </div>

    <div class="hero-slider">
        <div class="slides">
            <div class="slide active">
                <img src="<?php echo BASE_URL; ?>assets/img/one.png" alt="تصویر اول اسلایدر">
            </div>
            <div class="slide">
                <img src="<?php echo BASE_URL; ?>assets/img/two.png" alt="تصویر دوم اسلایدر">
            </div>
            <div class="slide">
                <img src="<?php echo BASE_URL; ?>assets/img/one.png" alt="تصویر سوم اسلایدر">
            </div>
        </div>
        <a class="prev" onclick="changeSlide(-1)">&#10094;</a>
        <a class="next" onclick="changeSlide(1)">&#10095;</a>
    </div>
</section>

</div> 

<section class="why-us flex-container">
    <img src="<?php echo BASE_URL; ?>assets/img/two.png" alt="تصویر جلسه مشاوره">
    <div class="why-text">
        <h2>چرا <span>مشاوره</span> حرفه‌ای ما را برای خود انتخاب کنید؟</h2>
        <p>
            مرکز مشاوره دانشجویی دانشگاه با هدف ارتقای سلامت روان، بهبود عملکرد تحصیلی 
            و کمک به دانشجویان در چالش‌های فردی و اجتماعی ایجاد شده است.
        </p>
        <p style="margin-top: 20px; font-size: 20px;">
            <strong><?php echo number_format($total_consultants); ?></strong> مشاور متخصص در کنار شما هستند.
        </p>
    </div>
</section>

<section class="services">
    <h2>این موارد <span>خدماتی</span> هستند که <br> ما به شما ارائه می‌کنیم</h2>

    <div class="cards-container">
        <div class="card">
            <div class="icon-wrapper">
                <i class="fas fa-briefcase"></i>
            </div>
            <h3>مشاوره شغلی</h3>
            <p>راهنمایی در انتخاب مسیر شغلی، تحلیل توانمندی‌ها و برنامه‌ریزی برای آینده حرفه‌ای</p>
            <a href="<?php echo BASE_URL; ?>consultants.php">مشاهده مشاوران <i class="fas fa-arrow-left"></i></a>
        </div>

        <div class="card">
            <div class="icon-wrapper">
                <i class="fas fa-brain"></i>
            </div>
            <h3>مشاوره روانشناسی</h3>
            <p>درمان اضطراب، افسردگی، استرس امتحان و مشکلات سازگاری با محیط دانشگاه</p>
            <a href="<?php echo BASE_URL; ?>consultants.php">مشاهده مشاوران <i class="fas fa-arrow-left"></i></a>
        </div>

        <div class="card">
            <div class="icon-wrapper">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3>مشاوره تحصیلی</h3>
            <p>برنامه‌ریزی درسی، مدیریت زمان، انتخاب واحد و افزایش انگیزه تحصیلی</p>
            <a href="<?php echo BASE_URL; ?>consultants.php">مشاهده مشاوران <i class="fas fa-arrow-left"></i></a>
        </div>
    </div>
</section>

<section class="consultants">
    <h2>مشاوران</h2>

    <div class="consultants-container">
        <?php if (count($consultants) > 0): ?>
            <?php foreach ($consultants as $consultant): ?>
                <div class="consultant-card">
                    <img src="<?php echo BASE_URL; ?>assets/img/<?php echo htmlspecialchars($consultant['image'] ?? 'default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($consultant['name']); ?>">
                    <h3><?php echo htmlspecialchars($consultant['name']); ?></h3>
                    <p><?php echo htmlspecialchars($consultant['specialty']); ?></p>
                    <span><i class="fas fa-star"></i> <?php echo number_format($consultant['avg_rating'], 1); ?> / 5</span>
                    <a href="<?php echo BASE_URL; ?>consultant.php?id=<?php echo $consultant['id']; ?>" 
                       class="consultant-link" style="display: block; margin-top: 10px;">
                        مشاهده پروفایل <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1/-1; color: #fff; font-size: 20px;">
                هنوز مشاوری ثبت نشده است.
            </p>
        <?php endif; ?>
    </div>

    <?php if ($total_consultants > 6): ?>
        <div style="text-align: center; margin-top: 50px;">
            <a href="<?php echo BASE_URL; ?>consultants.php" class="btn-signup" style="padding: 12px 35px;">
                مشاهده همه مشاوران (<?php echo number_format($total_consultants); ?>)
            </a>
        </div>
    <?php endif; ?>
    
</section>

<section class="footer-section">
    <div class="cta-box">
        <h2>نیاز به مشاوره داری؟</h2>
        <p>همین حالا وقت مشاوره خودت را با مشاوران متخصص دانشگاه رزرو کن</p>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="reserve-btn" onclick="window.location.href='<?php echo BASE_URL; ?>consultants.php'">
                رزرو وقت مشاوره
            </button>
        <?php else: ?>
            <button class="reserve-btn" onclick="window.location.href='<?php echo BASE_URL; ?>login.php'">
                ابتدا وارد شوید
            </button>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
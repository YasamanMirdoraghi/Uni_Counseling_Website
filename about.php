<?php
require_once 'includes/config.php';
?>

<?php include 'includes/header.php'; ?>

<section class="hero flex-container about-hero">
    <div class="hero-text about-hero-text">
        <h1>
            درباره <span>مرکز مشاوره دانشجویی</span>
        </h1>
        <p>
            ما اینجا هستیم تا در مسیر تحصیلی، فردی و شغلی همراهت باشیم.
            مرکز مشاوره دانشجویی دانشگاه با تیمی از مشاوران متخصص، فضایی امن و محرمانه
            برای گفت‌وگو و دریافت راهنمایی فراهم کرده است.
        </p>
    </div>
    <img src="<?php echo BASE_URL; ?>assets/img/one.png" alt="مرکز مشاوره">
</section>

</div> 

<section class="about-section flex-container">
    <img src="<?php echo BASE_URL; ?>assets/img/Group 4.png" alt="معرفی مرکز مشاوره" class="about-image">
    <div class="about-text">
        <h2>ماموریت ما</h2>
        <p>
            مرکز مشاوره دانشجویی با هدف ارتقای سلامت روان، بهبود عملکرد تحصیلی
            و کمک به دانشجویان در مدیریت چالش‌های فردی، خانوادگی و اجتماعی تأسیس شده است.
            ما باور داریم که سلامت روان بخش جدانشدنی از موفقیت تحصیلی و کیفیت زندگی است.
        </p>

        <h2>چه کمکی به تو می‌کنیم؟</h2>
        <p>
            در این مرکز می‌توانی درباره موضوعاتی مثل استرس امتحان، اضطراب، افسردگی،
            مشکلات خواب، سردرگمی در انتخاب رشته یا شغل، مهارت‌های ارتباطی و حتی مسائل
            خانوادگی صحبت کنی. مشاوران ما در کنار تو هستند تا تصمیم‌های آگاهانه‌تری بگیری
            و احساس بهتری نسبت به خودت و آینده‌ات داشته باشی.
        </p>
    </div>
</section>

<section class="why-us about-why flex-container">
    <div class="why-text">
        <h2>چرا <span>مرکز مشاوره دانشگاه</span>؟</h2>
        <p>
            • حفظ کامل محرمانگی اطلاعات و گفت‌وگوها<br>
            • حضور مشاوران متخصص در حوزه‌های تحصیلی، روانشناسی و شغلی<br>
            • دسترسی آسان برای تمام دانشجویان دانشگاه<br>
            • امکان رزرو وقت مشاوره حضوری و آنلاین<br>
            • فضای دوستانه و بدون قضاوت برای بیان احساسات و دغدغه‌ها
        </p>
    </div>
    <img src="<?php echo BASE_URL; ?>assets/img/two.png" alt="چرا ما">
</section>

<section class="about-info">
    <h2>اطلاعات مرکز مشاوره</h2>
    <div class="about-info-grid">
        <div class="info-item">
            <h3>آدرس مرکز</h3>
            <p>دانشگاه جندی شاپور دزفول، ساختمان خدمات دانشجویی، طبقه دوم — مرکز مشاوره</p>
        </div>
        <div class="info-item">
            <h3>ساعات کاری</h3>
            <p>شنبه تا چهارشنبه — ساعت ۸ تا ۱۶</p>
        </div>
        <div class="info-item">
            <h3>راه‌های تماس</h3>
            <p>تلفن: ۰۶۱-۴۲۶۷۹۸۵۶<br>ایمیل: counseling@university.edu</p>
        </div>
        <div class="info-item">
            <h3>نحوه رزرو وقت</h3>
            <p>رزرو از طریق سامانه آنلاین مرکز مشاوره یا مراجعه حضوری به واحد پذیرش.</p>
        </div>
    </div>
</section>

<section class="about-cta">
    <h2>نیاز به صحبت با یک مشاور داری؟</h2>
    <p>
        اگر احساس می‌کنی تحت فشار هستی، استرس داری یا در تصمیم‌گیری دچار تردید شده‌ای،
        مشاوران ما آماده‌اند تا در محیطی امن و محرمانه شنونده‌ات باشند.
    </p>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <button class="btn-signup" onclick="window.location.href='<?php echo BASE_URL; ?>consultants.php'">
            رزرو وقت مشاوره
        </button>
    <?php else: ?>
        <button class="btn-signup" onclick="window.location.href='<?php echo BASE_URL; ?>login.php'">
            ابتدا وارد شوید
        </button>
    <?php endif; ?>
</section>

<section class="about-faq">
    <h2>سوالات متداول</h2>

    <div class="about-faq-list">
        <div class="about-faq-item">
            <button class="about-faq-question">
                مرکز مشاوره چه خدماتی ارائه می‌دهد؟
                <span class="faq-icon">+</span>
            </button>
            <div class="about-faq-answer">
                <p>
                    مرکز مشاوره خدماتی مانند مشاوره فردی، خانوادگی، تحصیلی و راهنمایی در زمینه‌های مختلف را ارائه
                    می‌دهد.
                </p>
            </div>
        </div>

        <div class="about-faq-item">
            <button class="about-faq-question">
                چگونه می‌توانم از امکانات سایت استفاده کنم؟
                <span class="faq-icon">+</span>
            </button>
            <div class="about-faq-answer">
                <p>
                    با مراجعه به بخش‌های مختلف سایت می‌توانید اطلاعات موردنیاز خود را مشاهده کرده و از خدمات و
                    امکانات سایت استفاده کنید.
                </p>
            </div>
        </div>

        <div class="about-faq-item">
            <button class="about-faq-question">
                آیا برای استفاده از خدمات نیاز به ثبت‌نام دارم؟
                <span class="faq-icon">+</span>
            </button>
            <div class="about-faq-answer">
                <p>
                    برخی خدمات ممکن است بدون ثبت‌نام قابل استفاده باشند، اما برای بهره‌مندی از تمام امکانات، ثبت‌نام
                    توصیه می‌شود.
                </p>
            </div>
        </div>

        <div class="about-faq-item">
            <button class="about-faq-question">
                چگونه می‌توانم با مرکز مشاوره ارتباط بگیرم؟
                <span class="faq-icon">+</span>
            </button>
            <div class="about-faq-answer">
                <p>
                    از طریق بخش تماس با ما یا فرم ارتباطی سایت می‌توانید درخواست یا سوال خود را ارسال کنید.
                </p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
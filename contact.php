<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'لطفاً تمام فیلدها را پر کنید.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = ' فرمت ایمیل صحیح نیست.';
    } else {
        //Save in DataBase
        $success = ' پیام شما با موفقیت ارسال شد! به زودی با شما تماس می‌گیریم.';
        
        $_POST = [];
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
    .contact-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 50px 20px;
    }
    .contact-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    .contact-info-card {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: 0.3s;
    }
    .contact-info-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.08);
    }
    .contact-info-card .icon {
        font-size: 30px;
        margin-bottom: 10px;
    }
    .contact-info-card h4 {
        color: #f6c900;
        margin-bottom: 8px;
    }
    .contact-info-card p {
        opacity: 0.8;
        font-size: 14px;
    }
    .contact-form-box {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 30px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .contact-info{
        background: rgba(255, 255, 255, 0.05);
        padding: 20px 20px;
        border-radius:10px;
    }
    .light-theme .contact-form-box{
        background: rgba(255, 255, 255, 0.48);
    }
    .light-theme .contact-form-box input,.light-theme .contact-form-box textarea{
        border: 1px solid rgba(3, 2, 2, 0.47);
    }
    .light-theme .contact-info{
        background: rgba(255, 255, 255, 0.33);
        padding: 20px 20px;
        border-radius:10px;
    }
    .contact-form-box h2 {
        color: #f6c900;
        margin-bottom: 10px;
        text-align: center;
    }
    .contact-form-box .subtitle {
        text-align: center;
        opacity: 0.7;
        margin-bottom: 25px;
    }
    .contact-form .form-group {
        margin-bottom: 18px;
    }
    .contact-form label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #f6c900;
    }
    .contact-form input,
    .contact-form textarea {
        width: 100%;
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
        font-size: 15px;
        transition: 0.3s;
        box-sizing: border-box;
    }
    .contact-form input:focus,
    .contact-form textarea:focus {
        outline: none;
        border-color: #f6c900;
        background: rgba(255, 255, 255, 0.08);
    }
    .contact-form textarea {
        resize: vertical;
        min-height: 120px;
    }
    .contact-form .btn-submit {
        background: linear-gradient(to right, #f6c900, #f0a500);
        color: #000;
        padding: 14px 35px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
        width: 100%;
    }
    .contact-form .btn-submit:hover {
        transform: scale(1.02);
        box-shadow: 0 5px 20px rgba(246, 201, 0, 0.3);
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .map-container {
        margin-top: 30px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .map-container iframe {
        width: 100%;
        height: 300px;
        border: none;
        display: block;
    }
    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        .contact-info-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
<section class="contact-container">

    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #f6c900; font-size: 36px;"> تماس با ما</h1>
        <p style="opacity: 0.7; font-size: 18px;">
            ما اینجا هستیم تا به سوالات و نیازهای شما پاسخ دهیم.
        </p>
    </div>

    <div class="contact-form-box">
        <h2>ارسال پیام</h2>
        <p class="subtitle">سوال یا نظری دارید؟ با ما در میان بگذارید.</p>

        <?php if ($error): ?>
            <div style="background: rgba(255, 77, 77, 0.15); padding: 12px; border-radius: 8px; margin-bottom: 15px; text-align: center; border: 1px solid #ff4d4d;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: rgba(76, 175, 80, 0.15); padding: 12px; border-radius: 8px; margin-bottom: 15px; text-align: center; border: 1px solid #4CAF50;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form class="contact-form" method="POST" action="contact.php">
            <div class="form-row">
                <div class="form-group">
                    <label>نام و نام خانوادگی</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required placeholder="مثلاً علی احمدی">
                </div>
                <div class="form-group">
                    <label>ایمیل</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="example@email.com">
                </div>
            </div>

            <div class="form-group">
                <label>موضوع</label>
                <input type="text" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required placeholder="موضوع پیام خود را وارد کنید">
            </div>

            <div class="form-group">
                <label>متن پیام</label>
                <textarea name="message" required placeholder="پیام خود را بنویسید..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-submit"> ارسال پیام</button>
        </form>
    </div>

    <div class="map-container">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3300.0!2d48.4!3d32.4!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzLCsDI0JzAwLjAiTiA0OMKwMjQnMDAuMCJF!5e0!3m2!1sen!2s!4v1234567890" 
            allowfullscreen="" 
            loading="lazy">
        </iframe>
    </div>
   

</section>

<?php include 'includes/footer.php'; ?>
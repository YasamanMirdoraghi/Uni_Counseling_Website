<!--Footer -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-contact">
            <div class="footer-info-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <span>آدرس</span>
                    <p>دانشگاه صنعتی جندی شاپور دزفول - رو به روی پایگاه چهارم شکاری</p>
                </div>
            </div>
            <div class="footer-info-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <span>ایمیل</span>
                    <p>counseling@university.edu</p>
                </div>
            </div>
            <div class="footer-info-item">
                <i class="fas fa-clock"></i>
                <div>
                    <span>ساعات کاری</span>
                    <p>شنبه تا چهارشنبه — ۸ تا ۱۶</p>
                </div>
            </div>
            <div class="footer-info-item">
                <i class="fas fa-phone"></i>
                <div>
                    <span>شماره تماس</span>
                    <p>۰۶۱-۴۲۶۷۹۸۵۶</p>
                </div>
            </div>
        </div>

        <hr class="footer-divider">

        <div class="footer-bottom">
            <p>
                <i class="fas fa-copyright"></i> 
                <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> — تمامی حقوق محفوظ است
            </p>
            <div class="footer-social">
                <a href="#" aria-label="اینستاگرام"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="تلگرام"><i class="fab fa-telegram"></i></a>
                <a href="#" aria-label="ایتا"><i class="fab fa-ello"></i></a>
                <a href="#" aria-label="واتساپ"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="<?php echo BASE_URL; ?>assets/js/theme.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/header.js"></script>

<?php
// spa script
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$js_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/js/' . $current_page . '.js';

if (file_exists($js_path)): ?>
    <script src="<?php echo BASE_URL; ?>assets/js/<?php echo $current_page; ?>.js"></script>
<?php endif; ?>

</body>
</html>
<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
// دریافت consultant_id هم از GET و هم از POST
$consultant_id = isset($_GET['consultant_id']) ? (int)$_GET['consultant_id'] : (isset($_POST['consultant_id']) ? (int)$_POST['consultant_id'] : 0);
$error = '';
$success = '';
$tracking_code = '';

if ($consultant_id == 0) {
    header('Location: consultants.php');
    exit;
}

// دریافت اطلاعات مشاور
try {
    $stmt = $pdo->prepare("SELECT * FROM consultants WHERE id = ?");
    $stmt->execute([$consultant_id]);
    $consultant = $stmt->fetch();
    
    if (!$consultant) {
        header('Location: consultants.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'خطا در دریافت اطلاعات مشاور: ' . $e->getMessage();
}

// دریافت زمان‌های موجود مشاور
try {
    $stmt = $pdo->prepare("
        SELECT id, slot_date, slot_time, status 
        FROM available_slots 
        WHERE consultant_id = ? AND status = 'available' AND slot_date >= CURDATE()
        ORDER BY slot_date, slot_time
    ");
    $stmt->execute([$consultant_id]);
    $available_slots = $stmt->fetchAll();
} catch (PDOException $e) {
    $available_slots = [];
    $error = 'خطا در دریافت زمان‌های موجود: ' . $e->getMessage();
}

// پردازش فرم رزرو
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_id'])) {
    $slot_id = (int)$_POST['slot_id'];
    
    if ($slot_id == 0) {
        $error = 'لطفاً یک زمان را انتخاب کنید.';
    } else {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                SELECT id, status FROM available_slots 
                WHERE id = ? AND status = 'available' AND slot_date >= CURDATE()
            ");
            $stmt->execute([$slot_id]);
            $slot = $stmt->fetch();
            
            if (!$slot) {
                throw new Exception('زمان انتخاب شده دیگر موجود نیست.');
            }
            
            $tracking_code = 'TRK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            $stmt = $pdo->prepare("
                INSERT INTO reservations (user_id, slot_id, tracking_code, status) 
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->execute([$user_id, $slot_id, $tracking_code]);
            
            $stmt = $pdo->prepare("
                UPDATE available_slots 
                SET status = 'booked', is_booked = TRUE 
                WHERE id = ?
            ");
            $stmt->execute([$slot_id]);
            
            $pdo->commit();
            $success = true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'خطا در ثبت رزرو: ' . $e->getMessage();
        }
    }
}

// دریافت رزروهای اخیر کاربر
try {
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            c.name as consultant_name,
            c.id as consultant_id,
            a.slot_date,
            a.slot_time
        FROM reservations r
        JOIN available_slots a ON r.slot_id = a.id
        JOIN consultants c ON a.consultant_id = c.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $user_reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    $user_reservations = [];
}

// دریافت ستاره‌ها برای امتیاز
function renderStars($rating) {
    $output = '';
    $full = round($rating);
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $output .= '<i class="fas fa-star" style="color:#f6c900;font-size:14px;"></i>';
        } else {
            $output .= '<i class="far fa-star" style="color:#4a4a7a;font-size:14px;"></i>';
        }
    }
    return $output;
}
?>

<style>
.reserve-container {
    max-width: 820px;
    margin: 40px auto;
    padding: 0 20px;
    margin-top: 100px;
}

.reserve-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 18px;
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.06);
    margin-bottom: 25px;
    transition: all 0.3s ease;
}

.reserve-card .card-title {
    font-size: 22px;
    font-weight: 700;
    color: #f6c900;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.reserve-card .card-title i {
    font-size: 24px;
}

/* =============================================
   CONSULTANT INFO
   ============================================= */
.consultant-info-row {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.consultant-info-row .avatar {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #f6c900;
    flex-shrink: 0;
}

.consultant-info-row .avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.consultant-info-row .info h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.consultant-info-row .info .degree {
    color: #f6c900;
    font-size: 15px;
    font-weight: 600;
    margin: 3px 0;
}

.consultant-info-row .info .specialty {
    opacity: 0.7;
    font-size: 14px;
    margin: 0;
}

/* =============================================
   SLOTS GRID
   ============================================= */
.slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 25px;
}

.slots-grid .slot-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.04);
    padding: 12px 16px;
    border-radius: 10px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.slots-grid .slot-item:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(246, 201, 0, 0.2);
}

.slots-grid .slot-item input[type="radio"] {
    accent-color: #f6c900;
    width: 18px;
    height: 18px;
    cursor: pointer;
    flex-shrink: 0;
}

.slots-grid .slot-item .slot-date {
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.slots-grid .slot-item .slot-time {
    font-size: 13px;
    opacity: 0.7;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* =============================================
   BUTTONS
   ============================================= */
.btn-reserve {
    width: 100%;
    padding: 14px;
    font-size: 17px;
    font-weight: 700;
    border: none;
    border-radius: 50px;
    background: linear-gradient(135deg, #f6c900, #f0a500);
    color: #1a1a4e;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-reserve:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(246, 201, 0, 0.3);
}

.btn-reserve:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.btn-reserve.outline {
    background: transparent;
    color: #f6c900;
    border: 2px solid #f6c900;
}

.btn-reserve.outline:hover {
    background: rgba(246, 201, 0, 0.08);
    box-shadow: none;
    transform: translateY(-2px);
}

/* =============================================
   ALERT BOXES
   ============================================= */
.alert-box {
    padding: 16px 22px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    text-align: right;
}

.alert-box.success {
    background: rgba(76, 175, 80, 0.12);
    color: #4CAF50;
    border: 1px solid rgba(76, 175, 80, 0.15);
}

.alert-box.success i {
    color: #4CAF50;
}

.alert-box.error {
    background: rgba(255, 77, 77, 0.12);
    color: #ff4d4d;
    border: 1px solid rgba(255, 77, 77, 0.15);
}

.alert-box.error i {
    color: #ff4d4d;
}

.alert-box.info {
    background: rgba(255, 193, 7, 0.12);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.15);
}

/* =============================================
   SUCCESS BOX
   ============================================= */
.success-box {
    background: rgba(76, 175, 80, 0.08);
    border: 2px solid #4CAF50;
    border-radius: 16px;
    padding: 35px;
    text-align: center;
    margin-bottom: 25px;
}

.success-box h2 {
    color: #4CAF50;
    font-size: 28px;
    margin-bottom: 10px;
}

.success-box .tracking-code {
    background: #4CAF50;
    color: #fff;
    padding: 8px 30px;
    border-radius: 50px;
    font-size: 22px;
    font-weight: 700;
    display: inline-block;
    margin: 10px 0;
    letter-spacing: 1px;
}

.success-box .actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 20px;
}

.success-box .actions a {
    padding: 10px 28px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.success-box .actions .btn-back {
    background: linear-gradient(135deg, #f6c900, #f0a500);
    color: #1a1a4e;
}

.success-box .actions .btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(246, 201, 0, 0.3);
}

.success-box .actions .btn-dashboard {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.success-box .actions .btn-dashboard:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

/* =============================================
   RECENT TABLE
   ============================================= */
.recent-table-wrap {
    overflow-x: auto;
    margin-top: 20px;
}

.recent-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    text-align: center;
    color: #fff;
}

.recent-table thead {
    border-bottom: 2px solid rgba(255, 255, 255, 0.06);
}

.recent-table th {
    padding: 12px 10px;
    color: #f6c900;
    font-weight: 600;
    font-size: 13px;
}

.recent-table td {
    padding: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.recent-table tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

.recent-table .tracking-code {
    color: #f6c900;
    font-weight: 700;
    font-size: 13px;
}

.recent-table .status-badge {
    padding: 4px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-pending { background: #ffc107; color: #000; }
.status-confirmed { background: #4CAF50; color: #fff; }
.status-canceled { background: #ff4d4d; color: #fff; }
.status-done { background: #2196F3; color: #fff; }

/* =============================================
   EMPTY STATE
   ============================================= */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #888;
}

.empty-state i {
    font-size: 40px;
    display: block;
    margin-bottom: 12px;
    opacity: 0.5;
}

/* =============================================
   LIGHT THEME 
   ============================================= */
body.light-theme {
    background:rgb(240, 244, 249) !important;
}

body.light-theme .reserve-card {
    background: #ffffff36 !important;
    border-color: rgba(0, 0, 0, 0.04) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
}

body.light-theme .reserve-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04) !important;
}

body.light-theme .reserve-card .card-title {
    color: #4f46e5 !important;
}

body.light-theme .reserve-card .card-title i {
    color: #4f46e5 !important;
}

body.light-theme .consultant-info-row .info h2 {
    color: #1f2937 !important;
}

body.light-theme .consultant-info-row .info .degree {
    color: #4f46e5 !important;
}

body.light-theme .consultant-info-row .info .specialty {
    color: #6b7280 !important;
}

body.light-theme .slots-grid .slot-item {
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
}

body.light-theme .slots-grid .slot-item:hover {
    background: rgba(79, 70, 229, 0.04) !important;
    border-color: rgba(79, 70, 229, 0.2) !important;
}

body.light-theme .slots-grid .slot-item .slot-date {
    color: #1f2937 !important;
}

body.light-theme .slots-grid .slot-item .slot-time {
    color: #6b7280 !important;
}

body.light-theme .btn-reserve {
    background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.25) !important;
}

body.light-theme .btn-reserve:hover {
    box-shadow: 0 8px 35px rgba(79, 70, 229, 0.35) !important;
}

body.light-theme .btn-reserve.outline {
    background: transparent !important;
    color: #4f46e5 !important;
    border: 2px solid #4f46e5 !important;
}

body.light-theme .btn-reserve.outline:hover {
    background: rgba(79, 70, 229, 0.04) !important;
}

body.light-theme .success-box {
    background: rgba(79, 70, 229, 0.04) !important;
    border-color: #4f46e5 !important;
}

body.light-theme .success-box h2 {
    color: #4f46e5 !important;
}

body.light-theme .success-box .tracking-code {
    background: #4f46e5 !important;
}

body.light-theme .success-box .actions .btn-back {
    background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
    color: #ffffff !important;
}

body.light-theme .success-box .actions .btn-dashboard {
    background: #f8fafc !important;
    color: #1f2937 !important;
    border-color: #e2e8f0 !important;
}

body.light-theme .success-box .actions .btn-dashboard:hover {
    background: #f1f5f9 !important;
}

body.light-theme .recent-table {
    color: #1f2937 !important;
}

body.light-theme .recent-table thead {
    border-color: rgba(0, 0, 0, 0.04) !important;
}

body.light-theme .recent-table th {
    color: #4f46e5 !important;
}

body.light-theme .recent-table td {
    color: #1f2937 !important;
}

body.light-theme .recent-table td a {
    color: #4f46e5 !important;
}

body.light-theme .recent-table td a:hover {
    color: #6366f1 !important;
}

body.light-theme .recent-table .tracking-code {
    color: #4f46e5 !important;
}

body.light-theme .recent-table tr:hover td {
    background: rgba(79, 70, 229, 0.02) !important;
}

body.light-theme .recent-table .status-pending {
    background: #fef3c7 !important;
    color: #92400e !important;
}

body.light-theme .recent-table .status-confirmed {
    background: #d1fae5 !important;
    color: #065f46 !important;
}

body.light-theme .recent-table .status-canceled {
    background: #fee2e2 !important;
    color: #991b1b !important;
}

body.light-theme .recent-table .status-done {
    background: #dbeafe !important;
    color: #1e40af !important;
}

body.light-theme .alert-box.success {
    background: #ecfdf5 !important;
    color: #065f46 !important;
    border-color: #a7f3d0 !important;
}

body.light-theme .alert-box.error {
    background: #fef2f2 !important;
    color: #991b1b !important;
    border-color: #fca5a5 !important;
}

body.light-theme .alert-box.info {
    background: #fffbeb !important;
    color: #92400e !important;
    border-color: #fcd34d !important;
}

body.light-theme .empty-state {
    color: #6b7280 !important;
}

body.light-theme .empty-state i {
    color: #9ca3af !important;
}

/* =============================================
   RESPONSIVE
   ============================================= */
@media (max-width: 768px) {
    .reserve-container {
        padding: 0 15px;
    }
    
    .reserve-card {
        padding: 20px;
    }
    
    .consultant-info-row {
        flex-direction: column;
        text-align: center;
    }
    
    .consultant-info-row .info h2 {
        font-size: 20px;
    }
    
    .slots-grid {
        grid-template-columns: 1fr;
    }
    
    .success-box {
        padding: 20px;
    }
    
    .success-box .tracking-code {
        font-size: 16px;
        padding: 6px 18px;
    }
    
    .recent-table {
        font-size: 12px;
    }
    
    .recent-table th,
    .recent-table td {
        padding: 8px 6px;
    }
    
    .btn-reserve {
        font-size: 15px;
        padding: 12px;
    }
}

@media (max-width: 480px) {
    .reserve-card .card-title {
        font-size: 18px;
    }
    
    .consultant-info-row .avatar {
        width: 60px;
        height: 60px;
    }
    
    .slots-grid .slot-item {
        padding: 10px 12px;
    }
    
    .success-box h2 {
        font-size: 22px;
    }
}
</style>

<?php include 'includes/header.php'; ?>

<div class="reserve-container">

    <?php if ($success): ?>
        <div class="success-box">
            <h2><i class="fas fa-check-circle" style="color:#4CAF50;"></i> رزرو با موفقیت انجام شد!</h2>
            <p style="font-size:16px; opacity:0.8;">شماره پیگیری شما:</p>
            <div class="tracking-code"><?php echo htmlspecialchars($tracking_code); ?></div>
            <p style="font-size:14px; opacity:0.6; margin-top:12px;">لطفاً این شماره را برای پیگیری رزرو خود نگهداری کنید.</p>
            <div class="actions">
                <a href="<?php echo BASE_URL; ?>consultant.php?id=<?php echo $consultant_id; ?>" class="btn-back">
                    <i class="fas fa-arrow-right"></i> بازگشت به پروفایل
                </a>
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn-dashboard">
                    <i class="fas fa-tachometer-alt"></i> پنل کاربری
                </a>
            </div>
        </div>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="alert-box error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- اطلاعات مشاور -->
        <div class="reserve-card">
            <div class="consultant-info-row">
                <div class="avatar">
                    <img src="<?php echo BASE_URL; ?>assets/img/<?php echo htmlspecialchars($consultant['image'] ?? 'default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($consultant['name']); ?>">
                </div>
                <div class="info">
                    <h2><?php echo htmlspecialchars($consultant['name']); ?></h2>
                    <div class="degree"><?php echo htmlspecialchars($consultant['degree']); ?></div>
                    <div class="specialty"><?php echo htmlspecialchars($consultant['specialty']); ?></div>
                </div>
            </div>
        </div>

        <!-- انتخاب زمان -->
        <div class="reserve-card">
            <div class="card-title">
                <i class="fas fa-clock"></i> انتخاب زمان مشاوره
            </div>
            <p style="opacity:0.7; margin-bottom:20px; font-size:15px;">لطفاً یکی از زمان‌های خالی زیر را انتخاب کنید.</p>

            <?php if (count($available_slots) > 0): ?>
                <form method="POST" action="reserve.php">
                    <input type="hidden" name="consultant_id" value="<?php echo $consultant_id; ?>">
                    
                    <div class="slots-grid">
                        <?php foreach ($available_slots as $slot): ?>
                            <label class="slot-item">
                                <input type="radio" name="slot_id" value="<?php echo $slot['id']; ?>" required>
                                <div>
                                    <div class="slot-date"><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($slot['slot_date']); ?></div>
                                    <div class="slot-time"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($slot['slot_time']); ?></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn-reserve">
                        <i class="fas fa-check-circle"></i> تایید و رزرو نوبت
                    </button>
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p style="font-size:18px; color:#ff6b6b; font-weight:600;">در حال حاضر هیچ زمانی برای رزرو موجود نیست.</p>
                    <p style="opacity:0.6; margin-top:8px;">لطفاً بعداً مراجعه کنید یا مشاور دیگری را انتخاب کنید.</p>
                    <a href="<?php echo BASE_URL; ?>consultants.php" class="btn-reserve outline" style="margin-top:15px; display:inline-block; width:auto; padding:10px 30px;">
                        <i class="fas fa-arrow-right"></i> بازگشت به لیست مشاوران
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (count($user_reservations) > 0): ?>
            <div class="reserve-card">
                <div class="card-title">
                    <i class="fas fa-history"></i> رزروهای اخیر شما
                </div>
                <div class="recent-table-wrap">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>شماره پیگیری</th>
                                <th>مشاور</th>
                                <th>تاریخ</th>
                                <th>ساعت</th>
                                <th>وضعیت</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_reservations as $res): ?>
                                <tr>
                                    <td><span class="tracking-code"><?php echo htmlspecialchars($res['tracking_code']); ?></span></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>consultant.php?id=<?php echo $res['consultant_id']; ?>" style="text-decoration:none;">
                                            <?php echo htmlspecialchars($res['consultant_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($res['slot_date']); ?></td>
                                    <td><?php echo htmlspecialchars($res['slot_time']); ?></td>
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
                <div style="text-align:center; margin-top:15px;">
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="consultant-link">
                        مشاهده همه رزروها در پنل کاربری <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
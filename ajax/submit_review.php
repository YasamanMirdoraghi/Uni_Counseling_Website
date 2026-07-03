<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'لطفاً وارد شوید.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$consultant_id = isset($_POST['consultant_id']) ? (int)$_POST['consultant_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review_text = trim($_POST['review_text'] ?? '');

if ($consultant_id == 0 || $rating < 1 || $rating > 5 || empty($review_text)) {
    echo json_encode(['success' => false, 'message' => 'لطفاً همه فیلدها را کامل کنید.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ========== ✅ فقط چک میکنیم که حداقل یک جلسه داشته باشه ==========
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_done
        FROM reservations r
        JOIN available_slots a ON r.slot_id = a.id
        WHERE r.user_id = ? AND a.consultant_id = ? AND r.status IN ('confirmed', 'done')
    ");
    $stmt->execute([$user_id, $consultant_id]);
    $total_done_sessions = $stmt->fetch()['total_done'];

    // ========== ✅ اگر جلسه نداشته باشه، خطا ==========
    if ($total_done_sessions == 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'شما هیچ جلسه‌ای با این مشاور نداشته‌اید.'
        ]);
        $pdo->rollBack();
        exit;
    }

    // ========== ✅ ثبت نظر جدید (بدون محدودیت تعداد) ==========
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, consultant_id, rating, review_text) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $consultant_id, $rating, $review_text]);

    // به‌روزرسانی avg_rating مشاور
    $stmt = $pdo->prepare("
        UPDATE consultants 
        SET avg_rating = (
            SELECT COALESCE(AVG(rating), 0) 
            FROM reviews 
            WHERE consultant_id = ?
        )
        WHERE id = ?
    ");
    $stmt->execute([$consultant_id, $consultant_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'نظر شما با موفقیت ثبت شد!'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'خطا در ثبت نظر: ' . $e->getMessage()]);
}
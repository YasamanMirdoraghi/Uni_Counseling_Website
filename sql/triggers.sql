-- =============================================
-- انتخاب دیتابیس
-- =============================================
USE university_consulting;

-- =============================================
-- تریگر 1: به‌روزرسانی میانگین بعد از درج نظر
-- =============================================
DELIMITER //

DROP TRIGGER IF EXISTS update_consultant_avg_rating//
CREATE TRIGGER update_consultant_avg_rating
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE consultants 
    SET avg_rating = (
        SELECT COALESCE(AVG(rating), 0)
        FROM reviews
        WHERE consultant_id = NEW.consultant_id
    )
    WHERE id = NEW.consultant_id;
END//

DELIMITER ;

-- =============================================
-- تریگر 2: به‌روزرسانی میانگین بعد از حذف نظر
-- =============================================
DELIMITER //

DROP TRIGGER IF EXISTS update_consultant_avg_rating_delete//
CREATE TRIGGER update_consultant_avg_rating_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE consultants 
    SET avg_rating = (
        SELECT COALESCE(AVG(rating), 0)
        FROM reviews
        WHERE consultant_id = OLD.consultant_id
    )
    WHERE id = OLD.consultant_id;
END//

DELIMITER ;

-- =============================================
-- تریگر 3: به‌روزرسانی میانگین بعد از ویرایش نظر
-- =============================================
DELIMITER //

DROP TRIGGER IF EXISTS update_consultant_avg_rating_update//
CREATE TRIGGER update_consultant_avg_rating_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE consultants 
    SET avg_rating = (
        SELECT COALESCE(AVG(rating), 0)
        FROM reviews
        WHERE consultant_id = NEW.consultant_id
    )
    WHERE id = NEW.consultant_id;
END//

DELIMITER ;

-- =============================================
-- تریگر 4: بوک کردن زمان بعد از رزرو
-- =============================================
DELIMITER //

DROP TRIGGER IF EXISTS book_slot_after_reservation//
CREATE TRIGGER book_slot_after_reservation
AFTER INSERT ON reservations
FOR EACH ROW
BEGIN
    UPDATE available_slots 
    SET status = 'booked', is_booked = TRUE
    WHERE id = NEW.slot_id;
END//

DELIMITER ;

-- =============================================
-- تریگر 5: آزادسازی زمان بعد از لغو رزرو
-- =============================================
DELIMITER //

DROP TRIGGER IF EXISTS free_slot_after_cancel//
CREATE TRIGGER free_slot_after_cancel
AFTER UPDATE ON reservations
FOR EACH ROW
BEGIN
    IF NEW.status = 'canceled' AND OLD.status != 'canceled' THEN
        UPDATE available_slots 
        SET status = 'available', is_booked = FALSE
        WHERE id = NEW.slot_id;
    END IF;
END//

DELIMITER ;

-- =============================================
-- تریگر 6: جلوگیری از رزرو دوباره (قبل از رزرو)
-- =============================================
DELIMITER //

DROP TRIGGER IF EXISTS prevent_double_booking//
CREATE TRIGGER prevent_double_booking
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    DECLARE slot_status ENUM('available', 'booked', 'canceled');
    
    SELECT status INTO slot_status 
    FROM available_slots 
    WHERE id = NEW.slot_id;
    
    IF slot_status != 'available' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'این زمان قبلاً رزرو شده است';
    END IF;
END//

DELIMITER ;

-- =============================================
-- نمایش همه تریگرها
-- =============================================
SHOW TRIGGERS;
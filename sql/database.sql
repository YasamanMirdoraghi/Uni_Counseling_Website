-- =============================================
-- 1. ایجاد دیتابیس
-- =============================================
CREATE DATABASE IF NOT EXISTS university_consulting;
USE university_consulting;

-- =============================================
-- 2. جدول کاربران
-- =============================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    student_id VARCHAR(15) UNIQUE NOT NULL,
    mobile VARCHAR(11) NOT NULL,
    birth_year INT NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 3. جدول مشاوران
-- =============================================
DROP TABLE IF EXISTS consultants;
CREATE TABLE consultants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    specialty VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT 'default.jpg',
    avg_rating DECIMAL(2,1) DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_specialty (specialty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 4. جدول زمان‌های موجود
-- =============================================
DROP TABLE IF EXISTS available_slots;
CREATE TABLE available_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consultant_id INT NOT NULL,
    slot_date DATE NOT NULL,
    slot_time TIME NOT NULL,
    is_booked BOOLEAN DEFAULT FALSE,
    status ENUM('available', 'booked', 'canceled') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (consultant_id) REFERENCES consultants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_slot (consultant_id, slot_date, slot_time),
    INDEX idx_date (slot_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 5. جدول رزروها
-- =============================================
DROP TABLE IF EXISTS reservations;
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    slot_id INT NOT NULL,
    tracking_code VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('pending', 'confirmed', 'canceled', 'done') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES available_slots(id) ON DELETE CASCADE,
    INDEX idx_tracking (tracking_code),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- 6. جدول نظرات و امتیازات
-- =============================================
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    consultant_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (consultant_id) REFERENCES consultants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, consultant_id),
    INDEX idx_consultant (consultant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
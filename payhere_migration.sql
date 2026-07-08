
-- BODIMAK.LK - PayHere Payment Gateway DB Migration
-- Run this in phpMyAdmin or MySQL CLI
USE bodimak_db;

-- 1. Update advertisements status ENUM to include new statuses
ALTER TABLE advertisements 
MODIFY COLUMN status ENUM('pending','approved','active','rejected') NOT NULL DEFAULT 'pending';

-- 2. System settings table (admin-configurable key/value)
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('commission_percentage', '20');

-- 3. Payment records table
CREATE TABLE IF NOT EXISTS ad_payments (
    payment_id     INT AUTO_INCREMENT PRIMARY KEY,
    ad_id          INT NOT NULL,
    seller_id      INT NOT NULL,
    order_id       VARCHAR(100) UNIQUE NOT NULL,
    amount         DECIMAL(10,2) NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL,
    payhere_payment_id VARCHAR(100) DEFAULT NULL,
    status         ENUM('pending','success','failed','cancelled') DEFAULT 'pending',
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ad_id) REFERENCES advertisements(ad_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE DATABASE IF NOT EXISTS bodimak_db;
USE bodimak_db;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role ENUM('user', 'seller', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS advertisements (
    ad_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    facilities TEXT,
    gender_preference VARCHAR(20) NOT NULL,
    contact_no VARCHAR(20) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ad_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (ad_id) REFERENCES advertisements(ad_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS saved_listings (
    save_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ad_id INT NOT NULL,
    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (ad_id) REFERENCES advertisements(ad_id) ON DELETE CASCADE,
    UNIQUE KEY unique_save (user_id, ad_id)
);

-- Insert default admin account (password is 'admin123')
-- password_hash('admin123', PASSWORD_DEFAULT)
INSERT IGNORE INTO users (user_id, full_name, email, password, phone, role, status) VALUES
(1, 'System Admin', 'admin@bodimak.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0000000000', 'admin', 'active');

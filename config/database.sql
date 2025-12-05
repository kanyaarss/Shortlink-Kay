-- Shortlink Kay v1 - Database Schema
-- MySQL/MariaDB 10.11.10
-- Character Set: utf8mb4
-- Collation: utf8mb4_unicode_ci

-- ============================================
-- CREATE DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS `shortlink_kay`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `shortlink_kay`;

-- ============================================
-- TABLE: users (Admin Users)
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'User ID',
    `username` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Username untuk login',
    `password` VARCHAR(255) NOT NULL COMMENT 'Password hash (bcrypt)',
    `email` VARCHAR(100) UNIQUE NOT NULL COMMENT 'Email admin',
    `full_name` VARCHAR(100) COMMENT 'Nama lengkap admin',
    `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Status aktif user',
    `last_login` DATETIME COMMENT 'Waktu login terakhir',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu akun dibuat',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu akun diupdate',
    
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel admin users';

-- ============================================
-- TABLE: links (Shortlink)
-- ============================================
CREATE TABLE IF NOT EXISTS `links` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Link ID',
    `code` VARCHAR(20) UNIQUE NOT NULL COMMENT 'Short code (misal: abc123)',
    `url` TEXT NOT NULL COMMENT 'URL target asli',
    `custom_code` BOOLEAN DEFAULT FALSE COMMENT 'Flag custom code atau random',
    `created_by` INT NOT NULL COMMENT 'User ID yang membuat link',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu link dibuat',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu link diupdate',
    `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Status link aktif/nonaktif',
    `expires_at` DATETIME COMMENT 'Waktu link expire (NULL = tidak expire)',
    `click_count` INT DEFAULT 0 COMMENT 'Cache jumlah klik (denormalisasi)',
    `description` TEXT COMMENT 'Deskripsi link (optional)',
    
    FOREIGN KEY `fk_created_by` (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    
    INDEX `idx_code` (`code`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel shortlink';

-- ============================================
-- TABLE: clicks_log (Access Log)
-- ============================================
CREATE TABLE IF NOT EXISTS `clicks_log` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Log ID',
    `link_id` INT NOT NULL COMMENT 'Link ID yang diklik',
    `ip` VARCHAR(45) NOT NULL COMMENT 'IP address pengunjung (IPv4/IPv6)',
    `user_agent` TEXT COMMENT 'User agent browser/device',
    `referer` VARCHAR(255) COMMENT 'Referer URL',
    `country` VARCHAR(100) COMMENT 'Negara pengunjung (optional)',
    `city` VARCHAR(100) COMMENT 'Kota pengunjung (optional)',
    `device_type` VARCHAR(50) COMMENT 'Tipe device (mobile/desktop/tablet)',
    `browser` VARCHAR(100) COMMENT 'Browser name',
    `os` VARCHAR(100) COMMENT 'Operating system',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu klik',
    
    FOREIGN KEY `fk_link_id` (`link_id`) REFERENCES `links`(`id`) ON DELETE CASCADE,
    
    INDEX `idx_link_id` (`link_id`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_ip` (`ip`),
    INDEX `idx_country` (`country`),
    INDEX `idx_device_type` (`device_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel log akses shortlink';

-- ============================================
-- TABLE: rate_limit (Rate Limiting)
-- ============================================
CREATE TABLE IF NOT EXISTS `rate_limit` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Rate limit ID',
    `ip` VARCHAR(45) NOT NULL COMMENT 'IP address',
    `endpoint` VARCHAR(100) NOT NULL COMMENT 'Endpoint (misal: /api/create)',
    `request_count` INT DEFAULT 1 COMMENT 'Jumlah request',
    `reset_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu reset counter',
    
    UNIQUE KEY `unique_ip_endpoint` (`ip`, `endpoint`),
    INDEX `idx_reset_at` (`reset_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel rate limiting';

-- ============================================
-- TABLE: api_keys (API Authentication)
-- ============================================
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'API Key ID',
    `user_id` INT NOT NULL COMMENT 'User ID pemilik API key',
    `key_hash` VARCHAR(255) UNIQUE NOT NULL COMMENT 'Hash dari API key',
    `name` VARCHAR(100) COMMENT 'Nama API key (untuk identifikasi)',
    `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Status aktif API key',
    `last_used` DATETIME COMMENT 'Waktu penggunaan terakhir',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu API key dibuat',
    `expires_at` DATETIME COMMENT 'Waktu API key expire (NULL = tidak expire)',
    
    FOREIGN KEY `fk_user_id` (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel API keys';

-- ============================================
-- TABLE: settings (Application Settings)
-- ============================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Setting ID',
    `key` VARCHAR(100) UNIQUE NOT NULL COMMENT 'Setting key',
    `value` LONGTEXT COMMENT 'Setting value',
    `type` VARCHAR(50) COMMENT 'Tipe value (string/int/boolean/json)',
    `description` TEXT COMMENT 'Deskripsi setting',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update',
    
    INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel aplikasi settings';

-- ============================================
-- TABLE: audit_log (Audit Trail)
-- ============================================
CREATE TABLE IF NOT EXISTS `audit_log` (
    `id` INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Audit log ID',
    `user_id` INT COMMENT 'User ID yang melakukan aksi',
    `action` VARCHAR(100) NOT NULL COMMENT 'Aksi yang dilakukan',
    `entity_type` VARCHAR(50) COMMENT 'Tipe entity (links, users, settings)',
    `entity_id` INT COMMENT 'ID dari entity',
    `old_value` LONGTEXT COMMENT 'Nilai lama (JSON)',
    `new_value` LONGTEXT COMMENT 'Nilai baru (JSON)',
    `ip_address` VARCHAR(45) COMMENT 'IP address user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu aksi',
    
    FOREIGN KEY `fk_audit_user_id` (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_entity_type` (`entity_type`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel audit log';

-- ============================================
-- INITIAL DATA
-- ============================================

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `is_active`) 
VALUES (
    'admin',
    'admin@kanyaars.cloud',
    '$2y$12$R9h7cIPz0gi.URNNX3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jKMm6', -- bcrypt hash of 'admin123'
    'Administrator',
    TRUE
);

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `description`) VALUES
('app_name', 'Shortlink Kay v1', 'string', 'Nama aplikasi'),
('app_version', '1.0.0', 'string', 'Versi aplikasi'),
('short_code_length', '6', 'int', 'Panjang short code default'),
('rate_limit_requests', '10', 'int', 'Jumlah request per menit'),
('session_timeout', '1800', 'int', 'Session timeout dalam detik'),
('maintenance_mode', 'false', 'boolean', 'Status maintenance mode');

-- ============================================
-- VIEWS (Optional - untuk reporting)
-- ============================================

-- View: Top Links by Clicks
CREATE OR REPLACE VIEW `v_top_links` AS
SELECT 
    l.id,
    l.code,
    l.url,
    l.click_count,
    COUNT(c.id) as total_clicks,
    l.created_at,
    u.username as created_by
FROM `links` l
LEFT JOIN `clicks_log` c ON l.id = c.link_id
LEFT JOIN `users` u ON l.created_by = u.id
WHERE l.is_active = TRUE
GROUP BY l.id
ORDER BY l.click_count DESC;

-- View: Daily Statistics
CREATE OR REPLACE VIEW `v_daily_stats` AS
SELECT 
    DATE(c.created_at) as date,
    COUNT(DISTINCT c.link_id) as unique_links,
    COUNT(c.id) as total_clicks,
    COUNT(DISTINCT c.ip) as unique_visitors
FROM `clicks_log` c
GROUP BY DATE(c.created_at)
ORDER BY date DESC;

-- ============================================
-- TRIGGERS (Optional - untuk automation)
-- ============================================

-- Trigger: Update click_count saat ada klik baru
DELIMITER //
CREATE TRIGGER `tr_update_click_count` AFTER INSERT ON `clicks_log`
FOR EACH ROW
BEGIN
    UPDATE `links` SET `click_count` = `click_count` + 1
    WHERE `id` = NEW.link_id;
END//
DELIMITER ;

-- Trigger: Audit log untuk update links
DELIMITER //
CREATE TRIGGER `tr_audit_links_update` AFTER UPDATE ON `links`
FOR EACH ROW
BEGIN
    INSERT INTO `audit_log` 
    (`user_id`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`)
    VALUES 
    (NULL, 'UPDATE', 'links', NEW.id, 
     JSON_OBJECT('url', OLD.url, 'is_active', OLD.is_active),
     JSON_OBJECT('url', NEW.url, 'is_active', NEW.is_active));
END//
DELIMITER ;

-- ============================================
-- STORED PROCEDURES (Optional - untuk complex queries)
-- ============================================

-- Procedure: Get link statistics
DELIMITER //
CREATE PROCEDURE `sp_get_link_stats`(IN p_link_id INT)
BEGIN
    SELECT 
        l.id,
        l.code,
        l.url,
        l.click_count,
        COUNT(c.id) as total_clicks,
        COUNT(DISTINCT c.ip) as unique_visitors,
        MAX(c.created_at) as last_click,
        l.created_at,
        l.is_active
    FROM `links` l
    LEFT JOIN `clicks_log` c ON l.id = c.link_id
    WHERE l.id = p_link_id
    GROUP BY l.id;
END//
DELIMITER ;

-- ============================================
-- INDEXES OPTIMIZATION
-- ============================================

-- Composite index untuk query yang sering digunakan
ALTER TABLE `clicks_log` ADD INDEX `idx_link_created` (`link_id`, `created_at`);
ALTER TABLE `links` ADD INDEX `idx_active_created` (`is_active`, `created_at`);

-- ============================================
-- PERMISSIONS (untuk production)
-- ============================================

-- Create user dengan privileges terbatas (optional)
-- CREATE USER 'shortlink_user'@'localhost' IDENTIFIED BY 'secure_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON shortlink_kay.* TO 'shortlink_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================
-- BACKUP RECOMMENDATION
-- ============================================
-- mysqldump -u root -p shortlink_kay > backup_$(date +%Y%m%d_%H%M%S).sql
-- Jalankan setiap hari untuk backup otomatis

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================

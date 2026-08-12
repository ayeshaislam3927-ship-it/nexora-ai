-- ========================================================
-- NEXORA FULL-STACK AI PLATFORM DATABASE SCHEMA
-- MySQL 8.0+ Compatible
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS user_settings;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS chats;
DROP TABLE IF EXISTS email_otps;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS TABLE
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(100) DEFAULT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) DEFAULT NULL,
  `google_id` VARCHAR(255) DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `status` ENUM('active', 'suspended', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` DATETIME DEFAULT NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. EMAIL OTPS TABLE
CREATE TABLE `email_otps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `otp_code` VARCHAR(10) NOT NULL,
  `purpose` VARCHAR(50) NOT NULL DEFAULT 'registration',
  `expires_at` DATETIME NOT NULL,
  `verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email_otp` (`email`, `otp_code`),
  INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CHATS TABLE
CREATE TABLE `chats` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT 'New Chat',
  `model` VARCHAR(100) NOT NULL DEFAULT 'gemini-2.5-flash',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_chats` (`user_id`),
  CONSTRAINT `fk_chats_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. MESSAGES TABLE
CREATE TABLE `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `chat_id` INT NOT NULL,
  `role` ENUM('user', 'assistant', 'system') NOT NULL,
  `content` LONGTEXT NOT NULL,
  `model` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_chat_messages` (`chat_id`),
  CONSTRAINT `fk_messages_chat` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. USER SETTINGS TABLE
CREATE TABLE `user_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `theme` VARCHAR(20) NOT NULL DEFAULT 'dark',
  `language` VARCHAR(20) NOT NULL DEFAULT 'en',
  `selected_model` VARCHAR(100) NOT NULL DEFAULT 'gemini-2.5-flash',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ADMIN LOGS TABLE
CREATE TABLE `admin_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_admin` (`admin_id`),
  CONSTRAINT `fk_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. SYSTEM SETTINGS TABLE
CREATE TABLE `system_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- INITIAL SEED DATA
-- ========================================================

-- Initial Admin Account (Email: admin@nexora.ai / Password: admin123)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `email_verified`, `role`, `status`)
VALUES ('NEXORA', 'Admin', 'admin@nexora.ai', '$2y$10$wK1k6a/K9N/z1R.sY9O9/.kS41Q6Gq3/XhN5a1Kz8Y5G2M1Q0L2.G', 1, 'admin', 'active');

-- Initial System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('platform_name', 'NEXORA'),
('default_model', 'gemini-2.5-flash'),
('registration_enabled', '1'),
('guest_chat_enabled', '1'),
('maintenance_mode', '0'),
('max_upload_size', '10485760');

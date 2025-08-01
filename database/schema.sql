-- CureConnect Medical Tourism Portal Database Schema
-- Updated schema matching requirements with proper indexing and relationships

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Database creation
CREATE DATABASE IF NOT EXISTS `cureconnect_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cureconnect_db`;

-- Users table (updated structure)
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `is_admin` BOOLEAN DEFAULT FALSE,
  `role` ENUM('user','admin','hospital_admin','doctor') NOT NULL DEFAULT 'user',
  `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `email_verification_token` VARCHAR(255) DEFAULT NULL,
  `password_reset_token` VARCHAR(255) DEFAULT NULL,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_email` (`email`),
  INDEX `idx_status` (`status`),
  INDEX `idx_role` (`role`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles table (updated structure)
CREATE TABLE `articles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `content` LONGTEXT NOT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `language` VARCHAR(5) DEFAULT 'en',
  `meta_description` TEXT DEFAULT NULL,
  `meta_keywords` TEXT DEFAULT NULL,
  `tags` JSON DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `author_name` VARCHAR(100) DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `reading_time` INT DEFAULT NULL,
  `views` INT DEFAULT 0,
  `featured` TINYINT(1) DEFAULT 0,
  `scheduled_at` DATETIME DEFAULT NULL,
  `published_at` DATETIME DEFAULT NULL,
  `status` ENUM('draft','scheduled','published','archived') DEFAULT 'draft',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_slug` (`slug`),
  INDEX `idx_status_lang` (`status`, `language`),
  INDEX `idx_category` (`category`),
  INDEX `idx_published_at` (`published_at`),
  INDEX `idx_featured` (`featured`),
  FULLTEXT KEY `ft_content` (`title`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Countries table
CREATE TABLE `countries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(3) NOT NULL UNIQUE,
  `phone_code` VARCHAR(10) DEFAULT NULL,
  `medical_visa_eligible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX `idx_code` (`code`),
  INDEX `idx_visa_eligible` (`medical_visa_eligible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hospitals table
CREATE TABLE `hospitals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT,
  `address` TEXT,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `established_year` INT(4) DEFAULT NULL,
  `bed_count` INT(11) DEFAULT NULL,
  `accreditations` JSON DEFAULT NULL,
  `specialties` JSON DEFAULT NULL,
  `facilities` JSON DEFAULT NULL,
  `images` JSON DEFAULT NULL,
  `rating` DECIMAL(2,1) DEFAULT 0.0,
  `review_count` INT(11) DEFAULT 0,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_slug` (`slug`),
  INDEX `idx_city` (`city`),
  INDEX `idx_status` (`status`),
  INDEX `idx_featured` (`featured`),
  INDEX `idx_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Doctors table
CREATE TABLE `doctors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `hospital_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `qualification` VARCHAR(500) DEFAULT NULL,
  `specialization` VARCHAR(255) NOT NULL,
  `sub_specialization` VARCHAR(255) DEFAULT NULL,
  `experience_years` INT DEFAULT NULL,
  `biography` TEXT,
  `consultation_fee` DECIMAL(10,2) DEFAULT NULL,
  `languages` JSON DEFAULT NULL,
  `availability` JSON DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `rating` DECIMAL(2,1) DEFAULT 0.0,
  `review_count` INT DEFAULT 0,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_hospital_id` (`hospital_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_specialization` (`specialization`),
  INDEX `idx_featured` (`featured`),
  CONSTRAINT `doctors_hospital_fk` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `doctors_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Treatments table
CREATE TABLE `treatments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `category` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `procedure_details` LONGTEXT,
  `duration` VARCHAR(100) DEFAULT NULL,
  `recovery_time` VARCHAR(100) DEFAULT NULL,
  `success_rate` DECIMAL(5,2) DEFAULT NULL,
  `min_cost_inr` DECIMAL(12,2) DEFAULT NULL,
  `max_cost_inr` DECIMAL(12,2) DEFAULT NULL,
  `cost_comparison` JSON DEFAULT NULL,
  `prerequisites` JSON DEFAULT NULL,
  `risks` JSON DEFAULT NULL,
  `benefits` JSON DEFAULT NULL,
  `images` JSON DEFAULT NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `seo_title` VARCHAR(255) DEFAULT NULL,
  `seo_description` TEXT DEFAULT NULL,
  `seo_keywords` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_slug` (`slug`),
  INDEX `idx_category` (`category`),
  INDEX `idx_featured` (`featured`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inquiries table for lead management
CREATE TABLE `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `treatment_interest` VARCHAR(255) DEFAULT NULL,
  `hospital_preference` VARCHAR(255) DEFAULT NULL,
  `message` TEXT,
  `status` ENUM('new','contacted','qualified','converted','closed') NOT NULL DEFAULT 'new',
  `assigned_to` INT DEFAULT NULL,
  `source` VARCHAR(100) DEFAULT 'website',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_status` (`status`),
  INDEX `idx_assigned_to` (`assigned_to`),
  INDEX `idx_treatment_interest` (`treatment_interest`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `inquiries_assigned_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gallery table for images
CREATE TABLE `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `thumbnail_url` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) NOT NULL,
  `hospital_id` INT DEFAULT NULL,
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_category` (`category`),
  INDEX `idx_hospital_id` (`hospital_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_sort_order` (`sort_order`),
  CONSTRAINT `gallery_hospital_fk` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(255) NOT NULL UNIQUE,
  `value` LONGTEXT,
  `type` ENUM('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT INTO `users` (`email`, `password_hash`, `name`, `is_admin`, `role`, `status`, `email_verified`) VALUES
('admin@cureconnect.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', TRUE, 'admin', 'active', 1);

-- Insert sample countries
INSERT INTO `countries` (`name`, `code`, `phone_code`, `medical_visa_eligible`) VALUES
('United States', 'USA', '+1', 1),
('United Kingdom', 'GBR', '+44', 1),
('Canada', 'CAN', '+1', 1),
('Australia', 'AUS', '+61', 1),
('Germany', 'DEU', '+49', 1),
('France', 'FRA', '+33', 1),
('Japan', 'JPN', '+81', 1),
('Singapore', 'SGP', '+65', 1),
('UAE', 'ARE', '+971', 1),
('Saudi Arabia', 'SAU', '+966', 1),
('Bangladesh', 'BGD', '+880', 1),
('Pakistan', 'PAK', '+92', 1),
('Nigeria', 'NGA', '+234', 1),
('Kenya', 'KEN', '+254', 1),
('South Africa', 'ZAF', '+27', 1);

-- Insert sample articles
INSERT INTO `articles` (`title`, `slug`, `content`, `excerpt`, `language`, `meta_description`, `category`, `author_name`, `status`, `published_at`) VALUES
('Complete Guide to Medical Tourism in India', 'complete-guide-medical-tourism-india',
'<h2>Why Choose India for Medical Tourism?</h2><p>India has emerged as a global leader in medical tourism...</p>',
'Comprehensive guide covering everything about medical tourism in India including costs, procedures, and visa information.',
'en', 'Complete guide to medical tourism in India. Learn about top hospitals, treatments, visa process, and cost savings.',
'Medical Tourism', 'Dr. Rajesh Sharma', 'published', NOW()),

('Top 10 Hospitals for International Patients', 'top-10-hospitals-international-patients',
'<h2>Best Hospitals in India</h2><p>Discover the top-rated hospitals for international patients...</p>',
'Explore the best hospitals in India that cater to international patients with world-class facilities.',
'en', 'Top 10 hospitals in India for international patients with JCI accreditation and excellent facilities.',
'Hospitals', 'Dr. Priya Patel', 'published', NOW());

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `description`) VALUES
('site_name', 'CureConnect Medical Tourism', 'string', 'Website name'),
('site_tagline', 'World-Class Healthcare in India', 'string', 'Website tagline'),
('contact_email', 'info@cureconnect.in', 'string', 'Main contact email'),
('contact_phone', '+91-1800-123-4567', 'string', 'Main contact phone'),
('google_analytics_id', '', 'string', 'Google Analytics tracking ID'),
('google_adsense_client', '', 'string', 'Google AdSense client ID'),
('smtp_enabled', '0', 'boolean', 'Enable SMTP email'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode');

COMMIT;

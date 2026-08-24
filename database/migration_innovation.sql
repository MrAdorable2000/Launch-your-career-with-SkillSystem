-- ============================================================
-- SkillSystem — Innovation Features Migration
-- Run AFTER importing the base skillsystem.sql
--
-- Adds: badges, student_badges, mentorship_sessions,
--       event_registrations, career_roadmaps, qr_verifications,
--       ai_analyses
--
-- COMPATIBILITY: MySQL 5.7+ / MariaDB 10.2+ / XAMPP
-- All ALTER TABLE operations use a safe stored procedure that
-- checks if the column already exists before adding it.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table: badges — Achievement badges
-- ----------------------------
CREATE TABLE IF NOT EXISTS `badges` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `description` VARCHAR(500) DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT 'fa-trophy',
  `color` VARCHAR(20) DEFAULT 'primary',
  `criteria` JSON DEFAULT NULL,
  `points` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: student_badges — Awarded badges (junction)
-- ----------------------------
CREATE TABLE IF NOT EXISTS `student_badges` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `badge_id` INT UNSIGNED NOT NULL,
  `awarded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_student_badge` (`student_id`, `badge_id`),
  KEY `fk_sb_badge` (`badge_id`),
  CONSTRAINT `fk_sb_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sb_badge` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: mentorship_sessions — Booked sessions between mentor & student
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mentorship_sessions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `mentor_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `topic` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `scheduled_at` DATETIME NOT NULL,
  `duration_minutes` INT DEFAULT 60,
  `status` ENUM('requested','scheduled','completed','cancelled','no_show') DEFAULT 'requested',
  `meeting_link` VARCHAR(500) DEFAULT NULL,
  `feedback` TEXT DEFAULT NULL,
  `rating` TINYINT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_ms_mentor` (`mentor_id`),
  KEY `fk_ms_student` (`student_id`),
  CONSTRAINT `fk_ms_mentor` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ms_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: event_registrations — Students/users registering for events
-- ----------------------------
CREATE TABLE IF NOT EXISTS `event_registrations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `attended` TINYINT DEFAULT 0,
  UNIQUE KEY `uq_event_user` (`event_id`, `user_id`),
  KEY `fk_er_user` (`user_id`),
  CONSTRAINT `fk_er_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_er_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: career_roadmaps — Personalized roadmap for a student
-- ----------------------------
CREATE TABLE IF NOT EXISTS `career_roadmaps` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `target_role` VARCHAR(150) DEFAULT NULL,
  `milestones` JSON DEFAULT NULL,
  `progress_pct` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_student_roadmap` (`student_id`),
  CONSTRAINT `fk_cr_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: qr_verifications — QR codes for student/certificate verification
-- ----------------------------
CREATE TABLE IF NOT EXISTS `qr_verifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `target_type` ENUM('student','certificate','event') NOT NULL,
  `target_id` INT UNSIGNED NOT NULL,
  `code` VARCHAR(64) NOT NULL UNIQUE,
  `payload` TEXT DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `scans` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_qr_code` (`code`),
  INDEX `idx_qr_target` (`target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: ai_analyses — Cache of AI rule-based results
-- ----------------------------
CREATE TABLE IF NOT EXISTS `ai_analyses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `score` INT DEFAULT 0,
  `result` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ai_user_type` (`user_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Seed: Default badges
-- ----------------------------
INSERT IGNORE INTO `badges` (`name`, `slug`, `description`, `icon`, `color`, `criteria`, `points`) VALUES
('Profile Pioneer', 'profile-pioneer', 'Completed 80% of your profile', 'fa-user-check', 'primary', '{"profile_complete": 80}', 10),
('Skill Master', 'skill-master', 'Added 5 or more skills to your profile', 'fa-code', 'info', '{"min_skills": 5}', 15),
('Portfolio Pro', 'portfolio-pro', 'Showcased at least 3 portfolio projects', 'fa-folder-plus', 'success', '{"min_portfolio": 3}', 20),
('Go-Getter', 'go-getter', 'Submitted 5 or more job applications', 'fa-paper-plane', 'warning', '{"min_applications": 5}', 25),
('Certified Pro', 'certified-pro', 'Earned 2 or more verified certificates', 'fa-certificate', 'accent', '{"min_certificates": 2}', 30),
('Top Talent', 'top-talent', 'Profile 100% complete', 'fa-crown', 'accent', '{"profile_complete": 100}', 50);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Safe ALTER TABLE: Add verification_code to certificates
-- Uses a stored procedure to check if column exists first
-- (compatible with MySQL 5.7+ and MariaDB 10.2+)
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `ss_add_column_if_missing`$$
CREATE PROCEDURE `ss_add_column_if_missing`(
  IN tbl_name VARCHAR(100),
  IN col_name VARCHAR(100),
  IN col_def VARCHAR(500)
)
BEGIN
  DECLARE col_exists INT DEFAULT 0;
  SELECT COUNT(*) INTO col_exists
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tbl_name
      AND COLUMN_NAME = col_name;
  IF col_exists = 0 THEN
    SET @sql = CONCAT('ALTER TABLE `', tbl_name, '` ADD COLUMN `', col_name, '` ', col_def);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$

DELIMITER ;

-- Add verification_code to certificates table
CALL `ss_add_column_if_missing`('certificates', 'verification_code', 'VARCHAR(64) NULL UNIQUE AFTER `certificate_number`');

-- Backfill verification codes for existing certificates
UPDATE `certificates` SET `verification_code` = CONCAT('SS-', UPPER(SUBSTRING(MD5(RAND()), 1, 12))) WHERE `verification_code` IS NULL;

-- Clean up the procedure (optional — can keep for future use)
-- DROP PROCEDURE IF EXISTS `ss_add_column_if_missing`;

-- ============================================================
-- Done. Verify with:
--   DESCRIBE certificates;
--   SELECT * FROM badges;
--   SHOW TABLES LIKE '%badge%';
-- ============================================================

-- ============================================================
-- Language preference: Add language column to users table
-- ============================================================
CALL `ss_add_column_if_missing`('users', 'language', "VARCHAR(5) DEFAULT 'en' AFTER `phone`");

-- Backfill default language for existing users
UPDATE `users` SET `language` = 'en' WHERE `language` IS NULL;

-- ============================================================
-- Branding & Social Media Settings
-- ============================================================
INSERT IGNORE INTO `settings` (`key`, `value`, `type`) VALUES
('site_logo', '', 'string'),
('site_favicon', '', 'string'),
('site_keywords', 'skills, education, jobs, careers, internships, Rwanda', 'string'),
('contact_phone', '+250788000001', 'string'),
('contact_address', 'Kigali, Rwanda', 'string'),
('footer_text', 'SkillSystem — Connecting student talent with real-world opportunities.', 'string'),
('social_facebook', 'https://facebook.com/skillsystem', 'string'),
('social_twitter', 'https://twitter.com/skillsystem', 'string'),
('social_linkedin', 'https://linkedin.com/company/skillsystem', 'string'),
('social_instagram', 'https://instagram.com/skillsystem', 'string'),
('social_youtube', 'https://youtube.com/@skillsystem', 'string'),
('social_whatsapp', '+250788000001', 'string'),
('social_telegram', '', 'string'),
('social_github', 'https://github.com/skillsystem', 'string'),
('google_analytics_id', '', 'string'),
('facebook_pixel_id', '', 'string');

-- ============================================================
-- Homepage Content Management
-- ============================================================
CREATE TABLE IF NOT EXISTS `homepage_content` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `section` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `subtitle` VARCHAR(500) DEFAULT NULL,
  `body` TEXT DEFAULT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `video_url` VARCHAR(500) DEFAULT NULL,
  `link_url` VARCHAR(500) DEFAULT NULL,
  `link_text` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_hc_section` (`section`),
  INDEX `idx_hc_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default homepage content
INSERT IGNORE INTO `homepage_content` (`section`, `title`, `subtitle`, `body`, `sort_order`, `is_active`) VALUES
('hero', 'Launch your career with SkillSystem', 'The all-in-one platform connecting student talent with real-world opportunities. Build your portfolio, get AI-powered career recommendations, and land your dream job.', '', 1, 1),
('announcement', 'Welcome to SkillSystem v3.0!', 'Our new premium dashboard is now live with AI insights, real-time analytics, and a beautiful new design.', 'Check out the new features today!', 1, 1),
('announcement', 'Career Fair 2025 — Registration Open', 'Join us at the Kigali Convention Centre on February 15, 2025. Meet 50+ employers hiring students.', 'Register now to secure your spot!', 2, 1),
('video', 'How SkillSystem Works', 'Watch this 2-minute overview of how SkillSystem connects students with employers.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 1, 1),
('video', 'Student Success Story — Jean Pierre', 'From student to hired developer in 3 weeks. Watch Jean Pierre''s journey with SkillSystem.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 2, 0),
('event', 'SkillSystem Career Fair 2025', 'Annual career fair connecting students with top employers in Rwanda.', 'Kigali Convention Centre', 1, 1),
('event', 'AI for Rwanda Hackathon', '48-hour hackathon building AI solutions for Rwanda.', 'University of Rwanda - CBE Campus', 2, 1),
('event', 'Resume Writing Workshop', 'Interactive workshop on writing effective resumes and cover letters.', 'Online (Zoom)', 3, 1);

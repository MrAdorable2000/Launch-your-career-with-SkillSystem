-- ============================================
-- SkillSystem Database - Complete Schema & Data
-- FIXED: Tables ordered so every foreign key
-- references a table that already exists.
-- ============================================
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+02:00";
SET NAMES utf8mb4;

-- ============================================
-- TIER 1: No dependencies (foundational tables)
-- ============================================

-- ----------------------------
-- Table: roles
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `cover_letters`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `analytics`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `ratings`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `discussions`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `interview_results`;
DROP TABLE IF EXISTS `interviews`;
DROP TABLE IF EXISTS `resumes`;
DROP TABLE IF EXISTS `certificates`;
DROP TABLE IF EXISTS `portfolios`;
DROP TABLE IF EXISTS `experience`;
DROP TABLE IF EXISTS `education`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `freelance_projects`;
DROP TABLE IF EXISTS `internships`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `student_skills`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `administrators`;
DROP TABLE IF EXISTS `mentors`;
DROP TABLE IF EXISTS `employers`;
DROP TABLE IF EXISTS `companies`;
DROP TABLE IF EXISTS `universities`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Administrator', 'admin', 'Full system access'),
(2, 'Student', 'student', 'Student account with portfolio and application access'),
(3, 'Employer', 'employer', 'Employer account with job posting access'),
(4, 'University', 'university', 'University account for student management'),
(5, 'Mentor', 'mentor', 'Mentor account for career guidance');

-- ----------------------------
-- Table: permissions
-- ----------------------------
CREATE TABLE `permissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `module` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Dashboard', 'view-dashboard', 'dashboard'),
('Manage Users', 'manage-users', 'admin'),
('Manage Jobs', 'manage-jobs', 'jobs'),
('Manage Internships', 'manage-internships', 'internships'),
('Manage Freelance', 'manage-freelance', 'freelance'),
('Manage Settings', 'manage-settings', 'settings'),
('View Analytics', 'view-analytics', 'analytics'),
('Manage Students', 'manage-students', 'students'),
('Manage Employers', 'manage-employers', 'employers'),
('Manage Universities', 'manage-universities', 'universities'),
('Manage Certificates', 'manage-certificates', 'certificates'),
('Manage Events', 'manage-events', 'events'),
('View Audit Logs', 'view-audit-logs', 'audit'),
('Post Jobs', 'post-jobs', 'jobs'),
('Apply Jobs', 'apply-jobs', 'jobs'),
('Build Portfolio', 'build-portfolio', 'portfolio'),
('Send Messages', 'send-messages', 'messages');

-- ----------------------------
-- Table: role_permissions
-- References: roles, permissions (both exist above)
-- ----------------------------
CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  KEY `fk_rp_permission` (`permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin gets all permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

-- Student permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2,1),(2,4),(2,5),(2,7),(2,14),(2,16),(2,17);

-- Employer permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(3,1),(3,3),(3,4),(3,5),(3,7),(3,13),(3,17);

-- University permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(4,1),(4,4),(4,7),(4,9),(4,17);

-- Mentor permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(5,1),(5,7),(5,17);

-- ============================================
-- TIER 2: Users (depends on roles)
-- ============================================

-- ----------------------------
-- Table: users
-- References: roles (exists above)
-- ----------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(500) DEFAULT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `status` ENUM('active','inactive','suspended','banned') DEFAULT 'active',
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_users_role` (`role_id`),
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_role_status` (`role_id`, `status`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password hash for "password": $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO `users` (`id`, `role_id`, `email`, `password`, `first_name`, `last_name`, `phone`, `email_verified_at`, `status`) VALUES
(1, 1, 'ethiennemugisha35@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ethienne', 'Mugisha', '+250788000001', NOW(), 'active'),
(2, 2, 'jean.pierre@ur.ac.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean Pierre', 'Habarugira', '+250788100001', NOW(), 'active'),
(3, 2, 'grace.uwimana@ur.ac.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace', 'Uwimana', '+250788100002', NOW(), 'active'),
(4, 2, 'eric.niyonzima@cmu.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eric', 'Niyonzima', '+250788100003', NOW(), 'active'),
(5, 2, 'sarah.mukantwari@alu.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Mukantwari', '+250788100004', NOW(), 'active'),
(6, 2, 'alice.uwase@ur.ac.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Uwase', '+250788100005', NOW(), 'active'),
(7, 2, 'claude.bizimana@ur.ac.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Claude', 'Bizimana', '+250788100006', NOW(), 'active'),
(8, 2, 'diane.mukamana@inatek.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Diane', 'Mukamana', '+250788100007', NOW(), 'active'),
(9, 3, 'admin@rwandatech.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Patrick', 'Mugisha', '+250788200001', NOW(), 'active'),
(10, 3, 'hr@bankofkigali.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie', 'Ishimwe', '+250788200002', NOW(), 'active'),
(11, 3, 'jobs@mtn.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Olivier', 'Nshimirimana', '+250788200003', NOW(), 'active'),
(12, 3, 'careers@zipline.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Faustin', 'Habyarimana', '+250788200004', NOW(), 'active'),
(13, 4, 'admin@universityofrwanda.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Jean', 'Kagame', '+250788300001', NOW(), 'active'),
(14, 4, 'career@cmurwanda.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Claire', 'Nyirahabimana', '+250788300002', NOW(), 'active'),
(15, 5, 'marie.claire@mentor.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie Claire', 'Uwimana', '+250788400001', NOW(), 'active'),
(16, 5, 'patrick.mentor@mentor.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Patrick', 'Nsengiyumva', '+250788400002', NOW(), 'active');

-- ============================================
-- TIER 3: Role profiles (depend on users)
-- ============================================

-- ----------------------------
-- Table: universities
-- References: users (exists above)
-- MOVED HERE — must exist BEFORE students table
-- ----------------------------
CREATE TABLE `universities` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL UNIQUE,
  `uni_name` VARCHAR(255) NOT NULL,
  `uni_logo` VARCHAR(500) DEFAULT NULL,
  `uni_code` VARCHAR(20) DEFAULT NULL,
  `location` VARCHAR(150) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `total_students` INT UNSIGNED DEFAULT 0,
  `career_center_email` VARCHAR(191) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_uni_user` (`user_id`),
  CONSTRAINT `fk_uni_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `universities` (`id`, `user_id`, `uni_name`, `uni_code`, `location`, `description`, `website`, `total_students`, `career_center_email`) VALUES
(1, 13, 'University of Rwanda', 'UR', 'Kigali', 'The largest and oldest university in Rwanda with multiple colleges across the country.', 'https://www.ur.ac.rw', 32000, 'career@ur.ac.rw'),
(2, 14, 'Carnegie Mellon University Rwanda', 'CMU-R', 'Kigali', 'Top-tier US university campus in Kigali offering graduate programs in IT and ECE.', 'https://rwanda.cmu.edu', 450, 'career@cmurwanda.rw'),
(3, NULL, 'African Leadership University Rwanda', 'ALU', 'Kigali', 'Pan-African university focused on developing entrepreneurial leaders for the continent.', 'https://www.alu.edu', 1200, 'careers@alu.edu');

-- ----------------------------
-- Table: students
-- References: users (exists), universities (exists NOW ✓)
-- ----------------------------
CREATE TABLE `students` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `university_id` INT UNSIGNED DEFAULT NULL,
  `student_id_number` VARCHAR(50) DEFAULT NULL,
  `department` VARCHAR(150) DEFAULT NULL,
  `year_of_study` TINYINT UNSIGNED DEFAULT NULL,
  `gpa` DECIMAL(4,2) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `skills_summary` TEXT DEFAULT NULL,
  `linkedin` VARCHAR(255) DEFAULT NULL,
  `github` VARCHAR(255) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `profile_completion` TINYINT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_students_user` (`user_id`),
  KEY `fk_students_uni` (`university_id`),
  CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_students_uni` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `students` (`id`, `user_id`, `university_id`, `student_id_number`, `department`, `year_of_study`, `gpa`, `bio`, `profile_completion`) VALUES
(1, 2, 1, 'UR2020/CS/001', 'Computer Science & Engineering', 4, 3.72, 'Passionate full-stack developer with experience in React, Node.js, and cloud technologies. Seeking opportunities to build impactful software solutions.', 85),
(2, 3, 1, 'UR2021/IS/015', 'Information Systems', 3, 3.58, 'Data-driven student with strong analytical skills. Experienced in Python, SQL, and data visualization. Passionate about using data to solve real-world problems.', 72),
(3, 4, 2, 'CMU2022/MSE/003', 'Software Engineering', 2, 3.85, 'Graduate student at CMU-R with a focus on distributed systems and machine learning. Previously worked at Zipline Rwanda as a software intern.', 90),
(4, 5, 3, 'ALU2021/CS/008', 'Computer Science', 3, 3.65, 'Creative technologist and UI/UX enthusiast. Building digital products that solve African challenges. Freelance designer and developer.', 78),
(5, 6, 1, 'UR2022/CE/022', 'Computer Engineering', 2, 3.45, 'Hardware and networking enthusiast with CCNA certification. Interested in IoT and embedded systems for agricultural applications.', 60),
(6, 7, 1, 'UR2021/IT/009', 'Information Technology', 3, 3.51, 'Cybersecurity-focused student with bug bounty experience. Active CTF player and member of the UR Cybersecurity Club.', 68),
(7, 8, NULL, 'INATEK2020/CS/004', 'Computer Science', 4, 3.40, 'Full-stack developer proficient in PHP, Laravel, and MySQL. Building web applications for local businesses in Eastern Province.', 55);

-- ----------------------------
-- Table: employers
-- References: users (exists)
-- ----------------------------
CREATE TABLE `employers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `company_name` VARCHAR(255) NOT NULL,
  `company_logo` VARCHAR(500) DEFAULT NULL,
  `industry` VARCHAR(150) DEFAULT NULL,
  `company_size` VARCHAR(50) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `location` VARCHAR(150) DEFAULT NULL,
  `founded_year` YEAR DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_employers_user` (`user_id`),
  CONSTRAINT `fk_employers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `employers` (`id`, `user_id`, `company_name`, `industry`, `company_size`, `website`, `description`, `location`, `founded_year`) VALUES
(1, 9, 'Rwanda Tech Ltd', 'Technology', '51-200', 'https://rwandatech.rw', 'Leading software development company in Rwanda building innovative solutions for businesses across East Africa.', 'Kigali', 2015),
(2, 10, 'Bank of Kigali', 'Banking & Finance', '1000+', 'https://bk.rw', 'The largest commercial bank in Rwanda by assets, offering digital banking and financial services.', 'Kigali', 1964),
(3, 11, 'MTN Rwanda', 'Telecommunications', '500-1000', 'https://mtn.co.rw', 'Leading telecommunications provider in Rwanda with mobile money, data, and enterprise solutions.', 'Kigali', 1998),
(4, 12, 'Zipline Rwanda', 'Drone Delivery / Logistics', '200-500', 'https://flyzipline.com', 'Autonomous drone delivery service delivering medical supplies to hospitals across Rwanda.', 'Musanze', 2016);

-- ----------------------------
-- Table: companies
-- References: employers (exists)
-- ----------------------------
CREATE TABLE `companies` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employer_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `logo` VARCHAR(500) DEFAULT NULL,
  `industry` VARCHAR(150) DEFAULT NULL,
  `size` VARCHAR(50) DEFAULT NULL,
  `location` VARCHAR(150) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `verified` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_companies_employer` (`employer_id`),
  CONSTRAINT `fk_companies_employer` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `companies` (`employer_id`, `name`, `industry`, `size`, `location`, `website`, `verified`) VALUES
(1, 'Rwanda Tech Ltd', 'Technology', '51-200', 'Kigali', 'https://rwandatech.rw', 1),
(2, 'Bank of Kigali', 'Banking & Finance', '1000+', 'Kigali', 'https://bk.rw', 1),
(3, 'MTN Rwanda', 'Telecommunications', '500-1000', 'Kigali', 'https://mtn.co.rw', 1),
(4, 'Zipline Rwanda', 'Drone Delivery', '200-500', 'Musanze', 'https://flyzipline.com', 1),
(NULL, 'HeHe Ltd', 'Technology', '11-50', 'Kigali', 'https://hehe.rw', 1),
(NULL, 'Rwanda Development Board', 'Government', '100-500', 'Kigali', 'https://rdb.rw', 1),
(NULL, 'Airtel Rwanda', 'Telecommunications', '200-500', 'Kigali', 'https://airtel.rw', 1),
(NULL, 'ACGROUP RW', 'Technology', '11-50', 'Kigali', 'https://acgroup.rw', 0),
(NULL, 'Villgro Africa', 'Social Enterprise', '11-50', 'Kigali', 'https://villgroafrica.org', 1),
(NULL, 'One Acre Fund Rwanda', 'Agriculture', '500-1000', 'Kigali', 'https://oneacrefund.org', 1),
(NULL, 'Cogebanque', 'Banking & Finance', '200-500', 'Kigali', 'https://cogebanque.co.rw', 1),
(NULL, 'Africa Digital Hub', 'Technology', '11-50', 'Kigali', NULL, 0);

-- ----------------------------
-- Table: mentors
-- References: users (exists)
-- ----------------------------
CREATE TABLE `mentors` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `specialization` VARCHAR(200) DEFAULT NULL,
  `company` VARCHAR(255) DEFAULT NULL,
  `title` VARCHAR(150) DEFAULT NULL,
  `years_experience` TINYINT UNSIGNED DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `hourly_rate` DECIMAL(10,2) DEFAULT NULL,
  `availability` ENUM('available','busy','unavailable') DEFAULT 'available',
  `linkedin` VARCHAR(255) DEFAULT NULL,
  `total_sessions` INT UNSIGNED DEFAULT 0,
  `rating` DECIMAL(3,2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_mentors_user` (`user_id`),
  CONSTRAINT `fk_mentors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mentors` (`id`, `user_id`, `specialization`, `company`, `title`, `years_experience`, `bio`, `hourly_rate`, `availability`, `total_sessions`, `rating`) VALUES
(1, 15, 'Software Engineering, System Design', 'Remote (ex-Meta)', 'Senior Software Engineer', 12, 'Former Meta engineer with 12 years of industry experience. Passionate about mentoring the next generation of African software engineers. Specializes in distributed systems, React, and system design.', 25.00, 'available', 142, 4.90),
(2, 16, 'Cloud Computing, DevOps, AWS', 'Rwanda Cloud Solutions', 'Principal Cloud Architect', 10, 'AWS certified solutions architect with deep expertise in cloud migration, Kubernetes, and DevOps practices. Helps companies and individuals leverage cloud technologies effectively.', 20.00, 'available', 98, 4.80);

-- ----------------------------
-- Table: administrators
-- References: users (exists)
-- ----------------------------
CREATE TABLE `administrators` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `department` VARCHAR(100) DEFAULT 'System Administration',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_admin_user` (`user_id`),
  CONSTRAINT `fk_admin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `administrators` (`user_id`, `department`) VALUES (1, 'System Administration');

-- ============================================
-- TIER 4: Content tables (depend on tier 2-3)
-- ============================================

-- ----------------------------
-- Table: skills
-- No dependencies
-- ----------------------------
CREATE TABLE `skills` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `category` VARCHAR(100) DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `skills` (`name`, `category`, `icon`) VALUES
('JavaScript', 'Programming', 'fa-js'),
('Python', 'Programming', 'fa-python'),
('PHP', 'Programming', 'fa-php'),
('Java', 'Programming', 'fa-java'),
('React', 'Frontend', 'fa-react'),
('Vue.js', 'Frontend', 'fa-vuejs'),
('Node.js', 'Backend', 'fa-node-js'),
('Laravel', 'Backend', 'fa-laravel'),
('SQL', 'Database', 'fa-database'),
('MongoDB', 'Database', 'fa-leaf'),
('AWS', 'Cloud', 'fa-aws'),
('Docker', 'DevOps', 'fa-docker'),
('Git', 'Tools', 'fa-git-alt'),
('HTML/CSS', 'Frontend', 'fa-html5'),
('TypeScript', 'Programming', 'fa-code'),
('Flutter', 'Mobile', 'fa-mobile-alt'),
('Cybersecurity', 'Security', 'fa-shield-halved'),
('Data Science', 'Data', 'fa-chart-bar'),
('Machine Learning', 'AI', 'fa-brain'),
('UI/UX Design', 'Design', 'fa-palette'),
('Graphic Design', 'Design', 'fa-paint-brush'),
('Digital Marketing', 'Marketing', 'fa-bullhorn'),
('Project Management', 'Management', 'fa-tasks'),
('Networking', 'Infrastructure', 'fa-network-wired'),
('Linux', 'Infrastructure', 'fa-linux'),
('C++', 'Programming', 'fa-code'),
('C#', 'Programming', 'fa-code'),
('Swift', 'Mobile', 'fa-mobile-alt'),
('Kubernetes', 'DevOps', 'fa-dharmachakra'),
('Terraform', 'DevOps', 'fa-cogs');

-- ----------------------------
-- Table: student_skills
-- References: students, skills (both exist)
-- ----------------------------
CREATE TABLE `student_skills` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `skill_id` INT UNSIGNED NOT NULL,
  `proficiency_level` ENUM('beginner','intermediate','advanced','expert') DEFAULT 'intermediate',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_student_skill` (`student_id`, `skill_id`),
  KEY `fk_ss_student` (`student_id`),
  KEY `fk_ss_skill` (`skill_id`),
  CONSTRAINT `fk_ss_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ss_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `student_skills` (`student_id`, `skill_id`, `proficiency_level`) VALUES
(1, 1, 'advanced'), (1, 5, 'advanced'), (1, 7, 'intermediate'), (1, 9, 'intermediate'), (1, 13, 'advanced'), (1, 14, 'advanced'), (1, 15, 'intermediate'), (1, 11, 'beginner'),
(2, 2, 'advanced'), (2, 9, 'advanced'), (2, 18, 'intermediate'), (2, 19, 'beginner'), (2, 13, 'intermediate'),
(3, 1, 'expert'), (3, 15, 'advanced'), (3, 7, 'expert'), (3, 11, 'advanced'), (3, 12, 'advanced'), (3, 29, 'intermediate'), (3, 13, 'expert'),
(4, 14, 'advanced'), (4, 5, 'intermediate'), (4, 20, 'advanced'), (4, 21, 'advanced'), (4, 13, 'intermediate'), (4, 22, 'beginner'),
(5, 24, 'advanced'), (5, 25, 'intermediate'), (5, 1, 'beginner'), (5, 9, 'intermediate'),
(6, 17, 'advanced'), (6, 25, 'advanced'), (6, 2, 'intermediate'), (6, 13, 'advanced'),
(7, 3, 'advanced'), (7, 8, 'advanced'), (7, 9, 'advanced'), (7, 14, 'advanced'), (7, 13, 'advanced');

-- ----------------------------
-- Table: jobs
-- References: employers, companies (both exist)
-- ----------------------------
CREATE TABLE `jobs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employer_id` INT UNSIGNED NOT NULL,
  `company_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `requirements` TEXT DEFAULT NULL,
  `responsibilities` TEXT DEFAULT NULL,
  `salary_min` INT UNSIGNED DEFAULT NULL,
  `salary_max` INT UNSIGNED DEFAULT NULL,
  `salary_currency` VARCHAR(10) DEFAULT 'RWF',
  `location` VARCHAR(150) DEFAULT NULL,
  `type` ENUM('full-time','part-time','contract','freelance') DEFAULT 'full-time',
  `remote` TINYINT(1) DEFAULT 0,
  `deadline` DATE DEFAULT NULL,
  `status` ENUM('draft','published','closed','archived') DEFAULT 'published',
  `views_count` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_jobs_employer` (`employer_id`),
  KEY `fk_jobs_company` (`company_id`),
  KEY `idx_jobs_status` (`status`),
  KEY `idx_jobs_type` (`type`),
  CONSTRAINT `fk_jobs_employer` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jobs_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jobs` (`employer_id`, `company_id`, `title`, `description`, `requirements`, `responsibilities`, `salary_min`, `salary_max`, `salary_currency`, `location`, `type`, `remote`, `deadline`, `status`, `views_count`) VALUES
(1, 1, 'Senior Frontend Developer', 'We are looking for an experienced Frontend Developer to join our team and build modern web applications using React and TypeScript.', 'React, TypeScript, 3+ years experience, REST APIs, Git', 'Build responsive interfaces, write clean code, participate in code reviews', 800000, 1200000, 'RWF', 'Kigali', 'full-time', 1, '2025-02-28', 'published', 342),
(1, 1, 'Backend Developer (PHP/Laravel)', 'Join our backend team to build robust APIs and microservices for enterprise clients across East Africa.', 'PHP 8, Laravel, MySQL, Redis, Docker, 2+ years', 'Build and maintain API endpoints, write tests, document code', 600000, 900000, 'RWF', 'Kigali', 'full-time', 0, '2025-03-15', 'published', 215),
(2, 2, 'Cybersecurity Analyst', 'Bank of Kigali is seeking a Cybersecurity Analyst to monitor, detect, and respond to security threats across our digital banking infrastructure.', 'CISSP or equivalent, SIEM tools, Python, network security, 2+ years', 'Monitor threats, analyze incidents, write reports, implement security measures', 700000, 1100000, 'RWF', 'Kigali', 'full-time', 0, '2025-03-01', 'published', 456),
(3, 3, 'Data Engineer', 'Build and maintain data pipelines that power MTN Rwanda analytics and business intelligence platforms.', 'Python, SQL, AWS/Azure, Airflow, 3+ years experience', 'Design pipelines, optimize queries, create dashboards', 900000, 1400000, 'RWF', 'Kigali', 'full-time', 1, '2025-03-20', 'published', 289),
(3, 3, 'Mobile App Developer', 'Develop and maintain MTN Rwanda mobile applications for Android and iOS platforms.', 'Flutter or React Native, REST APIs, Firebase, 2+ years', 'Build features, fix bugs, publish updates', 600000, 950000, 'RWF', 'Kigali', 'full-time', 1, '2025-02-20', 'published', 178),
(4, 4, 'Software Engineer - Autonomous Systems', 'Work on the software that powers Zipline autonomous drone delivery fleet in Rwanda.', 'Python, C++, ROS, computer vision, distributed systems', 'Develop flight software, test systems, collaborate with hardware team', 1200000, 1800000, 'RWF', 'Musanze', 'full-time', 0, '2025-04-01', 'published', 523),
(1, 1, 'UI/UX Designer', 'Design intuitive and beautiful interfaces for our SaaS products used by businesses across Africa.', 'Figma, user research, prototyping, design systems, 2+ years', 'Create wireframes, design components, conduct user testing', 500000, 800000, 'RWF', 'Kigali', 'full-time', 1, '2025-03-10', 'published', 134),
(2, 2, 'Database Administrator', 'Manage and optimize Bank of Kigali database infrastructure supporting millions of transactions.', 'MySQL, PostgreSQL, backup/recovery, performance tuning, 3+ years', 'Optimize queries, manage backups, ensure availability', 800000, 1200000, 'RWF', 'Kigali', 'full-time', 0, '2025-03-25', 'published', 98),
(1, 1, 'DevOps Engineer', 'Build and maintain CI/CD pipelines, cloud infrastructure, and monitoring systems.', 'AWS, Docker, Kubernetes, Terraform, Jenkins, 2+ years', 'Set up pipelines, manage infrastructure, monitor systems', 900000, 1500000, 'RWF', 'Kigali', 'full-time', 1, '2025-04-15', 'published', 167),
(1, 1, 'Project Manager', 'Lead cross-functional teams to deliver software projects on time and within budget.', 'PMP/Agile, Jira, stakeholder management, 4+ years', 'Plan sprints, manage risks, report progress', 1000000, 1600000, 'RWF', 'Kigali', 'full-time', 0, '2025-03-30', 'published', 89),
(3, 3, 'Digital Marketing Specialist', 'Drive MTN Rwanda digital marketing campaigns across social media, email, and web channels.', 'Google Ads, social media, analytics, content marketing, 2+ years', 'Create campaigns, analyze metrics, optimize spend', 500000, 750000, 'RWF', 'Kigali', 'full-time', 1, '2025-02-25', 'published', 201),
(1, 1, 'Intern - Software Development', '6-month internship program for final-year students to gain real-world software development experience.', 'Basic programming knowledge, willingness to learn, team player', 'Assist senior developers, write code, attend meetings', 150000, 150000, 'RWF', 'Kigali', 'contract', 0, '2025-02-15', 'published', 567);

-- ----------------------------
-- Table: internships
-- References: employers, companies (both exist)
-- ----------------------------
CREATE TABLE `internships` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employer_id` INT UNSIGNED NOT NULL,
  `company_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `requirements` TEXT DEFAULT NULL,
  `duration` INT UNSIGNED NOT NULL,
  `duration_unit` ENUM('weeks','months') DEFAULT 'months',
  `allowance` INT UNSIGNED DEFAULT 0,
  `allowance_currency` VARCHAR(10) DEFAULT 'RWF',
  `location` VARCHAR(150) DEFAULT NULL,
  `deadline` DATE DEFAULT NULL,
  `positions_available` TINYINT UNSIGNED DEFAULT 1,
  `status` ENUM('draft','published','closed','archived') DEFAULT 'published',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_intern_employer` (`employer_id`),
  KEY `fk_intern_company` (`company_id`),
  CONSTRAINT `fk_intern_employer` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_intern_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `internships` (`employer_id`, `company_id`, `title`, `description`, `requirements`, `duration`, `duration_unit`, `allowance`, `location`, `deadline`, `positions_available`, `status`) VALUES
(1, 1, 'Software Engineering Intern', 'Join our engineering team for a 6-month internship program. Work on real projects with mentorship from senior engineers.', 'Python, JavaScript, Git, problem-solving skills', 6, 'months', 150000, 'Kigali', '2025-02-28', 5, 'published'),
(2, 2, 'Cybersecurity Intern', 'Learn cybersecurity operations in a real banking environment. Monitor threats, analyze incidents, and build security tools.', 'Basic networking, Linux, Python, passion for security', 4, 'months', 120000, 'Kigali', '2025-03-15', 3, 'published'),
(3, 3, 'Data Analytics Intern', 'Work with MTN data team to analyze customer behavior, build dashboards, and generate business insights.', 'SQL, Python basics, Excel, analytical thinking', 3, 'months', 100000, 'Kigali', '2025-03-01', 4, 'published'),
(4, 4, 'Engineering Intern - Drones', 'Hands-on internship working with autonomous drone systems including flight software and ground control.', 'C++, Python, embedded systems, physics/math', 6, 'months', 200000, 'Musanze', '2025-04-01', 2, 'published'),
(1, 1, 'UI/UX Design Intern', 'Design user interfaces for web and mobile applications. Conduct user research and create prototypes.', 'Figma basics, design thinking, creativity', 3, 'months', 100000, 'Kigali', '2025-03-10', 2, 'published'),
(3, 3, 'Network Engineering Intern', 'Learn to manage MTN network infrastructure including 4G/5G, fiber, and core network systems.', 'CCNA preferred, networking fundamentals, Linux', 4, 'months', 130000, 'Kigali', '2025-03-20', 3, 'published');

-- ----------------------------
-- Table: freelance_projects
-- References: employers (exists)
-- ----------------------------
CREATE TABLE `freelance_projects` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employer_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `budget_min` INT UNSIGNED DEFAULT NULL,
  `budget_max` INT UNSIGNED DEFAULT NULL,
  `currency` VARCHAR(10) DEFAULT 'RWF',
  `skills_required` TEXT DEFAULT NULL,
  `deadline` DATE DEFAULT NULL,
  `status` ENUM('open','in-progress','completed','cancelled') DEFAULT 'open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_freelance_employer` (`employer_id`),
  CONSTRAINT `fk_freelance_employer` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `freelance_projects` (`employer_id`, `title`, `description`, `budget_min`, `budget_max`, `skills_required`, `deadline`, `status`) VALUES
(1, 'E-Commerce Website Development', 'Build a modern e-commerce website with payment integration for a local retail business.', 500000, 800000, 'React, Node.js, PostgreSQL, Stripe', '2025-03-31', 'open'),
(1, 'Company Logo & Brand Identity', 'Design a complete brand identity including logo, color palette, typography, and brand guidelines.', 100000, 200000, 'Graphic Design, Branding, Illustrator', '2025-02-28', 'open'),
(3, 'Mobile App - Customer Loyalty', 'Develop a cross-platform mobile app for MTN customer loyalty program.', 800000, 1200000, 'Flutter, Firebase, REST API', '2025-04-30', 'open'),
(2, 'Data Migration Tool', 'Build a tool to migrate legacy database records to the new banking system.', 300000, 500000, 'Python, SQL, ETL, Data Validation', '2025-03-15', 'open'),
(1, 'Poster & Social Media Designs', 'Create marketing posters and social media graphics for a product launch.', 50000, 100000, 'Graphic Design, Photoshop, Canva', '2025-02-20', 'open'),
(3, 'Network Security Assessment', 'Comprehensive network security assessment with vulnerability scanning and report.', 400000, 700000, 'Cybersecurity, Penetration Testing, Network Analysis', '2025-04-15', 'open');

-- ----------------------------
-- Table: applications
-- References: users, jobs, internships, freelance_projects (all exist)
-- ----------------------------
CREATE TABLE `applications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `job_id` INT UNSIGNED DEFAULT NULL,
  `internship_id` INT UNSIGNED DEFAULT NULL,
  `freelance_id` INT UNSIGNED DEFAULT NULL,
  `type` ENUM('job','internship','freelance') NOT NULL,
  `cover_letter` TEXT DEFAULT NULL,
  `status` ENUM('pending','reviewing','shortlisted','interview','offered','rejected','withdrawn') DEFAULT 'pending',
  `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_app_user` (`user_id`),
  KEY `fk_app_job` (`job_id`),
  KEY `fk_app_intern` (`internship_id`),
  KEY `fk_app_freelance` (`freelance_id`),
  KEY `idx_app_status` (`status`),
  CONSTRAINT `fk_app_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_intern` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_freelance` FOREIGN KEY (`freelance_id`) REFERENCES `freelance_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `applications` (`user_id`, `job_id`, `internship_id`, `freelance_id`, `type`, `cover_letter`, `status`) VALUES
(2, 1, NULL, NULL, 'job', 'Dear Hiring Manager, I am a final-year Computer Science student at the University of Rwanda with strong React and TypeScript skills.', 'shortlisted'),
(3, 4, NULL, NULL, 'job', 'I am a data-driven Information Systems student with experience in Python, SQL, and data visualization.', 'interview'),
(4, 6, NULL, NULL, 'job', 'As a CMU-R graduate student specializing in distributed systems, I believe my skills align perfectly with Zipline.', 'offered'),
(5, 7, NULL, NULL, 'job', 'I am a creative CS student at ALU with a strong portfolio in UI/UX design.', 'pending'),
(6, 3, NULL, NULL, 'job', 'With my cybersecurity focus and CTF experience, I am well-prepared for this role.', 'reviewing'),
(2, NULL, 1, NULL, 'internship', 'I am applying for the Software Engineering Internship.', 'shortlisted'),
(3, NULL, 3, NULL, 'internship', 'My data analysis skills make me a strong candidate.', 'pending'),
(7, NULL, NULL, 1, 'freelance', 'I have built several e-commerce sites using Laravel.', 'pending'),
(5, NULL, NULL, 2, 'freelance', 'I have created brand identities for multiple startups.', 'shortlisted'),
(2, 2, NULL, NULL, 'job', 'I have strong PHP and Laravel skills.', 'pending'),
(6, 8, NULL, NULL, 'job', 'With my database knowledge and security background, I can manage databases.', 'pending'),
(8, 9, NULL, NULL, 'job', 'I have DevOps experience with Docker and AWS.', 'pending');

-- ----------------------------
-- Table: education
-- References: students (exists)
-- ----------------------------
CREATE TABLE `education` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `institution` VARCHAR(255) NOT NULL,
  `degree` VARCHAR(100) NOT NULL,
  `field_of_study` VARCHAR(150) NOT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `gpa` DECIMAL(4,2) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_edu_student` (`student_id`),
  CONSTRAINT `fk_edu_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `education` (`student_id`, `institution`, `degree`, `field_of_study`, `start_date`, `end_date`, `gpa`) VALUES
(1, 'University of Rwanda', 'Bachelor of Science', 'Computer Science & Engineering', '2020-09-01', '2024-07-31', 3.72),
(2, 'University of Rwanda', 'Bachelor of Science', 'Information Systems', '2021-09-01', '2025-07-31', 3.58),
(3, 'Carnegie Mellon University Rwanda', 'Master of Science', 'Software Engineering', '2022-08-01', '2024-05-31', 3.85),
(4, 'African Leadership University', 'Bachelor of Science', 'Computer Science', '2021-09-01', '2025-07-31', 3.65),
(5, 'University of Rwanda', 'Bachelor of Engineering', 'Computer Engineering', '2022-09-01', '2026-07-31', 3.45),
(6, 'University of Rwanda', 'Bachelor of Science', 'Information Technology', '2021-09-01', '2025-07-31', 3.51),
(7, 'INATEK', 'Bachelor of Science', 'Computer Science', '2020-09-01', '2024-07-31', 3.40);

-- ----------------------------
-- Table: experience
-- References: students (exists)
-- ----------------------------
CREATE TABLE `experience` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `company_name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(150) NOT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `is_current` TINYINT(1) DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_exp_student` (`student_id`),
  CONSTRAINT `fk_exp_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `experience` (`student_id`, `company_name`, `position`, `start_date`, `end_date`, `is_current`, `description`) VALUES
(1, 'Rwanda Tech Ltd', 'Frontend Developer Intern', '2023-06-01', '2023-12-31', 0, 'Built responsive web interfaces using React and TypeScript.'),
(1, 'Freelance', 'Web Developer', '2024-01-01', NULL, 1, 'Building custom websites for local businesses.'),
(3, 'Zipline Rwanda', 'Software Engineering Intern', '2023-01-15', '2023-08-15', 0, 'Worked on flight planning software for autonomous drone delivery.'),
(4, 'HeHe Ltd', 'UI/UX Design Intern', '2023-06-01', '2023-09-30', 0, 'Designed user interfaces for mobile applications.'),
(4, 'Freelance', 'Graphic & UI Designer', '2023-10-01', NULL, 1, 'Design services for startups in Kigali.'),
(6, 'Rwanda Information Society Authority', 'Cybersecurity Intern', '2023-07-01', '2023-12-31', 0, 'Assisted in vulnerability assessments and incident response.');

-- ----------------------------
-- Table: portfolios
-- References: students (exists)
-- ----------------------------
CREATE TABLE `portfolios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(500) DEFAULT NULL,
  `url` VARCHAR(500) DEFAULT NULL,
  `technologies` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_portfolio_student` (`student_id`),
  CONSTRAINT `fk_portfolio_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `portfolios` (`student_id`, `title`, `description`, `url`, `technologies`) VALUES
(1, 'SkillSystem Rwanda Platform', 'A full-stack web platform connecting Rwandan students with jobs and internships.', 'https://github.com/jeanpierre/skilllink', 'React, Node.js, MongoDB, Socket.io'),
(1, 'E-Commerce Dashboard', 'Admin dashboard for managing products, orders, and customers.', 'https://github.com/jeanpierre/ecom-dash', 'React, TypeScript, Chart.js, Tailwind CSS'),
(3, 'Drone Flight Planner', 'Web-based tool for planning autonomous drone delivery routes.', 'https://github.com/eric/drone-planner', 'Python, Flask, Leaflet.js, PostgreSQL'),
(4, 'AgriTech Mobile App', 'Mobile application helping Rwandan farmers track crop prices and market locations.', 'https://github.com/sarah/agritech', 'Flutter, Dart, Firebase, Google Maps API'),
(6, 'Network Security Scanner', 'Automated vulnerability scanning tool with reporting dashboard.', 'https://github.com/claude/net-scanner', 'Python, Nmap, Flask, Vue.js');

-- ----------------------------
-- Table: certificates
-- References: students (exists)
-- ----------------------------
CREATE TABLE `certificates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `issuing_organization` VARCHAR(255) NOT NULL,
  `certificate_number` VARCHAR(100) DEFAULT NULL,
  `issued_date` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `image` VARCHAR(500) DEFAULT NULL,
  `verified` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_cert_student` (`student_id`),
  CONSTRAINT `fk_cert_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `certificates` (`student_id`, `title`, `issuing_organization`, `certificate_number`, `issued_date`, `verified`) VALUES
(1, 'Meta Frontend Developer Professional Certificate', 'Meta (Coursera)', 'META-FE-2024-001', '2024-03-15', 1),
(5, 'Cisco Certified Network Associate (CCNA)', 'Cisco Systems', 'CCNA-2023-RW-0456', '2023-11-20', 1),
(5, 'CompTIA Network+', 'CompTIA', 'NET-2024-7823', '2024-01-10', 1),
(6, 'CompTIA Security+', 'CompTIA', 'SEC-2023-1234', '2023-09-15', 1),
(3, 'AWS Certified Solutions Architect Associate', 'Amazon Web Services', 'AWS-SAA-2024-5678', '2024-02-28', 1),
(7, 'PHP Certification', 'Zend Technologies', 'ZEND-PHP-2023-9012', '2023-06-30', 0);

-- ----------------------------
-- Table: resumes
-- References: students (exists)
-- ----------------------------
CREATE TABLE `resumes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) DEFAULT NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_resume_student` (`student_id`),
  CONSTRAINT `fk_resume_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `resumes` (`student_id`, `title`, `file_path`, `is_default`) VALUES
(1, 'Jean Pierre Habarugira - Resume', 'uploads/resumes/jean_pierre_resume.pdf', 1),
(2, 'Grace Uwimana - Resume', 'uploads/resumes/grace_uwimana_resume.pdf', 1),
(3, 'Eric Niyonzima - Resume', 'uploads/resumes/eric_niyonzima_resume.pdf', 1),
(4, 'Sarah Mukantwari - Resume', 'uploads/resumes/sarah_mukantwari_resume.pdf', 1),
(5, 'Alice Uwase - Resume', 'uploads/resumes/alice_uwase_resume.pdf', 1);

-- ----------------------------
-- Table: interviews
-- References: applications, employers, students (all exist)
-- ----------------------------
CREATE TABLE `interviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT UNSIGNED NOT NULL,
  `employer_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `scheduled_at` DATETIME NOT NULL,
  `duration_minutes` INT UNSIGNED DEFAULT 60,
  `type` ENUM('video','phone','in-person') DEFAULT 'video',
  `status` ENUM('scheduled','completed','cancelled','rescheduled') DEFAULT 'scheduled',
  `meeting_link` VARCHAR(500) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_interview_app` (`application_id`),
  KEY `fk_interview_employer` (`employer_id`),
  KEY `fk_interview_student` (`student_id`),
  CONSTRAINT `fk_interview_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_interview_employer` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_interview_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `interviews` (`application_id`, `employer_id`, `student_id`, `scheduled_at`, `duration_minutes`, `type`, `status`, `meeting_link`) VALUES
(1, 1, 1, '2025-01-20 10:00:00', 60, 'video', 'completed', 'https://meet.google.com/abc-defg-hij'),
(2, 3, 3, '2025-01-25 14:00:00', 45, 'video', 'scheduled', 'https://meet.google.com/klm-nopq-rst'),
(3, 3, 4, '2025-01-18 09:00:00', 90, 'in-person', 'completed', NULL);

-- ----------------------------
-- Table: interview_results
-- References: interviews (exists)
-- ----------------------------
CREATE TABLE `interview_results` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `interview_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED DEFAULT NULL,
  `feedback` TEXT DEFAULT NULL,
  `decision` ENUM('pass','fail','pending') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `fk_ir_interview` (`interview_id`),
  CONSTRAINT `fk_ir_interview` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `interview_results` (`interview_id`, `rating`, `feedback`, `decision`) VALUES
(1, 8, 'Strong technical skills in React and TypeScript. Recommended for next round.', 'pass'),
(3, 9, 'Exceptional candidate with deep understanding of distributed systems. Highly recommended.', 'pass');

-- ----------------------------
-- Table: messages
-- References: users (exists)
-- ----------------------------
CREATE TABLE `messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT UNSIGNED NOT NULL,
  `receiver_id` INT UNSIGNED NOT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `body` TEXT NOT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_by_sender` TINYINT(1) DEFAULT 0,
  `deleted_by_receiver` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `fk_msg_sender` (`sender_id`),
  KEY `fk_msg_receiver` (`receiver_id`),
  CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `messages` (`sender_id`, `receiver_id`, `subject`, `body`, `read_at`) VALUES
(9, 2, 'Interview Invitation - Frontend Developer', 'Dear Jean Pierre,\n\nWe were impressed by your application. We invite you for a technical interview.\n\nPlease confirm availability for January 20, 2025 at 10:00 AM.\n\nBest regards,\nPatrick Mugisha\nRwanda Tech Ltd', NOW()),
(2, 9, 'Re: Interview Invitation - Frontend Developer', 'Dear Patrick,\n\nThank you for the invitation. I am available on January 20 at 10:00 AM.\n\nBest regards,\nJean Pierre', NOW()),
(15, 2, 'Mentorship Session Request', 'Hi Jean Pierre,\n\nI saw your profile and I am impressed with your React skills. I would be happy to mentor you on system design.\n\nWould you like to schedule a session?\n\nBest regards,\nMarie Claire Uwimana', NULL),
(3, 11, 'Question about Data Engineer position', 'Hello,\n\nI applied for the Data Engineer position. Is the team using Apache Airflow for pipeline orchestration?\n\nThank you,\nGrace Uwimana', NULL),
(10, 6, 'Security Assessment Follow-up', 'Dear Claude,\n\nFollowing your application, we would like you to complete a technical assessment.\n\nRegards,\nMarie Ishimwe\nBank of Kigali HR', NULL);

-- ----------------------------
-- Table: notifications
-- References: users (exists)
-- ----------------------------
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `data` JSON DEFAULT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `fk_notif_user` (`user_id`),
  KEY `idx_notif_read` (`user_id`, `read_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `read_at`) VALUES
(2, 'interview', 'Interview Scheduled', 'Your interview for Senior Frontend Developer at Rwanda Tech Ltd has been scheduled for Jan 20, 2025.', NOW()),
(2, 'application', 'Application Shortlisted', 'Your application for Senior Frontend Developer has been shortlisted.', NOW()),
(2, 'message', 'New Message', 'You received a message from Marie Claire Uwimana regarding mentorship.', NULL),
(3, 'interview', 'Interview Scheduled', 'Your interview for Data Engineer at MTN Rwanda has been scheduled for Jan 25, 2025.', NULL),
(4, 'offer', 'Job Offer', 'Congratulations! Zipline Rwanda has extended a job offer.', NOW()),
(5, 'application', 'Application Received', 'Your application for UI/UX Designer at Rwanda Tech Ltd has been received.', NOW()),
(6, 'application', 'Application Under Review', 'Your application for Cybersecurity Analyst is being reviewed.', NULL),
(6, 'message', 'New Message', 'You received a message from Bank of Kigali HR.', NULL),
(9, 'application', 'New Applicant', 'Jean Pierre Habarugira applied for Senior Frontend Developer.', NOW()),
(9, 'application', 'New Applicant', 'Claude Bizimana applied for DevOps Engineer.', NULL),
(1, 'system', 'New User Registration', 'Diane Mukamana registered as a student from INATEK.', NOW()),
(1, 'system', 'Fraud Alert', 'Suspicious activity detected on company account ACGROUP RW.', NULL);

-- ----------------------------
-- Table: events
-- References: users (exists)
-- ----------------------------
CREATE TABLE `events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `type` ENUM('career-fair','workshop','hackathon','webinar','meeting','other') DEFAULT 'other',
  `organizer_id` INT UNSIGNED DEFAULT NULL,
  `image` VARCHAR(500) DEFAULT NULL,
  `max_participants` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_event_organizer` (`organizer_id`),
  CONSTRAINT `fk_event_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `events` (`title`, `description`, `start_date`, `end_date`, `location`, `type`, `organizer_id`, `max_participants`, `status`) VALUES
('SkillSystem Career Fair 2025', 'Annual career fair connecting students with top employers in Rwanda.', '2025-02-15 09:00:00', '2025-02-15 17:00:00', 'Kigali Convention Centre', 'career-fair', 1, 500, 'upcoming'),
('AI for Rwanda Hackathon', '48-hour hackathon building AI solutions for Rwanda.', '2025-03-01 08:00:00', '2025-03-03 20:00:00', 'University of Rwanda - CBE Campus', 'hackathon', 13, 200, 'upcoming'),
('Resume Writing Workshop', 'Interactive workshop on writing effective resumes and cover letters.', '2025-02-05 14:00:00', '2025-02-05 16:00:00', 'Online (Zoom)', 'workshop', 1, 100, 'upcoming'),
('Cybersecurity Awareness Talk', 'Talk by industry experts on cybersecurity threats and careers.', '2025-02-20 10:00:00', '2025-02-20 12:00:00', 'UR - College of Science and Technology', 'webinar', 13, 150, 'upcoming');

-- ----------------------------
-- Table: discussions
-- References: users (exists)
-- ----------------------------
CREATE TABLE `discussions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `category` VARCHAR(100) DEFAULT 'general',
  `tags` VARCHAR(500) DEFAULT NULL,
  `views_count` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_disc_user` (`user_id`),
  CONSTRAINT `fk_disc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `discussions` (`user_id`, `title`, `body`, `category`, `tags`, `views_count`) VALUES
(2, 'Best resources for learning React in 2025?', 'I want to improve my React skills. What are the best resources?', 'programming', 'react,javascript,frontend,learning', 145),
(6, 'How to prepare for CISSP certification?', 'I am planning to take the CISSP exam this year. Any tips?', 'cybersecurity', 'cissp,certification,security,career', 89),
(4, 'Freelancing vs Full-time: Which is better for fresh graduates?', 'I am in my final year and trying to decide between freelancing and a full-time job.', 'career', 'freelance,jobs,career,graduation', 234),
(3, 'Data Engineering vs Data Science: Career Path Comparison', 'As someone interested in both data engineering and data science, I would love to hear from professionals.', 'career', 'data,engineering,science,career', 167);

-- ----------------------------
-- Table: comments
-- References: discussions, users (both exist)
-- ----------------------------
CREATE TABLE `comments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `discussion_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `body` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_comment_disc` (`discussion_id`),
  KEY `fk_comment_user` (`user_id`),
  CONSTRAINT `fk_comment_disc` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` (`discussion_id`, `user_id`, `body`) VALUES
(1, 3, 'I highly recommend the React documentation itself. Also check out Kent C. Dodds blog for advanced patterns.'),
(1, 5, 'For state management, I suggest learning Zustand before Redux. Simpler and more modern.'),
(2, 15, 'For CISSP, I recommend the official (ISC)² CBK reference and Boson practice exams. Start studying at least 3 months in advance.'),
(3, 15, 'Great question! In Rwanda, I recommend starting with a full-time job to build experience, then transition to freelancing after 1-2 years.'),
(3, 2, 'I agree. I started freelancing while still in school and it was tough to find consistent clients. A full-time job provides stability.'),
(4, 2, 'Data Engineering is more about building infrastructure. Data Science is about analysis and modeling. In Rwanda, Data Engineering roles are less common but pay well.');

-- ----------------------------
-- Table: ratings
-- References: users (exists)
-- ----------------------------
CREATE TABLE `ratings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `target_id` INT UNSIGNED NOT NULL,
  `target_type` ENUM('user','company','mentor','event') NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `review` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_rating_user` (`user_id`),
  CONSTRAINT `fk_rating_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ratings` (`user_id`, `target_id`, `target_type`, `rating`, `review`) VALUES
(2, 1, 'mentor', 5, 'Marie Claire is an exceptional mentor. Her guidance on system design helped me ace my interview at Rwanda Tech.'),
(3, 1, 'mentor', 5, 'Incredibly knowledgeable and patient. Helps me transition to mid-level developer mindset.'),
(4, 2, 'mentor', 4, 'Great at explaining complex cloud concepts. Very practical approach.'),
(2, 1, 'company', 4, 'Great company culture and learning opportunities.');

-- ----------------------------
-- Table: subscriptions
-- References: users (exists)
-- ----------------------------
CREATE TABLE `subscriptions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `plan` ENUM('free','basic','premium','enterprise') DEFAULT 'free',
  `amount` DECIMAL(10,2) DEFAULT 0.00,
  `currency` VARCHAR(10) DEFAULT 'RWF',
  `status` ENUM('active','expired','cancelled') DEFAULT 'active',
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_sub_user` (`user_id`),
  CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `subscriptions` (`user_id`, `plan`, `amount`, `start_date`, `end_date`, `status`) VALUES
(9, 'premium', 50000.00, '2025-01-01', '2025-12-31', 'active'),
(10, 'premium', 50000.00, '2025-01-01', '2025-12-31', 'active'),
(11, 'enterprise', 150000.00, '2025-01-01', '2025-12-31', 'active'),
(12, 'basic', 20000.00, '2025-01-01', '2025-06-30', 'active');

-- ----------------------------
-- Table: payments
-- References: users, subscriptions (both exist)
-- ----------------------------
CREATE TABLE `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'RWF',
  `method` VARCHAR(50) DEFAULT NULL,
  `transaction_id` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `fk_pay_user` (`user_id`),
  KEY `fk_pay_sub` (`subscription_id`),
  CONSTRAINT `fk_pay_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_sub` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`user_id`, `subscription_id`, `amount`, `method`, `transaction_id`, `status`, `paid_at`) VALUES
(9, 1, 50000.00, 'MTN MoMo', 'MTM-2025-001', 'completed', '2025-01-01 10:00:00'),
(10, 2, 50000.00, 'Bank Transfer', 'BK-2025-045', 'completed', '2025-01-02 14:30:00'),
(11, 3, 150000.00, 'MTN MoMo', 'MTM-2025-002', 'completed', '2025-01-01 09:15:00'),
(12, 4, 20000.00, 'Airtel Money', 'AM-2025-012', 'completed', '2025-01-03 11:00:00');

-- ----------------------------
-- Table: reports
-- References: users (exists)
-- ----------------------------
CREATE TABLE `reports` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reporter_id` INT UNSIGNED NOT NULL,
  `target_id` INT UNSIGNED NOT NULL,
  `target_type` ENUM('user','job','company','discussion','freelance') NOT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_report_reporter` (`reporter_id`),
  CONSTRAINT `fk_report_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reports` (`reporter_id`, `target_id`, `target_type`, `reason`, `description`, `status`) VALUES
(2, 12, 'company', 'Suspicious company', 'Africa Digital Hub appears to have fake contact information and no verifiable online presence.', 'pending'),
(6, 9, 'user', 'Inappropriate message', 'Received unprofessional content from this employer.', 'reviewed');

-- ----------------------------
-- Table: analytics
-- References: users (exists)
-- ----------------------------
CREATE TABLE `analytics` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(50) NOT NULL,
  `data` JSON DEFAULT NULL,
  `date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `fk_analytics_user` (`user_id`),
  KEY `idx_analytics_date` (`date`),
  KEY `idx_analytics_type` (`type`),
  CONSTRAINT `fk_analytics_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `analytics` (`user_id`, `type`, `data`, `date`) VALUES
(NULL, 'daily_stats', '{"new_users": 15, "new_jobs": 3, "applications": 28, "active_sessions": 342}', '2025-01-15'),
(NULL, 'daily_stats', '{"new_users": 12, "new_jobs": 5, "applications": 35, "active_sessions": 289}', '2025-01-14'),
(NULL, 'daily_stats', '{"new_users": 18, "new_jobs": 2, "applications": 22, "active_sessions": 378}', '2025-01-13'),
(NULL, 'daily_stats', '{"new_users": 8, "new_jobs": 4, "applications": 19, "active_sessions": 256}', '2025-01-12'),
(NULL, 'daily_stats', '{"new_users": 22, "new_jobs": 6, "applications": 41, "active_sessions": 412}', '2025-01-11'),
(NULL, 'daily_stats', '{"new_users": 14, "new_jobs": 3, "applications": 30, "active_sessions": 334}', '2025-01-10'),
(NULL, 'daily_stats', '{"new_users": 10, "new_jobs": 1, "applications": 25, "active_sessions": 278}', '2025-01-09'),
(2, 'profile_views', '{"count": 23}', '2025-01-15'),
(2, 'profile_views', '{"count": 18}', '2025-01-14'),
(3, 'profile_views', '{"count": 12}', '2025-01-15');

-- ----------------------------
-- Table: audit_logs
-- References: users (exists)
-- ----------------------------
CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `model` VARCHAR(50) NOT NULL,
  `model_id` INT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `old_values` JSON DEFAULT NULL,
  `new_values` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `fk_audit_user` (`user_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_model` (`model`),
  KEY `idx_audit_created` (`created_at`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `audit_logs` (`user_id`, `action`, `model`, `model_id`, `ip_address`) VALUES
(1, 'login', 'users', 1, '192.168.1.100'),
(2, 'login', 'users', 2, '192.168.1.101'),
(9, 'create', 'jobs', 1, '192.168.1.200'),
(9, 'create', 'jobs', 2, '192.168.1.200'),
(2, 'create', 'applications', 1, '192.168.1.101'),
(3, 'create', 'applications', 2, '192.168.1.102'),
(1, 'update', 'users', 8, '192.168.1.100');

-- ----------------------------
-- Table: activity_logs
-- References: users (exists)
-- ----------------------------
CREATE TABLE `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(50) NOT NULL,
  `description` VARCHAR(500) NOT NULL,
  `properties` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `fk_activity_user` (`user_id`),
  KEY `idx_activity_type` (`type`),
  KEY `idx_activity_created` (`created_at`),
  CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_logs` (`user_id`, `type`, `description`) VALUES
(2, 'login', 'Jean Pierre Habarugira logged in'),
(2, 'application', 'Applied for Senior Frontend Developer at Rwanda Tech Ltd'),
(2, 'application', 'Applied for Backend Developer (PHP/Laravel) at Rwanda Tech Ltd'),
(2, 'application', 'Applied for Software Engineering Intern at Rwanda Tech'),
(3, 'login', 'Grace Uwimana logged in'),
(3, 'application', 'Applied for Data Engineer at MTN Rwanda'),
(3, 'application', 'Applied for Data Analytics Intern at MTN Rwanda'),
(4, 'login', 'Eric Niyonzima logged in'),
(4, 'application', 'Applied for Software Engineer at Zipline Rwanda'),
(9, 'login', 'Patrick Mugisha logged in'),
(9, 'job', 'Posted job: Senior Frontend Developer'),
(9, 'job', 'Posted job: Backend Developer (PHP/Laravel)'),
(9, 'job', 'Posted job: UI/UX Designer'),
(9, 'job', 'Posted job: DevOps Engineer'),
(9, 'job', 'Posted job: Project Manager'),
(9, 'job', 'Posted job: Intern - Software Development'),
(1, 'login', 'Admin User logged in'),
(1, 'user', 'Verified company: Africa Digital Hub (rejected)');

-- ----------------------------
-- Table: settings
-- No dependencies
-- ----------------------------
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT DEFAULT NULL,
  `type` ENUM('string','integer','boolean','json','text') DEFAULT 'string',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key`, `value`, `type`) VALUES
('site_name', 'SkillSystem', 'string'),
('site_description', 'Student Skills, Internship & Career Management System', 'string'),
('site_email', 'noreply@skillsystem.rw', 'string'),
('default_currency', 'RWF', 'string'),
('max_file_upload_size', '5242880', 'integer'),
('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx', 'string'),
('enable_registration', '1', 'boolean'),
('enable_dark_mode', '1', 'boolean'),
('require_email_verification', '0', 'boolean'),
('posts_per_page', '10', 'integer'),
('maintenance_mode', '0', 'boolean'),
('free_plan_max_applications', '10', 'integer'),
('basic_plan_price', '20000', 'integer'),
('premium_plan_price', '50000', 'integer'),
('enterprise_plan_price', '150000', 'integer');

-- ----------------------------
-- Table: password_resets
-- No dependencies
-- ----------------------------
CREATE TABLE `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_pr_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: cover_letters
-- References: students (exists)
-- ----------------------------
CREATE TABLE `cover_letters` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_cl_student` (`student_id`),
  CONSTRAINT `fk_cl_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: projects (student personal projects)
-- References: students (exists)
-- ----------------------------
CREATE TABLE `projects` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `technologies` VARCHAR(500) DEFAULT NULL,
  `url` VARCHAR(500) DEFAULT NULL,
  `image` VARCHAR(500) DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_proj_student` (`student_id`),
  CONSTRAINT `fk_proj_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
-- Road2Job - Premium Dynamic Student Portfolio.
-- Adds: extended social/coding-profile links, career preferences (feeds
-- the portfolio's "Hire Me" section), a featured-project flag, and
-- portfolio display settings (theme + section order). All additive/
-- nullable-or-defaulted - zero risk to existing rows, no backfill needed.
-- profile_visibility (already exists, see 004_phase4_student.sql) is
-- deliberately reused as the publish/unpublish flag rather than adding a
-- second "published" column - the Portfolio Manager just relabels it.

SET NAMES utf8mb4;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'leetcode_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN leetcode_url VARCHAR(255) NULL AFTER github_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'hackerrank_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN hackerrank_url VARCHAR(255) NULL AFTER leetcode_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'codechef_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN codechef_url VARCHAR(255) NULL AFTER hackerrank_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'behance_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN behance_url VARCHAR(255) NULL AFTER codechef_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'dribbble_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN dribbble_url VARCHAR(255) NULL AFTER behance_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'youtube_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN youtube_url VARCHAR(255) NULL AFTER dribbble_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'website_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN website_url VARCHAR(255) NULL AFTER youtube_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'interested_roles');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN interested_roles TEXT NULL AFTER website_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'preferred_locations');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN preferred_locations TEXT NULL AFTER interested_roles', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'domains_of_interest');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN domains_of_interest TEXT NULL AFTER preferred_locations', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'work_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN work_type VARCHAR(100) NULL AFTER domains_of_interest', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'availability');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN availability VARCHAR(30) NULL AFTER work_type', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'portfolio_theme');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN portfolio_theme VARCHAR(30) NOT NULL DEFAULT ''modern'' AFTER resume_template', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'portfolio_section_order');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_profiles ADD COLUMN portfolio_section_order VARCHAR(255) NULL AFTER portfolio_theme', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_projects' AND COLUMN_NAME = 'is_featured');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE student_projects ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER project_type', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

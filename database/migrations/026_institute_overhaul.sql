-- Road2Job - Institute Ecosystem Overhaul.
-- Adds: extended institute portfolio fields (cover, type, specializations,
-- facilities, training modes, city/state, phone, official email, social
-- links), admin-only verification/moderation flags, and a precomputed
-- dynamic-ranking score (written at CRUD time, read as a plain indexed
-- ORDER BY - see InstituteRankingScorer). Extends institute_placements with
-- richer fields for search/filter and ranking recency math. Adds the new
-- institute_updates feed and institute_rank_events activity ledger. All
-- additive/nullable-or-defaulted - zero risk to existing rows.

SET NAMES utf8mb4;

-- ===================== institutes =====================

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'cover_path');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN cover_path VARCHAR(255) NULL AFTER logo_path', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'institute_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN institute_type VARCHAR(50) NULL AFTER description', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'specializations');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN specializations TEXT NULL AFTER institute_type', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'facilities');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN facilities TEXT NULL AFTER specializations', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'training_modes');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN training_modes VARCHAR(255) NULL AFTER facilities', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'city');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN city VARCHAR(100) NULL AFTER location', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'state');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN state VARCHAR(100) NULL AFTER city', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'phone');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN phone VARCHAR(20) NULL AFTER state', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'official_email');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN official_email VARCHAR(150) NULL AFTER phone', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'linkedin_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN linkedin_url VARCHAR(255) NULL AFTER website', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'instagram_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN instagram_url VARCHAR(255) NULL AFTER linkedin_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'youtube_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN youtube_url VARCHAR(255) NULL AFTER instagram_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'facebook_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN facebook_url VARCHAR(255) NULL AFTER youtube_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'twitter_url');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN twitter_url VARCHAR(255) NULL AFTER facebook_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'verification_status');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN verification_status ENUM(''unverified'',''verified'') NOT NULL DEFAULT ''unverified'' AFTER twitter_url', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'status');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN status ENUM(''active'',''deactivated'') NOT NULL DEFAULT ''active'' AFTER verification_status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'profile_view_count');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN profile_view_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'rank_score');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN rank_score DECIMAL(8,3) NOT NULL DEFAULT 0 AFTER profile_view_count', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'rank_score_updated_at');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN rank_score_updated_at DATETIME NULL AFTER rank_score', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND COLUMN_NAME = 'profile_completion_percent');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institutes ADD COLUMN profile_completion_percent TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER rank_score_updated_at', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institutes' AND INDEX_NAME = 'idx_institutes_status_rank');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE institutes ADD INDEX idx_institutes_status_rank (status, rank_score)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ===================== institute_placements =====================

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institute_placements' AND COLUMN_NAME = 'student_photo_path');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institute_placements ADD COLUMN student_photo_path VARCHAR(255) NULL AFTER student_name', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institute_placements' AND COLUMN_NAME = 'job_role');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institute_placements ADD COLUMN job_role VARCHAR(150) NULL AFTER company_name', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institute_placements' AND COLUMN_NAME = 'placement_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institute_placements ADD COLUMN placement_type VARCHAR(50) NULL AFTER job_role', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institute_placements' AND COLUMN_NAME = 'placement_date');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institute_placements ADD COLUMN placement_date DATE NULL AFTER placement_year', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institute_placements' AND COLUMN_NAME = 'description');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institute_placements ADD COLUMN description TEXT NULL AFTER course_name', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institute_placements' AND COLUMN_NAME = 'status');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE institute_placements ADD COLUMN status ENUM(''active'',''deactivated'') NOT NULL DEFAULT ''active'' AFTER description', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institute_placements' AND INDEX_NAME = 'idx_institute_placements_institute_status');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE institute_placements ADD INDEX idx_institute_placements_institute_status (institute_id, status)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ===================== institute_updates (new) =====================

CREATE TABLE IF NOT EXISTS institute_updates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    category ENUM('announcement','placement_news','achievement','event','course_update','general') NOT NULL DEFAULT 'general',
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    status ENUM('active','deactivated') NOT NULL DEFAULT 'active',
    content_hash CHAR(64) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_institute_updates_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE,
    INDEX idx_institute_updates_institute_id (institute_id),
    INDEX idx_institute_updates_status (status),
    INDEX idx_institute_updates_hash (content_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================== institute_rank_events (new) =====================

CREATE TABLE IF NOT EXISTS institute_rank_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    event_type ENUM('placement_added','update_posted','profile_updated') NOT NULL,
    event_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_institute_rank_events_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE,
    INDEX idx_rank_events_institute_time (institute_id, event_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

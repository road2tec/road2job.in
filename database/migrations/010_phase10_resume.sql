-- Road2Job - Phase 10: Resume Builder
-- Run after 009_phase9_jobs.sql (idempotent-safe via information_schema check)

SET NAMES utf8mb4;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'resume_template'
);

SET @sql = IF(@col_exists = 0,
    "ALTER TABLE student_profiles ADD COLUMN resume_template ENUM('professional', 'ats', 'creative') NOT NULL DEFAULT 'professional' AFTER profile_visibility",
    "SELECT 'resume_template already exists'"
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

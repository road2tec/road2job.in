-- Road2Job - Student Profile Builder fast multi-entry upgrade (Pass 1).
-- Adds tag-style "skills used"/"technologies used" storage to Experience
-- and Projects (plain columns, not a junction table - matches how this
-- schema already prefers simple columns over relational tag tables), a
-- new Project Type field, and extends the employment_type options.
-- All additive/nullable - zero risk to existing rows, no backfill needed.

SET NAMES utf8mb4;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_projects' AND COLUMN_NAME = 'technologies_used'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE student_projects ADD COLUMN technologies_used TEXT NULL AFTER description',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_projects' AND COLUMN_NAME = 'project_type'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE student_projects ADD COLUMN project_type VARCHAR(100) NULL AFTER role',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_experience' AND COLUMN_NAME = 'skills_used'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE student_experience ADD COLUMN skills_used TEXT NULL AFTER description',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_experience' AND COLUMN_NAME = 'location'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE student_experience ADD COLUMN location VARCHAR(150) NULL AFTER company_name',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ENUM MODIFY is safe to run unconditionally (same pattern as 023's
-- resume_template default change) - existing rows keep whatever value
-- they already have, this only widens the allowed set going forward.
ALTER TABLE student_experience
    MODIFY COLUMN employment_type ENUM('internship','full_time','part_time','freelance','contract','training') NOT NULL DEFAULT 'internship';

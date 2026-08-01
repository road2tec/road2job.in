-- Road2Job - Resume Builder overhaul: adds LinkedIn/GitHub link fields to
-- student profiles (the resume header can already source a "Portfolio" link
-- for free from the existing /u/{username} route - only these two are new),
-- and switches the default resume template to "ats" (the new ATS Classic
-- layout) for newly-created profiles. Existing students' saved
-- resume_template choice is untouched - this only changes the column
-- DEFAULT, not any stored row.

SET NAMES utf8mb4;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'linkedin_url'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE student_profiles ADD COLUMN linkedin_url VARCHAR(255) NULL AFTER career_objective',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND COLUMN_NAME = 'github_url'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE student_profiles ADD COLUMN github_url VARCHAR(255) NULL AFTER linkedin_url',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

ALTER TABLE student_profiles
    MODIFY COLUMN resume_template ENUM('professional','ats','creative') NOT NULL DEFAULT 'ats';

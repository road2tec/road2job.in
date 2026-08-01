-- Road2Job - Phase 16: Learning Marketplace
-- Run after 015_phase15_research.sql. Extends Phase 7's institute_courses/enrollment
-- system rather than building a parallel one.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Extend institute_courses with format (course/bootcamp/workshop, distinct axis from
-- the existing online/offline/hybrid `mode`) and optional date bounds for time-boxed formats.
SET @format_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'institute_courses' AND COLUMN_NAME = 'format'
);
SET @sql = IF(@format_exists = 0,
    "ALTER TABLE institute_courses
        ADD COLUMN format ENUM('course', 'bootcamp', 'workshop') NOT NULL DEFAULT 'course' AFTER mode,
        ADD COLUMN start_date DATE NULL AFTER format,
        ADD COLUMN end_date DATE NULL AFTER start_date",
    "SELECT 'institute_courses already extended'"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Extend enrollment status with 'completed' - a real access-consequential state, not
-- just a label. Existing rows/values are untouched by widening the enum.
ALTER TABLE institute_enrollment_requests
    MODIFY COLUMN status ENUM('pending', 'contacted', 'enrolled', 'completed', 'declined') NOT NULL DEFAULT 'pending';

CREATE TABLE IF NOT EXISTS institute_course_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    type ENUM('assignment', 'project') NOT NULL DEFAULT 'assignment',
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    due_date DATE NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_ica_course_id (course_id),
    CONSTRAINT fk_ica_course FOREIGN KEY (course_id) REFERENCES institute_courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institute_assignment_submissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    submission_text TEXT NULL,
    submission_file_path VARCHAR(255) NULL,
    status ENUM('submitted', 'reviewed') NOT NULL DEFAULT 'submitted',
    feedback TEXT NULL,
    submitted_at DATETIME NOT NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_ias_assignment_student (assignment_id, student_id),
    KEY idx_ias_student_id (student_id),
    CONSTRAINT fk_ias_assignment FOREIGN KEY (assignment_id) REFERENCES institute_course_assignments(id) ON DELETE CASCADE,
    CONSTRAINT fk_ias_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learning_roadmaps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_lr_institute_id (institute_id),
    CONSTRAINT fk_lr_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roadmap_milestones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    roadmap_id INT UNSIGNED NOT NULL,
    order_index TINYINT UNSIGNED NOT NULL DEFAULT 0,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    course_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_rm_roadmap_id (roadmap_id),
    CONSTRAINT fk_rm_roadmap FOREIGN KEY (roadmap_id) REFERENCES learning_roadmaps(id) ON DELETE CASCADE,
    CONSTRAINT fk_rm_course FOREIGN KEY (course_id) REFERENCES institute_courses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

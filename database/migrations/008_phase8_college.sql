-- Road2Job - Phase 8: College Module
-- Run after 007_phase7_institute.sql (idempotent, safe to re-run)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS colleges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NULL,
    logo_path VARCHAR(255) NULL,
    description TEXT NULL,
    established_year SMALLINT UNSIGNED NULL,
    website VARCHAR(255) NULL,
    location VARCHAR(150) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_colleges_user_id (user_id),
    CONSTRAINT fk_colleges_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS college_campus_drives (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    college_id INT UNSIGNED NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    drive_date DATE NULL,
    eligible_departments VARCHAR(255) NULL,
    min_cgpa DECIMAL(3,2) NULL,
    description TEXT NULL,
    status ENUM('draft', 'published', 'closed') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_college_campus_drives_college_id (college_id),
    KEY idx_college_campus_drives_status (status),
    CONSTRAINT fk_college_campus_drives_college FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS college_department_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    college_id INT UNSIGNED NOT NULL,
    department_name VARCHAR(150) NOT NULL,
    academic_year VARCHAR(20) NULL,
    total_students SMALLINT UNSIGNED NULL,
    students_placed SMALLINT UNSIGNED NULL,
    average_package INT UNSIGNED NULL,
    highest_package INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_college_department_stats_college_id (college_id),
    CONSTRAINT fk_college_department_stats_college FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS college_alumni (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    college_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    batch_year SMALLINT UNSIGNED NULL,
    department VARCHAR(150) NULL,
    current_position VARCHAR(150) NULL,
    current_company VARCHAR(150) NULL,
    testimonial TEXT NULL,
    photo_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_college_alumni_college_id (college_id),
    CONSTRAINT fk_college_alumni_college FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS college_drive_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    drive_id INT UNSIGNED NOT NULL,
    college_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'shortlisted', 'selected', 'rejected') NOT NULL DEFAULT 'pending',
    message VARCHAR(500) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_college_drive_registrations_drive_id (drive_id),
    KEY idx_college_drive_registrations_college_id (college_id),
    KEY idx_college_drive_registrations_student_id (student_id),
    CONSTRAINT fk_college_drive_registrations_drive FOREIGN KEY (drive_id) REFERENCES college_campus_drives(id) ON DELETE CASCADE,
    CONSTRAINT fk_college_drive_registrations_college FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE,
    CONSTRAINT fk_college_drive_registrations_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

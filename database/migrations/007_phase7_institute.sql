-- Road2Job - Phase 7: Training Institute Module
-- Run after 006_phase6_employer.sql (idempotent, safe to re-run)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS institutes (
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
    UNIQUE KEY uq_institutes_user_id (user_id),
    CONSTRAINT fk_institutes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institute_courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    duration VARCHAR(50) NULL,
    mode ENUM('online', 'offline', 'hybrid') NOT NULL DEFAULT 'offline',
    fee INT UNSIGNED NULL,
    syllabus_highlights TEXT NULL,
    status ENUM('draft', 'published', 'closed') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_institute_courses_institute_id (institute_id),
    KEY idx_institute_courses_status (status),
    CONSTRAINT fk_institute_courses_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institute_faculty (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    designation VARCHAR(150) NULL,
    bio TEXT NULL,
    photo_path VARCHAR(255) NULL,
    expertise VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_institute_faculty_institute_id (institute_id),
    CONSTRAINT fk_institute_faculty_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institute_gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_institute_gallery_institute_id (institute_id),
    CONSTRAINT fk_institute_gallery_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institute_certificates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    issuing_body VARCHAR(150) NULL,
    document_path VARCHAR(255) NULL,
    issued_year SMALLINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_institute_certificates_institute_id (institute_id),
    CONSTRAINT fk_institute_certificates_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institute_placements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    student_name VARCHAR(150) NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    package_amount INT UNSIGNED NULL,
    placement_year SMALLINT UNSIGNED NULL,
    course_name VARCHAR(150) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_institute_placements_institute_id (institute_id),
    CONSTRAINT fk_institute_placements_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institute_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    review_text TEXT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uq_institute_reviews_institute_user (institute_id, user_id),
    KEY idx_institute_reviews_institute_id (institute_id),
    CONSTRAINT fk_institute_reviews_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE,
    CONSTRAINT fk_institute_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institute_enrollment_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'contacted', 'enrolled', 'declined') NOT NULL DEFAULT 'pending',
    message VARCHAR(500) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_enrollment_requests_institute_id (institute_id),
    KEY idx_enrollment_requests_course_id (course_id),
    KEY idx_enrollment_requests_student_id (student_id),
    CONSTRAINT fk_enrollment_requests_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollment_requests_course FOREIGN KEY (course_id) REFERENCES institute_courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollment_requests_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

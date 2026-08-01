-- Road2Job - Consolidated production database (schema + roles + all migrations + admin account)
-- Generated 2026-08-01 - single-file import, run this ONE file against a fresh empty database.
-- Admin login: admin@road2job.in / see DEPLOYMENT.md or your own deploy notes for the password
-- (never stored in plaintext in this file - only its bcrypt hash is below).
-- CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN.

SET NAMES utf8mb4;

-- ============================================================
-- SCHEMA (base tables)
-- ============================================================
-- Road2Job - Phase 1: Foundation schema
-- Charset/engine standardized for Hostinger MySQL 8

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- roles
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL,
    label VARCHAR(100) NOT NULL,
    self_registerable TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('pending', 'active', 'suspended', 'banned') NOT NULL DEFAULT 'pending',
    email_verified_at DATETIME NULL,
    phone_verified_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_phone (phone),
    KEY idx_users_role_id (role_id),
    KEY idx_users_status (status),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- otp_verifications
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    phone VARCHAR(15) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    purpose ENUM('registration', 'login', 'password_reset') NOT NULL DEFAULT 'registration',
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    KEY idx_otp_user_id (user_id),
    KEY idx_otp_phone (phone),
    CONSTRAINT fk_otp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- login_attempts
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(150) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at DATETIME NOT NULL,
    KEY idx_login_attempts_identifier (identifier),
    KEY idx_login_attempts_ip (ip_address),
    KEY idx_login_attempts_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- password_resets
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    KEY idx_password_resets_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- user_sessions (device / login history)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    login_at DATETIME NOT NULL,
    logout_at DATETIME NULL,
    KEY idx_user_sessions_user_id (user_id),
    CONSTRAINT fk_user_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- audit_logs
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL,
    KEY idx_audit_logs_user_id (user_id),
    KEY idx_audit_logs_action (action),
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- notifications
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'general',
    title VARCHAR(150) NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    KEY idx_notifications_user_id (user_id),
    KEY idx_notifications_is_read (is_read),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- contact_messages (public /contact form submissions)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    KEY idx_contact_messages_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ROLES (required reference data)
-- ============================================================
INSERT INTO roles (slug, label, self_registerable, created_at, updated_at) VALUES
    ('student', 'Student', 1, NOW(), NOW()),
    ('employer', 'Employer', 1, NOW(), NOW()),
    ('recruiter', 'Recruiter', 1, NOW(), NOW()),
    ('institute', 'Training Institute', 1, NOW(), NOW()),
    ('college', 'College', 1, NOW(), NOW()),
    ('mentor', 'Mentor', 1, NOW(), NOW()),
    ('admin', 'Admin', 0, NOW(), NOW()),
    ('super_admin', 'Super Admin', 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- ============================================================
-- MIGRATION: 003_phase3_auth.sql
-- ============================================================
-- Road2Job - Phase 3: Authentication Module deepening
-- Run after schema.sql + seed.sql (idempotent, safe to re-run)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- email_verifications
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    email VARCHAR(150) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    KEY idx_email_verifications_user_id (user_id),
    KEY idx_email_verifications_email (email),
    CONSTRAINT fk_email_verifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- remember_tokens
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    selector VARCHAR(40) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uq_remember_tokens_selector (selector),
    KEY idx_remember_tokens_user_id (user_id),
    CONSTRAINT fk_remember_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- user_sessions: track the PHP session id so a revoke can actually
-- invalidate that device's session on its next request.
-- ---------------------------------------------------------------------
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_sessions' AND COLUMN_NAME = 'session_id'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE user_sessions ADD COLUMN session_id VARCHAR(128) NULL AFTER user_id, ADD INDEX idx_user_sessions_session_id (session_id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- MIGRATION: 004_phase4_student.sql
-- ============================================================
-- Road2Job - Phase 4: Student Module
-- Run after 003_phase3_auth.sql (idempotent, safe to re-run)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- student_profiles (1:1 with users)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    headline VARCHAR(150) NULL,
    bio TEXT NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NULL,
    address_line VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,
    avatar_path VARCHAR(255) NULL,
    career_objective TEXT NULL,
    profile_visibility ENUM('public', 'private') NOT NULL DEFAULT 'private',
    email_notifications TINYINT(1) NOT NULL DEFAULT 1,
    sms_notifications TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_student_profiles_user_id (user_id),
    CONSTRAINT fk_student_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- student_education
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_education (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    degree VARCHAR(150) NOT NULL,
    institution_name VARCHAR(150) NOT NULL,
    field_of_study VARCHAR(150) NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    grade VARCHAR(50) NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_student_education_user_id (user_id),
    CONSTRAINT fk_student_education_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- student_skills
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_skills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    proficiency ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL DEFAULT 'beginner',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_student_skills_user_id (user_id),
    CONSTRAINT fk_student_skills_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- student_projects
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    role VARCHAR(100) NULL,
    description TEXT NOT NULL,
    project_url VARCHAR(255) NULL,
    attachment_path VARCHAR(255) NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_student_projects_user_id (user_id),
    CONSTRAINT fk_student_projects_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- student_experience
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_experience (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    job_title VARCHAR(150) NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    employment_type ENUM('internship', 'full_time', 'part_time', 'freelance') NOT NULL DEFAULT 'internship',
    start_date DATE NOT NULL,
    end_date DATE NULL,
    currently_working TINYINT(1) NOT NULL DEFAULT 0,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_student_experience_user_id (user_id),
    CONSTRAINT fk_student_experience_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- student_certificates
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_certificates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    issuing_organization VARCHAR(150) NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE NULL,
    credential_url VARCHAR(255) NULL,
    attachment_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_student_certificates_user_id (user_id),
    CONSTRAINT fk_student_certificates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- student_achievements
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    achieved_on DATE NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_student_achievements_user_id (user_id),
    CONSTRAINT fk_student_achievements_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- student_languages
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_languages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    language_name VARCHAR(100) NOT NULL,
    proficiency ENUM('basic', 'conversational', 'fluent', 'native') NOT NULL DEFAULT 'basic',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_student_languages_user_id (user_id),
    CONSTRAINT fk_student_languages_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- MIGRATION: 005_phase5_portfolio.sql
-- ============================================================
-- Road2Job - Phase 5: Student Portfolio Website
-- Run after 003_phase3_auth.sql and 004_phase4_student.sql (idempotent, safe to re-run)

SET NAMES utf8mb4;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'username'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE users ADD COLUMN username VARCHAR(50) NULL AFTER phone, ADD UNIQUE INDEX uq_users_username (username)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS portfolio_views (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    viewed_at DATETIME NOT NULL,
    KEY idx_portfolio_views_user_id (user_id),
    CONSTRAINT fk_portfolio_views_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MIGRATION: 006_phase6_employer.sql
-- ============================================================
-- Road2Job - Phase 6: Employer Module
-- Run after 005_phase5_portfolio.sql (idempotent, safe to re-run)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS companies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NULL,
    logo_path VARCHAR(255) NULL,
    industry VARCHAR(100) NULL,
    company_size VARCHAR(50) NULL,
    website VARCHAR(255) NULL,
    description TEXT NULL,
    founded_year SMALLINT UNSIGNED NULL,
    headquarters_location VARCHAR(150) NULL,
    verification_status ENUM('unverified', 'pending', 'verified', 'rejected') NOT NULL DEFAULT 'unverified',
    verification_document_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_companies_user_id (user_id),
    CONSTRAINT fk_companies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS job_postings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    type ENUM('full_time', 'part_time', 'internship', 'contract', 'remote') NOT NULL DEFAULT 'full_time',
    location VARCHAR(150) NULL,
    is_remote TINYINT(1) NOT NULL DEFAULT 0,
    description TEXT NULL,
    requirements TEXT NULL,
    min_salary INT UNSIGNED NULL,
    max_salary INT UNSIGNED NULL,
    experience_level ENUM('fresher', 'junior', 'mid', 'senior') NOT NULL DEFAULT 'fresher',
    application_deadline DATE NULL,
    status ENUM('draft', 'published', 'closed') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_job_postings_company_id (company_id),
    KEY idx_job_postings_status (status),
    CONSTRAINT fk_job_postings_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- MIGRATION: 007_phase7_institute.sql
-- ============================================================
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

-- ============================================================
-- MIGRATION: 008_phase8_college.sql
-- ============================================================
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

-- ============================================================
-- MIGRATION: 009_phase9_jobs.sql
-- ============================================================
-- Road2Job - Phase 9: Job & Internship Module
-- Run after 008_phase8_college.sql (idempotent, safe to re-run)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS job_applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_posting_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    company_id INT UNSIGNED NOT NULL,
    resume_snapshot LONGTEXT NULL,
    cover_note TEXT NULL,
    status ENUM('applied', 'under_review', 'shortlisted', 'rejected', 'selected') NOT NULL DEFAULT 'applied',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_job_applications_job_student (job_posting_id, student_id),
    KEY idx_job_applications_company_id (company_id),
    KEY idx_job_applications_student_id (student_id),
    CONSTRAINT fk_job_applications_job FOREIGN KEY (job_posting_id) REFERENCES job_postings(id) ON DELETE CASCADE,
    CONSTRAINT fk_job_applications_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_job_applications_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    job_posting_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uq_saved_jobs_student_job (student_id, job_posting_id),
    KEY idx_saved_jobs_job_posting_id (job_posting_id),
    CONSTRAINT fk_saved_jobs_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_jobs_job FOREIGN KEY (job_posting_id) REFERENCES job_postings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- MIGRATION: 010_phase10_resume.sql
-- ============================================================
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

-- ============================================================
-- MIGRATION: 012_phase12_interview.sql
-- ============================================================
-- Road2Job - Phase 12: Interview Engine
-- Run after 011_phase11... (Phase 11 added no tables) / 010_phase10_resume.sql
-- Idempotent-safe: CREATE TABLE IF NOT EXISTS + seed guarded by a row-count check

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS interview_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    round_type ENUM('technical', 'hr', 'coding') NOT NULL,
    question_text TEXT NOT NULL,
    time_limit_seconds SMALLINT UNSIGNED NOT NULL DEFAULT 90,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_interview_questions_round_type (round_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS interview_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_application_id INT UNSIGNED NOT NULL,
    company_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    requested_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_interview_sessions_application (job_application_id),
    KEY idx_interview_sessions_company_id (company_id),
    KEY idx_interview_sessions_student_id (student_id),
    CONSTRAINT fk_interview_sessions_application FOREIGN KEY (job_application_id) REFERENCES job_applications(id) ON DELETE CASCADE,
    CONSTRAINT fk_interview_sessions_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_interview_sessions_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS interview_session_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    interview_session_id INT UNSIGNED NOT NULL,
    interview_question_id INT UNSIGNED NOT NULL,
    order_index TINYINT UNSIGNED NOT NULL DEFAULT 0,
    video_path VARCHAR(255) NULL,
    text_answer TEXT NULL,
    answered_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_isq_session_id (interview_session_id),
    KEY idx_isq_question_id (interview_question_id),
    CONSTRAINT fk_isq_session FOREIGN KEY (interview_session_id) REFERENCES interview_sessions(id) ON DELETE CASCADE,
    CONSTRAINT fk_isq_question FOREIGN KEY (interview_question_id) REFERENCES interview_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS interview_scores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    interview_session_id INT UNSIGNED NOT NULL,
    keyword_score TINYINT UNSIGNED NOT NULL,
    confidence_score TINYINT UNSIGNED NOT NULL,
    technical_score TINYINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    scored_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_interview_scores_session (interview_session_id),
    CONSTRAINT fk_interview_scores_session FOREIGN KEY (interview_session_id) REFERENCES interview_sessions(id) ON DELETE CASCADE,
    CONSTRAINT fk_interview_scores_scored_by FOREIGN KEY (scored_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed a starter question bank, only if empty (safe to re-run the migration)
SET @question_count = (SELECT COUNT(*) FROM interview_questions);

INSERT INTO interview_questions (round_type, question_text, time_limit_seconds, created_at, updated_at)
SELECT * FROM (SELECT 'technical' AS round_type, 'Explain the difference between an array and an object, and when you would use each.' AS question_text, 90 AS time_limit_seconds, NOW() AS created_at, NOW() AS updated_at
    UNION ALL SELECT 'technical', 'What is the difference between SQL INNER JOIN and LEFT JOIN? Give an example.', 90, NOW(), NOW()
    UNION ALL SELECT 'technical', 'How would you optimize a web page that is loading slowly?', 90, NOW(), NOW()
    UNION ALL SELECT 'technical', 'Explain what REST API means and describe one you have used or built.', 90, NOW(), NOW()
    UNION ALL SELECT 'technical', 'What is version control and why is it important in a team project?', 90, NOW(), NOW()
    UNION ALL SELECT 'technical', 'Describe the difference between synchronous and asynchronous code execution.', 90, NOW(), NOW()
    UNION ALL SELECT 'hr', 'Tell us about yourself and why you are interested in this role.', 90, NOW(), NOW()
    UNION ALL SELECT 'hr', 'Describe a time you faced a challenge while working on a project. How did you handle it?', 90, NOW(), NOW()
    UNION ALL SELECT 'hr', 'Where do you see yourself in the next few years?', 90, NOW(), NOW()
    UNION ALL SELECT 'hr', 'How do you handle working under pressure or tight deadlines?', 90, NOW(), NOW()
    UNION ALL SELECT 'hr', 'Describe a time you worked as part of a team. What was your role?', 90, NOW(), NOW()
    UNION ALL SELECT 'hr', 'Why should we hire you for this position?', 90, NOW(), NOW()
    UNION ALL SELECT 'coding', 'Write a function that returns whether a given string is a palindrome.', 300, NOW(), NOW()
    UNION ALL SELECT 'coding', 'Write a function that returns the second largest number in an array of integers.', 300, NOW(), NOW()
    UNION ALL SELECT 'coding', 'Write a function that counts how many times each word appears in a given sentence.', 300, NOW(), NOW()
    UNION ALL SELECT 'coding', 'Write a function that reverses a linked list, or explain your approach in pseudocode if you prefer.', 300, NOW(), NOW()
) AS seed_rows
WHERE @question_count = 0;

-- ============================================================
-- MIGRATION: 013_phase13_assessment.sql
-- ============================================================
-- Road2Job - Phase 13: Assessment System
-- Run after 012_phase12_interview.sql
-- Idempotent-safe: CREATE TABLE IF NOT EXISTS + seed guarded by a row-count check

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS assessment_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category ENUM('technical', 'coding', 'english', 'aptitude', 'communication') NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('a', 'b', 'c', 'd') NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_assessment_questions_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    category ENUM('technical', 'coding', 'english', 'aptitude', 'communication') NOT NULL,
    score TINYINT UNSIGNED NULL,
    total_questions TINYINT UNSIGNED NOT NULL DEFAULT 5,
    percent TINYINT UNSIGNED NULL,
    passed TINYINT(1) NULL,
    started_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_assessment_attempts_student_id (student_id),
    KEY idx_assessment_attempts_category (category),
    CONSTRAINT fk_assessment_attempts_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_attempt_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assessment_attempt_id INT UNSIGNED NOT NULL,
    assessment_question_id INT UNSIGNED NOT NULL,
    order_index TINYINT UNSIGNED NOT NULL DEFAULT 0,
    selected_option ENUM('a', 'b', 'c', 'd') NULL,
    is_correct TINYINT(1) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_aaa_attempt_id (assessment_attempt_id),
    KEY idx_aaa_question_id (assessment_question_id),
    CONSTRAINT fk_aaa_attempt FOREIGN KEY (assessment_attempt_id) REFERENCES assessment_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_aaa_question FOREIGN KEY (assessment_question_id) REFERENCES assessment_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed a starter question bank (10 per category), only if empty (safe to re-run)
SET @question_count = (SELECT COUNT(*) FROM assessment_questions);

INSERT INTO assessment_questions (category, question_text, option_a, option_b, option_c, option_d, correct_option, created_at, updated_at)
SELECT * FROM (
    SELECT 'technical' AS category, 'Which HTTP method is typically used to update an existing resource?' AS question_text, 'GET' AS option_a, 'POST' AS option_b, 'PUT' AS option_c, 'DELETE' AS option_d, 'c' AS correct_option, NOW() AS created_at, NOW() AS updated_at
    UNION ALL SELECT 'technical', 'Which of these is a NoSQL database?', 'MySQL', 'PostgreSQL', 'MongoDB', 'SQLite', 'c', NOW(), NOW()
    UNION ALL SELECT 'technical', 'What does CSS stand for?', 'Computer Style Sheets', 'Cascading Style Sheets', 'Creative Style System', 'Colorful Style Sheets', 'b', NOW(), NOW()
    UNION ALL SELECT 'technical', 'Which data structure uses LIFO (Last In First Out) order?', 'Queue', 'Stack', 'Array', 'Linked List', 'b', NOW(), NOW()
    UNION ALL SELECT 'technical', 'What is the primary purpose of an index in a database table?', 'Encrypt data', 'Speed up data retrieval', 'Reduce storage size', 'Enforce foreign keys', 'b', NOW(), NOW()
    UNION ALL SELECT 'technical', 'Which of these is used for version control?', 'Docker', 'Git', 'Jenkins', 'Kubernetes', 'b', NOW(), NOW()
    UNION ALL SELECT 'technical', 'In REST APIs, which status code means \"Not Found\"?', '200', '301', '404', '500', 'c', NOW(), NOW()
    UNION ALL SELECT 'technical', 'What does API stand for?', 'Application Programming Interface', 'Advanced Program Integration', 'Application Process Index', 'Applied Programming Instruction', 'a', NOW(), NOW()
    UNION ALL SELECT 'technical', 'Which language runs natively in a web browser?', 'Python', 'Java', 'JavaScript', 'C++', 'c', NOW(), NOW()
    UNION ALL SELECT 'technical', 'What is the main purpose of CSS media queries?', 'Database queries', 'Responsive design', 'Form validation', 'API requests', 'b', NOW(), NOW()

    UNION ALL SELECT 'coding', 'What will `console.log(2 + \"2\")` output in JavaScript?', '4', '"22"', 'NaN', 'undefined', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What is the output of `print(5 // 2)` in Python?', '2.5', '2', '3', 'Error', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'In PHP, what does `===` check that `==` does not?', 'Nothing, they are identical', 'Value AND type equality', 'Only type', 'Only value', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What is the time complexity of searching in a balanced binary search tree?', 'O(1)', 'O(n)', 'O(log n)', 'O(n^2)', 'c', NOW(), NOW()
    UNION ALL SELECT 'coding', 'Which line contains the bug? Line 1: function add(a, b) { Line 2:   return a + b Line 3: } Line 4: console.log(add(2, 3);', 'Line 1', 'Line 2', 'Line 3', 'Line 4', 'd', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What does this SQL return: SELECT COUNT(*) FROM users WHERE 1=0;', 'All rows', 'NULL', '0', 'An error', 'c', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What is the output of `[1,2,3].length` in JavaScript?', '2', '3', '4', 'undefined', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'In a for loop `for(i=0; i<5; i++)`, how many times does it run?', '4', '5', '6', 'Infinite', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'What does `array_merge()` do in PHP?', 'Sorts an array', 'Combines two or more arrays', 'Removes duplicates', 'Reverses an array', 'b', NOW(), NOW()
    UNION ALL SELECT 'coding', 'Which of these correctly declares a constant in JavaScript?', 'var x = 5', 'let x = 5', 'const x = 5', 'static x = 5', 'c', NOW(), NOW()

    UNION ALL SELECT 'english', 'Choose the correctly spelled word.', 'Recieve', 'Receive', 'Receeve', 'Receve', 'b', NOW(), NOW()
    UNION ALL SELECT 'english', 'Fill in the blank: She ___ to the market yesterday.', 'go', 'goes', 'went', 'going', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Choose the correct synonym for "Happy".', 'Sad', 'Joyful', 'Angry', 'Tired', 'b', NOW(), NOW()
    UNION ALL SELECT 'english', 'Choose the correct antonym for "Increase".', 'Grow', 'Expand', 'Decrease', 'Raise', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Which sentence is grammatically correct?', 'He don''t like it.', 'He doesn''t likes it.', 'He doesn''t like it.', 'He not like it.', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Identify the noun in: "The quick fox jumps."', 'Quick', 'Fox', 'Jumps', 'The', 'b', NOW(), NOW()
    UNION ALL SELECT 'english', 'What is the plural of "child"?', 'Childs', 'Childes', 'Children', 'Childrens', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Choose the correct form: "I have ___ that movie already."', 'see', 'saw', 'seen', 'seeing', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Which word is an adverb in: "She sings beautifully."', 'She', 'Sings', 'Beautifully', 'None', 'c', NOW(), NOW()
    UNION ALL SELECT 'english', 'Choose the correct preposition: "The book is ___ the table."', 'in', 'on', 'at', 'by', 'b', NOW(), NOW()

    UNION ALL SELECT 'aptitude', 'If a train travels 60 km in 1 hour, how far does it travel in 2.5 hours at the same speed?', '120 km', '150 km', '100 km', '180 km', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'What is 15% of 200?', '20', '25', '30', '35', 'c', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'Find the next number in the series: 2, 4, 8, 16, ?', '20', '24', '32', '18', 'c', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'If 5 workers can build a wall in 10 days, how many days will 10 workers take?', '20 days', '5 days', '10 days', '2 days', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'A shirt costs Rs.500 after a 20% discount. What was the original price?', 'Rs.600', 'Rs.625', 'Rs.650', 'Rs.700', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'What is the average of 10, 20, 30, and 40?', '20', '25', '30', '35', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'If today is Monday, what day will it be after 10 days?', 'Wednesday', 'Thursday', 'Friday', 'Tuesday', 'a', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'A is twice as old as B. If B is 12, how old is A?', '6', '12', '24', '36', 'c', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'What comes next: A, C, E, G, ?', 'H', 'I', 'J', 'K', 'b', NOW(), NOW()
    UNION ALL SELECT 'aptitude', 'A car covers 240 km using 20 litres of fuel. What is its mileage?', '10 km/l', '12 km/l', '14 km/l', '16 km/l', 'b', NOW(), NOW()

    UNION ALL SELECT 'communication', 'In a professional email, which greeting is most appropriate?', 'Hey!', 'Yo,', 'Dear Sir/Madam,', 'Sup,', 'c', NOW(), NOW()
    UNION ALL SELECT 'communication', 'What is "active listening" primarily about?', 'Speaking loudly', 'Fully focusing on and understanding the speaker', 'Interrupting to add ideas', 'Multitasking while listening', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'Which is the best way to give constructive feedback?', 'Focus only on flaws', 'Be vague and general', 'Be specific, balanced, and respectful', 'Give feedback publicly to embarrass', 'c', NOW(), NOW()
    UNION ALL SELECT 'communication', 'In a job interview, what does maintaining eye contact typically signal?', 'Aggression', 'Confidence and engagement', 'Nervousness', 'Disinterest', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'What is the purpose of a follow-up email after an interview?', 'To negotiate salary', 'To express thanks and reinforce interest', 'To ask for feedback on other candidates', 'To reschedule the interview', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'Which body language typically suggests openness in a conversation?', 'Crossed arms', 'Avoiding eye contact', 'Relaxed posture and nodding', 'Looking at your phone', 'c', NOW(), NOW()
    UNION ALL SELECT 'communication', 'What is the best approach when you disagree with a colleague in a meeting?', 'Stay silent and complain later', 'Interrupt and argue loudly', 'Respectfully share your perspective with reasons', 'Ignore the discussion entirely', 'c', NOW(), NOW()
    UNION ALL SELECT 'communication', 'Why is clarity important in written communication?', 'It makes emails longer', 'It reduces misunderstanding', 'It impresses the reader with vocabulary', 'It is not important', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'What does "elevator pitch" refer to?', 'A sales pitch for elevators', 'A short, persuasive summary of yourself or an idea', 'A complaint about slow elevators', 'A long detailed presentation', 'b', NOW(), NOW()
    UNION ALL SELECT 'communication', 'When giving a presentation, what best helps keep the audience engaged?', 'Reading directly from slides', 'Speaking in a monotone voice', 'Clear structure, eye contact, and pacing', 'Using very technical jargon throughout', 'c', NOW(), NOW()
) AS seed_rows
WHERE @question_count = 0;

-- ============================================================
-- MIGRATION: 014_phase14_community.sql
-- ============================================================
-- Road2Job - Phase 14: Community
-- Run after 013_phase13_assessment.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS community_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category ENUM('discussion', 'question', 'interview-experience', 'success-story') NOT NULL DEFAULT 'discussion',
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    tag VARCHAR(100) NULL,
    accepted_reply_id INT UNSIGNED NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_community_posts_user_id (user_id),
    KEY idx_community_posts_category (category),
    CONSTRAINT fk_community_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS community_replies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    community_post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_community_replies_post_id (community_post_id),
    KEY idx_community_replies_user_id (user_id),
    CONSTRAINT fk_community_replies_post FOREIGN KEY (community_post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_community_replies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'community_posts' AND CONSTRAINT_NAME = 'fk_community_posts_accepted_reply'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE community_posts ADD CONSTRAINT fk_community_posts_accepted_reply FOREIGN KEY (accepted_reply_id) REFERENCES community_replies(id) ON DELETE SET NULL',
    'SELECT ''fk_community_posts_accepted_reply already exists'''
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS mentor_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    expertise VARCHAR(255) NULL,
    bio TEXT NULL,
    availability_note VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_mentor_profiles_user_id (user_id),
    CONSTRAINT fk_mentor_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mentorship_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mentor_profile_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    message VARCHAR(500) NULL,
    status ENUM('pending', 'accepted', 'declined') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_mentorship_requests_mentor_id (mentor_profile_id),
    KEY idx_mentorship_requests_student_id (student_id),
    CONSTRAINT fk_mentorship_requests_mentor FOREIGN KEY (mentor_profile_id) REFERENCES mentor_profiles(id) ON DELETE CASCADE,
    CONSTRAINT fk_mentorship_requests_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- MIGRATION: 015_phase15_research.sql
-- ============================================================
-- Road2Job - Phase 15: Research Hub
-- Run after 014_phase14_community.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS research_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('research-paper', 'project', 'publication', 'conference-paper', 'patent') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    authors_collaborators VARCHAR(255) NULL,
    publication_date DATE NULL,
    external_reference VARCHAR(255) NULL,
    attachment_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_research_items_user_id (user_id),
    KEY idx_research_items_type (type),
    CONSTRAINT fk_research_items_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- MIGRATION: 016_phase16_learning_marketplace.sql
-- ============================================================
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

-- ============================================================
-- MIGRATION: 017_phase17_events.sql
-- ============================================================
-- Road2Job - Phase 17: Event Module
-- Run after 016_phase16_learning_marketplace.sql. Covers only what's genuinely new
-- (Hackathon/Job Fair/Seminar/Webinar) - Campus Drives (Phase 8) and Workshops
-- (Phase 16's institute_courses format) already exist and are not rebuilt here.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_user_id INT UNSIGNED NOT NULL,
    category ENUM('hackathon', 'job-fair', 'seminar', 'webinar') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    location VARCHAR(200) NULL,
    is_online TINYINT(1) NOT NULL DEFAULT 0,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NULL,
    status ENUM('draft', 'published', 'completed') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_events_organizer (organizer_user_id),
    KEY idx_events_category (category),
    KEY idx_events_status (status),
    CONSTRAINT fk_events_organizer FOREIGN KEY (organizer_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    status ENUM('registered', 'attended') NOT NULL DEFAULT 'registered',
    registered_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_event_registrations_event_user (event_id, user_id),
    KEY idx_event_registrations_user (user_id),
    CONSTRAINT fk_event_registrations_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_event_registrations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- MIGRATION: 018_phase18_career_services.sql
-- ============================================================
-- Road2Job - Phase 18: Career Services
-- Run after 017_phase17_events.sql
-- Idempotent-safe: CREATE TABLE IF NOT EXISTS + guarded ALTER (information_schema check), same idiom as migration 005

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS mock_interview_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'completed') NOT NULL DEFAULT 'pending',
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_mock_interview_sessions_student_id (student_id),
    CONSTRAINT fk_mock_interview_sessions_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mock_interview_session_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mock_interview_session_id INT UNSIGNED NOT NULL,
    interview_question_id INT UNSIGNED NOT NULL,
    order_index TINYINT UNSIGNED NOT NULL DEFAULT 0,
    video_path VARCHAR(255) NULL,
    text_answer TEXT NULL,
    answered_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_miqs_session_id (mock_interview_session_id),
    KEY idx_miqs_question_id (interview_question_id),
    CONSTRAINT fk_miqs_session FOREIGN KEY (mock_interview_session_id) REFERENCES mock_interview_sessions(id) ON DELETE CASCADE,
    CONSTRAINT fk_miqs_question FOREIGN KEY (interview_question_id) REFERENCES interview_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS job_alerts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    keyword VARCHAR(150) NULL,
    location VARCHAR(150) NULL,
    type ENUM('full_time', 'part_time', 'internship', 'contract', 'remote') NULL,
    experience_level ENUM('fresher', 'junior', 'mid', 'senior') NULL,
    is_remote TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    KEY idx_job_alerts_student_id (student_id),
    CONSTRAINT fk_job_alerts_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Extend mentorship_requests (Phase 14) with Career Services fields, guarded per-column so this is safe to re-run

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mentorship_requests' AND COLUMN_NAME = 'service_type'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE mentorship_requests ADD COLUMN service_type ENUM('mentorship','resume-review','portfolio-review','career-counseling','mock-interview-feedback') NOT NULL DEFAULT 'mentorship' AFTER student_id, ADD KEY idx_mentorship_requests_service_type (service_type)",
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mentorship_requests' AND COLUMN_NAME = 'resume_snapshot'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE mentorship_requests ADD COLUMN resume_snapshot JSON NULL AFTER message',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mentorship_requests' AND COLUMN_NAME = 'mock_interview_session_id'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE mentorship_requests ADD COLUMN mock_interview_session_id INT UNSIGNED NULL AFTER resume_snapshot, ADD CONSTRAINT fk_mentorship_requests_mock_session FOREIGN KEY (mock_interview_session_id) REFERENCES mock_interview_sessions(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mentorship_requests' AND COLUMN_NAME = 'feedback'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE mentorship_requests ADD COLUMN feedback TEXT NULL AFTER status',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- MIGRATION: 020_phase20_admin.sql
-- ============================================================
-- Road2Job - Phase 20: Admin Panel
-- Run after 019... (Phase 19 added no tables) / 018_phase18_career_services.sql
-- Users/Companies/Institutes/Colleges/Jobs/Applications/Events moderation, Notifications
-- broadcast, Reports, Security and Audit Logs reuse existing tables (audit_logs,
-- user_sessions, users.status, companies.verification_status, job_postings.status,
-- events.status) - only Settings and Blog are genuinely new schema.
-- Payments/CMS/Advertisements deliberately not built - see coming_soon.php.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_blog_posts_author_id (author_id),
    KEY idx_blog_posts_status (status),
    CONSTRAINT fk_blog_posts_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed the 3 curated settings keys, only if empty (safe to re-run)
SET @settings_count = (SELECT COUNT(*) FROM settings);

INSERT INTO settings (setting_key, setting_value, updated_at)
SELECT * FROM (
    SELECT 'site_name' AS setting_key, 'Road2Job' AS setting_value, NOW() AS updated_at
    UNION ALL SELECT 'support_email', 'support@road2job.in', NOW()
    UNION ALL SELECT 'maintenance_mode', '0', NOW()
) AS seed_rows
WHERE @settings_count = 0;

-- ============================================================
-- MIGRATION: 022_phase22_optimization.sql
-- ============================================================
-- Road2Job - Phase 22: Optimization (Database Optimization)
-- Targeted composite indexes for the specific query shape that recurs across
-- every public listing page: WHERE status = 'x' ORDER BY <date column> -
-- confirmed via SHOW INDEX that each table only had single-column indexes on
-- status/company_id separately, forcing a filesort on every listing page load
-- as these tables grow. Additive only - existing single-column indexes are
-- left in place (still useful, e.g. plain WHERE company_id = X lookups).

SET NAMES utf8mb4;

SET @idx_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'job_postings' AND INDEX_NAME = 'idx_job_postings_status_published_at'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE job_postings ADD INDEX idx_job_postings_status_published_at (status, published_at)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'job_postings' AND INDEX_NAME = 'idx_job_postings_company_id_created_at'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE job_postings ADD INDEX idx_job_postings_company_id_created_at (company_id, created_at)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND INDEX_NAME = 'idx_events_status_starts_at'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE events ADD INDEX idx_events_status_starts_at (status, starts_at)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'blog_posts' AND INDEX_NAME = 'idx_blog_posts_status_published_at'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE blog_posts ADD INDEX idx_blog_posts_status_published_at (status, published_at)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- MIGRATION: 023_resume_links.sql
-- ============================================================
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

-- ============================================================
-- MIGRATION: 024_profile_builder_upgrade.sql
-- ============================================================
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

-- ============================================================
-- MIGRATION: 025_premium_portfolio.sql
-- ============================================================
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

-- ============================================================
-- MIGRATION: 026_institute_overhaul.sql
-- ============================================================
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

-- ============================================================
-- MIGRATION: 027_interview_video.sql
-- ============================================================
-- Road2Job - Interview module redesign: one continuous video per interview
-- instead of one video per question. Adds a session-level video path/
-- duration on both interview_sessions and mock_interview_sessions, plus
-- per-question timing offsets (answer_started_at/answer_ended_at, seconds
-- elapsed within the ONE recording) on both session-question tables so a
-- future "jump to this answer" seek link has the data it needs.
--
-- Deliberately NOT dropping the old per-question video_path columns on
-- interview_session_questions/mock_interview_session_questions - they stay
-- in place, unused going forward, so any already-completed old session's
-- per-question videos remain viewable. New sessions simply never populate
-- that column again (server-side code stops writing to it).

SET NAMES utf8mb4;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'interview_sessions' AND COLUMN_NAME = 'video_path');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE interview_sessions ADD COLUMN video_path VARCHAR(255) NULL AFTER status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'interview_sessions' AND COLUMN_NAME = 'video_duration_seconds');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE interview_sessions ADD COLUMN video_duration_seconds SMALLINT UNSIGNED NULL AFTER video_path', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mock_interview_sessions' AND COLUMN_NAME = 'video_path');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mock_interview_sessions ADD COLUMN video_path VARCHAR(255) NULL AFTER status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mock_interview_sessions' AND COLUMN_NAME = 'video_duration_seconds');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mock_interview_sessions ADD COLUMN video_duration_seconds SMALLINT UNSIGNED NULL AFTER video_path', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'interview_session_questions' AND COLUMN_NAME = 'answer_started_at');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE interview_session_questions ADD COLUMN answer_started_at SMALLINT UNSIGNED NULL AFTER order_index', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'interview_session_questions' AND COLUMN_NAME = 'answer_ended_at');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE interview_session_questions ADD COLUMN answer_ended_at SMALLINT UNSIGNED NULL AFTER answer_started_at', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mock_interview_session_questions' AND COLUMN_NAME = 'answer_started_at');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mock_interview_session_questions ADD COLUMN answer_started_at SMALLINT UNSIGNED NULL AFTER order_index', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mock_interview_session_questions' AND COLUMN_NAME = 'answer_ended_at');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mock_interview_session_questions ADD COLUMN answer_ended_at SMALLINT UNSIGNED NULL AFTER answer_started_at', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- ADMIN ACCOUNT (freshly generated password, NOT the old public default)
-- Login: admin@road2job.in - password not stored in plaintext in this file, only its bcrypt hash below
-- CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN.
-- ============================================================
INSERT INTO users (role_id, full_name, email, phone, password_hash, status, email_verified_at, phone_verified_at, created_at, updated_at)
SELECT r.id, 'Super Admin', 'admin@road2job.in', '9999999999',
       '$2y$10$dut/m9FFzAKgYFbQoHhcBuxnJ3yI9YE1EI81X8qjFJA8hesRbWhXS',
       'active', NOW(), NOW(), NOW(), NOW()
FROM roles r WHERE r.slug = 'super_admin'
ON DUPLICATE KEY UPDATE email = VALUES(email);

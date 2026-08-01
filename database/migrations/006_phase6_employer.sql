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

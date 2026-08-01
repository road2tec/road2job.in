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

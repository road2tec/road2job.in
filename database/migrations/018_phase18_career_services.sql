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

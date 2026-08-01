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

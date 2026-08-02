-- Road2Job - Chat/messaging system (employer <-> student, request-then-accept)
-- Explicit schema exception approved by the user for this one feature - every
-- other change in this pass is frontend/query-only. Three tables:
-- chat_requests (accept/decline gate, mirrors mentorship_requests' status
-- convention) -> on accept, exactly one chat_threads row is created (UNIQUE
-- constraint enforces this at the DB level, not just in app code) -> chat_messages
-- hangs off the thread. If a student declines, no chat_threads row is ever
-- created - "no chat happens" is a structural guarantee, not just a UI state.

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS chat_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_user_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    job_application_id INT UNSIGNED NULL,
    message VARCHAR(500) NULL,
    status ENUM('pending', 'accepted', 'declined') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_chat_requests_employer (employer_user_id),
    KEY idx_chat_requests_student (student_id, status),
    KEY idx_chat_requests_application (job_application_id),
    CONSTRAINT fk_chat_requests_employer FOREIGN KEY (employer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_requests_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_requests_application FOREIGN KEY (job_application_id) REFERENCES job_applications(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_threads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_request_id INT UNSIGNED NOT NULL,
    employer_user_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    last_message_at DATETIME NULL,
    suspended_by INT UNSIGNED NULL,
    suspended_reason VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_chat_threads_request (chat_request_id),
    KEY idx_chat_threads_employer (employer_user_id, status, last_message_at),
    KEY idx_chat_threads_student (student_id, status, last_message_at),
    CONSTRAINT fk_chat_threads_request FOREIGN KEY (chat_request_id) REFERENCES chat_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_threads_employer FOREIGN KEY (employer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_threads_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_threads_suspended_by FOREIGN KEY (suspended_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_thread_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    body VARCHAR(2000) NULL,
    attachment_path VARCHAR(255) NULL,
    attachment_original_name VARCHAR(255) NULL,
    attachment_mime VARCHAR(100) NULL,
    attachment_type ENUM('image', 'file') NULL,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    KEY idx_chat_messages_thread (chat_thread_id, id),
    KEY idx_chat_messages_thread_unread (chat_thread_id, sender_id, read_at),
    CONSTRAINT fk_chat_messages_thread FOREIGN KEY (chat_thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

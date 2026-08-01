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

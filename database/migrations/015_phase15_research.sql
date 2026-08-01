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

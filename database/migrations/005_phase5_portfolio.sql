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

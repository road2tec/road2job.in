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

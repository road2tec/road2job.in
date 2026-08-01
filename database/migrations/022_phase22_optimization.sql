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

-- Road2Job - Interview module redesign: one continuous video per interview
-- instead of one video per question. Adds a session-level video path/
-- duration on both interview_sessions and mock_interview_sessions, plus
-- per-question timing offsets (answer_started_at/answer_ended_at, seconds
-- elapsed within the ONE recording) on both session-question tables so a
-- future "jump to this answer" seek link has the data it needs.
--
-- Deliberately NOT dropping the old per-question video_path columns on
-- interview_session_questions/mock_interview_session_questions - they stay
-- in place, unused going forward, so any already-completed old session's
-- per-question videos remain viewable. New sessions simply never populate
-- that column again (server-side code stops writing to it).

SET NAMES utf8mb4;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'interview_sessions' AND COLUMN_NAME = 'video_path');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE interview_sessions ADD COLUMN video_path VARCHAR(255) NULL AFTER status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'interview_sessions' AND COLUMN_NAME = 'video_duration_seconds');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE interview_sessions ADD COLUMN video_duration_seconds SMALLINT UNSIGNED NULL AFTER video_path', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mock_interview_sessions' AND COLUMN_NAME = 'video_path');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mock_interview_sessions ADD COLUMN video_path VARCHAR(255) NULL AFTER status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mock_interview_sessions' AND COLUMN_NAME = 'video_duration_seconds');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mock_interview_sessions ADD COLUMN video_duration_seconds SMALLINT UNSIGNED NULL AFTER video_path', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'interview_session_questions' AND COLUMN_NAME = 'answer_started_at');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE interview_session_questions ADD COLUMN answer_started_at SMALLINT UNSIGNED NULL AFTER order_index', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'interview_session_questions' AND COLUMN_NAME = 'answer_ended_at');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE interview_session_questions ADD COLUMN answer_ended_at SMALLINT UNSIGNED NULL AFTER answer_started_at', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mock_interview_session_questions' AND COLUMN_NAME = 'answer_started_at');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mock_interview_session_questions ADD COLUMN answer_started_at SMALLINT UNSIGNED NULL AFTER order_index', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mock_interview_session_questions' AND COLUMN_NAME = 'answer_ended_at');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mock_interview_session_questions ADD COLUMN answer_ended_at SMALLINT UNSIGNED NULL AFTER answer_started_at', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

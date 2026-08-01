-- Road2Job - Phase: Application status timeline
-- Real, append-only event log so students can see a genuine history of what
-- happened to their application (applied, recruiter viewed, status changes),
-- instead of just the current status. Interview requested/completed events
-- are NOT duplicated here - interview_sessions.requested_at/completed_at
-- already record those and is joined in at query time.

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS job_application_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_application_id INT UNSIGNED NOT NULL,
    event_type ENUM('applied', 'viewed', 'status_changed') NOT NULL,
    from_status VARCHAR(20) NULL,
    to_status VARCHAR(20) NULL,
    event_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    KEY idx_job_application_events_app (job_application_id, event_at),
    CONSTRAINT fk_job_application_events_app FOREIGN KEY (job_application_id) REFERENCES job_applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Backfill an 'applied' event for every application that predates this table -
-- created_at is a genuine historical fact (when they actually applied), not
-- fabricated. Safe to re-run: the WHERE NOT IN guard skips rows already backfilled.
INSERT INTO job_application_events (job_application_id, event_type, to_status, event_at, created_at)
SELECT id, 'applied', 'applied', created_at, created_at
FROM job_applications
WHERE id NOT IN (SELECT job_application_id FROM job_application_events WHERE event_type = 'applied');

-- Road2Job - Phase 1: Seed data
-- Run after schema.sql

SET NAMES utf8mb4;

INSERT INTO roles (slug, label, self_registerable, created_at, updated_at) VALUES
    ('student', 'Student', 1, NOW(), NOW()),
    ('employer', 'Employer', 1, NOW(), NOW()),
    ('recruiter', 'Recruiter', 1, NOW(), NOW()),
    ('institute', 'Training Institute', 1, NOW(), NOW()),
    ('college', 'College', 1, NOW(), NOW()),
    ('mentor', 'Mentor', 1, NOW(), NOW()),
    ('admin', 'Admin', 0, NOW(), NOW()),
    ('super_admin', 'Super Admin', 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- Default super admin login: admin@road2job.in / Road2Job@Admin123
-- CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN.
INSERT INTO users (role_id, full_name, email, phone, password_hash, status, email_verified_at, phone_verified_at, created_at, updated_at)
SELECT r.id, 'Super Admin', 'admin@road2job.in', '9999999999',
       '$2y$10$dkTDAiSLP3lRKUi7cYsOA.iyMWlvzC7rVKQr2rLI8Jgn/7kzHB1c2',
       'active', NOW(), NOW(), NOW(), NOW()
FROM roles r WHERE r.slug = 'super_admin'
ON DUPLICATE KEY UPDATE email = VALUES(email);

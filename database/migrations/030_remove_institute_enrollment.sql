-- Removes the course-enrollment system (institutes pay Road2Job for
-- promotion/visibility, not course sales - there is no student "enroll"
-- action anymore). Confirmed no other table has an incoming FK into any
-- of these three, so dropping is safe. Idempotent (IF EXISTS).

DROP TABLE IF EXISTS institute_assignment_submissions;
DROP TABLE IF EXISTS institute_course_assignments;
DROP TABLE IF EXISTS institute_enrollment_requests;

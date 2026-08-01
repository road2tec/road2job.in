<?php

return [
    // slug => [label, self_registerable, dashboard_layout]
    'student' => ['label' => 'Student', 'self_registerable' => true, 'layout' => 'student'],
    'employer' => ['label' => 'Employer', 'self_registerable' => true, 'layout' => 'employer'],
    'recruiter' => ['label' => 'Recruiter', 'self_registerable' => true, 'layout' => 'employer'],
    'institute' => ['label' => 'Training Institute', 'self_registerable' => true, 'layout' => 'employer'],
    'college' => ['label' => 'College', 'self_registerable' => true, 'layout' => 'employer'],
    'mentor' => ['label' => 'Mentor', 'self_registerable' => true, 'layout' => 'student'],
    'admin' => ['label' => 'Admin', 'self_registerable' => false, 'layout' => 'admin'],
    'super_admin' => ['label' => 'Super Admin', 'self_registerable' => false, 'layout' => 'admin'],
];

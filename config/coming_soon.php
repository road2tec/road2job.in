<?php

return [
    'career-passport' => [
        'title' => 'Career Passport',
        'icon' => 'bi-patch-check',
        'description' => 'A verified, shareable summary of your skills, education and achievements.',
        'phase' => 'not currently planned - would need dedicated scoping if ever revisited',
    ],
    'revenue-analytics' => [
        'title' => 'Revenue Analytics',
        'icon' => 'bi-graph-up-arrow',
        'description' => 'Revenue reporting will unlock once the Payments module goes live.',
        'phase' => 'not currently planned - depends on Payments, which needs a monetization model and payment gateway decided first',
    ],
    'payments' => [
        'title' => 'Payments',
        'icon' => 'bi-credit-card',
        'description' => 'No monetization model or payment gateway has been decided yet - this module would need its own dedicated scoping effort.',
        'phase' => 'not currently planned',
    ],
    'advertisements' => [
        'title' => 'Advertisements',
        'icon' => 'bi-megaphone',
        'description' => 'Ad placements and campaign management for advertisers - not built, no advertiser demand exists on the platform currently.',
        'phase' => 'not currently planned',
    ],
    'database-backup' => [
        'title' => 'Database Backup',
        'icon' => 'bi-database',
        'description' => 'Handled operationally rather than as an in-app feature - an on-demand web download of a full database dump (including password hashes) was deliberately not built, since it has no established retention or access-control strategy. See DEPLOYMENT.md for the actual backup approach (Hostinger hPanel scheduled backups).',
        'phase' => 'handled operationally - see DEPLOYMENT.md',
    ],
    'api-configuration' => [
        'title' => 'API Configuration',
        'icon' => 'bi-plug',
        'description' => 'Road2Job has no public API layer - this becomes relevant only if one is built.',
        'phase' => 'not currently planned',
    ],
    'cron-jobs' => [
        'title' => 'Cron Jobs',
        'icon' => 'bi-clock-history',
        'description' => 'No scheduled/background tasks exist - every time-sensitive feature in this app uses event-driven triggers instead.',
        'phase' => 'not currently planned',
    ],
    'fraud-detection' => [
        'title' => 'Fraud Detection',
        'icon' => 'bi-exclamation-triangle',
        'description' => 'No fraud vectors have been defined for this platform.',
        'phase' => 'not currently planned',
    ],
];

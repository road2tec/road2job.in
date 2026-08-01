<?php

namespace App\Services;

use App\Models\JobAlert;
use App\Models\Notification;

class JobAlertMatcher
{
    public static function notifyForNewPosting(array $job): void
    {
        $notifiedStudentIds = [];

        foreach (JobAlert::all() as $alert) {
            if (!self::matches($alert, $job)) {
                continue;
            }

            $studentId = (int) $alert['student_id'];

            if (in_array($studentId, $notifiedStudentIds, true)) {
                continue;
            }

            $notifiedStudentIds[] = $studentId;

            Notification::push(
                $studentId,
                'New job matches your alert',
                $job['title'] . ' has just been posted - check it out on the Jobs page.',
                'job_alert'
            );
        }
    }

    protected static function matches(array $alert, array $job): bool
    {
        if (!empty($alert['type']) && $alert['type'] !== $job['type']) {
            return false;
        }

        if (!empty($alert['experience_level']) && $alert['experience_level'] !== $job['experience_level']) {
            return false;
        }

        if (!empty($alert['is_remote']) && (int) ($job['is_remote'] ?? 0) !== 1) {
            return false;
        }

        if (!empty($alert['location']) && stripos((string) $job['location'], (string) $alert['location']) === false) {
            return false;
        }

        if (!empty($alert['keyword'])) {
            $haystack = ($job['title'] ?? '') . ' ' . ($job['description'] ?? '');

            if (stripos($haystack, (string) $alert['keyword']) === false) {
                return false;
            }
        }

        return true;
    }
}

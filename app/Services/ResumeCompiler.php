<?php

namespace App\Services;

use App\Models\ResearchItem;
use App\Models\StudentAchievement;
use App\Models\StudentCertificate;
use App\Models\StudentEducation;
use App\Models\StudentExperience;
use App\Models\StudentLanguage;
use App\Models\StudentProfile;
use App\Models\StudentProject;
use App\Models\StudentSkill;
use App\Models\User;

class ResumeCompiler
{
    public static function compile(int $userId): array
    {
        $account = User::find($userId);

        return [
            // Only display-relevant fields - this gets persisted verbatim into
            // job_applications.resume_snapshot, so password_hash and other
            // internal columns must never end up in here.
            'account' => $account !== null ? [
                'full_name' => $account['full_name'],
                'email' => $account['email'],
                'phone' => $account['phone'],
                'username' => $account['username'],
            ] : null,
            'profile' => StudentProfile::findByUserId($userId),
            'education' => StudentEducation::forUser($userId),
            'skills' => StudentSkill::forUser($userId),
            'projects' => StudentProject::forUser($userId),
            'experience' => StudentExperience::forUser($userId),
            'certificates' => StudentCertificate::forUser($userId),
            'achievements' => StudentAchievement::forUser($userId),
            'languages' => StudentLanguage::forUser($userId),
            'research' => ResearchItem::forUser($userId),
        ];
    }
}

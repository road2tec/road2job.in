<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InterviewQuestion extends Model
{
    protected static string $table = 'interview_questions';

    /**
     * @param array<string,int> $roundCounts e.g. ['technical' => 2, 'hr' => 2, 'coding' => 1]
     */
    public static function randomForRounds(array $roundCounts): array
    {
        $questions = [];

        foreach ($roundCounts as $roundType => $count) {
            $stmt = Database::connection()->prepare(
                "SELECT * FROM interview_questions WHERE round_type = :round_type ORDER BY RAND() LIMIT " . (int) $count
            );
            $stmt->execute(['round_type' => $roundType]);

            $questions = array_merge($questions, $stmt->fetchAll());
        }

        return $questions;
    }
}

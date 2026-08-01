<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstituteReview extends Model
{
    protected static string $table = 'institute_reviews';
    protected static bool $timestamps = false;

    public static function forInstitute(int $instituteId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT ir.*, u.full_name AS reviewer_name
             FROM institute_reviews ir
             JOIN users u ON u.id = ir.user_id
             WHERE ir.institute_id = :institute_id
             ORDER BY ir.created_at DESC"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return $stmt->fetchAll();
    }

    public static function findByInstituteAndUser(int $instituteId, int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institute_reviews WHERE institute_id = :institute_id AND user_id = :user_id LIMIT 1"
        );
        $stmt->execute(['institute_id' => $instituteId, 'user_id' => $userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function averageRating(int $instituteId): ?float
    {
        $stmt = Database::connection()->prepare(
            "SELECT AVG(rating) FROM institute_reviews WHERE institute_id = :institute_id"
        );
        $stmt->execute(['institute_id' => $instituteId]);
        $avg = $stmt->fetchColumn();

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public static function create(int $instituteId, int $userId, int $rating, ?string $reviewText): string
    {
        return static::insert([
            'institute_id' => $instituteId,
            'user_id' => $userId,
            'rating' => $rating,
            'review_text' => $reviewText,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

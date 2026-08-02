<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class ChatThread extends Model
{
    protected static string $table = 'chat_threads';

    public static function createFromRequest(int $chatRequestId, int $employerUserId, int $studentId): string
    {
        return static::insert([
            'chat_request_id' => $chatRequestId,
            'employer_user_id' => $employerUserId,
            'student_id' => $studentId,
            'status' => 'active',
        ]);
    }

    public static function findByRequest(int $chatRequestId): ?array
    {
        return static::findBy('chat_request_id', $chatRequestId);
    }

    /**
     * A thread, once created, is never deleted - so its existence alone
     * means this employer/student pair is already connected. Used to stop
     * a second chat request being sent to someone who already accepted one.
     */
    public static function findBetween(int $employerUserId, int $studentId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM chat_threads WHERE employer_user_id = :employer_user_id AND student_id = :student_id LIMIT 1"
        );
        $stmt->execute(['employer_user_id' => $employerUserId, 'student_id' => $studentId]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public static function touchLastMessage(int $threadId): void
    {
        static::update($threadId, ['last_message_at' => date('Y-m-d H:i:s')]);
    }

    public static function setStatus(int $id, string $status, ?int $actorId = null, ?string $reason = null): void
    {
        $data = ['status' => $status];

        if ($status === 'suspended') {
            $data['suspended_by'] = $actorId;
            $data['suspended_reason'] = $reason;
        } else {
            $data['suspended_by'] = null;
            $data['suspended_reason'] = null;
        }

        static::update($id, $data);
    }

    /**
     * Returns employer_user_id/student_id for an ownership check, or null
     * if the thread doesn't exist - callers use this before allowing any
     * read/write against a thread.
     */
    public static function participants(int $id): ?array
    {
        return static::find($id);
    }

    /**
     * Inbox listing for one side of a thread. $role picks whether we filter
     * on employer_user_id or student_id; other-party name/company and an
     * unread-count subquery (messages from the OTHER party, not yet read)
     * are joined in for the list view.
     */
    public static function forUser(int $userId, string $role): array
    {
        // Two distinct placeholders bound to the same value - PDO's native
        // (non-emulated) prepares reject reusing one named parameter twice
        // in one query (SQLSTATE[HY093]), same pattern already established
        // in Institute::buildPublicFilterClause().
        if ($role === 'student') {
            $sql = "SELECT t.*, u.full_name AS other_party_name, c.name AS other_party_company,
                        (SELECT COUNT(*) FROM chat_messages m WHERE m.chat_thread_id = t.id AND m.sender_id != :user_id_unread AND m.read_at IS NULL) AS unread_count,
                        (SELECT body FROM chat_messages m WHERE m.chat_thread_id = t.id ORDER BY m.id DESC LIMIT 1) AS last_message_body
                    FROM chat_threads t
                    JOIN users u ON u.id = t.employer_user_id
                    LEFT JOIN companies c ON c.user_id = t.employer_user_id
                    WHERE t.student_id = :user_id_where
                    ORDER BY (t.last_message_at IS NULL), t.last_message_at DESC, t.created_at DESC";
        } else {
            $sql = "SELECT t.*, u.full_name AS other_party_name, NULL AS other_party_company,
                        (SELECT COUNT(*) FROM chat_messages m WHERE m.chat_thread_id = t.id AND m.sender_id != :user_id_unread AND m.read_at IS NULL) AS unread_count,
                        (SELECT body FROM chat_messages m WHERE m.chat_thread_id = t.id ORDER BY m.id DESC LIMIT 1) AS last_message_body
                    FROM chat_threads t
                    JOIN users u ON u.id = t.student_id
                    WHERE t.employer_user_id = :user_id_where
                    ORDER BY (t.last_message_at IS NULL), t.last_message_at DESC, t.created_at DESC";
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['user_id_unread' => $userId, 'user_id_where' => $userId]);

        return $stmt->fetchAll();
    }

    public static function unreadCountForUser(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM chat_messages m
             JOIN chat_threads t ON t.id = m.chat_thread_id
             WHERE (t.employer_user_id = :user_id OR t.student_id = :user_id2)
               AND m.sender_id != :user_id3 AND m.read_at IS NULL"
        );
        $stmt->execute(['user_id' => $userId, 'user_id2' => $userId, 'user_id3' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public static function adminListing(?string $keyword = null, int $page = 1, int $perPage = 20): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($keyword)) {
            $where = '(eu.full_name LIKE :keyword_employer OR su.full_name LIKE :keyword_student OR c.name LIKE :keyword_company)';
            $params['keyword_employer'] = '%' . $keyword . '%';
            $params['keyword_student'] = '%' . $keyword . '%';
            $params['keyword_company'] = '%' . $keyword . '%';
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM chat_threads t
             JOIN users eu ON eu.id = t.employer_user_id
             JOIN users su ON su.id = t.student_id
             LEFT JOIN companies c ON c.user_id = t.employer_user_id
             WHERE {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT t.*, eu.full_name AS employer_name, su.full_name AS student_name, c.name AS company_name,
                    (SELECT COUNT(*) FROM chat_messages m WHERE m.chat_thread_id = t.id) AS message_count
             FROM chat_threads t
             JOIN users eu ON eu.id = t.employer_user_id
             JOIN users su ON su.id = t.student_id
             LEFT JOIN companies c ON c.user_id = t.employer_user_id
             WHERE {$where}
             ORDER BY t.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }
}

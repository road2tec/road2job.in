<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class ChatMessage extends Model
{
    protected static string $table = 'chat_messages';
    protected static bool $timestamps = false;

    public static function send(int $threadId, int $senderId, ?string $body, ?array $attachment = null): string
    {
        return static::insert([
            'chat_thread_id' => $threadId,
            'sender_id' => $senderId,
            'body' => $body,
            'attachment_path' => $attachment['path'] ?? null,
            'attachment_original_name' => $attachment['original_name'] ?? null,
            'attachment_mime' => $attachment['mime'] ?? null,
            'attachment_type' => $attachment['type'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Used both for the initial thread render (afterId=0, all messages) and
     * the AJAX poll (afterId = last message id the client already has).
     */
    public static function forThread(int $threadId, int $afterId = 0): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM chat_messages WHERE chat_thread_id = :thread_id AND id > :after_id ORDER BY id ASC"
        );
        $stmt->execute(['thread_id' => $threadId, 'after_id' => $afterId]);

        return $stmt->fetchAll();
    }

    /**
     * Marks unread messages FROM THE OTHER PARTY as read - a genuine read
     * receipt, never fabricated: only fires when the actual reader calls
     * this, and never touches the reader's own sent messages.
     */
    public static function markThreadRead(int $threadId, int $readerId): int
    {
        $stmt = Database::connection()->prepare(
            "UPDATE chat_messages SET read_at = :read_at
             WHERE chat_thread_id = :thread_id AND sender_id != :reader_id AND read_at IS NULL"
        );
        $stmt->execute([
            'read_at' => date('Y-m-d H:i:s'),
            'thread_id' => $threadId,
            'reader_id' => $readerId,
        ]);

        return $stmt->rowCount();
    }
}

<?php

namespace App\Models;

use Core\Model;

class ContactMessage extends Model
{
    protected static string $table = 'contact_messages';
    protected static bool $timestamps = false;

    public static function submit(string $name, string $email, string $subject, string $message): string
    {
        return static::insert([
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

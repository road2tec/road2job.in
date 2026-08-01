<?php

namespace App\Models;

use Core\Model;

class Role extends Model
{
    protected static string $table = 'roles';
    protected static bool $timestamps = true;

    public static function findBySlug(string $slug): ?array
    {
        return static::findBy('slug', $slug);
    }
}

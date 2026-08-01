<?php

namespace Core;

/**
 * Minimal PSR-4 autoloader for the Core\ and App\ namespaces so the
 * framework boots even before `composer install` has been run.
 * When vendor/autoload.php exists (Composer packages like PHPMailer),
 * it is loaded alongside this, not instead of it.
 */
class Autoloader
{
    protected static array $prefixes = [
        'Core\\' => __DIR__ . '/',
    ];

    public static function register(): void
    {
        self::$prefixes['App\\'] = dirname(__DIR__) . '/app/';
        spl_autoload_register([self::class, 'load']);
    }

    protected static function load(string $class): void
    {
        foreach (self::$prefixes as $prefix => $baseDir) {
            if (!str_starts_with($class, $prefix)) {
                continue;
            }

            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }
}

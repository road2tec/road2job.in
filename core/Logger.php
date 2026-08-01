<?php

namespace Core;

class Logger
{
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';

    public static function info(string $message, array $context = []): void
    {
        self::write(self::INFO, $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write(self::WARNING, $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write(self::ERROR, $message, $context);
    }

    protected static function write(string $level, string $message, array $context): void
    {
        $dir = base_path('storage/logs');

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = $dir . '/app-' . date('Y-m-d') . '.log';
        $contextStr = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        $line = sprintf('[%s] %s: %s%s%s', date('Y-m-d H:i:s'), $level, $message, $contextStr, PHP_EOL);

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}

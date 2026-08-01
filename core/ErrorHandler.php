<?php

namespace Core;

use Throwable;
use ErrorException;

class ErrorHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }

        throw new ErrorException($message, 0, $level, $file, $line);
    }

    public static function handleException(Throwable $e): void
    {
        Logger::error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        self::render(500, $e);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            Logger::error($error['message'], [
                'file' => $error['file'],
                'line' => $error['line'],
            ]);

            self::render(500);
        }
    }

    public static function render(int $status, ?Throwable $e = null): void
    {
        if (!headers_sent()) {
            http_response_code($status);
        }

        $view = $status === 404 ? '404' : ($status === 403 ? '403' : '500');
        $viewFile = base_path("app/views/errors/{$view}.php");

        $debug = config('app.debug') === true;

        if (is_file($viewFile)) {
            require $viewFile;
        } else {
            echo "Error {$status}";
        }

        exit;
    }
}

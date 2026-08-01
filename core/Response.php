<?php

namespace Core;

class Response
{
    public static function redirect(string $to): void
    {
        $url = str_starts_with($to, 'http') ? $to : url($to);
        header("Location: {$url}");
        exit;
    }

    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function abort(int $status): void
    {
        ErrorHandler::render($status);
    }
}

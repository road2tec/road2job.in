<?php

namespace Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = null): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $to): void
    {
        Response::redirect($to);
    }

    protected function json(array $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function back(string $fallback = '/'): void
    {
        $to = $_SERVER['HTTP_REFERER'] ?? $fallback;
        Response::redirect($to);
    }
}

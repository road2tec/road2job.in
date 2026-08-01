<?php

namespace Core;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        $viewFile = base_path("app/views/{$view}.php");

        if (!is_file($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = base_path("app/views/layouts/{$layout}.php");

        if (!is_file($layoutFile)) {
            throw new \RuntimeException("Layout not found: {$layout}");
        }

        require $layoutFile;
    }

    public static function partial(string $view, array $data = []): void
    {
        $viewFile = base_path("app/views/{$view}.php");

        if (!is_file($viewFile)) {
            throw new \RuntimeException("Partial not found: {$view}");
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
    }
}

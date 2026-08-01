<?php

use Core\Session;

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return rtrim(BASE_PATH, '/') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => $value,
        };
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $cache = [];

        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($cache[$file])) {
            $path = base_path("config/{$file}.php");
            $cache[$file] = is_file($path) ? require $path : [];
        }

        if ($item === null) {
            return $cache[$file];
        }

        return $cache[$file][$item] ?? $default;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim((string) config('app.url'), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('current_path')) {
    function current_path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir !== '' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir));
        }

        return '/' . trim($path, '/');
    }
}

if (!function_exists('is_active_path')) {
    function is_active_path(string $href): bool
    {
        $current = current_path();
        $target = '/' . trim($href, '/');

        if ($target === '/dashboard') {
            return $current === '/dashboard';
        }

        return $current === $target || str_starts_with($current . '/', $target . '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Appends a ?v={mtime} cache-buster so edited CSS/JS/images are picked
     * up immediately despite the long-lived ExpiresByType headers in
     * public/.htaccess (1 month for CSS/JS, 1 year for images/fonts) -
     * without this, browsers keep serving a stale cached copy of an asset
     * until the file's URL itself changes.
     */
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $absolute = BASE_PATH . '/public/assets/' . $path;
        $version = is_file($absolute) ? filemtime($absolute) : null;

        $url = url('assets/' . $path);

        return $version !== null ? $url . '?v=' . $version : $url;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('display_date')) {
    /**
     * Defensive rendering for date strings that may already be stored as
     * a degenerate value like "0000-00-00" (see core/Validator.php's date
     * rule for the corresponding intake-side fix) - shows nothing rather
     * than the literal zero-date so old bad data doesn't display as-is.
     */
    function display_date(?string $value, string $fallback = ''): string
    {
        if (empty($value) || (int) substr($value, 0, 4) < 1) {
            return $fallback;
        }

        return e($value);
    }
}

if (!function_exists('resume_month_year')) {
    /**
     * "2024-02-01" -> "Feb 2024" for resume/print output, where a raw ISO
     * date or a bare "?" fallback (display_date()'s style, fine for a
     * dashboard) reads as unfinished/broken on a document meant to leave
     * this site. Shares display_date()'s zero-date guard rather than
     * duplicating it with different logic.
     */
    function resume_month_year(?string $value): string
    {
        if (empty($value) || (int) substr($value, 0, 4) < 1) {
            return '';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? '' : date('M Y', $timestamp);
    }
}

if (!function_exists('resume_date_range')) {
    /**
     * Builds a resume date range from two possibly-invalid/legacy dates -
     * never falls back to a bare "?" the way display_date() does. If only
     * one side is a genuine date (the common real case: a legacy
     * "0000-00-00" start_date on an old record), shows just that one
     * value instead of "? - Aug 2025".
     */
    function resume_date_range(?string $start, ?string $end, bool $ongoing = false): string
    {
        $startFormatted = resume_month_year($start);
        $endFormatted = $ongoing ? 'Present' : resume_month_year($end);

        if ($startFormatted !== '' && $endFormatted !== '') {
            return "{$startFormatted} \u{2013} {$endFormatted}";
        }

        return $startFormatted !== '' ? $startFormatted : $endFormatted;
    }
}

if (!function_exists('resume_bullet_lines')) {
    /**
     * Splits a free-text description (a plain <textarea>, may or may not
     * contain newlines) into individual bullet-point lines for resume
     * templates that render descriptions as a <ul> rather than a
     * paragraph. A single-line description still returns a one-item array
     * (renders as one bullet) - this never invents line breaks that
     * weren't there, just respects the ones the student typed.
     */
    function resume_bullet_lines(?string $text): array
    {
        if (empty($text)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($text));

        return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
    }
}

if (!function_exists('initials')) {
    function initials(?string $name): string
    {
        $words = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return '?';
        }

        $letters = array_map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)), array_slice($words, 0, 2));

        return implode('', $letters);
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        $old = Session::getFlash('_old');
        return e($old[$key] ?? $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $default = null): mixed
    {
        return Session::getFlash($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::put('_csrf_token', bin2hex(random_bytes(32)));
        }

        return Session::get('_csrf_token');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return Session::get('_user');
    }
}

if (!function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return Session::has('_user');
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): void
    {
        echo '<pre style="background:#111;color:#0f0;padding:1rem;">';
        foreach ($vars as $var) {
            print_r($var);
        }
        echo '</pre>';
        exit;
    }
}

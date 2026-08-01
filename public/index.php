<?php

define('BASE_PATH', dirname(__DIR__));
// Where publicly-served uploads/assets physically live - this file's own
// directory (public/) here, but a different deployment layout (flattened
// production, see DEPLOYMENT.md) sets this to the same value as BASE_PATH
// instead. Anything writing to or resolving uploads/assets must use this,
// never a hardcoded "public/..." path, so it works under either layout.
define('WEB_ROOT', __DIR__);

// Composer's autoloader (PHPMailer, etc.) is optional - the app boots without it.
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

require BASE_PATH . '/core/Autoloader.php';
require BASE_PATH . '/app/helpers/functions.php';

Core\Autoloader::register();

use Core\Env;
use Core\ErrorHandler;
use Core\Session;
use Core\Router;
use Core\Request;

Env::load(BASE_PATH . '/.env');

date_default_timezone_set(config('app.timezone', 'UTC'));

ErrorHandler::register();

if (config('app.debug') === true) {
    ini_set('display_errors', '1');
}

Session::start();

$request = new Request();

// Both of these need the database, which the specific route about to be
// dispatched may not (e.g. static marketing pages). A transient DB outage
// here must not 500 every single request site-wide - fail open (treat as
// "not logged in via remember-me" / "not in maintenance mode") and let
// routing continue; a route that genuinely needs the DB will still fail on
// its own and get the normal 500 page from ErrorHandler.
try {
    if (!Session::has('_user')) {
        (new App\Services\RememberMeService())->attempt($request);
    }

    if (App\Models\Setting::get('maintenance_mode', '0') === '1') {
        $sessionUser = Session::get('_user');
        $isPrivileged = $sessionUser !== null && in_array($sessionUser['role_slug'], ['admin', 'super_admin'], true);

        if (!$isPrivileged && $request->path() !== '/login') {
            http_response_code(503);
            require BASE_PATH . '/app/views/errors/maintenance.php';
            exit;
        }
    }
} catch (\Throwable $e) {
    \Core\Logger::error('Pre-routing DB check failed, continuing without it', ['message' => $e->getMessage()]);
}

$router = new Router();
$router->registerMiddleware([
    'auth' => App\Middleware\AuthMiddleware::class,
    'guest' => App\Middleware\GuestMiddleware::class,
    'role' => App\Middleware\RoleMiddleware::class,
    'csrf' => App\Middleware\CsrfMiddleware::class,
]);

require BASE_PATH . '/routes/web.php';

$router->dispatch($request);

<?php

namespace App\Middleware;

use Core\Request;
use Core\Response;
use Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request): bool
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        $token = $request->input('_csrf_token', '');
        $sessionToken = Session::get('_csrf_token', '');

        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
            Response::abort(403);
            return false;
        }

        return true;
    }
}

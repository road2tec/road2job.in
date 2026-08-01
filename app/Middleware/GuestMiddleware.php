<?php

namespace App\Middleware;

use Core\Request;
use Core\Response;
use Core\Session;

class GuestMiddleware
{
    public function handle(Request $request): bool
    {
        if (Session::has('_user')) {
            Response::redirect('/dashboard');
            return false;
        }

        return true;
    }
}

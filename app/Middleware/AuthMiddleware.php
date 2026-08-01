<?php

namespace App\Middleware;

use App\Models\UserSession;
use Core\Request;
use Core\Response;
use Core\Session;

class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        if (!Session::has('_user')) {
            Session::flash('error', 'Please log in to continue.');
            Response::redirect('/login');
            return false;
        }

        $record = UserSession::findBySessionId(session_id());

        if ($record === null || $record['logout_at'] !== null) {
            Session::invalidate();
            Session::flash('error', 'You were logged out because this session was ended (possibly from another device).');
            Response::redirect('/login');
            return false;
        }

        return true;
    }
}

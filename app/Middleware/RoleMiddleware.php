<?php

namespace App\Middleware;

use App\Models\AuditLog;
use Core\Request;
use Core\Response;
use Core\Session;

class RoleMiddleware
{
    public function handle(Request $request, ?string $allowedRoles = null): bool
    {
        $user = Session::get('_user');

        if ($user === null) {
            Response::redirect('/login');
            return false;
        }

        $allowed = $allowedRoles ? explode(',', $allowedRoles) : [];

        if (!empty($allowed) && !in_array($user['role_slug'], $allowed, true)) {
            AuditLog::record($user['id'], 'access_denied', "Attempted to access a route requiring: {$allowedRoles}", $request->ip());
            Response::abort(403);
            return false;
        }

        return true;
    }
}

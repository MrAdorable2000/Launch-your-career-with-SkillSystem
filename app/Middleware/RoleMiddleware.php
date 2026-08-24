<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\URL;

class RoleMiddleware
{
    public static function handle(array $allowedRoles): bool
    {
        if (!Session::isLoggedIn()) {
            URL::redirect('login');
            return false;
        }

        if (!in_array(Session::userRole(), $allowedRoles)) {
            http_response_code(403);
            die('Access Denied: You do not have permission to access this page.');
        }

        return true;
    }
}
<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\URL;

class AuthMiddleware
{
    public static function handle(): bool
    {
        if (!Session::isLoggedIn()) {
            Session::flash('intended_url', URL::current());
            URL::redirect('login');
            return false;
        }
        return true;
    }
}
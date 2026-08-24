<?php

namespace App\Config;

class App
{
    public static string $name = APP_NAME;
    public static string $url = APP_URL;
    public static string $env = APP_ENV;
    public static bool $debug = APP_DEBUG;
    public static string $version = '1.0.0';

    public static function isLocal(): bool
    {
        return self::$env === 'local';
    }

    public static function asset(string $path): string
    {
        return self::$url . '/public/assets/' . ltrim($path, '/');
    }

    public static function uploadUrl(string $path): string
    {
        return self::$url . '/public/assets/uploads/' . ltrim($path, '/');
    }
}
<?php

namespace App\Helpers;

class CSRF
{
    private static string $name = '_token';

    public static function init(): void
    {
        if (empty($_SESSION[self::$name])) {
            self::generate();
        }
    }

    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::$name] = $token;
        return $token;
    }

    public static function token(): string
    {
        return $_SESSION[self::$name] ?? '';
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::$name . '" value="' . self::escape(self::token()) . '">';
    }

    public static function validate(string $token): bool
    {
        if (empty($_SESSION[self::$name]) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION[self::$name], $token);
    }

    public static function check(): bool
    {
        $token = $_POST[self::$name] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!self::validate($token)) {
            return false;
        }
        self::generate(); // Rotate token after validation
        return true;
    }

    private static function escape(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
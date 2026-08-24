<?php

namespace App\Helpers;

class Session
{
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, string $value): void
    {
        $_SESSION['flash'][$key] = $value;
    }

    public static function getFlash(string $key, string $default = ''): string
    {
        $value = $_SESSION['flash'][$key] ?? $default;
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['flash'][$key]);
    }

    public static function regenerate(): bool
    {
        return session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        session_destroy();
    }

    public static function setUser(array $user): void
    {
        self::set('userId', $user['id']);
        self::set('userRole', $user['role_slug']);
        self::set('userName', $user['first_name'] . ' ' . $user['last_name']);
        self::set('userEmail', $user['email']);
        self::set('userAvatar', $user['avatar'] ?? null);
        // Language priority on login: DB column > cookie (set by previous language switch) > default 'en'
        // The cookie fallback ensures the language preference survives even if the users.language
        // column does not exist yet (migration_innovation.sql not run).
        $lang = $user['language'] ?? ($_COOKIE['ss_lang'] ?? 'en');
        self::set('userLanguage', $lang);
        self::set('isLoggedIn', true);
    }

    public static function getLanguage(): string
    {
        return self::get('userLanguage', $_COOKIE['ss_lang'] ?? 'en');
    }

    public static function isLoggedIn(): bool
    {
        return (bool) self::get('isLoggedIn', false);
    }

    public static function userId(): ?int
    {
        return self::get('userId');
    }

    public static function userRole(): ?string
    {
        return self::get('userRole');
    }

    public static function userName(): ?string
    {
        return self::get('userName');
    }

    public static function isAdmin(): bool
    {
        return self::userRole() === 'admin';
    }

    public static function isStudent(): bool
    {
        return self::userRole() === 'student';
    }

    public static function isEmployer(): bool
    {
        return self::userRole() === 'employer';
    }

    public static function isUniversity(): bool
    {
        return self::userRole() === 'university';
    }

    public static function isMentor(): bool
    {
        return self::userRole() === 'mentor';
    }
}
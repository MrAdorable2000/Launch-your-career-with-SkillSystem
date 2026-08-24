<?php

namespace App\Helpers;

class URL
{
    public static function to(string $path): string
    {
        return APP_URL . '/' . ltrim($path, '/');
    }

    public static function current(): string
    {
        $url = $_GET['url'] ?? '/';
        return '/' . rtrim($url, '/');
    }

    public static function redirect(string $path): void
    {
        header("Location: " . self::to($path));
        exit;
    }

    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? self::to('/');
        header("Location: " . $referer);
        exit;
    }

    public static function asset(string $path): string
    {
        return APP_URL . '/public/assets/' . ltrim($path, '/');
    }
}
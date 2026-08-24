<?php

namespace App\Helpers;

class Flash
{
    public static function set(string $type, string $message): void
    {
        Session::flash('flash_type', $type);
        Session::flash('flash_message', $message);
    }

    public static function success(string $message): void { self::set('success', $message); }
    public static function error(string $message): void { self::set('error', $message); }
    public static function warning(string $message): void { self::set('warning', $message); }
    public static function info(string $message): void { self::set('info', $message); }

    public static function has(): bool { return Session::hasFlash('flash_message'); }

    public static function display(): string
    {
        if (!self::has()) return '';
        $type = Session::getFlash('flash_type', 'info');
        $message = Session::getFlash('flash_message', '');
        $icons = [
            'success' => 'fa-check-circle',
            'error'   => 'fa-times-circle',
            'warning' => 'fa-exclamation-triangle',
            'info'    => 'fa-info-circle'
        ];
        $typeClass = $type === 'error' ? 'danger' : $type;
        $icon = $icons[$type] ?? $icons['info'];
        return '<div class="ss-alert ss-alert-' . $typeClass . ' ss-animate-fade-down" role="alert">'
            . '<i class="fas ' . $icon . ' alert-icon"></i>'
            . '<div class="alert-body">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>'
            . '<button type="button" class="alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>'
            . '</div>';
    }
}

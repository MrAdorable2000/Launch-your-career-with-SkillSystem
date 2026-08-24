<?php
/**
 * Theme — Server-side theme helper.
 *
 * Reads the ss_theme cookie (or falls back to light).
 * The actual visual switching is done client-side via [data-theme] on <html>.
 */

namespace App\Helpers;

class Theme
{
    public static function current(): string
    {
        return $_COOKIE['ss_theme'] ?? 'light';
    }

    public static function isDark(): bool
    {
        return self::current() === 'dark';
    }

    public static function htmlAttr(): string
    {
        $attrs = 'data-theme="' . htmlspecialchars(self::current(), ENT_QUOTES) . '"';

        // Determine current language + RTL safely (works even if I18n helper isn't loaded)
        $lang = 'en';
        $isRtl = false;
        if (class_exists(__NAMESPACE__ . '\\I18n', false)) {
            try {
                $lang = \App\Helpers\I18n::current();
                $isRtl = \App\Helpers\I18n::isRtl();
            } catch (\Throwable $e) {
                // fall through to defaults
            }
        } else {
            // Fallback: read from session/cookie
            $lang = $_SESSION['userLanguage'] ?? ($_COOKIE['ss_lang'] ?? 'en');
            if (!in_array($lang, ['en', 'fr', 'rw', 'sw', 'ar'], true)) {
                $lang = 'en';
            }
            $isRtl = ($lang === 'ar');
        }

        if ($isRtl) {
            $attrs .= ' dir="rtl" lang="ar"';
        } else {
            $attrs .= ' lang="' . htmlspecialchars($lang, ENT_QUOTES) . '"';
        }
        return $attrs;
    }

    /**
     * Read a CSS variable color (used for charts).
     * Returns a hex/rgb string for the given role in the current theme.
     */
    public static function chartColors(): array
    {
        if (self::isDark()) {
            return [
                'primary'   => '#6366F1',
                'secondary' => '#06B6D4',
                'success'   => '#10B981',
                'warning'   => '#F59E0B',
                'danger'    => '#EF4444',
                'info'      => '#3B82F6',
                'accent'    => '#A855F7',
                'text'      => '#CBD5E1',
                'grid'      => 'rgba(255,255,255,0.06)',
                'surface'   => '#131C31',
            ];
        }
        return [
            'primary'   => '#4F46E5',
            'secondary' => '#06B6D4',
            'success'   => '#10B981',
            'warning'   => '#F59E0B',
            'danger'    => '#EF4444',
            'info'      => '#3B82F6',
            'accent'    => '#7C3AED',
            'text'      => '#475569',
            'grid'      => 'rgba(15,23,42,0.06)',
            'surface'   => '#FFFFFF',
        ];
    }
}

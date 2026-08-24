<?php
/**
 * SkillSystem — View Helper Functions
 * These are global functions available in all view files.
 * They must be loaded AFTER the class aliases in index.php.
 */

if (!function_exists('timeAgo')) {
    /**
     * Convert a datetime string to a human-readable "time ago" format.
     * e.g. "2025-06-30 09:42:11" → "5h ago"
     */
    function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        if ($time === false) return 'unknown';

        $diff = time() - $time;
        if ($diff < 0) $diff = 0;

        if ($diff < 60) return 'just now';
        $mins = floor($diff / 60);
        if ($mins < 60) return $mins . 'm ago';
        $hours = floor($mins / 60);
        if ($hours < 24) return $hours . 'h ago';
        $days = floor($hours / 24);
        if ($days < 7) return $days . 'd ago';
        $weeks = floor($days / 7);
        if ($weeks < 4) return $weeks . 'w ago';
        $months = floor($days / 30);
        if ($months < 12) return $months . 'mo ago';
        $years = floor($days / 365);
        return $years . 'y ago';
    }
}

if (!function_exists('formatRWF')) {
    /**
     * Format a number as Rwandan Francs.
     * e.g. 800000 → "800,000 RWF"
     */
    function formatRWF(float|int $amount): string
    {
        return number_format($amount) . ' RWF';
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format a date string for display.
     * e.g. "2025-06-30" → "Jun 30, 2025"
     */
    function formatDate(?string $date): string
    {
        if (!$date) return '—';
        $time = strtotime($date);
        if ($time === false) return $date;
        return date('M j, Y', $time);
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format a datetime string for display.
     * e.g. "2025-06-30 09:42:11" → "Jun 30, 2025 09:42 AM"
     */
    function formatDateTime(?string $datetime): string
    {
        if (!$datetime) return '—';
        $time = strtotime($datetime);
        if ($time === false) return $datetime;
        return date('M j, Y g:i A', $time);
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncate a string to a max length, adding an ellipsis.
     */
    function truncate(string $text, int $max = 100): string
    {
        if (strlen($text) <= $max) return $text;
        return substr($text, 0, $max) . '…';
    }
}

if (!function_exists('statusBadgeClass')) {
    /**
     * Get the CSS class for a status badge.
     */
    function statusBadgeClass(string $status): string
    {
        return match (strtolower($status)) {
            'active', 'published', 'accepted', 'open', 'available' => 'ss-badge-success',
            'pending', 'reviewing', 'draft' => 'ss-badge-warning',
            'rejected', 'closed', 'banned', 'suspended' => 'ss-badge-danger',
            'inactive' => 'ss-badge-muted',
            default => 'ss-badge-muted',
        };
    }
}

if (!function_exists('roleBadgeClass')) {
    /**
     * Get the CSS class for a role badge.
     */
    function roleBadgeClass(string $role): string
    {
        return match (strtolower($role)) {
            'admin' => 'ss-badge-danger',
            'employer' => 'ss-badge-primary',
            'university' => 'ss-badge-info',
            'mentor' => 'ss-badge-warning',
            'student' => 'ss-badge-success',
            default => 'ss-badge-muted',
        };
    }
}

/**
 * Translation function __()
 *
 * Tries App\Helpers\I18n::translate() first. If the I18n class is not available
 * (e.g. on a partial upgrade where index.php / I18n.php weren't replaced), falls
 * back gracefully to returning the key itself — so the page NEVER crashes with
 * "Call to undefined function __()". The UI will simply show English until the
 * full upgrade is in place.
 */
if (!function_exists('__')) {
    function __(string $key, array $params = []): string
    {
        // Try the proper I18n system first
        if (class_exists('\\App\\Helpers\\I18n', false)) {
            try {
                return \App\Helpers\I18n::translate($key, $params);
            } catch (\Throwable $e) {
                // fall through to safe fallback
            }
        }
        // Safe fallback: substitute placeholders into the key string, or just return the key.
        // This keeps the page rendering even if language files are missing.
        $text = $key;
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $text = str_replace('{' . $k . '}', (string) $v, $text);
            }
        }
        return $text;
    }
}

/**
 * Get the current language code (defensive — works even without I18n loaded).
 */
if (!function_exists('current_language')) {
    function current_language(): string
    {
        if (class_exists('\\App\\Helpers\\I18n', false)) {
            try {
                return \App\Helpers\I18n::current();
            } catch (\Throwable $e) {}
        }
        // Fallback: read from session or cookie
        $lang = $_SESSION['userLanguage'] ?? ($_COOKIE['ss_lang'] ?? 'en');
        return in_array($lang, ['en', 'fr', 'rw', 'sw', 'ar'], true) ? $lang : 'en';
    }
}

/**
 * Check if current language is RTL (defensive).
 */
if (!function_exists('is_rtl_language')) {
    function is_rtl_language(): bool
    {
        return current_language() === 'ar';
    }
}

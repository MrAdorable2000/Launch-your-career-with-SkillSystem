<?php
/**
 * I18n — Internationalization helper.
 *
 * Loads the appropriate language file based on the user's language preference
 * (Session > Cookie > 'en') and provides the __() translation function.
 *
 * Usage in views:
 *     <?= __('dashboard') ?>
 *     <?= __('welcome_back', ['name' => 'John']) ?>
 *
 * Language files live in app/Languages/{code}.php (e.g. en.php, fr.php).
 * Each file returns an array of 'key' => 'translated string'.
 *
 * If a key is not found in the active language file, the English translation
 * is used as fallback. If the key is not in English either, the key itself
 * is returned (so the UI never breaks).
 *
 * Parameter interpolation: use {name} placeholders in translation strings,
 * e.g. 'welcome_name' => 'Welcome, {name}!' → __('welcome_name', ['name' => 'John'])
 */

namespace App\Helpers;

class I18n
{
    /** @var array<string, array<string, string>> Cached language files */
    private static array $cache = [];

    /** @var string|null Current active language code */
    private static ?string $active = null;

    /** @var string[] Supported language codes */
    public const SUPPORTED = ['en', 'fr', 'rw', 'sw', 'ar'];

    /** @var string Default/fallback language */
    public const DEFAULT = 'en';

    /**
     * Get the current active language code.
     * Priority: Session::getLanguage() > $_COOKIE['ss_lang'] > 'en'
     */
    public static function current(): string
    {
        if (self::$active !== null) {
            return self::$active;
        }

        $lang = Session::getLanguage();

        // Validate against supported languages
        if (!in_array($lang, self::SUPPORTED, true)) {
            $lang = self::DEFAULT;
        }

        self::$active = $lang;
        return $lang;
    }

    /**
     * Whether the current language is RTL (right-to-left).
     * Used to set dir="rtl" on <html>.
     */
    public static function isRtl(): bool
    {
        return self::current() === 'ar';
    }

    /**
     * Load a language file and cache it.
     *
     * @return array<string, string>
     */
    private static function load(string $code): array
    {
        if (isset(self::$cache[$code])) {
            return self::$cache[$code];
        }

        $path = LANGUAGES_PATH . '/' . $code . '.php';
        if (!is_file($path)) {
            self::$cache[$code] = [];
            return [];
        }

        $data = include $path;
        if (!is_array($data)) {
            self::$cache[$code] = [];
            return [];
        }

        self::$cache[$code] = $data;
        return $data;
    }

    /**
     * Translate a key.
     *
     * @param string $key     Translation key (e.g. 'dashboard')
     * @param array  $params  Placeholder substitution: ['name' => 'John']
     *                        replaces {name} in the translation string.
     * @return string  Translated string, or the key itself if not found.
     */
    public static function translate(string $key, array $params = []): string
    {
        if ($key === '') {
            return '';
        }

        $code = self::current();

        // 1. Try the active language
        $strings = self::load($code);
        $text = $strings[$key] ?? null;

        // 2. Fallback to English
        if ($text === null && $code !== self::DEFAULT) {
            $enStrings = self::load(self::DEFAULT);
            $text = $enStrings[$key] ?? null;
        }

        // 3. Final fallback: return the key itself so the UI never breaks
        if ($text === null) {
            return $key;
        }

        // Substitute placeholders
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $text = str_replace('{' . $k . '}', (string) $v, $text);
            }
        }

        return $text;
    }
}

/**
 * Global shorthand for I18n::translate().
 *
 * NOTE: The canonical definition lives in app/Helpers/functions.php (which is
 * loaded earlier in the bootstrap). The guard here ensures we don't redefine
 * it if functions.php was already loaded; if functions.php wasn't loaded yet
 * (someone loading I18n.php directly), this provides a working fallback.
 */
if (!function_exists('__')) {
    function __(string $key, array $params = []): string
    {
        return \App\Helpers\I18n::translate($key, $params);
    }
}

<?php
/**
 * TranslationFilter — Runtime HTML-aware output translator.
 *
 * This is the engine that makes the ENTIRE system switch languages automatically.
 * It hooks into PHP's output buffer and replaces English UI strings with the
 * user's chosen language — but ONLY in safe locations (text nodes and display
 * attributes), never inside HTML tags, URLs, CSS classes, or JavaScript.
 *
 * How it works:
 * 1. Loads the English dictionary (en.php) and the target language dictionary
 * 2. Protects <script>, <style>, <pre>, <code>, <textarea> blocks entirely
 * 3. Splits the HTML into alternating tokens: text nodes and tag tokens
 * 4. Translates ONLY text nodes (content between > and <)
 * 5. Translates ONLY whitelisted display attributes (placeholder, title, alt, aria-label)
 * 6. NEVER touches: tag names, attribute names, href, src, action, class, id,
 *    name, value, data-*, onclick, style, or any other structural attribute
 * 7. Restores protected blocks
 *
 * Safety guarantees:
 * - HTML structure is never corrupted (tags, attributes, URLs stay intact)
 * - JavaScript is never touched (inside <script> or onclick="...")
 * - CSS is never touched (inside <style> or class="...")
 * - Form field names and values are never touched
 * - URLs are never touched
 * - Data attributes are never touched
 * - Only curated dictionary phrases are replaced
 */

namespace App\Helpers;

class TranslationFilter
{
    /** @var array<string, array<string, string>> Cached replacement dictionaries per language */
    private static array $dicts = [];

    /** @var int Character threshold — don't translate phrases shorter than this */
    private const MIN_PHRASE_LENGTH = 3;

    /**
     * Attributes whose values are safe to translate (display-facing only).
     * NEVER add href, src, action, class, id, name, value, data-*, or event
     * handlers here — those are structural/code attributes.
     */
    private const TRANSLATABLE_ATTRIBUTES = [
        'placeholder',
        'title',
        'alt',
        'aria-label',
    ];

    /**
     * HTML blocks whose content should never be translated (code/data blocks).
     */
    private const PROTECTED_BLOCK_PATTERN =
        '/(<script\b[^>]*>.*?<\/script>' .
        '|<style\b[^>]*>.*?<\/style>' .
        '|<pre\b[^>]*>.*?<\/pre>' .
        '|<code\b[^>]*>.*?<\/code>' .
        '|<textarea\b[^>]*>.*?<\/textarea>' .
        '|<svg\b[^>]*>.*?<\/svg>' .
        ')/is';

    /**
     * Apply translation to a complete HTML document.
     *
     * @param string $html  The full HTML output
     * @param string $lang  Target language code (e.g. 'fr', 'rw', 'sw', 'ar')
     * @return string  Translated HTML
     */
    public static function apply(string $html, string $lang): string
    {
        // Skip entirely for English (zero overhead)
        if ($lang === 'en' || $lang === '' || !in_array($lang, I18n::SUPPORTED, true)) {
            return $html;
        }

        $dict = self::buildDictionary($lang);
        if (empty($dict)) {
            return $html;
        }

        // 1. Protect <script>, <style>, <pre>, <code>, <textarea>, <svg> blocks
        $placeholders = [];
        $protected = preg_replace_callback(
            self::PROTECTED_BLOCK_PATTERN,
            function ($m) use (&$placeholders) {
                $key = "\x00BLK" . count($placeholders) . "\x00";
                $placeholders[$key] = $m[0];
                return $key;
            },
            $html
        );

        // 2. Split HTML into alternating tokens: text nodes and tag tokens
        //    PREG_SPLIT_DELIM_CAPTURE keeps the tag delimiters in the result.
        //    Result: [text, tag, text, tag, text, ...]
        //    Even indices = text nodes (safe to translate)
        //    Odd indices  = tags (only translate whitelisted attributes)
        $parts = preg_split('/(<[^>]+>)/', $protected, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false || count($parts) <= 1) {
            // No tags found or split failed — translate the whole thing as text
            $result = strtr($protected, $dict);
        } else {
            $result = '';
            $partCount = count($parts);
            for ($i = 0; $i < $partCount; $i++) {
                if ($i % 2 === 0) {
                    // Text node — translate it
                    $result .= strtr($parts[$i], $dict);
                } else {
                    // Tag — only translate whitelisted display attributes
                    $result .= self::translateTagAttributes($parts[$i], $dict);
                }
            }
        }

        // 3. Restore protected blocks
        if (!empty($placeholders)) {
            $result = strtr($result, $placeholders);
        }

        return $result;
    }

    /**
     * Translate whitelisted display attributes inside an HTML tag.
     *
     * Only translates: placeholder, title, alt, aria-label
     * NEVER touches: href, src, class, id, name, value, data-*, onclick, style, etc.
     *
     * @param string $tag  The full HTML tag (e.g. '<input placeholder="Search...">')
     * @param array<string, string> $dict  Replacement dictionary
     * @return string  Tag with translated display attributes
     */
    private static function translateTagAttributes(string $tag, array $dict): string
    {
        // Build a regex that matches only our whitelisted attributes
        // Pattern: whitespace + attrName + = + "value"
        // Case-insensitive, supports single and double quotes
        $attrPattern = implode('|', self::TRANSLATABLE_ATTRIBUTES);

        return preg_replace_callback(
            '/(\s)(' . $attrPattern . ')\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i',
            function ($m) use ($dict) {
                $prefix   = $m[1];      // leading whitespace
                $attrName = $m[2];      // attribute name (e.g. "placeholder")
                $value    = $m[3] ?? ($m[4] ?? '');  // attribute value (double or single quoted)

                // Translate the attribute value
                $translated = strtr($value, $dict);

                // Re-encode the value using the original quote style
                $quote = isset($m[3]) ? '"' : "'";
                return $prefix . $attrName . '=' . $quote . $translated . $quote;
            },
            $tag
        );
    }

    /**
     * Build the replacement dictionary for a given language.
     *
     * Reads en.php and {lang}.php, then for each key where both have values
     * and they differ, adds:
     *   'English text' => 'Translated text'
     *   'HTML-encoded English text' => 'HTML-encoded translated text'  (if different)
     *
     * @param string $lang  Target language code
     * @return array<string, string>  Replacement map
     */
    private static function buildDictionary(string $lang): array
    {
        if (isset(self::$dicts[$lang])) {
            return self::$dicts[$lang];
        }

        $enPath = LANGUAGES_PATH . '/en.php';
        $langPath = LANGUAGES_PATH . '/' . $lang . '.php';

        if (!is_file($enPath) || !is_file($langPath)) {
            self::$dicts[$lang] = [];
            return [];
        }

        $enDict = include $enPath;
        $langDict = include $langPath;

        if (!is_array($enDict) || !is_array($langDict)) {
            self::$dicts[$lang] = [];
            return [];
        }

        $replacements = [];

        foreach ($enDict as $key => $enText) {
            // Skip if no translation or same as English
            if (!isset($langDict[$key]) || $langDict[$key] === $enText) {
                continue;
            }
            if (mb_strlen($enText) < self::MIN_PHRASE_LENGTH) {
                continue;
            }

            $transText = $langDict[$key];

            // Skip if the translation is identical to English (no point replacing)
            if (trim($transText) === trim($enText)) {
                continue;
            }

            // Raw version (matches text in HTML text nodes)
            $replacements[$enText] = $transText;

            // HTML-encoded version (matches text that went through htmlspecialchars)
            // e.g. "Email & SMS" → "Email &amp; SMS"
            $encodedEn = htmlspecialchars($enText, ENT_QUOTES, 'UTF-8');
            $encodedTrans = htmlspecialchars($transText, ENT_QUOTES, 'UTF-8');
            if ($encodedEn !== $enText) {
                $replacements[$encodedEn] = $encodedTrans;
            }

            // Apostrophe variants — PHP htmlspecialchars encodes ' as &#039;
            // but sometimes views use &apos; or &rsquo; or raw '
            if (strpos($enText, "'") !== false) {
                $aposEn = str_replace("'", '&#039;', $enText);
                $aposTrans = str_replace("'", '&#039;', $transText);
                if ($aposEn !== $enText) {
                    $replacements[$aposEn] = $aposTrans;
                }
            }
        }

        // Sort by key length descending — strtr() already does longest-first,
        // but sorting helps ensure correct priority for overlapping phrases
        // (e.g. "All Users" matches before "All")
        uksort($replacements, function ($a, $b) {
            $lenA = mb_strlen($a);
            $lenB = mb_strlen($b);
            if ($lenA === $lenB) return 0;
            return $lenB - $lenA;
        });

        self::$dicts[$lang] = $replacements;
        return $replacements;
    }

    /**
     * Reset the dictionary cache (useful for testing).
     */
    public static function resetCache(): void
    {
        self::$dicts = [];
    }
}

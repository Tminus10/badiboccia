<?php

/**
 * Website translations. German (Swiss spelling, never "ß") is the source language: every
 * user-facing text is written in German and wrapped in t(), and lang/<code>.php maps those
 * German texts to the other languages -- the same model as the iOS app's string catalog, so a
 * text can be looked up under the same German key in both. Terms shared with the app are
 * fixed in lang/glossary.json; dev/check-translations.php checks both projects against it.
 *
 * Adding a language: add it to LANGUAGES, copy lang/en.php to lang/<code>.php and translate
 * it, and add src/Views/rules/<code>.php (pages without one fall back to German).
 */
final class I18n
{
    /** code => [name in that language, <html lang>]. The first one is the source language. */
    public const LANGUAGES = [
        'de' => ['Deutsch', 'de-CH'],
        'en' => ['English', 'en'],
    ];
    public const DEFAULT = 'de';

    private const COOKIE = 'lang';

    private static string $locale = self::DEFAULT;
    /** @var array<string, string> German text => translation, for the current locale */
    private static array $messages = [];

    /**
     * Picks the language for this request: an explicit ?lang= (remembered in a cookie, that's
     * how the language links and the app's links choose), else the cookie, else the browser's
     * (or the app's) Accept-Language, else German.
     */
    public static function init(): void
    {
        $requested = self::normalize($_GET['lang'] ?? null);
        if ($requested !== null) {
            setcookie(self::COOKIE, $requested, [
                'expires' => time() + 60 * 60 * 24 * 365,
                'path' => '/',
                'samesite' => 'Lax',
            ]);
        }
        self::setLocale($requested
            ?? self::normalize($_COOKIE[self::COOKIE] ?? null)
            ?? self::fromAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
            ?? self::DEFAULT);
    }

    public static function setLocale(string $locale): void
    {
        self::$locale = isset(self::LANGUAGES[$locale]) ? $locale : self::DEFAULT;
        self::$messages = [];
        if (self::$locale !== self::DEFAULT) {
            $file = self::langDir() . '/' . self::$locale . '.php';
            self::$messages = is_file($file) ? require $file : [];
        }
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function htmlLang(): string
    {
        return self::LANGUAGES[self::$locale][1];
    }

    /** The translation of a German source text; the German text itself when there is none. */
    public static function translate(string $text): string
    {
        $translated = self::$messages[$text] ?? '';
        return $translated !== '' ? $translated : $text;
    }

    public static function langDir(): string
    {
        return __DIR__ . '/../lang';
    }

    private static function normalize(mixed $code): ?string
    {
        if (!is_string($code)) {
            return null;
        }
        $code = strtolower(substr($code, 0, 2));
        return isset(self::LANGUAGES[$code]) ? $code : null;
    }

    /** Best supported language from an Accept-Language header ("de-CH,de;q=0.9,en;q=0.8"). */
    private static function fromAcceptLanguage(string $header): ?string
    {
        $best = null;
        $bestQuality = 0.0;
        foreach (explode(',', $header) as $part) {
            $pieces = explode(';', trim($part));
            $code = self::normalize($pieces[0]);
            $quality = 1.0;
            foreach (array_slice($pieces, 1) as $param) {
                if (str_starts_with(trim($param), 'q=')) {
                    $quality = (float) substr(trim($param), 2);
                }
            }
            if ($code !== null && $quality > $bestQuality) {
                [$best, $bestQuality] = [$code, $quality];
            }
        }
        return $best;
    }
}

<?php

/**
 * Checks the translations of the website and the iOS app:
 *  - every t('…') text in src/ has a translation in each lang/<code>.php, and nothing unused is left there
 *  - translations keep the same sprintf placeholders as the German text
 *  - each language has a rules page (src/Views/rules/<code>.php)
 *  - German texts never use "ß" (Swiss spelling)
 *  - both projects use the shared terms from lang/glossary.json
 *
 * Usage: php dev/check-translations.php [path/to/Localizable.xcstrings]
 * (default: ../badiboccia-ios/BocciaScore/Resources/Localizable.xcstrings, skipped if missing)
 * Exits with 1 when something needs fixing.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/src/I18n.php';

$problems = [];
$report = function (string $message) use (&$problems): void {
    $problems[] = $message;
};

// --- Website: texts used in the code ------------------------------------------------------

$sourceTexts = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/src', FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $code = file_get_contents($file->getPathname());
    preg_match_all("/\\bt\\(\\s*'((?:[^'\\\\]|\\\\.)*)'/u", $code, $single);
    preg_match_all('/\bt\(\s*"((?:[^"\\\\$]|\\\\.)*)"/u', $code, $double);
    foreach ($single[1] as $text) {
        $sourceTexts[stripcslashes(str_replace("\\'", "'", $text))] = substr($file->getPathname(), strlen($root) + 1);
    }
    foreach ($double[1] as $text) {
        $sourceTexts[stripcslashes($text)] = substr($file->getPathname(), strlen($root) + 1);
    }
}

/** sprintf placeholders of a text, numbered positionally ("%s %d" and "%1$s %2$d" both give [1 => s, 2 => d]). */
function placeholders(string $text): array
{
    preg_match_all('/%(?:(\d+)\$)?([sd])/', str_replace('%%', '', $text), $matches, PREG_SET_ORDER);
    $result = [];
    $next = 1;
    foreach ($matches as $m) {
        $result[$m[1] !== '' ? (int) $m[1] : $next++] = $m[2];
    }
    ksort($result);
    return $result;
}

$websiteTranslations = [];
foreach (array_keys(I18n::LANGUAGES) as $code) {
    if (!is_file($root . "/src/Views/rules/$code.php")) {
        $report("rules: src/Views/rules/$code.php is missing (shows German)");
    }
    if ($code === I18n::DEFAULT) {
        continue;
    }
    $file = $root . "/lang/$code.php";
    $messages = is_file($file) ? require $file : [];
    $websiteTranslations[$code] = $messages;
    foreach ($sourceTexts as $text => $where) {
        if (($messages[$text] ?? '') === '') {
            $report("website/$code: missing translation for '$text' ($where)");
        } elseif (placeholders($text) !== placeholders($messages[$text])) {
            $report("website/$code: placeholders differ in '$text' → '{$messages[$text]}'");
        }
    }
    foreach (array_keys($messages) as $text) {
        if (!isset($sourceTexts[$text])) {
            $report("website/$code: unused translation for '$text'");
        }
    }
}
foreach (array_keys($sourceTexts) as $text) {
    if (str_contains($text, 'ß')) {
        $report("website/de: '$text' uses ß (Swiss spelling: ss)");
    }
}

// --- App: string catalog ------------------------------------------------------------------

$catalogPath = $argv[1] ?? dirname($root) . '/badiboccia-ios/BocciaScore/Resources/Localizable.xcstrings';
$appTranslations = [];
if (is_file($catalogPath)) {
    $catalog = json_decode(file_get_contents($catalogPath), true, flags: JSON_THROW_ON_ERROR);
    foreach ($catalog['strings'] as $text => $entry) {
        if (($entry['shouldTranslate'] ?? true) === false) {
            continue;
        }
        if (str_contains($text, 'ß')) {
            $report("app/de: '$text' uses ß (Swiss spelling: ss)");
        }
        foreach (array_keys(I18n::LANGUAGES) as $code) {
            if ($code === I18n::DEFAULT) {
                continue;
            }
            $value = $entry['localizations'][$code]['stringUnit']['value'] ?? null;
            if ($value === null) {
                $report("app/$code: missing translation for '$text'");
                continue;
            }
            $appTranslations[$code][$text] = $value;
        }
    }
} else {
    fwrite(STDERR, "Note: app catalog not found at $catalogPath, only checking the website.\n");
}

// --- Shared terms -------------------------------------------------------------------------

$glossary = json_decode(file_get_contents($root . '/lang/glossary.json'), true, flags: JSON_THROW_ON_ERROR);
$check = function (string $project, string $code, array $translations) use ($glossary, $report): void {
    foreach ($glossary['terms'] as $term) {
        if (!isset($term[$code])) {
            continue;
        }
        $pattern = '/(?<![\p{L}\p{N}])' . preg_quote($term[I18n::DEFAULT], '/') . '(?![\p{L}\p{N}])/u';
        $expected = explode('|', mb_strtolower($term[$code]));
        foreach ($translations as $text => $translated) {
            if (!preg_match($pattern, $text)) {
                continue;
            }
            $lower = mb_strtolower(str_replace(['’', '‑'], ["'", '-'], $translated));
            $found = array_filter($expected, fn (string $e): bool => str_contains($lower, $e));
            if ($found === []) {
                $report("$project/$code: '$text' → '$translated' should use \"{$term[$code]}\" for \"{$term[I18n::DEFAULT]}\"");
            }
        }
    }
};
foreach ($websiteTranslations as $code => $translations) {
    $check('website', $code, $translations);
}
foreach ($appTranslations as $code => $translations) {
    $check('app', $code, $translations);
}

// --- Result -------------------------------------------------------------------------------

if ($problems === []) {
    echo 'Translations OK: ' . count($sourceTexts) . ' website texts'
        . ($appTranslations !== [] ? ', ' . count(reset($appTranslations)) . ' app texts' : '') . ".\n";
    exit(0);
}
echo implode("\n", $problems) . "\n\n" . count($problems) . " problem(s).\n";
exit(1);

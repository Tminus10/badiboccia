<?php
// Router script for PHP's built-in dev server: serves real static files directly,
// otherwise hands off to the app's front controller (mirrors what .htaccess does on Apache).
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$file = __DIR__ . '/../public' . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/../public/index.php';

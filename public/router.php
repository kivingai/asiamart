<?php
// PHP built-in router. Запускается из php -S 0.0.0.0:8080 -t public router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

// Существующий файл — отдаём как есть (статика или .php)
if ($path !== '/' && is_file($file)) return false;

// Директория — попробовать index.php внутри неё
if (is_dir($file)) {
    $idx = rtrim($file, '/') . '/index.php';
    if (is_file($idx)) { require $idx; return true; }
}

// Корень — index.php
if ($path === '/' || $path === '') {
    require __DIR__ . '/index.php';
    return true;
}

// Не нашли — 404
http_response_code(404);
require __DIR__ . '/404.php';
return true;

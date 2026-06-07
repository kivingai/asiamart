<?php
// ============================================================
//  AsiaMart — конфигурация
//  Меняйте значения под своё локальное окружение.
// ============================================================

// --- Подключение к базе данных ---
// Для XAMPP / OpenServer / Денвер обычно:
//   DB_USER = 'root'    DB_PASS = ''   (пустой пароль)
// На этом сервере проект работает с отдельным пользователем asiamart.
// Раскомментируйте нужный вариант:

// ВАРИАНТ 1 — локальный XAMPP / OpenServer (root без пароля):
// define('DB_HOST', '127.0.0.1');
// define('DB_PORT', 3306);
// define('DB_NAME', 'asiamart');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// ВАРИАНТ 2 — отдельный пользователь (как на демо-сервере):
define('DB_HOST', getenv('ASIAMART_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', (int)(getenv('ASIAMART_DB_PORT') ?: 3306));
define('DB_NAME', getenv('ASIAMART_DB_NAME') ?: 'asiamart');
define('DB_USER', getenv('ASIAMART_DB_USER') ?: 'asiamart');
define('DB_PASS', getenv('ASIAMART_DB_PASS') ?: 'asiamart_pass');

define('DB_CHARSET', 'utf8mb4');

// --- Сайт ---
define('SITE_NAME', 'AsiaMart');
define('SITE_TAGLINE', 'Магазин азиатских продуктов');
define('SITE_URL', '');  // оставить пустым = автоопределение

// --- Загрузка картинок товаров ---
define('UPLOADS_DIR', __DIR__ . '/../public/uploads');
define('UPLOADS_URL', '/uploads');

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding('UTF-8');

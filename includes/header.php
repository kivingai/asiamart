<?php
require_once __DIR__ . '/helpers.php';

// Параметры можно задать в странице ДО подключения header.php:
//   $page_title       — заголовок <title>
//   $page_description — meta description
//   $page_class       — CSS-класс для <body>
//   $current_page     — slug текущей страницы
//   $page_css         — дополнительный CSS-файл (имя без .css)
$page_title       ??= SITE_NAME . ' — ' . SITE_TAGLINE;
$page_description ??= 'AsiaMart — интернет-магазин азиатских продуктов. Рамен, удон, саке, матча, мисо, соевые соусы и многое другое — с доставкой за 1–2 дня.';
$page_class       ??= '';
$current_page     ??= '';
$page_css         ??= '';

$u = current_user();
$cartCount = cart_count();

function nav_active(string $slug): string {
    return ($GLOBALS['current_page'] ?? '') === $slug ? ' is-active' : '';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?></title>
  <meta name="description" content="<?= e($page_description) ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500&family=DM+Sans:wght@400;500;700&family=Noto+Serif+JP:wght@400;600&display=swap">

  <link rel="stylesheet" href="<?= asset('/assets/css/global.css') ?>">
  <link rel="stylesheet" href="<?= asset('/assets/css/asiamart.css') ?>">
  <link rel="stylesheet" href="<?= asset('/assets/css/ryokan.css') ?>">
  <?php if ($page_css): ?>
    <link rel="stylesheet" href="<?= asset('/assets/css/' . $page_css . '.css') ?>">
  <?php endif; ?>
</head>
<body<?= $page_class ? ' class="' . e($page_class) . '"' : '' ?>>

  <header class="ry-header" id="siteHeader">
    <div class="ry-header-inner">

      <a href="/" class="ry-brand" aria-label="AsiaMart — главная">
        <span class="ry-brand-name">AsiaMart</span>
        <span class="ry-brand-stamp" aria-hidden="true">朱</span>
      </a>

      <nav class="ry-nav" aria-label="Основная навигация">
        <a href="/"             class="ry-nav-link<?= nav_active('index') ?>">Главная</a>
        <a href="/katalog.php"  class="ry-nav-link<?= nav_active('katalog') ?>">Каталог</a>
        <a href="/about.php"    class="ry-nav-link<?= nav_active('about') ?>">О нас</a>
        <a href="/support.php"  class="ry-nav-link<?= nav_active('support') ?>">Поддержка</a>
      </nav>

      <div class="ry-actions">
        <a href="/cart.php" class="ry-cart" aria-label="Корзина">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 4h2l2.5 11.5a2 2 0 0 0 2 1.5h8.5a2 2 0 0 0 2-1.5L21 7H6.5"/>
            <circle cx="9.5" cy="20.5" r="1.2"/>
            <circle cx="17.5" cy="20.5" r="1.2"/>
          </svg>
          <?php if ($cartCount > 0): ?>
            <span class="ry-cart-bubble" aria-label="<?= (int)$cartCount ?> товаров в корзине"><?= (int)$cartCount ?></span>
          <?php endif; ?>
        </a>

        <?php if ($u): ?>
          <a href="/profile.php" class="ry-user" title="Личный кабинет">
            <?php if ($u['role'] === 'admin'): ?>
              <span class="ry-user-star" aria-hidden="true">★</span>
            <?php endif; ?>
            <span class="ry-user-name"><?= e(mb_strimwidth($u['name'], 0, 18, '…')) ?></span>
          </a>
          <?php if ($u['role'] === 'admin'): ?>
            <a href="/admin/" class="ry-admin" title="Админ-панель" aria-label="Открыть админ-панель">部</a>
          <?php endif; ?>
        <?php else: ?>
          <a href="/login.php" class="ry-login">Войти</a>
        <?php endif; ?>
      </div>

    </div>
  </header>

  <main>
    <?php
    $flashOk  = flash_get('success');
    $flashErr = flash_get('error');
    $flashInfo = flash_get('info');
    if ($flashOk || $flashErr || $flashInfo): ?>
      <div class="container" style="margin-top:20px">
        <?php if ($flashOk): ?>
          <div class="flash flash-ok"><?= e($flashOk) ?></div>
        <?php endif; ?>
        <?php if ($flashErr): ?>
          <div class="flash flash-err"><?= e($flashErr) ?></div>
        <?php endif; ?>
        <?php if ($flashInfo): ?>
          <div class="flash flash-info"><?= e($flashInfo) ?></div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

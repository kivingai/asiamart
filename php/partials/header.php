<?php
// Общая шапка магазина AsiaMart.
// Подключается через include в каждой публичной странице.

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../cart.php';

session_init();

$cfg        = app_config();
$user       = current_user();
$cartState  = cart_state();
$cartCount  = (int) $cartState['count'];
$page       = $page ?? '';
?>
<header class="site-header" id="siteHeader">
  <div class="container header-wrapper">
    <a href="index.php" class="logo">
      <h1><?= e($cfg['name']) ?></h1>
      <span class="logo-underline"></span>
    </a>
    <nav class="main-nav">
      <a href="index.php"   class="nav-link <?= $page === 'home'     ? 'active' : '' ?>">Главная</a>
      <a href="katalog.php" class="nav-link <?= $page === 'catalog'  ? 'active' : '' ?>">Каталог</a>
      <a href="sup.php"     class="nav-link <?= $page === 'support'  ? 'active' : '' ?>">Поддержка</a>
      <a href="info.php"    class="nav-link <?= $page === 'info'     ? 'active' : '' ?>">О магазине</a>
    </nav>
    <div class="header-actions">
      <a href="cart.php" class="icon-btn" aria-label="Корзина" id="cartIconLink">
        <svg class="icon" viewBox="-5 -3 35 27" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="8" cy="21" r="1"></circle>
          <circle cx="19" cy="21" r="1"></circle>
          <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
        </svg>
        <span class="cart-badge" id="cartBadge"<?= $cartCount === 0 ? ' hidden' : '' ?>><?= $cartCount ?></span>
      </a>
      <?php if ($user): ?>
        <a href="profile.php" class="btn btn-login" title="<?= e($user['name']) ?>">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span>Кабинет</span>
        </a>
        <?php if ($user['role'] === 'admin'): ?>
          <a href="admin.php" class="btn btn-login" style="background:linear-gradient(135deg,#ef4444,#dc2626);">Админ</a>
        <?php endif; ?>
        <a href="php/logout.php" class="btn btn-login" style="background:#374151;">Выйти</a>
      <?php else: ?>
        <a href="Bxodandreg.php" class="btn btn-login">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span>Вход</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

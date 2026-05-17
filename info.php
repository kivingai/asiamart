<?php
$page = 'info';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/php/cart.php';

$cfg = app_config();
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>О магазине — <?= e($cfg['name']) ?></title>
  <meta name="description" content="<?= e($cfg['name']) ?> — интернет-магазин премиальных продуктов из стран Азии. Узнайте о нашей миссии и преимуществах." />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/global.css" />
  <link rel="stylesheet" href="css/info.css" />
</head>
<body>
  <div class="bg-decor">
    <div class="circle circle-amber"></div>
    <div class="circle circle-red"></div>
    <div class="circle circle-cyan"></div>
  </div>

  <?php include __DIR__ . '/php/partials/header.php'; ?>

  <main>
    <section class="info-hero">
      <div class="container hero-grid">
        <div class="hero-text">
          <div class="badge"><span>О компании</span></div>
          <h2 class="hero-title">
            <span class="hero-title-main"><?= e($cfg['name']) ?></span>
            <span class="hero-title-sub">всё лучшее из азиатских кухонь</span>
          </h2>
          <p class="hero-desc">
            <?= e($cfg['name']) ?> — это интернет-магазин премиальных продуктов
            из Японии, Кореи, Китая, Тайланда и Вьетнама. Мы привозим лапшу
            рамэн и удон, соусы Kikkoman и Lee Kum Kee, чаи сенча и матча,
            традиционные снеки и сладости — напрямую от проверенных
            производителей, без лишних посредников.
          </p>
          <div class="hero-buttons">
            <a href="katalog.php" class="btn btn-cta">В каталог</a>
            <a href="sup.php" class="btn btn-outline">Связаться с нами</a>
          </div>
        </div>
        <div class="hero-image">
          <div class="image-wrapper">
            <img src="img/info-hero.png" alt="<?= e($cfg['name']) ?>" />
          </div>
        </div>
      </div>
    </section>

    <section class="info-mission container">
      <h2>Наша миссия</h2>
      <p>
        Сделать настоящую азиатскую кухню доступной каждому в России.
        Мы тщательно отбираем поставщиков, контролируем сроки годности
        и оригинальность каждой партии и доставляем по всей стране.
      </p>
    </section>

    <section class="info-values container">
      <h2>Наши ценности</h2>
      <div class="info-values-grid">
        <div class="info-card">
          <h3>Качество</h3>
          <p>Только оригинальные товары проверенных производителей с полным комплектом сертификатов.</p>
        </div>
        <div class="info-card">
          <h3>Прозрачность</h3>
          <p>Понятные цены без скрытых наценок, открытая информация о составе и стране производства.</p>
        </div>
        <div class="info-card">
          <h3>Скорость</h3>
          <p>Курьерская доставка по России за 1–2 дня и бесплатная доставка при заказе от <?= money((float) $cfg['free_ship']) ?>.</p>
        </div>
        <div class="info-card">
          <h3>Забота</h3>
          <p>Поддержка 24/7 в Telegram, ВКонтакте и Одноклассниках. Поможем подобрать товары и оформить заказ.</p>
        </div>
      </div>
    </section>

    <section class="info-countries container">
      <h2>Страны, которые мы привозим</h2>
      <ul class="country-list">
        <li>🇯🇵 Япония — рамэн, удон, соба, соевый соус Kikkoman, паста мисо, матча, моти</li>
        <li>🇰🇷 Корея — острая лапша Samyang, паста гочуджан, нори-снеки</li>
        <li>🇨🇳 Китай — пуэр, улун, устричный соус, фунчоза, печенье с предсказанием</li>
        <li>🇹🇭 Тайланд — соус Sriracha, кокосовая вода, сушёное манго</li>
        <li>🇻🇳 Вьетнам — рисовая лапша Pho, рыбный соус Nuoc Mam</li>
      </ul>
    </section>
  </main>

  <?php include __DIR__ . '/php/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="js/site.js"></script>
</body>
</html>

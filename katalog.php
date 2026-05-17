<?php
$page = 'catalog';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/php/cart.php';

$cfg          = app_config();
$categories   = get_categories();
$currentCat   = $_GET['cat']    ?? 'all';
$searchQuery  = trim((string) ($_GET['q'] ?? ''));
$products     = get_products($currentCat, $searchQuery !== '' ? $searchQuery : null);
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Каталог — <?= e($cfg['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />
  <link rel="stylesheet" href="css/global.css" />
  <link rel="stylesheet" href="css/katalog.css" />
</head>
<body>
  <div class="bg-decor">
    <div class="circle circle-amber"></div>
    <div class="circle circle-red"></div>
    <div class="circle circle-cyan"></div>
  </div>

  <?php include __DIR__ . '/php/partials/header.php'; ?>

  <main class="catalog-main">
    <div class="container">
      <div class="catalog-top">
        <h2>Каталог товаров</h2>
        <p>Выберите категорию или воспользуйтесь поиском</p>
      </div>

      <form method="get" class="catalog-search-form" id="searchForm">
        <input type="hidden" name="cat" id="hiddenCat" value="<?= e($currentCat) ?>" />
        <div class="search-wrapper">
          <input
            type="text"
            name="q"
            id="searchInput"
            class="search-input"
            placeholder="Найти товар…"
            value="<?= e($searchQuery) ?>"
            autocomplete="off"
          />
          <button type="submit" class="search-btn" aria-label="Искать">Найти</button>
        </div>
      </form>

      <div class="catalog-layout">
        <aside class="filter-panel open" id="filterPanel">
          <h3>Категории</h3>
          <div class="category-list" id="categoryList">
            <a href="katalog.php" class="category-btn <?= $currentCat === 'all' ? 'active' : '' ?>">
              <span class="emoji">🛒</span>
              <span>Все товары</span>
            </a>
            <?php foreach ($categories as $cat): ?>
              <a href="katalog.php?cat=<?= e($cat['slug']) ?>"
                 class="category-btn <?= $currentCat === $cat['slug'] ? 'active' : '' ?>">
                <span class="emoji"><?= e($cat['icon']) ?></span>
                <span><?= e($cat['name']) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </aside>

        <section class="catalog-grid-wrap">
          <div class="catalog-grid" id="productGrid">
            <?php if (empty($products)): ?>
              <div class="no-products" id="noProducts">
                <p>По вашему запросу ничего не найдено.</p>
              </div>
            <?php else: foreach ($products as $p): ?>
              <div class="product-card" data-product-id="<?= (int) $p['id'] ?>">
                <div class="product-image">
                  <img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>" />
                  <div class="badge-country"><?= e($p['country']) ?></div>
                  <div class="badge-rating">
                    <?= number_format((float) $p['rating'], 1, '.', '') ?>
                    <svg class="star-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round">
                      <polygon points="12 2 15 8.9 22 9.3 17 14 18.4 21 12 17.3 5.6 21 7 14 2 9.3 9 8.9 12 2" />
                    </svg>
                  </div>
                  <div class="product-overlay">
                    <button class="overlay-button" data-action="details">Подробнее</button>
                  </div>
                </div>
                <div class="product-info">
                  <h4><?= e($p['title']) ?></h4>
                  <p><?= e($p['description']) ?></p>
                </div>
                <div class="product-bottom">
                  <span class="product-price"><?= money((float) $p['price']) ?></span>
                  <button class="product-add" data-action="add" aria-label="Добавить в корзину">
                    <svg class="icon" viewBox="-5 -3 35 27" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="8" cy="21" r="1"></circle>
                      <circle cx="19" cy="21" r="1"></circle>
                      <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                    </svg>
                  </button>
                </div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </section>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/php/partials/footer.php'; ?>

  <!-- jQuery UI dialog для «Подробнее» -->
  <div id="productDialog" title="О товаре" style="display:none;">
    <p id="productDialogBody">…</p>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <script src="js/site.js"></script>
  <script src="js/katalog.js"></script>
</body>
</html>

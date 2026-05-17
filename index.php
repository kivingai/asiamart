<?php
$page = 'home';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/php/cart.php';

$cfg        = app_config();
$categories = get_categories();

// статистика для hero
$productsCount = (int) db()->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
$countriesCount = (int) db()->query('SELECT COUNT(DISTINCT country) FROM products')->fetchColumn();
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($cfg['name']) ?> — <?= e($cfg['tagline']) ?></title>
  <meta name="description" content="<?= e($cfg['name']) ?> — интернет-магазин азиатских продуктов: японская и корейская лапша, соусы, чаи, снеки. Доставка по всей России." />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/global.css" />
  <link rel="stylesheet" href="css/index.css" />
</head>
<body>
  <div class="bg-decor">
    <div class="circle circle-amber"></div>
    <div class="circle circle-red"></div>
    <div class="circle circle-cyan"></div>
  </div>

  <?php include __DIR__ . '/php/partials/header.php'; ?>

  <main>
    <section class="hero">
      <div class="container hero-grid">
        <div class="hero-text">
          <div class="badge">
            <svg class="icon small" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"></path>
            </svg>
            <span>Премиальные продукты из&nbsp;Азии</span>
          </div>
          <h2 class="hero-title">
            <span class="hero-title-main"><?= e($cfg['name']) ?></span>
            <span class="hero-title-sub">вкус Японии, Кореи, Китая и Тайланда</span>
          </h2>
          <p class="hero-desc">
            Мы тщательно отбираем лапшу, соусы, чаи и снеки у проверенных
            азиатских производителей и доставляем по всей России. Откройте
            каталог и соберите свою корзину гастрономических открытий.
          </p>
          <div class="hero-buttons">
            <a href="katalog.php" class="btn btn-cta">
              <span>Перейти в&nbsp;каталог</span>
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"></path>
                <path d="m12 5 7 7-7 7"></path>
              </svg>
            </a>
            <a href="info.php" class="btn btn-outline">О магазине</a>
          </div>
          <div class="stats">
            <div class="stat-item">
              <span class="stat-number"><?= $productsCount ?>+</span>
              <span class="stat-label">Товаров</span>
            </div>
            <div class="stat-item">
              <span class="stat-number"><?= $countriesCount ?></span>
              <span class="stat-label">Стран&nbsp;Азии</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">24/7</span>
              <span class="stat-label">Поддержка</span>
            </div>
          </div>
        </div>
        <div class="hero-image">
          <div class="image-wrapper">
            <img src="img/hero.png" alt="Asian food products" />
          </div>
        </div>
      </div>
    </section>

    <section class="features">
      <div class="container features-grid">
        <div class="feature-card">
          <div class="icon-circle gradient-blue">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
            </svg>
          </div>
          <h3>Оригинальные товары</h3>
          <p>Только сертифицированный импорт</p>
        </div>
        <div class="feature-card">
          <div class="icon-circle gradient-amber">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
              <path d="M15 18H9"></path>
              <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path>
              <circle cx="17" cy="18" r="2"></circle>
              <circle cx="7" cy="18" r="2"></circle>
            </svg>
          </div>
          <h3>Быстрая доставка</h3>
          <p>Курьером за&nbsp;1–2 дня или самовывоз</p>
        </div>
        <div class="feature-card">
          <div class="icon-circle gradient-red">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
              <circle cx="12" cy="8" r="6"></circle>
            </svg>
          </div>
          <h3>Лучшие производители</h3>
          <p>Бренды Японии, Кореи, Китая</p>
        </div>
        <div class="feature-card">
          <div class="icon-circle gradient-purple">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 6v6l4 2"></path>
              <circle cx="12" cy="12" r="10"></circle>
            </svg>
          </div>
          <h3>Поддержка&nbsp;24/7</h3>
          <p>Всегда на связи с вами</p>
        </div>
      </div>
    </section>

    <section class="categories">
      <div class="container">
        <div class="section-header">
          <h2>Популярные категории</h2>
          <p>Откройте для себя широкий ассортимент азиатских деликатесов</p>
        </div>
        <div class="categories-grid">
          <?php
          $gradients = ['gradient-red', 'gradient-amber', 'gradient-cyan', 'gradient-gray'];
          foreach ($categories as $i => $cat):
              $count = (int) db()->query("SELECT COUNT(*) FROM products WHERE category_id = " . (int) $cat['id'] . " AND is_active=1")->fetchColumn();
          ?>
            <a href="katalog.php?cat=<?= e($cat['slug']) ?>" class="category-card <?= $gradients[$i % count($gradients)] ?>">
              <div class="cat-image-wrapper">
                <img src="<?= e($cat['image']) ?>" alt="<?= e($cat['name']) ?>" />
                <span class="cat-count"><?= $count ?>+</span>
              </div>
              <div class="cat-info">
                <h3><?= e($cat['name']) ?></h3>
                <p>Лучшее из стран Азии</p>
              </div>
              <span class="cat-link">Смотреть
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M7 7h10v10"></path>
                  <path d="M7 17 17 7"></path>
                </svg>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/php/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <script src="js/site.js"></script>
</body>
</html>

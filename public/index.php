<?php
require_once __DIR__ . '/../includes/helpers.php';

$page_title       = 'AsiaMart — Премиальные азиатские продукты';
$page_description = 'AsiaMart — интернет-магазин премиальных азиатских продуктов. Японский рамен, удон, саке, матча, корейский гочуджан, моти, Pocky и многое другое.';
$current_page     = 'index';
$page_class       = 'home-ryokan';
$page_css         = '';

$pdo = db();

// Категории + счётчики
$cats = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS cnt
    FROM categories c ORDER BY c.sort_order, c.id
")->fetchAll();

$cat_jp = [
    'noodles' => '麺',
    'drinks'  => '飲',
    'snacks'  => '菓',
    'sauces'  => '醤',
];

// Хиты продаж: 4 featured-товара
$hits = $pdo->query("
    SELECT p.*, c.slug AS cat_slug, c.name AS cat_name
    FROM products p
    JOIN categories c ON c.id = p.category_id
    WHERE p.is_active = 1
    ORDER BY p.is_featured DESC, p.rating DESC
    LIMIT 4
")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<!-- ════════════════════════════════════════════════════
     HERO
     ════════════════════════════════════════════════════ -->
<section class="hero" data-bg="/assets/img/banners/hero_main.jpg">
  <div class="hero-overlay" aria-hidden="true"></div>
  <div class="container hero-inner">
    <div class="hero-text">
      <div class="eyebrow eyebrow-light">
        <span class="eyebrow-dot"></span>
        <span>新作 · NEW SEASON · 2026</span>
      </div>
      <h1 class="hero-title">
        Вкус Азии<br>
        <em>в каждой</em><br>
        кладовке.
      </h1>
      <p class="hero-lead">
        Премиальные продукты из Японии, Кореи, Китая, Вьетнама и Таиланда —
        для тех, кто готовит дома как в любимом ресторане.
      </p>
      <div class="hero-cta">
        <a href="/katalog.php" class="btn-primary">
          <span>Открыть каталог</span>
          <span class="btn-arrow" aria-hidden="true">→</span>
        </a>
        <a href="/about.php" class="btn-ghost btn-ghost-light">Наша история</a>
      </div>
    </div>
  </div>

  <div class="hero-stats-strip">
    <div class="container hero-stats-grid">
      <div class="stat">
        <span class="stat-num">5</span>
        <span class="stat-lbl">стран · <span class="jp">国</span></span>
      </div>
      <div class="stat">
        <span class="stat-num">500<span class="plus">+</span></span>
        <span class="stat-lbl">товаров · <span class="jp">商品</span></span>
      </div>
      <div class="stat">
        <span class="stat-num">24<span class="plus">ч</span></span>
        <span class="stat-lbl">доставка · <span class="jp">配送</span></span>
      </div>
      <div class="stat hero-hanko-stat">
        <span class="hanko-mark">朱<br>印</span>
        <span class="stat-lbl">премиум · <span class="jp">特選</span></span>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════
     №01 — КАТЕГОРИИ (editorial rows)
     ════════════════════════════════════════════════════ -->
<div class="section-divider">
  <span class="line"></span>
  <span class="num">№01 <span class="jp">部</span> Категории</span>
  <span class="line"></span>
</div>

<section class="categories-strip">
  <div class="container">
    <div class="cat-section-header">
      <h2>
        Четыре направления, <em>одна кухня.</em>
      </h2>
      <div class="meta">
        От японского рамена до корейского кимчи: мы привозим продукты напрямую от&nbsp;производителей и&nbsp;храним их&nbsp;при&nbsp;правильной температуре.
      </div>
    </div>

    <div class="cat-rows">
      <?php foreach ($cats as $i => $c): $num = sprintf('0%d', $i + 1); ?>
        <a href="/katalog.php?cat=<?= e($c['slug']) ?>" class="cat-row">
          <div class="cat-num">№<?= $num ?></div>
          <div class="cat-photo">
            <img src="<?= e($c['image']) ?>" alt="<?= e($c['name']) ?>" decoding="async">
          </div>
          <div class="cat-content">
            <div class="cat-name">
              <?= e($c['name']) ?>
              <?php if (isset($cat_jp[$c['slug']])): ?>
                <span class="jp"><?= $cat_jp[$c['slug']] ?></span>
              <?php endif; ?>
            </div>
            <div class="cat-desc"><?= e($c['description']) ?></div>
          </div>
          <div class="cat-arrow">
            <span class="cat-count"><?= (int)$c['cnt'] ?> товаров</span>
            <span>смотреть →</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════
     EDITORIAL QUOTE
     ════════════════════════════════════════════════════ -->
<section class="editorial-quote">
  <div class="container">
    <blockquote>
      Тысячи рецептов начинаются с&nbsp;<em>одного ингредиента</em>.<br>
      Мы привозим эти ингредиенты прямо из&nbsp;их&nbsp;родной земли.
    </blockquote>
    <cite>— Команда AsiaMart · Москва, 2026</cite>
  </div>
</section>

<!-- ════════════════════════════════════════════════════
     №02 — ХИТЫ ПРОДАЖ
     ════════════════════════════════════════════════════ -->
<div class="section-divider">
  <span class="line"></span>
  <span class="num">№02 <span class="jp">人気</span> Хиты сезона</span>
  <span class="line"></span>
</div>

<section class="featured-section">
  <div class="container">
    <div class="section-head">
      <h2>То, что покупают <em>чаще всего.</em></h2>
      <a href="/katalog.php" class="view-all">смотреть весь каталог →</a>
    </div>

    <div class="featured-grid">
      <?php foreach ($hits as $idx => $p):
        $hasSale = !empty($p['old_price']) && $p['old_price'] > $p['price'];
        $stamps = ['新着', 'ベスト', '人気', '限定'];
      ?>
        <article class="pp-card">
          <a href="/product.php?id=<?= (int)$p['id'] ?>" class="pp-img">
            <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" decoding="async">
            <span class="pp-stamp"><?= $stamps[$idx % count($stamps)] ?></span>
          </a>
          <div class="pp-body">
            <span class="pp-cat"><?= e($p['cat_name']) ?></span>
            <a href="/product.php?id=<?= (int)$p['id'] ?>" style="text-decoration:none;color:inherit">
              <h3 class="pp-name"><?= e($p['name']) ?></h3>
            </a>
            <div class="pp-price-row">
              <div class="pp-price">
                <?php if ($hasSale): ?><s><?= price((float)$p['old_price']) ?></s><?php endif; ?>
                <?= price((float)$p['price']) ?>
              </div>
              <button type="button" class="pp-buy" data-product-id="<?= (int)$p['id'] ?>" aria-label="В корзину">+</button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════
     №03 — PROMISES
     ════════════════════════════════════════════════════ -->
<section class="promises">
  <div class="container">
    <div class="eyebrow" style="margin-bottom:18px"><span class="dot"></span>№03 · 約束 · что мы обещаем</div>
    <h2>Четыре <em>принципа</em>, на которых стоит магазин.</h2>

    <div class="promises-list">
      <div class="promise-row">
        <div class="promise-num">№01</div>
        <div class="promise-title">Прямые контракты</div>
        <div class="promise-desc">Работаем с&nbsp;Kikkoman, Nissin, Meiji, Pocky без&nbsp;посредников. Никаких подделок и&nbsp;серого&nbsp;импорта.</div>
      </div>
      <div class="promise-row">
        <div class="promise-num">№02</div>
        <div class="promise-title">Курьер 24&nbsp;часа</div>
        <div class="promise-desc">По Москве и&nbsp;Санкт-Петербургу — день в&nbsp;день. По&nbsp;всей России — через СДЭК и&nbsp;Boxberry.</div>
      </div>
      <div class="promise-row">
        <div class="promise-num">№03</div>
        <div class="promise-title">Свежесть и&nbsp;холод</div>
        <div class="promise-desc">Соусы, паста мисо, моти&nbsp;— только в&nbsp;термокурьере. Сроки годности всегда видны на&nbsp;карточке.</div>
      </div>
      <div class="promise-row">
        <div class="promise-num">№04</div>
        <div class="promise-title">Честные цены</div>
        <div class="promise-desc">Регулярные скидки до&nbsp;30%, бесплатная доставка от&nbsp;5000&nbsp;₽, программа лояльности с&nbsp;первой&nbsp;покупки.</div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════
     DARK CTA
     ════════════════════════════════════════════════════ -->
<section class="dark-cta">
  <div class="container">
    <div class="eyebrow" style="margin-bottom:18px;color:rgba(251,246,236,0.7)">
      <span class="dot"></span>
      <span>始まる · НАЧНИТЕ СЕЙЧАС</span>
    </div>
    <h2>
      Соберите свою <em>азиатскую</em> кладовку.
    </h2>
    <p>
      500+&nbsp;товаров, 4&nbsp;категории, 5&nbsp;стран — всё в&nbsp;одной корзине. Бесплатная доставка от&nbsp;5000&nbsp;₽.
    </p>
    <a href="/katalog.php" class="btn-primary">
      Открыть каталог
      <span class="arrow">→</span>
    </a>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<script>
  // Кнопка «+» добавляет в серверную корзину (использует /api/cart.php)
  document.querySelectorAll('.pp-buy').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const id = btn.dataset.productId;
      const original = btn.textContent;
      btn.textContent = '…';
      try {
        if (window.CartStore && typeof window.CartStore.add === 'function') {
          const ok = await window.CartStore.add(Number(id), 1);
          btn.textContent = ok ? '✓' : '!';
          setTimeout(() => btn.textContent = original, 1200);
          return;
        }

        const res = await fetch('/api/cart.php', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({action:'add', product_id: Number(id), qty: 1})
        });
        const data = await res.json();
        if (data.ok) {
          btn.textContent = '✓';
          if (window.CartStore && typeof window.CartStore.updateBadge === 'function') {
            window.CartStore.updateBadge(data.count);
          }
          setTimeout(() => btn.textContent = original, 1200);
        } else {
          btn.textContent = '!';
          setTimeout(() => btn.textContent = original, 1200);
        }
      } catch (err) {
        btn.textContent = '!';
        setTimeout(() => btn.textContent = original, 1200);
      }
    });
  });
</script>

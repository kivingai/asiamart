<?php
require_once __DIR__ . '/../includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare(
    'SELECT p.*, c.slug AS cat_slug, c.name AS cat_name
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1'
);
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    http_response_code(404);
    $page_title = 'Товар не найден · AsiaMart';
    $current_page = 'katalog';
    require __DIR__ . '/../includes/header.php'; ?>
    <div class="container" style="padding: 96px 0; text-align: center;">
      <h1 style="font-family: 'Fraunces', serif; font-size: 48px; margin-bottom: 16px;">Товар не найден.</h1>
      <a href="/katalog.php" style="color: #c63f2a; font-family: 'DM Sans', sans-serif;">← Вернуться в каталог</a>
    </div>
    <?php require __DIR__ . '/../includes/footer.php';
    exit;
}

$kanji_map = ['noodles' => '麺', 'drinks' => '飲', 'snacks' => '菓', 'sauces' => '醤'];
$kanji = $kanji_map[$p['cat_slug']] ?? '';

$hasDiscount = !empty($p['old_price']) && $p['old_price'] > $p['price'];
$disc = $hasDiscount ? round((1 - $p['price'] / $p['old_price']) * 100) : 0;

// похожие — из той же категории
$relStmt = db()->prepare(
    'SELECT p.*, c.slug AS cat_slug, c.name AS cat_name
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1 AND p.category_id = ? AND p.id <> ?
     ORDER BY RAND() LIMIT 4'
);
$relStmt->execute([$p['category_id'], $p['id']]);
$related = $relStmt->fetchAll();

$page_title       = $p['name'] . ' — AsiaMart';
$page_description = mb_substr($p['short_desc'] ?? '', 0, 160);
$current_page     = 'katalog';
$page_class       = 'prod-ryokan';
require __DIR__ . '/../includes/header.php';
?>

<main class="prod-main">
  <div class="container">
    <nav class="prod-crumbs">
      <a href="/">Главная</a>
      <span class="sep">/</span>
      <a href="/katalog.php">Каталог</a>
      <?php if ($p['cat_slug']): ?>
        <span class="sep">/</span>
        <a href="/katalog.php?cat=<?= e($p['cat_slug']) ?>"><?= e($p['cat_name']) ?></a>
      <?php endif; ?>
      <span class="sep">/</span>
      <span class="here"><?= e($p['name']) ?></span>
    </nav>

    <a class="prod-back" href="/katalog.php<?= $p['cat_slug'] ? '?cat=' . urlencode($p['cat_slug']) : '' ?>">← Каталог</a>

    <section class="prod-hero">
      <div class="prod-photo-frame">
        <img src="<?= e($p['image'] ?: '/assets/img/placeholder.png') ?>" alt="<?= e($p['name']) ?>" decoding="async">
        <?php if ($hasDiscount): ?>
          <div class="hanko">−<?= $disc ?>%<br>SALE</div>
        <?php else: ?>
          <div class="hanko">朱<br>印</div>
        <?php endif; ?>
      </div>

      <div class="prod-details">
        <div class="prod-eyebrow">
          <span><?= e($p['cat_name']) ?></span>
          <span class="dot"></span>
          <?php if ($kanji): ?><span class="jp"><?= e($kanji) ?></span><?php endif; ?>
        </div>

        <h1 class="prod-title"><?= e($p['name']) ?></h1>

        <?php if (!empty($p['rating'])): ?>
          <div class="prod-rating">
            <?php $r = (float)$p['rating']; ?>
            <span class="stars" aria-hidden="true">
              <?php for ($i = 1; $i <= 5; $i++): echo $i <= $r ? '★' : '☆'; endfor; ?>
            </span>
            <span class="val"><?= number_format($r, 1, ',', '') ?></span>
            <span>— <?= (int)$p['reviews_count'] ?> отзывов</span>
          </div>
        <?php endif; ?>

        <?php if (!empty($p['short_desc'])): ?>
          <p class="prod-lead"><?= e($p['short_desc']) ?></p>
        <?php endif; ?>

        <div class="prod-price-row">
          <span class="prod-price"><?= price($p['price']) ?></span>
          <?php if ($hasDiscount): ?>
            <span class="prod-old"><?= price($p['old_price']) ?></span>
            <span class="prod-discount">−<?= $disc ?>%</span>
          <?php endif; ?>
        </div>

        <div class="prod-specs">
          <?php if (!empty($p['weight'])): ?>
          <div class="spec">
            <div class="spec-lbl">Вес</div>
            <div class="spec-val"><?= e($p['weight']) ?></div>
          </div>
          <?php endif; ?>
          <?php if (!empty($p['country'])): ?>
          <div class="spec">
            <div class="spec-lbl">Страна</div>
            <div class="spec-val"><?= e($p['country']) ?></div>
          </div>
          <?php endif; ?>
          <?php if (!empty($p['brand'])): ?>
          <div class="spec">
            <div class="spec-lbl">Бренд</div>
            <div class="spec-val"><?= e($p['brand']) ?></div>
          </div>
          <?php endif; ?>
          <div class="spec">
            <div class="spec-lbl">Наличие</div>
            <div class="spec-val" style="color: <?= $p['stock'] > 0 ? '#15803d' : '#b91c1c' ?>;">
              <?= $p['stock'] > 0 ? 'В наличии · ' . (int)$p['stock'] . ' шт' : 'Нет в наличии' ?>
            </div>
          </div>
        </div>

        <div class="prod-actions" data-product-id="<?= (int)$p['id'] ?>">
          <div class="prod-qty">
            <button type="button" onclick="(()=>{const v=this.parentElement.querySelector('.v'); v.textContent = Math.max(1, +v.textContent - 1);}).call(this)">−</button>
            <span class="v">1</span>
            <button type="button" onclick="(()=>{const v=this.parentElement.querySelector('.v'); v.textContent = +v.textContent + 1;}).call(this)">+</button>
          </div>
          <button class="btn-buy" type="button" onclick="
            const qty = +this.parentElement.querySelector('.v').textContent;
            window.CartStore && CartStore.add(<?= (int)$p['id'] ?>, qty);
          ">
            <span>В корзину</span>
            <span class="arrow">→</span>
          </button>
        </div>

        <div class="prod-meta-actions">
          <button class="meta-btn" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            В избранное
          </button>
          <button class="meta-btn" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            Поделиться
          </button>
        </div>
      </div>
    </section>

    <?php if (!empty($p['description'])): ?>
    <section class="prod-desc">
      <div class="section-divider prod-desc-divider">
        <span class="vermilion-line"></span>
        <span class="section-num">№02 · 説 · Описание</span>
        <span class="vermilion-line"></span>
      </div>
      <div class="prod-desc-text"><?= e($p['description']) ?></div>
    </section>
    <?php endif; ?>

    <?php if (!empty($related)): ?>
    <section class="prod-related">
      <div class="section-divider prod-related-head">
        <span class="vermilion-line"></span>
        <span class="section-num">№03 · 似 · Похожие товары</span>
        <span class="vermilion-line"></span>
      </div>

      <div class="cat-grid">
        <?php foreach ($related as $r):
          $rKanji = $kanji_map[$r['cat_slug']] ?? '';
          $rDisc  = !empty($r['old_price']) && $r['old_price'] > $r['price'];
        ?>
          <a class="rcard" href="/product.php?id=<?= (int)$r['id'] ?>">
            <div class="rcard-photo">
              <img src="<?= e($r['image'] ?: '/assets/img/placeholder.png') ?>" alt="<?= e($r['name']) ?>" decoding="async">
            </div>
            <div class="rcard-eyebrow">
              <span><?= e($r['cat_name']) ?></span>
              <?php if ($rKanji): ?><span class="jp"><?= e($rKanji) ?></span><?php endif; ?>
            </div>
            <h3 class="rcard-title"><?= e($r['name']) ?></h3>
            <div class="rcard-foot">
              <span class="rcard-price"><?= price($r['price']) ?></span>
              <?php if ($rDisc): ?>
                <span class="rcard-old"><?= price($r['old_price']) ?></span>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/helpers.php';

$cat_slug = trim($_GET['cat']  ?? '');
$query    = trim($_GET['q']    ?? '');
$sort     = $_GET['sort'] ?? 'pop';

// JP-каны для категорий и параллельные seq-номера
$kanji_map = ['noodles' => '麺', 'drinks' => '飲', 'snacks' => '菓', 'sauces' => '醤'];

$cats = db()->query('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id AND p.is_active=1) AS n FROM categories c ORDER BY c.sort_order, c.id')->fetchAll();

$where = ['p.is_active=1'];
$params = [];
$active_cat = null;
if ($cat_slug !== '') {
    foreach ($cats as $c) if ($c['slug'] === $cat_slug) $active_cat = $c;
    if ($active_cat) { $where[] = 'p.category_id = ?'; $params[] = $active_cat['id']; }
}
if ($query !== '') {
    $where[] = '(p.name LIKE ? OR p.short_desc LIKE ?)';
    $params[] = "%$query%"; $params[] = "%$query%";
}

$order = match ($sort) {
    'new'        => 'p.created_at DESC',
    'price-asc'  => 'p.price ASC',
    'price-desc' => 'p.price DESC',
    default      => 'p.is_featured DESC, p.id DESC',
};

$sql = 'SELECT p.*, c.slug AS cat_slug, c.name AS cat_name FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE ' . implode(' AND ', $where) . " ORDER BY $order";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$total = count($products);

$page_title       = ($active_cat ? $active_cat['name'] . ' — ' : '') . 'Каталог · AsiaMart';
$page_description = 'Каталог азиатских продуктов: ' . $total . ' позиций.';
$current_page     = 'katalog';
$page_class       = 'cat-ryokan';
require __DIR__ . '/../includes/header.php';
?>

<section class="cat-hero">
  <div class="container">
    <div class="cat-hero-inner">
      <div>
        <div class="cat-hero-eyebrow">
          <span class="dot"></span>
          <span>商品 · CATALOGUE · 2026</span>
        </div>
        <h1>
          <?php if ($active_cat): ?>
            <?= e($active_cat['name']) ?><?php if (isset($kanji_map[$active_cat['slug']])): ?><span class="jp"><?= e($kanji_map[$active_cat['slug']]) ?></span><?php endif; ?>
          <?php elseif ($query !== ''): ?>
            <em>«<?= e($query) ?>»</em>
          <?php else: ?>
            Каталог<span class="jp">部</span>
          <?php endif; ?>
        </h1>
      </div>
      <div class="cat-hero-count">
        <div class="num"><?= $total ?><em>.</em></div>
        <div class="lbl"><?= plural_items($total) ?> · 商品</div>
      </div>
    </div>
  </div>
</section>

<section class="cat-filter">
  <div class="container">
    <div class="cat-filter-inner">
      <form class="search" method="get" action="/katalog.php">
        <?php if ($cat_slug): ?><input type="hidden" name="cat" value="<?= e($cat_slug) ?>"><?php endif; ?>
        <?php if ($sort && $sort !== 'pop'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>
        <input type="search" name="q" placeholder="Поиск товаров…" value="<?= e($query) ?>" autocomplete="off">
      </form>

      <div class="cat-chips">
        <a class="cat-chip <?= $cat_slug === '' ? 'active' : '' ?>" href="/katalog.php<?= $query ? '?q=' . urlencode($query) : '' ?>">
          <span class="num">00</span>
          <span>Всё</span>
          <span class="count"><?= (int)db()->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn() ?></span>
        </a>
        <?php foreach ($cats as $i => $c): ?>
          <a class="cat-chip <?= $cat_slug === $c['slug'] ? 'active' : '' ?>" href="/katalog.php?cat=<?= e($c['slug']) ?><?= $query ? '&q=' . urlencode($query) : '' ?>">
            <span class="num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <span><?= e($c['name']) ?></span>
            <span class="count"><?= (int)$c['n'] ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="cat-sort">
        <label for="sort">Сортировка</label>
        <select id="sort" onchange="
          const u = new URL(location.href);
          u.searchParams.set('sort', this.value);
          location.href = u.toString();
        ">
          <option value="pop"        <?= $sort === 'pop'        ? 'selected' : '' ?>>Популярные</option>
          <option value="new"        <?= $sort === 'new'        ? 'selected' : '' ?>>Новинки</option>
          <option value="price-asc"  <?= $sort === 'price-asc'  ? 'selected' : '' ?>>Цена ↑</option>
          <option value="price-desc" <?= $sort === 'price-desc' ? 'selected' : '' ?>>Цена ↓</option>
        </select>
      </div>
    </div>
  </div>
</section>

<main class="cat-main">
  <div class="container">
    <?php if ($total === 0): ?>
      <div class="cat-empty">
        <div class="em">Ничего не нашли.</div>
        <p>Попробуйте другой запрос или категорию.</p>
      </div>
    <?php else: ?>
      <div class="cat-grid">
        <?php foreach ($products as $i => $p):
          $kanji = $kanji_map[$p['cat_slug']] ?? '';
          $hasDiscount = !empty($p['old_price']) && $p['old_price'] > $p['price'];
          $disc = $hasDiscount ? round((1 - $p['price'] / $p['old_price']) * 100) : 0;
        ?>
          <a class="rcard" href="/product.php?id=<?= (int)$p['id'] ?>">
            <div class="rcard-photo">
              <img src="<?= e($p['image'] ?: '/assets/img/placeholder.png') ?>" alt="<?= e($p['name']) ?>" decoding="async">
              <?php if ($hasDiscount): ?>
                <span class="rcard-sale">−<?= $disc ?>%</span>
              <?php endif; ?>
              <button class="rcard-add" type="button"
                      onclick="event.preventDefault(); event.stopPropagation(); window.CartStore && CartStore.add(<?= (int)$p['id'] ?>, 1);"
                      aria-label="Добавить в корзину">+</button>
            </div>
            <div class="rcard-eyebrow">
              <span><?= e($p['cat_name']) ?></span>
              <?php if ($kanji): ?><span class="jp"><?= e($kanji) ?></span><?php endif; ?>
            </div>
            <h3 class="rcard-title"><?= e($p['name']) ?></h3>
            <div class="rcard-foot">
              <span class="rcard-price"><?= price($p['price']) ?></span>
              <?php if ($hasDiscount): ?>
                <span class="rcard-old"><?= price($p['old_price']) ?></span>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

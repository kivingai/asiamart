<?php
require_once __DIR__ . '/../includes/helpers.php';

// === Actions ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid    = (int)($_POST['product_id'] ?? 0);
    if ($action === 'set' && $pid) {
        cart_set_qty($pid, (int)($_POST['qty'] ?? 0));
    } elseif ($action === 'remove' && $pid) {
        cart_remove($pid);
    } elseif ($action === 'clear') {
        [$where, $params] = cart_owner();
        db()->prepare("DELETE FROM cart_items WHERE $where")->execute($params);
        flash_set('Корзина очищена.', 'info');
    }
    header('Location: /cart.php');
    exit;
}

$items = cart_items_full();
$count = array_sum(array_column($items, 'quantity'));
$subtotal = 0;
foreach ($items as $it) $subtotal += $it['price'] * $it['quantity'];
$FREE_DELIVERY_FROM = 5000;
$delivery = $subtotal >= $FREE_DELIVERY_FROM ? 0 : ($subtotal > 0 ? 250 : 0);
$total = $subtotal + $delivery;
$to_free = max(0, $FREE_DELIVERY_FROM - $subtotal);

$page_title = 'Корзина — AsiaMart';
$page_description = 'Ваши товары в корзине AsiaMart.';
$page_class = 'cart-ryokan';
$current_page = 'cart';
require __DIR__ . '/../includes/header.php';
?>

<?php if (empty($items)): ?>

  <section class="cart-empty">
    <div class="container">
      <div class="stamp">朱</div>
      <h2>Корзина пуста</h2>
      <p>Здесь будут товары, которые вы добавите в корзину. Загляните в каталог — у нас рамен, саке, моти и ещё 500+ позиций из Азии.</p>
      <a href="/katalog.php" class="empty-cta">Открыть каталог →</a>
    </div>
  </section>

<?php else: ?>

  <section class="cart-hero">
    <div class="container">
      <div class="cart-hero-inner">
        <div>
          <div class="ry-eyebrow"><span class="dot"></span>買い物 · CART · 2026</div>
          <h1>Корзина<span class="kanji">買</span></h1>
        </div>
        <div class="cart-count">
          <div class="num"><?= (int)$count ?><span class="dot">.</span></div>
          <div class="lbl"><?= e(plural_items($count)) ?> · 商品</div>
        </div>
      </div>
    </div>
  </section>

  <section class="cart-main-wrap">
    <div class="container cart-main">

      <div class="cart-items-col">
        <div class="cart-section-eyebrow">
          <span class="num"><em>№01</em></span>
          <span>ВАШИ ТОВАРЫ · 商品</span>
          <span class="line"></span>
        </div>

        <ul class="cart-items">
          <?php foreach ($items as $it): ?>
            <li class="cart-row">
              <a href="/product.php?id=<?= (int)$it['id'] ?>" class="ci-img">
                <img src="<?= e($it['image'] ?: '/assets/img/placeholder.png') ?>" alt="<?= e($it['name']) ?>" decoding="async">
              </a>
              <div class="ci-info">
                <div class="ci-cat">
                  <?= e($it['cat_name'] ?? 'Категория') ?>
                  <?php
                    $kanji = ['noodles'=>'麺','drinks'=>'飲','snacks'=>'菓','sauces'=>'醤'][$it['cat_slug'] ?? ''] ?? '';
                  ?>
                  <?php if ($kanji): ?><span class="k"><?= $kanji ?></span><?php endif; ?>
                </div>
                <a href="/product.php?id=<?= (int)$it['id'] ?>" class="ci-title"><?= e($it['name']) ?></a>
                <div class="ci-unit"><?= price((float)$it['price']) ?> · шт.</div>
              </div>
              <form method="post" class="ci-qty" data-pid="<?= (int)$it['id'] ?>">
                <input type="hidden" name="action" value="set">
                <input type="hidden" name="product_id" value="<?= (int)$it['id'] ?>">
                <button type="submit" name="qty" value="<?= max(0, (int)$it['quantity']-1) ?>" aria-label="Уменьшить" <?= $it['quantity']<=1?'disabled':'' ?>>−</button>
                <span class="val"><?= (int)$it['quantity'] ?></span>
                <button type="submit" name="qty" value="<?= (int)$it['quantity']+1 ?>" aria-label="Увеличить">+</button>
              </form>
              <div class="ci-total"><?= price((float)$it['price'] * $it['quantity']) ?></div>
              <form method="post" onsubmit="return confirm('Удалить товар из корзины?')">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="product_id" value="<?= (int)$it['id'] ?>">
                <button type="submit" class="ci-remove" aria-label="Удалить">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14z"/>
                  </svg>
                </button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="cart-actions">
          <a href="/katalog.php" class="back-link">← Продолжить покупки</a>
          <form method="post" onsubmit="return confirm('Очистить корзину?')">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="clear-btn">Очистить корзину</button>
          </form>
        </div>
      </div>

      <aside class="cart-summary">
        <div class="sum-eyebrow"><span class="k">合計</span> К ОПЛАТЕ</div>

        <div class="sum-row">
          <span class="label"><?= e(plural_items($count)) ?> <?= (int)$count ?></span>
          <span class="val"><?= price($subtotal) ?></span>
        </div>
        <div class="sum-row <?= $delivery === 0 ? 'free' : '' ?>">
          <span class="label">Доставка</span>
          <span class="val"><?= $delivery === 0 ? 'бесплатно' : price($delivery) ?></span>
        </div>

        <?php if ($to_free > 0): ?>
          <p class="sum-hint">До бесплатной доставки осталось <?= price($to_free) ?></p>
        <?php endif; ?>

        <div class="sum-total">
          <span class="label">Итого</span>
          <span class="val"><?= price($total) ?></span>
        </div>

        <a href="/checkout.php" class="sum-checkout">Оформить заказ →</a>
        <p class="sum-note">Имитация оплаты — это дипломный проект. Никакие деньги не списываются.</p>
      </aside>

    </div>
  </section>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>

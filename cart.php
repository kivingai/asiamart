<?php
$page = 'cart';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/php/cart.php';

$cfg   = app_config();
$state = cart_state();
$items = $state['items'];
$total = (float) $state['total'];
$ship  = ($total > 0 && $total < (float) $cfg['free_ship']) ? (float) $cfg['delivery_fee'] : 0.0;
$grand = $total + $ship;
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Корзина — <?= e($cfg['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/global.css" />
  <link rel="stylesheet" href="css/cart.css" />
</head>
<body>
  <div class="bg-decor">
    <div class="circle circle-amber"></div>
    <div class="circle circle-red"></div>
    <div class="circle circle-cyan"></div>
  </div>

  <?php include __DIR__ . '/php/partials/header.php'; ?>

  <main class="cart-page">
    <div class="container">
      <h2 class="cart-title">Корзина</h2>

      <?php if (empty($items)): ?>
        <div class="cart-empty">
          <p>Корзина пуста. <a href="katalog.php">Перейти в каталог</a></p>
        </div>
      <?php else: ?>
        <div class="cart-grid">
          <section class="cart-items" id="cartItems">
            <?php foreach ($items as $item): ?>
              <div class="cart-item" data-product-id="<?= (int) $item['product_id'] ?>">
                <img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>" class="ci-img" />
                <div class="ci-info">
                  <h4><?= e($item['title']) ?></h4>
                  <p class="ci-meta"><?= e($item['country']) ?> · <?= e($item['category_name']) ?></p>
                  <p class="ci-price"><?= money((float) $item['price']) ?></p>
                </div>
                <div class="ci-qty">
                  <button class="qty-btn" data-action="dec">−</button>
                  <input type="number" class="qty-input" min="1" value="<?= (int) $item['qty'] ?>" />
                  <button class="qty-btn" data-action="inc">+</button>
                </div>
                <div class="ci-sum"><?= money((float) $item['price'] * (int) $item['qty']) ?></div>
                <button class="ci-remove" data-action="remove" aria-label="Удалить">×</button>
              </div>
            <?php endforeach; ?>
          </section>

          <aside class="cart-summary">
            <h3>Итог</h3>
            <div class="cart-line"><span>Товары (<?= (int) $state['count'] ?>)</span><span id="sumItems"><?= money($total) ?></span></div>
            <div class="cart-line">
              <span>Доставка</span>
              <span id="sumShip">
                <?php if ($ship > 0): ?>
                  <?= money($ship) ?>
                <?php else: ?>
                  Бесплатно
                <?php endif; ?>
              </span>
            </div>
            <?php if ($total < (float) $cfg['free_ship']): ?>
              <p class="cart-hint">До бесплатной доставки осталось <?= money((float) $cfg['free_ship'] - $total) ?></p>
            <?php endif; ?>
            <div class="cart-line cart-grand"><span>К&nbsp;оплате</span><span id="sumGrand"><?= money($grand) ?></span></div>
            <a href="check.php" class="btn btn-cta cart-checkout">Оформить заказ</a>
            <button class="btn btn-outline cart-clear" id="cartClearBtn">Очистить корзину</button>
          </aside>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php include __DIR__ . '/php/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <script src="js/site.js"></script>
  <script src="js/cart.js"></script>
</body>
</html>

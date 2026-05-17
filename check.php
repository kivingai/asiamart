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

$user  = current_user();
$error = flash_get('order_error');
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Оформление заказа — <?= e($cfg['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/global.css" />
  <link rel="stylesheet" href="css/check.css" />
</head>
<body>
  <div class="bg-decor">
    <div class="circle circle-amber"></div>
    <div class="circle circle-red"></div>
    <div class="circle circle-cyan"></div>
  </div>

  <?php include __DIR__ . '/php/partials/header.php'; ?>

  <main class="check-page">
    <div class="container">
      <h2 class="check-title">Оформление заказа</h2>

      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <?php if (empty($items)): ?>
        <div class="alert alert-info">Корзина пуста. <a href="katalog.php">Перейти в каталог</a></div>
      <?php elseif (!$user): ?>
        <div class="alert alert-warning">
          Для оформления заказа нужно войти в личный кабинет.
          <a href="Bxodandreg.php" class="btn btn-cta">Войти / Зарегистрироваться</a>
        </div>
      <?php else: ?>
        <form method="post" action="php/order_handler.php" class="check-form">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
          <div class="check-grid">
            <section class="check-section">
              <h3>Контактная информация</h3>
              <div class="form-group">
                <label>Имя</label>
                <input class="form-control" value="<?= e($user['name']) ?>" disabled />
              </div>
              <div class="form-group">
                <label>E-mail</label>
                <input class="form-control" value="<?= e($user['email']) ?>" disabled />
              </div>
              <div class="form-group">
                <label for="phone">Телефон</label>
                <input id="phone" class="form-control" name="phone" required value="<?= e($user['phone'] ?? '') ?>" placeholder="+7 (___) ___-__-__" />
              </div>
            </section>

            <section class="check-section">
              <h3>Доставка</h3>
              <div class="form-group">
                <label class="form-check">
                  <input type="radio" name="delivery" value="courier" checked /> Курьером
                </label>
                <label class="form-check">
                  <input type="radio" name="delivery" value="pickup" /> Самовывоз
                </label>
              </div>
              <div class="form-group" id="addressGroup">
                <label for="address">Адрес доставки</label>
                <textarea id="address" class="form-control" name="address" rows="2" placeholder="г. Чебоксары, ул. ..."></textarea>
              </div>
              <div class="form-group">
                <label for="note">Комментарий к заказу</label>
                <textarea id="note" class="form-control" name="note" rows="2"></textarea>
              </div>
            </section>

            <section class="check-section">
              <h3>Оплата</h3>
              <label class="form-check">
                <input type="radio" name="payment" value="card" checked /> Картой онлайн
              </label>
              <label class="form-check">
                <input type="radio" name="payment" value="cash" /> Наличными при получении
              </label>
            </section>

            <aside class="check-summary">
              <h3>Ваш заказ</h3>
              <ul class="check-items">
                <?php foreach ($items as $i): ?>
                  <li>
                    <span><?= e($i['title']) ?> × <?= (int) $i['qty'] ?></span>
                    <span><?= money((float) $i['price'] * (int) $i['qty']) ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
              <div class="cart-line"><span>Товары</span><span><?= money($total) ?></span></div>
              <div class="cart-line"><span>Доставка</span><span><?= $ship > 0 ? money($ship) : 'Бесплатно' ?></span></div>
              <div class="cart-line cart-grand"><span>Итого</span><span><?= money($grand) ?></span></div>
              <button type="submit" class="btn btn-cta">Подтвердить заказ</button>
            </aside>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </main>

  <?php include __DIR__ . '/php/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <script src="js/site.js"></script>
  <script>
    jQuery(function ($) {
      function toggleAddress() {
        var pickup = $('input[name=delivery]:checked').val() === 'pickup';
        $('#addressGroup').toggle(!pickup);
        $('#address').prop('required', !pickup);
      }
      $('input[name=delivery]').on('change', toggleAddress);
      toggleAddress();
    });
  </script>
</body>
</html>

<?php
$page = 'profile';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/php/cart.php';

require_login();

$cfg   = app_config();
$user  = current_user();
$ok    = flash_get('order_ok');

// история заказов пользователя
$stmt = db()->prepare(
    'SELECT id, status, total, address, delivery, payment, phone, note, created_at
     FROM orders WHERE user_id = ? ORDER BY id DESC'
);
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

// детали по позициям заказов одной выборкой
$orderItems = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare('SELECT * FROM order_items WHERE order_id IN (' . $placeholders . ') ORDER BY id');
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
        $orderItems[$row['order_id']][] = $row;
    }
}

$statusLabels = [
    'new'      => 'Новый',
    'paid'     => 'Оплачен',
    'shipping' => 'В доставке',
    'done'     => 'Выполнен',
    'canceled' => 'Отменён',
];
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Личный кабинет — <?= e($cfg['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/global.css" />
  <link rel="stylesheet" href="css/profile.css" />
</head>
<body>
  <div class="bg-decor">
    <div class="circle circle-amber"></div>
    <div class="circle circle-red"></div>
    <div class="circle circle-cyan"></div>
  </div>

  <?php include __DIR__ . '/php/partials/header.php'; ?>

  <main class="profile-page">
    <div class="container">
      <h2 class="profile-title">Личный кабинет</h2>
      <?php if ($ok): ?>
        <div class="alert alert-success"><?= e($ok) ?></div>
      <?php endif; ?>

      <div class="profile-grid">
        <aside class="profile-card">
          <div class="profile-avatar">
            <?= e(mb_substr($user['name'], 0, 1)) ?>
          </div>
          <h3><?= e($user['name']) ?></h3>
          <p><?= e($user['email']) ?></p>
          <?php if ($user['phone']): ?><p><?= e($user['phone']) ?></p><?php endif; ?>
          <p class="profile-role">Роль: <?= $user['role'] === 'admin' ? 'администратор' : 'пользователь' ?></p>
          <a href="php/logout.php" class="btn btn-outline">Выйти</a>
        </aside>

        <section class="profile-orders">
          <h3>История заказов</h3>
          <?php if (empty($orders)): ?>
            <p>У вас пока нет заказов. <a href="katalog.php">Перейти в каталог</a></p>
          <?php else: ?>
            <?php foreach ($orders as $o): ?>
              <article class="order-card">
                <header class="order-head">
                  <div>
                    <strong>Заказ №<?= (int) $o['id'] ?></strong>
                    <span class="order-date"><?= e($o['created_at']) ?></span>
                  </div>
                  <span class="order-status order-status-<?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span>
                </header>
                <ul class="order-items">
                  <?php foreach (($orderItems[$o['id']] ?? []) as $it): ?>
                    <li>
                      <span><?= e($it['title']) ?> × <?= (int) $it['qty'] ?></span>
                      <span><?= money((float) $it['price'] * (int) $it['qty']) ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
                <footer class="order-foot">
                  <span>Сумма: <strong><?= money((float) $o['total']) ?></strong></span>
                  <span><?= $o['delivery'] === 'pickup' ? 'Самовывоз' : 'Курьер' ?>, <?= $o['payment'] === 'cash' ? 'наличными' : 'картой' ?></span>
                </footer>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/php/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="js/site.js"></script>
</body>
</html>

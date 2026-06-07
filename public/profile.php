<?php
require_once __DIR__ . '/../includes/helpers.php';

$user = current_user();
if (!$user) {
    header('Location: /login.php?next=' . urlencode('/profile.php'));
    exit;
}

$tab = $_GET['tab'] ?? 'orders';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'profile') {
    csrf_check();
    $name  = trim($_POST['name']  ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name === '') $errors[] = 'Имя обязательно.';
    if (!$errors) {
        $stmt = db()->prepare('UPDATE users SET name=?, phone=? WHERE id=?');
        $stmt->execute([$name, $phone, $user['id']]);
        flash_set('Профиль сохранён.');
        header('Location: /profile.php?tab=info');
        exit;
    }
    $user['name']  = $name;
    $user['phone'] = $phone;
}

$orders = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$orders->execute([$user['id']]);
$orders = $orders->fetchAll();

$itemsMap = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $place = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id IN ($place)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
        $itemsMap[$row['order_id']][] = $row;
    }
}

$statusLabels = [
    'new'       => 'Новый',
    'paid'      => 'Оплачен',
    'shipped'   => 'Отправлен',
    'delivered' => 'Доставлен',
    'cancelled' => 'Отменён',
];

$page_title   = 'Личный кабинет — AsiaMart';
$current_page = 'profile';
$page_class   = 'profile-ryokan';
require __DIR__ . '/../includes/header.php';
?>

<section class="profile-main">
  <div class="container">

    <div class="pr-head">
      <div>
        <div class="ry-eyebrow"><span class="dot"></span>プロフィール · ACCOUNT · 2026</div>
        <h1 class="pr-title">Личный <em>кабинет</em><span class="kanji">部</span></h1>
      </div>
      <div class="pr-head-meta">
        <div class="label">Член клуба с</div>
        <div class="value"><?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></div>
      </div>
    </div>

    <div class="pr-grid">

      <aside class="pr-side">
        <div class="pr-avatar-card">
          <div class="pr-avatar"><?= e(mb_substr($user['name'] ?: 'A', 0, 1)) ?></div>
          <h3 class="pr-name"><?= e($user['name']) ?></h3>
          <p class="pr-mail"><?= e($user['email']) ?></p>
          <?php if ($user['role'] === 'admin'): ?>
            <span class="pr-role">★ Администратор</span>
          <?php else: ?>
            <span class="pr-role" style="background:var(--rg-ink);">Клиент</span>
          <?php endif; ?>
        </div>

        <ul class="pr-tabs">
          <li>
            <a href="/profile.php?tab=orders" class="<?= $tab==='orders' ? 'is-active' : '' ?>">
              <span>Мои заказы</span>
              <span class="kanji">注</span>
            </a>
          </li>
          <li>
            <a href="/profile.php?tab=info" class="<?= $tab==='info' ? 'is-active' : '' ?>">
              <span>Личные данные</span>
              <span class="kanji">情</span>
            </a>
          </li>
          <li>
            <a href="/logout.php">
              <span>Выйти</span>
              <span class="kanji">出</span>
            </a>
          </li>
        </ul>

        <?php if ($user['role'] === 'admin'): ?>
          <div class="pr-admin-card">
            <span class="kanji">部</span>
            <div style="font-family:'DM Sans',sans-serif;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:var(--rg-mute);">Управление</div>
            <a href="/admin/">Админ-панель →</a>
          </div>
        <?php endif; ?>
      </aside>

      <div class="pr-content">

        <?php if ($tab === 'orders'): ?>
          <div class="pr-section-head">
            <h2>История <em>заказов</em></h2>
            <span class="meta"><?= count($orders) ?> · 注</span>
          </div>

          <?php if (empty($orders)): ?>
            <div class="pr-empty">
              <div class="kanji-big">空</div>
              <h3>Пока пусто.</h3>
              <p>Здесь будут все ваши заказы, когда вы их сделаете.</p>
              <a href="/katalog.php" class="pr-save" style="display:inline-block;text-decoration:none;">Открыть каталог →</a>
            </div>
          <?php else: ?>
            <div class="pr-orders">
              <?php foreach ($orders as $o): $items = $itemsMap[$o['id']] ?? []; ?>
                <article class="pr-order">
                  <header class="pr-order-head">
                    <div class="pr-order-num">
                      Заказ
                      <strong><?= e($o['order_number']) ?></strong>
                    </div>
                    <div class="pr-order-date">
                      <?= date('d.m.Y · H:i', strtotime($o['created_at'])) ?>
                    </div>
                    <span class="pr-status <?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span>
                    <div class="pr-order-total"><?= price((float)$o['total']) ?></div>
                  </header>
                  <div class="pr-order-body">
                    <div class="pr-order-items">
                      <?php foreach ($items as $it): ?>
                        <div class="pr-thumb">
                          <img src="<?= e($it['image'] ?: '/assets/img/placeholder.png') ?>" alt="<?= e($it['product_name']) ?>" decoding="async">
                          <?php if ((int)$it['quantity'] > 1): ?>
                            <span class="qty">×<?= (int)$it['quantity'] ?></span>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <div class="pr-order-meta">
                      <span><strong>Доставка</strong> <?= e($o['city']) ?>, <?= e($o['address']) ?></span>
                      <span><strong>Оплата</strong>
                        <?php
                          $pm = $o['payment_method'];
                          echo $pm === 'card' ? 'Картой онлайн' : ($pm === 'sbp' ? 'СБП' : 'При получении');
                        ?>
                      </span>
                      <span><strong>Позиций</strong> <?= count($items) ?></span>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        <?php else: /* === info tab === */ ?>
          <div class="pr-section-head">
            <h2>Личные <em>данные</em></h2>
            <span class="meta">情 · PROFILE</span>
          </div>

          <?php if ($errors): ?>
            <div style="background:rgba(198,63,42,0.06);border:1px solid var(--rg-vermilion);border-radius:14px;padding:14px 18px;margin-bottom:24px;color:var(--rg-vermilion);font-family:'DM Sans',sans-serif;font-size:13px;">
              <?php foreach ($errors as $err) echo '<div>'.e($err).'</div>'; ?>
            </div>
          <?php endif; ?>

          <form method="post" class="pr-form">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="op" value="profile">

            <div class="pr-field">
              <label for="name">Имя</label>
              <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="pr-field">
              <label for="email">Email</label>
              <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>
            </div>
            <div class="pr-field">
              <label for="phone">Телефон</label>
              <input type="tel" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+7 999 123-45-67">
            </div>

            <button type="submit" class="pr-save">Сохранить →</button>
          </form>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

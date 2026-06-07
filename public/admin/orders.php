<?php
require_once __DIR__ . '/_layout.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['op'] ?? '') === 'status') {
        $id = (int)$_POST['id'];
        $status = $_POST['status'];
        if (in_array($status, ['new','paid','shipped','delivered','cancelled'])) {
            $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$status, $id]);
            flash_set('Статус обновлён.');
        }
        header('Location: /admin/orders.php' . (isset($_GET['id']) ? '?id='.$id : ''));
        exit;
    }
}

$statusLabels = ['new'=>'Новый','paid'=>'Оплачен','shipped'=>'Отправлен','delivered'=>'Доставлен','cancelled'=>'Отменён'];
$paymentLabels = ['card'=>'Карта','sbp'=>'СБП','cash'=>'Наличные'];

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare('SELECT o.*, u.email AS user_email FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id=?');
    $stmt->execute([$id]);
    $o = $stmt->fetch();
    if (!$o) {
        http_response_code(404);
        admin_header('orders', 'Заказ не найден');
        echo '<p style="font-family:DM Sans,sans-serif;">Заказ не найден.</p>';
        admin_footer();
        exit;
    }
    $items = $pdo->prepare('SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?');
    $items->execute([$id]);
    $items = $items->fetchAll();

    admin_header('orders', "Заказ {$o['order_number']}");
    ?>

    <div class="admin-page-head">
      <div>
        <div class="admin-eyebrow"><span class="dot"></span>注 · ORDER · <?= date('d.m.Y', strtotime($o['created_at'])) ?></div>
        <h1 class="admin-page-title">
          <em><?= e($o['order_number']) ?></em><span class="kanji">注</span>
        </h1>
      </div>
      <div class="admin-page-actions">
        <a href="/admin/orders.php" class="admin-btn admin-btn-ghost">← Все заказы</a>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1.7fr 1fr;gap:24px;align-items:flex-start;">

      <section class="admin-section">
        <header class="admin-section-head">
          <h3>Товары<span class="kanji">商</span></h3>
          <span class="pill <?= e($o['status']) ?>"><?= e($statusLabels[$o['status']]) ?></span>
        </header>

        <table class="admin-table">
          <thead>
            <tr>
              <th></th>
              <th>Название</th>
              <th>Цена</th>
              <th>Кол-во</th>
              <th>Сумма</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td style="width:60px;">
                <div class="name-thumb"><img src="<?= e($it['image'] ?: '/assets/img/placeholder.png') ?>" alt=""></div>
              </td>
              <td>
                <div class="name-title"><?= e($it['product_name']) ?></div>
              </td>
              <td><span class="price"><?= price((float)$it['price']) ?></span></td>
              <td><?= $it['quantity'] ?> шт</td>
              <td><span class="price"><?= price($it['price']*$it['quantity']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <div style="display:flex;justify-content:flex-end;padding:20px 14px 6px;border-top:1px solid var(--rg-line);margin-top:6px;">
          <div style="text-align:right;font-family:'DM Sans',sans-serif;">
            <div style="color:var(--rg-mute);font-size:13px;">Товары: <span style="color:var(--rg-ink);"><?= price((float)$o['subtotal']) ?></span></div>
            <div style="color:var(--rg-mute);font-size:13px;margin-top:4px;">Доставка: <span style="color:var(--rg-ink);"><?= $o['delivery']>0 ? price((float)$o['delivery']) : 'бесплатно' ?></span></div>
            <div style="font-family:'Fraunces',serif;font-size:28px;margin-top:12px;color:var(--rg-ink);">
              Итого <strong><?= price((float)$o['total']) ?></strong>
            </div>
          </div>
        </div>
      </section>

      <aside style="display:flex;flex-direction:column;gap:20px;position:sticky;top:24px;">
        <section class="admin-section">
          <header class="admin-section-head">
            <h3>Клиент<span class="kanji">客</span></h3>
            <span class="meta"><?= e($paymentLabels[$o['payment_method']] ?? $o['payment_method']) ?></span>
          </header>
          <div style="font-family:'DM Sans',sans-serif;font-size:14px;line-height:1.7;">
            <div style="font-family:'Fraunces',serif;font-size:18px;color:var(--rg-ink);"><?= e($o['customer_name']) ?></div>
            <div style="color:var(--rg-mute);"><?= e($o['customer_email']) ?></div>
            <div style="color:var(--rg-mute);"><?= e($o['customer_phone']) ?></div>
            <?php if ($o['user_email']): ?>
              <div style="margin-top:10px;font-size:11px;letter-spacing:0.16em;text-transform:uppercase;color:var(--rg-mute);">аккаунт</div>
              <div style="font-size:13px;color:var(--rg-ink-2);"><?= e($o['user_email']) ?></div>
            <?php endif; ?>
          </div>
        </section>

        <section class="admin-section">
          <header class="admin-section-head">
            <h3>Доставка<span class="kanji">配</span></h3>
          </header>
          <div style="font-family:'DM Sans',sans-serif;font-size:14px;line-height:1.7;color:var(--rg-ink-2);">
            <div style="font-family:'Fraunces',serif;font-size:18px;color:var(--rg-ink);"><?= e($o['city']) ?></div>
            <div><?= e($o['address']) ?></div>
            <?php if($o['comment']): ?>
              <div style="margin-top:14px;padding:12px 14px;background:var(--rg-cream);border-radius:10px;font-size:13px;border-left:3px solid var(--rg-vermilion);">
                <div style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--rg-mute);margin-bottom:4px;">Комментарий</div>
                <?= e($o['comment']) ?>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <section class="admin-section">
          <header class="admin-section-head">
            <h3>Статус<span class="kanji">状</span></h3>
          </header>
          <form method="post" class="admin-form">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="op" value="status">
            <input type="hidden" name="id" value="<?= $o['id'] ?>">
            <div class="field">
              <label>Изменить статус заказа</label>
              <select name="status">
                <?php foreach ($statusLabels as $k=>$v): ?>
                  <option value="<?= $k ?>" <?= $k===$o['status']?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button class="admin-btn" type="submit">Сохранить →</button>
          </form>
        </section>
      </aside>
    </div>

    <?php
    admin_footer(); exit;
}

// === список заказов ===
$status_filter = $_GET['status'] ?? '';
$sql = 'SELECT o.*, u.email AS user_email FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE 1=1';
$params = [];
if (in_array($status_filter, ['new','paid','shipped','delivered','cancelled'])) {
    $sql .= ' AND o.status=?';
    $params[] = $status_filter;
}
$sql .= ' ORDER BY o.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

admin_header('orders', 'Заказы');
?>

<div class="admin-page-head">
  <div>
    <div class="admin-eyebrow"><span class="dot"></span>注 · ORDERS · <?= count($orders) ?></div>
    <h1 class="admin-page-title">Управление <em>заказами</em><span class="kanji">注</span></h1>
  </div>
</div>

<form method="get" class="admin-filter">
  <a href="?" class="chip <?= $status_filter===''?'is-active':'' ?>">Все</a>
  <?php foreach (['new'=>'Новые','paid'=>'Оплачены','shipped'=>'Отправлены','delivered'=>'Доставлены','cancelled'=>'Отменены'] as $k=>$v): ?>
    <a href="?status=<?= $k ?>" class="chip <?= $status_filter===$k?'is-active':'' ?>"><?= $v ?></a>
  <?php endforeach; ?>
</form>

<div class="admin-section">
  <?php if (!$orders): ?>
    <p style="color:var(--rg-mute);text-align:center;padding:32px;font-family:'DM Sans',sans-serif;">Заказы не найдены.</p>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>№ Заказа</th>
          <th>Дата</th>
          <th>Клиент</th>
          <th>Город</th>
          <th>Сумма</th>
          <th>Оплата</th>
          <th>Статус</th>
          <th style="text-align:right;"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><span style="font-family:'Fraunces',serif;font-style:italic;font-size:15px;color:var(--rg-ink);"><?= e($o['order_number']) ?></span></td>
          <td style="color:var(--rg-mute);font-size:12px;"><?= date('d.m.Y · H:i', strtotime($o['created_at'])) ?></td>
          <td><?= e($o['customer_name']) ?></td>
          <td><?= e($o['city']) ?></td>
          <td><span class="price"><?= price((float)$o['total']) ?></span></td>
          <td style="font-size:12px;color:var(--rg-mute);"><?= e($paymentLabels[$o['payment_method']] ?? $o['payment_method']) ?></td>
          <td><span class="pill <?= e($o['status']) ?>"><?= e($statusLabels[$o['status']]) ?></span></td>
          <td>
            <div class="row-actions">
              <a href="?id=<?= $o['id'] ?>" class="icon-act" title="Открыть">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>

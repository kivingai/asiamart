<?php
require_once __DIR__ . '/_layout.php';

$pdo = db();

$stats = [
    'orders_total'   => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'orders_new'     => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='new'")->fetchColumn(),
    'revenue'        => (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status IN ('paid','shipped','delivered')")->fetchColumn(),
    'avg_check'      => (float)$pdo->query("SELECT COALESCE(AVG(total),0) FROM orders WHERE status<>'cancelled'")->fetchColumn(),
    'products_total' => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn(),
    'out_of_stock'   => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock=0 AND is_active=1")->fetchColumn(),
    'users_total'    => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'users_new'      => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn(),
];

$recent_orders = $pdo->query(
    "SELECT o.*, u.email AS user_email FROM orders o LEFT JOIN users u ON u.id = o.user_id ORDER BY o.id DESC LIMIT 8"
)->fetchAll();

$top_products = $pdo->query(
    "SELECT p.id, p.name, p.image, p.price, SUM(oi.quantity) AS sold
       FROM order_items oi
       JOIN products p ON p.id = oi.product_id
      GROUP BY p.id
      ORDER BY sold DESC
      LIMIT 5"
)->fetchAll();

$statusLabels = [
    'new'=>'Новый','paid'=>'Оплачен','shipped'=>'Отправлен','delivered'=>'Доставлен','cancelled'=>'Отменён'
];

admin_header('dashboard', 'Дашборд');
?>

<div class="admin-page-head">
  <div>
    <div class="admin-eyebrow"><span class="dot"></span>板 · DASHBOARD · 2026</div>
    <h1 class="admin-page-title">Обзор<em> магазина</em><span class="kanji">板</span></h1>
  </div>
  <div class="admin-page-actions">
    <a href="/admin/products.php?action=new" class="admin-btn">+ Добавить товар</a>
  </div>
</div>

<div class="stats-grid">
  <div class="stat-card" data-kanji="注">
    <div class="label">Всего заказов · 注</div>
    <div class="value"><?= $stats['orders_total'] ?></div>
    <?php if ($stats['orders_new']): ?>
      <div class="hint">● <?= $stats['orders_new'] ?> новых</div>
    <?php endif; ?>
  </div>
  <div class="stat-card" data-kanji="円">
    <div class="label">Выручка · 円</div>
    <div class="value"><?= number_format($stats['revenue'], 0, ',', ' ') ?><span class="currency">₽</span></div>
    <div class="hint" style="color:var(--rg-mute);">сред. чек: <?= price($stats['avg_check']) ?></div>
  </div>
  <div class="stat-card" data-kanji="商">
    <div class="label">Товары · 商</div>
    <div class="value"><?= $stats['products_total'] ?></div>
    <?php if ($stats['out_of_stock']): ?>
      <div class="hint">● <?= $stats['out_of_stock'] ?> нет в наличии</div>
    <?php else: ?>
      <div class="hint" style="color:#047857;">● все в наличии</div>
    <?php endif; ?>
  </div>
  <div class="stat-card" data-kanji="客">
    <div class="label">Клиенты · 客</div>
    <div class="value"><?= $stats['users_total'] ?></div>
    <?php if ($stats['users_new']): ?>
      <div class="hint">● +<?= $stats['users_new'] ?> за неделю</div>
    <?php endif; ?>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:24px;align-items:flex-start;">
  <section class="admin-section">
    <header class="admin-section-head">
      <h3>Последние заказы<span class="kanji">注</span></h3>
      <span class="meta"><a href="/admin/orders.php">смотреть все →</a></span>
    </header>

    <?php if (!$recent_orders): ?>
      <p style="color:var(--rg-mute);font-family:'DM Sans',sans-serif;">Заказов пока нет.</p>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Номер</th>
            <th>Клиент</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recent_orders as $o): ?>
          <tr>
            <td>
              <div style="font-family:'Fraunces',serif;font-style:italic;font-size:15px;">
                <?= e($o['order_number']) ?>
              </div>
              <div style="font-size:11px;color:var(--rg-mute);margin-top:2px;">
                <?= date('d.m · H:i', strtotime($o['created_at'])) ?>
              </div>
            </td>
            <td>
              <div><?= e($o['customer_name']) ?></div>
              <div style="font-size:11px;color:var(--rg-mute);"><?= e($o['user_email'] ?? $o['customer_email']) ?></div>
            </td>
            <td class="price"><?= price((float)$o['total']) ?></td>
            <td><span class="pill <?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span></td>
            <td class="row-actions">
              <a href="/admin/orders.php?id=<?= $o['id'] ?>" class="icon-act" title="Открыть">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <section class="admin-section">
    <header class="admin-section-head">
      <h3>Хиты продаж<span class="kanji">人</span></h3>
      <span class="meta">TOP 5</span>
    </header>

    <?php if (!$top_products): ?>
      <p style="color:var(--rg-mute);font-family:'DM Sans',sans-serif;">Пока нет продаж.</p>
    <?php else: ?>
      <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;">
        <?php foreach ($top_products as $i => $p): ?>
          <li style="display:flex;align-items:center;gap:14px;padding:14px 0;<?= $i < count($top_products)-1 ? 'border-bottom:1px solid var(--rg-line);' : '' ?>">
            <span style="font-family:'Fraunces',serif;font-style:italic;color:var(--rg-mute);font-size:14px;min-width:24px;">№<?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></span>
            <img src="<?= e($p['image'] ?: '/assets/img/placeholder.png') ?>" alt="" style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid var(--rg-line);">
            <div style="flex:1;min-width:0;">
              <div style="font-family:'Fraunces',serif;font-size:14px;color:var(--rg-ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($p['name']) ?></div>
              <div style="font-family:'DM Sans',sans-serif;font-size:11px;color:var(--rg-mute);"><?= price((float)$p['price']) ?></div>
            </div>
            <div style="background:rgba(198,63,42,0.06);color:var(--rg-vermilion);padding:5px 11px;border-radius:999px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:11px;letter-spacing:0.08em;">
              <?= (int)$p['sold'] ?> шт
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>

<?php admin_footer(); ?>

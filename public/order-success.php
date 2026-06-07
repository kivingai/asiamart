<?php
require_once __DIR__ . '/../includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order || (int)($_SESSION['last_order_id'] ?? 0) !== $id) {
    header('Location: /');
    exit;
}

$itemsStmt = db()->prepare(
    'SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?'
);
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

$page_title = "Заказ {$order['order_number']} оформлен — AsiaMart";
$page_css = 'check';
require __DIR__ . '/../includes/header.php';

$paymentLabels = ['card' => 'Картой онлайн', 'sbp' => 'СБП', 'cash' => 'Наличными'];
?>

<div class="checkout-main">
  <div class="container" style="max-width:760px">
    <div class="success-card">
      <div class="success-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 6L9 17l-5-5"/>
        </svg>
      </div>
      <h1 class="success-title">Заказ оформлен!</h1>
      <p class="success-sub">Спасибо за покупку. Мы отправили подтверждение на <strong><?= e($order['customer_email']) ?></strong>.</p>

      <div class="success-meta">
        <div class="meta-row">
          <span class="meta-label">Номер заказа</span>
          <span class="meta-value mono"><?= e($order['order_number']) ?></span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Дата</span>
          <span class="meta-value"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Способ оплаты</span>
          <span class="meta-value"><?= e($paymentLabels[$order['payment_method']] ?? $order['payment_method']) ?></span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Статус</span>
          <span class="meta-value status-paid">✓ Оплачен (имитация)</span>
        </div>
      </div>

      <div class="success-section">
        <h2>Доставка</h2>
        <p>
          <?= e($order['customer_name']) ?> · <?= e($order['customer_phone']) ?><br>
          <?= e($order['city']) ?>, <?= e($order['address']) ?>
        </p>
      </div>

      <div class="success-section">
        <h2>Состав заказа</h2>
        <div class="success-items">
          <?php foreach ($items as $it): ?>
            <div class="success-item">
              <?php if (!empty($it['image'])): ?>
                <img src="<?= e($it['image']) ?>" alt="" class="summary-thumb">
              <?php endif; ?>
              <div class="success-item-info">
                <div class="success-item-name"><?= e($it['product_name']) ?></div>
                <div class="success-item-qty"><?= (int)$it['quantity'] ?> × <?= price($it['price']) ?></div>
              </div>
              <div class="success-item-total"><?= price($it['price'] * $it['quantity']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="success-totals">
        <div class="summary-row"><span>Товары</span><span><?= price($order['subtotal']) ?></span></div>
        <div class="summary-row"><span>Доставка</span><span><?= $order['delivery'] > 0 ? price($order['delivery']) : 'Бесплатно' ?></span></div>
        <div class="summary-total"><span>Итого</span><span class="total-amount"><?= price($order['total']) ?></span></div>
      </div>

      <div class="success-actions">
        <a href="/" class="pay-btn">На главную</a>
        <a href="/katalog.php" class="continue-link" style="text-align:center">Продолжить покупки →</a>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

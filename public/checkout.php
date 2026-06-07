<?php
require_once __DIR__ . '/../includes/helpers.php';

$items = cart_items_full();
if (empty($items)) {
    header('Location: /cart.php');
    exit;
}

$subtotal = 0;
foreach ($items as $it) $subtotal += $it['price'] * $it['quantity'];
$delivery = $subtotal >= 5000 ? 0 : 250;
$total = $subtotal + $delivery;
$totalQty = array_sum(array_column($items, 'quantity'));

$u = current_user();
$errors = [];
$form = [
    'name'    => $u['name']  ?? '',
    'email'   => $u['email'] ?? '',
    'phone'   => '',
    'city'    => '',
    'address' => '',
    'comment' => '',
    'payment' => 'card',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($form as $k => $_) {
        $form[$k] = trim($_POST[$k] ?? $form[$k]);
    }
    if (mb_strlen($form['name']) < 2)             $errors['name']    = 'Укажите имя';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Неверный email';
    if (mb_strlen(preg_replace('/\D/', '', $form['phone'])) < 10) $errors['phone'] = 'Неверный телефон';
    if (mb_strlen($form['city']) < 2)             $errors['city']    = 'Укажите город';
    if (mb_strlen($form['address']) < 5)          $errors['address'] = 'Укажите адрес доставки';
    if (!in_array($form['payment'], ['card', 'cash', 'sbp'], true)) $errors['payment'] = 'Способ оплаты';

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $orderNumber = 'AM-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $stmt = $pdo->prepare(
                'INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone,
                                     city, address, comment, payment_method, status,
                                     subtotal, delivery, total, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([
                $orderNumber, $u['id'] ?? null,
                $form['name'], $form['email'], $form['phone'],
                $form['city'], $form['address'], $form['comment'],
                $form['payment'], 'paid',
                $subtotal, $delivery, $total
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $stmtItem = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
                 VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($items as $it) {
                $stmtItem->execute([
                    $orderId, $it['product_id'], $it['name'], $it['price'], $it['quantity']
                ]);
            }

            [$where, $params] = cart_owner();
            $pdo->prepare("DELETE FROM cart_items WHERE $where")->execute($params);

            $pdo->commit();

            $_SESSION['last_order_id'] = $orderId;
            header('Location: /order-success.php?id=' . $orderId);
            exit;
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $errors['_'] = 'Не удалось создать заказ: ' . $ex->getMessage();
        }
    }
}

$page_title = 'Оформление заказа — AsiaMart';
$page_class = 'checkout-ryokan';
$current_page = 'cart';
require __DIR__ . '/../includes/header.php';
?>

<section class="co-hero">
  <div class="container">
    <a href="/cart.php" class="co-back">← Вернуться в корзину</a>
    <div class="co-eyebrow"><span class="dot"></span>注文 · CHECKOUT · 2026</div>
    <h1>Оформление<span class="kanji">注</span></h1>
    <p class="co-lead">Несколько шагов до того, как премиум-вкус Азии окажется у вас дома.</p>

    <div class="co-notice">
      <span class="k">注</span>
      <span><strong>Дипломный проект.</strong> Это демонстрация полного цикла заказа. Никакие деньги не списываются — оплата имитируется.</span>
    </div>
  </div>
</section>

<section class="co-main-wrap">
  <div class="container co-main">

    <form method="post" class="co-form" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <?php if (!empty($errors['_'])): ?>
        <div class="co-notice" style="border-color:#c63f2a;background:#fef3eb;color:#a8311e;margin-bottom:24px">
          <span class="k">!</span><span><?= e($errors['_']) ?></span>
        </div>
      <?php endif; ?>

      <div class="co-section">
        <div class="co-section-title">
          <span class="num"><em>№01</em></span>
          Контакты
          <span class="k">連絡</span>
        </div>
        <p class="co-section-hint">Куда нам писать и звонить, если что-то понадобится уточнить.</p>
        <div class="co-fields">
          <div class="co-field <?= isset($errors['name']) ? 'has-err' : '' ?>">
            <label>Имя и фамилия</label>
            <input type="text" name="name" value="<?= e($form['name']) ?>" placeholder="Иван Петров" required>
            <?php if (isset($errors['name'])): ?><span class="err"><?= e($errors['name']) ?></span><?php endif; ?>
          </div>
          <div class="co-field <?= isset($errors['phone']) ? 'has-err' : '' ?>">
            <label>Телефон</label>
            <input type="tel" name="phone" value="<?= e($form['phone']) ?>" placeholder="+7 999 000-00-00" required>
            <?php if (isset($errors['phone'])): ?><span class="err"><?= e($errors['phone']) ?></span><?php endif; ?>
          </div>
          <div class="co-field full <?= isset($errors['email']) ? 'has-err' : '' ?>">
            <label>Email</label>
            <input type="email" name="email" value="<?= e($form['email']) ?>" placeholder="you@asiamart.ru" required>
            <?php if (isset($errors['email'])): ?><span class="err"><?= e($errors['email']) ?></span><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="co-section">
        <div class="co-section-title">
          <span class="num"><em>№02</em></span>
          Адрес доставки
          <span class="k">配送</span>
        </div>
        <p class="co-section-hint">Курьер привезёт заказ в течение 24 часов в Москве, 2–3 дня по России.</p>
        <div class="co-fields">
          <div class="co-field <?= isset($errors['city']) ? 'has-err' : '' ?>">
            <label>Город</label>
            <input type="text" name="city" value="<?= e($form['city']) ?>" placeholder="Москва" required>
            <?php if (isset($errors['city'])): ?><span class="err"><?= e($errors['city']) ?></span><?php endif; ?>
          </div>
          <div class="co-field">
            <label>Индекс <span style="color:#b8b3aa">— необязательно</span></label>
            <input type="text" name="zip" value="" placeholder="101000">
          </div>
          <div class="co-field full <?= isset($errors['address']) ? 'has-err' : '' ?>">
            <label>Улица, дом, квартира</label>
            <input type="text" name="address" value="<?= e($form['address']) ?>" placeholder="ул. Тверская, 12, кв. 45" required>
            <?php if (isset($errors['address'])): ?><span class="err"><?= e($errors['address']) ?></span><?php endif; ?>
          </div>
          <div class="co-field full">
            <label>Комментарий курьеру <span style="color:#b8b3aa">— необязательно</span></label>
            <textarea name="comment" placeholder="Код домофона, удобное время, пожелания…"><?= e($form['comment']) ?></textarea>
          </div>
        </div>
      </div>

      <div class="co-section">
        <div class="co-section-title">
          <span class="num"><em>№03</em></span>
          Способ оплаты
          <span class="k">支払</span>
        </div>
        <p class="co-section-hint">Выберите как удобнее — оплата при оформлении или при получении.</p>
        <div class="co-pay">
          <label>
            <input type="radio" name="payment" value="card" <?= $form['payment']==='card'?'checked':'' ?>>
            <span class="co-pay-icon">⊞</span>
            <span class="co-pay-content">
              <span class="co-pay-title">Картой онлайн</span>
              <span class="co-pay-sub">Visa, MC, МИР</span>
            </span>
          </label>
          <label>
            <input type="radio" name="payment" value="sbp" <?= $form['payment']==='sbp'?'checked':'' ?>>
            <span class="co-pay-icon">支</span>
            <span class="co-pay-content">
              <span class="co-pay-title">СБП</span>
              <span class="co-pay-sub">по QR-коду</span>
            </span>
          </label>
          <label>
            <input type="radio" name="payment" value="cash" <?= $form['payment']==='cash'?'checked':'' ?>>
            <span class="co-pay-icon">¥</span>
            <span class="co-pay-content">
              <span class="co-pay-title">Наличными</span>
              <span class="co-pay-sub">при получении</span>
            </span>
          </label>
        </div>
      </div>

      <div class="co-submit-wrap">
        <button type="submit" class="co-submit">
          Оплатить <?= price($total) ?> →
        </button>
        <p class="co-submit-note">Нажимая кнопку, вы соглашаетесь с условиями обработки персональных данных. Оплата имитируется.</p>
      </div>

    </form>

    <aside class="co-summary">
      <div class="sum-eyebrow"><span class="k">合計</span> ВАШ ЗАКАЗ</div>

      <ul class="co-items">
        <?php foreach ($items as $it): ?>
          <li class="co-item">
            <div class="co-item-img">
              <img src="<?= e($it['image'] ?: '/assets/img/placeholder.png') ?>" alt="" decoding="async">
              <?php if ($it['quantity'] > 1): ?><span class="qty"><?= (int)$it['quantity'] ?></span><?php endif; ?>
            </div>
            <div class="co-item-info">
              <div class="co-item-name"><?= e($it['name']) ?></div>
            </div>
            <div class="co-item-price"><?= price((float)$it['price'] * $it['quantity']) ?></div>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="co-totals">
        <div class="row">
          <span class="label"><?= e(plural_items($totalQty)) ?> <?= (int)$totalQty ?></span>
          <span class="val"><?= price($subtotal) ?></span>
        </div>
        <div class="row <?= $delivery === 0 ? 'free' : '' ?>">
          <span class="label">Доставка</span>
          <span class="val"><?= $delivery === 0 ? 'бесплатно' : price($delivery) ?></span>
        </div>
        <div class="row grand">
          <span class="label">К оплате</span>
          <span class="val"><?= price($total) ?></span>
        </div>
      </div>
    </aside>

  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<?php
// Обработчик оформления заказа.

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/cart.php';

session_init();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/check.php');
}
if (!csrf_check($_POST['csrf'] ?? null)) {
    flash_set('order_error', 'Ошибка проверки CSRF-токена. Обновите страницу.');
    redirect('/check.php');
}

$user     = current_user();
$state    = cart_state();
$items    = $state['items'];
$total    = (float) $state['total'];

if (empty($items)) {
    flash_set('order_error', 'Корзина пуста.');
    redirect('/cart.php');
}

$address  = trim((string) ($_POST['address'] ?? ''));
$phone    = trim((string) ($_POST['phone'] ?? $user['phone'] ?? ''));
$delivery = ($_POST['delivery'] ?? 'courier') === 'pickup' ? 'pickup' : 'courier';
$payment  = ($_POST['payment'] ?? 'card') === 'cash' ? 'cash' : 'card';
$note     = trim((string) ($_POST['note'] ?? ''));

if ($delivery === 'courier' && $address === '') {
    flash_set('order_error', 'Для курьерской доставки укажите адрес.');
    redirect('/check.php');
}
if ($phone === '') {
    flash_set('order_error', 'Укажите телефон для связи.');
    redirect('/check.php');
}

$cfg = app_config();
$ship = ($delivery === 'courier' && $total < (float) $cfg['free_ship']) ? (float) $cfg['delivery_fee'] : 0.0;
$grand = $total + $ship;

$pdo = db();
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        'INSERT INTO orders (user_id, status, total, address, delivery, payment, phone, note)
         VALUES (?, "new", ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $user['id'],
        $grand,
        $delivery === 'pickup' ? 'Самовывоз из магазина' : $address,
        $delivery,
        $payment,
        $phone,
        $note,
    ]);
    $orderId = (int) $pdo->lastInsertId();

    $stmtItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, title, qty, price) VALUES (?, ?, ?, ?, ?)'
    );
    foreach ($items as $item) {
        $stmtItem->execute([
            $orderId,
            $item['product_id'],
            $item['title'],
            $item['qty'],
            $item['price'],
        ]);
    }

    cart_clear();
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    flash_set('order_error', 'Не удалось оформить заказ: ' . $e->getMessage());
    redirect('/check.php');
}

flash_set('order_ok', 'Заказ №' . $orderId . ' успешно оформлен. Ожидайте подтверждение по телефону.');
redirect('/profile.php');

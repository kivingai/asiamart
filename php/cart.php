<?php
// Серверный обработчик корзины.
// AJAX-эндпоинт: /php/cart.php?action=add|update|remove|state|clear
// Корзина для незарегистрированных хранится в сессии,
// для авторизованных — в таблице cart_items.

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

session_init();

$action = $_GET['action'] ?? $_POST['action'] ?? 'state';

function session_cart_get(): array
{
    return $_SESSION['cart'] ?? [];
}

function session_cart_set(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

function cart_add(int $product_id, int $qty): void
{
    $qty = max(1, $qty);
    $user = current_user();
    if ($user) {
        $stmt = db()->prepare(
            'INSERT INTO cart_items (user_id, product_id, qty) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)'
        );
        $stmt->execute([$user['id'], $product_id, $qty]);
    } else {
        $cart = session_cart_get();
        $cart[$product_id] = ($cart[$product_id] ?? 0) + $qty;
        session_cart_set($cart);
    }
}

function cart_update(int $product_id, int $qty): void
{
    $user = current_user();
    if ($user) {
        if ($qty <= 0) {
            $stmt = db()->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$user['id'], $product_id]);
        } else {
            $stmt = db()->prepare(
                'INSERT INTO cart_items (user_id, product_id, qty) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE qty = VALUES(qty)'
            );
            $stmt->execute([$user['id'], $product_id, $qty]);
        }
    } else {
        $cart = session_cart_get();
        if ($qty <= 0) {
            unset($cart[$product_id]);
        } else {
            $cart[$product_id] = $qty;
        }
        session_cart_set($cart);
    }
}

function cart_remove(int $product_id): void
{
    cart_update($product_id, 0);
}

function cart_clear(): void
{
    $user = current_user();
    if ($user) {
        $stmt = db()->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->execute([$user['id']]);
    } else {
        $_SESSION['cart'] = [];
    }
}

function cart_items(): array
{
    $user = current_user();
    $items = [];
    if ($user) {
        $stmt = db()->prepare(
            'SELECT ci.product_id, ci.qty, p.title, p.price, p.image, p.country, c.name AS category_name
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             JOIN categories c ON c.id = p.category_id
             WHERE ci.user_id = ?
             ORDER BY ci.created_at DESC'
        );
        $stmt->execute([$user['id']]);
        $items = $stmt->fetchAll();
    } else {
        $cart = session_cart_get();
        if (!empty($cart)) {
            $ids = array_keys($cart);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = db()->prepare(
                'SELECT p.id AS product_id, p.title, p.price, p.image, p.country, c.name AS category_name
                 FROM products p JOIN categories c ON c.id = p.category_id
                 WHERE p.id IN (' . $placeholders . ')'
            );
            $stmt->execute($ids);
            foreach ($stmt->fetchAll() as $row) {
                $row['qty'] = (int) $cart[$row['product_id']];
                $items[] = $row;
            }
        }
    }
    return $items;
}

function cart_state(): array
{
    $items = cart_items();
    $total = 0.0;
    $count = 0;
    foreach ($items as $i) {
        $total += (float) $i['price'] * (int) $i['qty'];
        $count += (int) $i['qty'];
    }
    return [
        'items' => $items,
        'total' => $total,
        'count' => $count,
    ];
}

// При логине переносим товары из сессии в БД пользователя
function cart_merge_session_to_user(int $user_id): void
{
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        return;
    }
    $stmt = db()->prepare(
        'INSERT INTO cart_items (user_id, product_id, qty) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)'
    );
    foreach ($cart as $product_id => $qty) {
        $stmt->execute([$user_id, (int) $product_id, (int) $qty]);
    }
    $_SESSION['cart'] = [];
}

// Если запрос пришёл напрямую (AJAX), выполняем нужное действие и возвращаем JSON
if (php_sapi_name() !== 'cli' && (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'cart.php')) {
    try {
        switch ($action) {
            case 'add':
                cart_add((int) ($_REQUEST['product_id'] ?? 0), (int) ($_REQUEST['qty'] ?? 1));
                break;
            case 'update':
                cart_update((int) ($_REQUEST['product_id'] ?? 0), (int) ($_REQUEST['qty'] ?? 0));
                break;
            case 'remove':
                cart_remove((int) ($_REQUEST['product_id'] ?? 0));
                break;
            case 'clear':
                cart_clear();
                break;
            case 'state':
            default:
                // ничего не меняем
                break;
        }
        json_response(['ok' => true] + cart_state());
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 500);
    }
}

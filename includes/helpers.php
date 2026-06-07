<?php
require_once __DIR__ . '/db.php';

// === HTTPS-детект через reverse-proxy (zo.computer / Cloudflare / nginx) ===
if (
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
) {
    $_SERVER['HTTPS'] = 'on';
}

// === Сессии: явные cookie-параметры, чтобы браузер не выбрасывал куку через прокси ===
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Экранирование вывода (защита от XSS)
function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Форматирование цены: 1234.50 -> "1 234 ₽"
function price(float $value): string {
    return number_format($value, 0, ',', ' ') . ' ₽';
}

function plural_items(int $n): string {
    $abs = abs($n);
    if ($abs % 100 >= 11 && $abs % 100 <= 19) return 'товаров';
    $last = $abs % 10;
    if ($last === 1) return 'товар';
    if ($last >= 2 && $last <= 4) return 'товара';
    return 'товаров';
}

// Текущий пользователь (или null)
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $stmt = db()->prepare('SELECT id, email, name, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    return $u ?: null;
}

// ===== Flash-сообщения и CSRF =====
function flash_set(string $message, string $type = 'success'): void {
    $_SESSION['_flash'][$type] = $message;
}
function flash_get(string $type = 'success'): ?string {
    $msg = $_SESSION['_flash'][$type] ?? null;
    if ($msg !== null) unset($_SESSION['_flash'][$type]);
    return $msg;
}
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf'];
}
function csrf_check(?string $token = null): bool {
    $token ??= $_POST['csrf'] ?? '';
    $ok = !empty($_SESSION['_csrf']) && is_string($token) && hash_equals($_SESSION['_csrf'], $token);
    if (!$ok) {
        // soft-fail: flash + redirect to referer (без die — у пользователя видны нормальные стили)
        flash_set('Сессия истекла, попробуйте ещё раз.', 'error');
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $ref);
        exit;
    }
    return $ok;
}

// ===== Корзина =====
// Возвращает массив [sql_where, params] для идентификации владельца корзины
function cart_owner(): array {
    if (!empty($_SESSION['user_id'])) {
        return ['user_id = ? AND session_id IS NULL', [$_SESSION['user_id']]];
    }
    return ['session_id = ? AND user_id IS NULL', [session_id()]];
}

function cart_add(int $product_id, int $qty = 1): void {
    [$where, $params] = cart_owner();
    $sel = db()->prepare("SELECT id, quantity FROM cart_items WHERE $where AND product_id = ?");
    $sel->execute([...$params, $product_id]);
    $row = $sel->fetch();
    if ($row) {
        $upd = db()->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
        $upd->execute([$row['quantity'] + $qty, $row['id']]);
    } else {
        $ins = db()->prepare("INSERT INTO cart_items (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
        $ins->execute([
            $_SESSION['user_id'] ?? null,
            empty($_SESSION['user_id']) ? session_id() : null,
            $product_id,
            $qty
        ]);
    }
}

function cart_set_qty(int $product_id, int $qty): void {
    [$where, $params] = cart_owner();
    if ($qty <= 0) {
        $del = db()->prepare("DELETE FROM cart_items WHERE $where AND product_id = ?");
        $del->execute([...$params, $product_id]);
    } else {
        $upd = db()->prepare("UPDATE cart_items SET quantity = ? WHERE $where AND product_id = ?");
        $upd->execute([$qty, ...$params, $product_id]);
    }
}

function cart_remove(int $product_id): void {
    cart_set_qty($product_id, 0);
}

function cart_items_full(): array {
    [$where, $params] = cart_owner();
    $sql = "SELECT p.id, ci.product_id, ci.quantity, p.name, p.slug, p.price, p.old_price, p.image, p.stock, c.slug AS cat_slug, c.name AS cat_name
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE $where
            ORDER BY ci.id";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function cart_count(): int {
    [$where, $params] = cart_owner();
    $stmt = db()->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE $where");
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function cart_total(): float {
    [$where, $params] = cart_owner();
    $stmt = db()->prepare(
        "SELECT COALESCE(SUM(ci.quantity * p.price), 0)
         FROM cart_items ci JOIN products p ON p.id = ci.product_id
         WHERE $where"
    );
    $stmt->execute($params);
    return (float)$stmt->fetchColumn();
}

// ===== Категории / товары =====
function all_categories(): array {
    return db()->query('SELECT * FROM categories ORDER BY sort_order, id')->fetchAll();
}

function asset(string $path): string {
    return $path . '?v=' . filemtime(__DIR__ . '/../public' . $path);
}

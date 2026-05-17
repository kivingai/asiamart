<?php
// Обработчик действий админ-панели: CRUD товаров, смена статусов заказов,
// смена ролей пользователей.

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

session_init();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin.php');
}
if (!csrf_check($_POST['csrf'] ?? null)) {
    flash_set('admin_error', 'Ошибка проверки CSRF-токена. Обновите страницу.');
    redirect('/admin.php');
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'product_save':
            $id          = (int) ($_POST['id'] ?? 0);
            $category_id = (int) ($_POST['category_id'] ?? 0);
            $title       = trim((string) ($_POST['title'] ?? ''));
            $country     = trim((string) ($_POST['country'] ?? ''));
            $price       = (float) ($_POST['price'] ?? 0);
            $rating      = (float) ($_POST['rating'] ?? 5);
            $stock       = (int) ($_POST['stock'] ?? 0);
            $description = trim((string) ($_POST['description'] ?? ''));
            $image       = trim((string) ($_POST['image'] ?? 'img/cat-grocery.png'));
            $is_active   = !empty($_POST['is_active']) ? 1 : 0;
            $slug        = $id > 0
                ? (string) ($_POST['slug'] ?? 'product-' . $id)
                : 'product-' . uniqid();

            if ($title === '' || $category_id <= 0 || $price <= 0) {
                flash_set('admin_error', 'Заполните название, цену и категорию.');
                break;
            }

            if ($id > 0) {
                $stmt = db()->prepare(
                    'UPDATE products SET category_id=?, title=?, country=?, price=?, rating=?,
                                         stock=?, description=?, image=?, is_active=? WHERE id=?'
                );
                $stmt->execute([$category_id, $title, $country, $price, $rating, $stock,
                                $description, $image, $is_active, $id]);
                flash_set('admin_ok', 'Товар обновлён.');
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO products (category_id, title, slug, country, price, rating, stock,
                                           description, image, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$category_id, $title, $slug, $country, $price, $rating, $stock,
                                $description, $image, $is_active]);
                flash_set('admin_ok', 'Товар добавлен.');
            }
            break;

        case 'product_delete':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
                $stmt->execute([$id]);
                flash_set('admin_ok', 'Товар удалён.');
            }
            break;

        case 'order_status':
            $id = (int) ($_POST['id'] ?? 0);
            $status = (string) ($_POST['status'] ?? 'new');
            $allowed = ['new', 'paid', 'shipping', 'done', 'canceled'];
            if ($id > 0 && in_array($status, $allowed, true)) {
                $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
                $stmt->execute([$status, $id]);
                flash_set('admin_ok', 'Статус заказа обновлён.');
            }
            break;

        case 'user_role':
            $id = (int) ($_POST['id'] ?? 0);
            $role = (string) ($_POST['role'] ?? 'user');
            if (!in_array($role, ['user', 'admin'], true)) {
                $role = 'user';
            }
            if ($id > 0) {
                $stmt = db()->prepare('UPDATE users SET role = ? WHERE id = ?');
                $stmt->execute([$role, $id]);
                flash_set('admin_ok', 'Роль пользователя обновлена.');
            }
            break;

        case 'user_delete':
            $id = (int) ($_POST['id'] ?? 0);
            $me = current_user();
            if ($id > 0 && $me && $id !== (int) $me['id']) {
                $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
                $stmt->execute([$id]);
                flash_set('admin_ok', 'Пользователь удалён.');
            } else {
                flash_set('admin_error', 'Нельзя удалить самого себя.');
            }
            break;

        default:
            flash_set('admin_error', 'Неизвестное действие.');
    }
} catch (Throwable $e) {
    flash_set('admin_error', 'Ошибка: ' . $e->getMessage());
}

$tab = $_POST['tab'] ?? '';
redirect('/admin.php' . ($tab ? '?tab=' . urlencode($tab) : ''));

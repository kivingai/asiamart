<?php
// Общие helper-функции, используемые в шаблонах и обработчиках.

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float $value): string
{
    $cfg = app_config();
    return number_format($value, 0, ',', ' ') . $cfg['currency'];
}

function get_categories(): array
{
    static $rows = null;
    if ($rows === null) {
        $rows = db()->query('SELECT id, slug, name, icon, image FROM categories ORDER BY sort, id')->fetchAll();
    }
    return $rows;
}

function get_products(?string $categorySlug = null, ?string $search = null, int $limit = 200): array
{
    $sql = 'SELECT p.*, c.slug AS category_slug, c.name AS category_name, c.icon AS category_icon
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.is_active = 1';
    $params = [];
    if ($categorySlug && $categorySlug !== 'all') {
        $sql .= ' AND c.slug = :slug';
        $params[':slug'] = $categorySlug;
    }
    if ($search !== null && $search !== '') {
        $sql .= ' AND p.title LIKE :search';
        $params[':search'] = '%' . $search . '%';
    }
    $sql .= ' ORDER BY p.id DESC LIMIT ' . (int) $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_product(int $id): ?array
{
    $stmt = db()->prepare('SELECT p.*, c.slug AS category_slug, c.name AS category_name, c.icon AS category_icon
                            FROM products p JOIN categories c ON c.id = p.category_id
                            WHERE p.id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function flash_set(string $key, string $msg): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['flash'][$key] = $msg;
}

function flash_get(string $key): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

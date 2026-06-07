<?php
// AsiaMart — Cart API. Принимает JSON или form-data.
require_once __DIR__ . '/../../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$action     = $data['action']     ?? 'add';
$product_id = (int)($data['product_id'] ?? 0);
$qty        = (int)($data['qty']        ?? 1);

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad product_id']);
    exit;
}

try {
    switch ($action) {
        case 'add':
            cart_add($product_id, max(1, $qty));
            break;
        case 'set':
            cart_set_qty($product_id, max(0, $qty));
            break;
        case 'remove':
            cart_remove($product_id);
            break;
        default:
            throw new RuntimeException('unknown action');
    }
    echo json_encode([
        'ok' => true,
        'count' => cart_count(),
        'total' => cart_total(),
    ]);
} catch (Throwable $err) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $err->getMessage()]);
}

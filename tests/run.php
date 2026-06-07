<?php
declare(strict_types=1);

ob_start();

$root = dirname(__DIR__);
$failed = 0;
$passed = 0;

function ok(string $name, bool $condition, string $details = ''): void
{
    global $failed, $passed;
    if ($condition) {
        $passed++;
        echo "[OK] {$name}\n";
        return;
    }

    $failed++;
    echo "[FAIL] {$name}";
    if ($details !== '') {
        echo " - {$details}";
    }
    echo "\n";
}

function file_text(string $path): string
{
    $text = file_get_contents($path);
    if ($text === false) {
        throw new RuntimeException("Cannot read {$path}");
    }
    return $text;
}

function contains(string $haystack, string $needle): bool
{
    return strpos($haystack, $needle) !== false;
}

echo "AsiaMart test suite\n";

ok('PHP version is 8.0+', PHP_VERSION_ID >= 80000, PHP_VERSION);
ok('mbstring extension is loaded', extension_loaded('mbstring'));
ok('pdo_mysql extension is loaded', extension_loaded('pdo_mysql'));

$phpFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);
$linted = 0;
foreach ($phpFiles as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    if (str_ends_with(str_replace('\\', '/', $path), '/public/adminer.php')) {
        continue;
    }

    $cmd = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path);
    exec($cmd, $output, $code);
    ok('PHP lint: ' . str_replace($root . DIRECTORY_SEPARATOR, '', $path), $code === 0, implode(' ', $output));
    $linted++;
}
ok('PHP files were linted', $linted > 0);

$schema = file_text($root . '/sql/schema.sql');
$fullDump = file_text($root . '/sql/asiamart_full.sql');
$helpers = file_text($root . '/includes/helpers.php');
$catalog = file_text($root . '/public/katalog.php');
$product = file_text($root . '/public/product.php');
$adminProducts = file_text($root . '/public/admin/products.php');
$adminUsers = file_text($root . '/public/admin/users.php');
$profile = file_text($root . '/public/profile.php');
$header = file_text($root . '/includes/header.php');
$css = file_text($root . '/public/assets/css/asiamart.css');

foreach ([$schema, $fullDump] as $idx => $sql) {
    $label = $idx === 0 ? 'schema.sql' : 'asiamart_full.sql';
    ok("{$label}: cart supports guest session_id", contains($sql, '`session_id`'));
    ok("{$label}: orders have order_number", contains($sql, '`order_number`'));
    ok("{$label}: products use short_desc", contains($sql, '`short_desc`'));
    ok("{$label}: products have brand", contains($sql, '`brand`'));
    ok("{$label}: products have rating", contains($sql, '`rating`'));
    ok("{$label}: products have reviews_count", contains($sql, '`reviews_count`'));
    ok("{$label}: payment supports sbp", contains($sql, "'sbp'"));
}

ok('cart_items_full returns product id for cart links', contains($helpers, 'SELECT p.id, ci.product_id'));
ok('catalog searches short_desc', contains($catalog, 'p.short_desc LIKE ?'));
ok('catalog does not use removed short_description', !contains($catalog, 'short_description'));
ok('product page reads short_desc', contains($product, "\$p['short_desc']"));
ok('product page does not use removed short_description', !contains($product, 'short_description'));
ok('admin products saves short_desc', contains($adminProducts, "'short_desc' =>"));
ok('admin products does not use short_descr', !contains($adminProducts, 'short_descr'));
ok('admin products guards deleting ordered products', contains($adminProducts, 'SELECT COUNT(*) FROM order_items WHERE product_id=?'));
ok('admin users guards deleting users with orders', contains($adminUsers, 'SELECT COUNT(*) FROM orders WHERE user_id=?'));
ok('profile order thumbnails use product_name', contains($profile, "\$it['product_name']"));
ok('public header renders info flash messages', contains($header, "flash_get('info')"));
ok('flash-info style exists', contains($css, '.flash-info'));

require_once $root . '/includes/helpers.php';
ok('e() escapes HTML', e('<tag "x">') === '&lt;tag &quot;x&quot;&gt;');
ok('price() formats rubles', price(1234.5) === '1 235 ₽');
ok('plural_items(1)', plural_items(1) === 'товар');
ok('plural_items(2)', plural_items(2) === 'товара');
ok('plural_items(5)', plural_items(5) === 'товаров');
ok('plural_items(11)', plural_items(11) === 'товаров');

echo "\nPassed: {$passed}; Failed: {$failed}\n";
ob_end_flush();
exit($failed === 0 ? 0 : 1);

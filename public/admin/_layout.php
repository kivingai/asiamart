<?php
require_once __DIR__ . '/../../includes/helpers.php';

$me = current_user();
if (!$me || $me['role'] !== 'admin') {
    flash_set('Доступ только для администраторов.', 'error');
    header('Location: /login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

function admin_counts(): array {
    static $c = null;
    if ($c !== null) return $c;
    $pdo = db();
    $c = [
        'products' => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'orders'   => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'users'    => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'new'      => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='new'")->fetchColumn(),
    ];
    return $c;
}

function admin_header(string $section, string $title): void {
    global $me;
    $counts = admin_counts();
    $cssTime = filemtime(__DIR__ . '/../assets/css/ryokan.css');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> — AsiaMart · Админ</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300..600;1,300..600&family=DM+Sans:wght@400;500;600&family=Noto+Serif+JP:wght@300;400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/ryokan.css?v=<?= $cssTime ?>">
</head>
<body class="admin-ryokan">

<div class="admin-shell">

  <aside class="admin-side">
    <div class="admin-brand">
      <a href="/admin/">
        <span class="name">AsiaMart</span>
        <sup class="hanko">朱</sup>
      </a>
      <span class="tag">部 · Admin Panel</span>
    </div>

    <ul class="admin-nav">
      <li><a href="/admin/" class="<?= $section==='dashboard'?'is-active':'' ?>">
        <span><span class="kanji">板</span> &nbsp; Дашборд</span>
      </a></li>
      <li><a href="/admin/products.php" class="<?= $section==='products'?'is-active':'' ?>">
        <span><span class="kanji">商</span> &nbsp; Товары</span>
        <span class="count"><?= $counts['products'] ?></span>
      </a></li>
      <li><a href="/admin/orders.php" class="<?= $section==='orders'?'is-active':'' ?>">
        <span><span class="kanji">注</span> &nbsp; Заказы</span>
        <span class="count"><?= $counts['orders'] ?><?= $counts['new']>0 ? ' · '.$counts['new'].' нов.' : '' ?></span>
      </a></li>
      <li><a href="/admin/users.php" class="<?= $section==='users'?'is-active':'' ?>">
        <span><span class="kanji">客</span> &nbsp; Пользователи</span>
        <span class="count"><?= $counts['users'] ?></span>
      </a></li>
    </ul>

    <div class="admin-side-foot">
      <div style="margin-bottom:6px;">Вы вошли как</div>
      <div style="color:var(--rg-paper);font-family:'Fraunces',serif;font-style:italic;font-size:16px;margin-bottom:10px;">
        <?= e($me['name']) ?>
      </div>
      <a href="/">← На сайт</a>
      &nbsp;·&nbsp;
      <a href="/logout.php">Выйти</a>
    </div>
  </aside>

  <main class="admin-content">
    <?php if ($msg = flash_get('success')): ?>
      <div style="background:rgba(4,120,87,0.08);border:1px solid rgba(4,120,87,0.3);color:#047857;padding:12px 16px;border-radius:12px;margin-bottom:24px;font-family:'DM Sans',sans-serif;font-size:13px;">
        <?= e($msg) ?>
      </div>
    <?php endif; ?>
    <?php if ($msg = flash_get('error')): ?>
      <div style="background:rgba(198,63,42,0.06);border:1px solid var(--rg-vermilion);color:var(--rg-vermilion);padding:12px 16px;border-radius:12px;margin-bottom:24px;font-family:'DM Sans',sans-serif;font-size:13px;">
        <?= e($msg) ?>
      </div>
    <?php endif; ?>
    <?php if ($msg = flash_get('info')): ?>
      <div style="background:rgba(29,78,216,0.06);border:1px solid rgba(29,78,216,0.25);color:#1d4ed8;padding:12px 16px;border-radius:12px;margin-bottom:24px;font-family:'DM Sans',sans-serif;font-size:13px;">
        <?= e($msg) ?>
      </div>
    <?php endif; ?>
<?php }

function admin_footer(): void { ?>
  </main>
</div>

</body>
</html>
<?php } ?>

<?php
$page = 'admin';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/helpers.php';

require_admin();

$cfg = app_config();
$tab = $_GET['tab'] ?? 'dashboard';

$err = flash_get('admin_error');
$ok  = flash_get('admin_ok');

$categories = get_categories();

$stats = [
    'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders'   => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'users'    => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'revenue'  => (float) db()->query('SELECT COALESCE(SUM(total),0) FROM orders WHERE status != "canceled"')->fetchColumn(),
];

$products = db()->query(
    'SELECT p.*, c.name AS category_name FROM products p
     JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC'
)->fetchAll();

$orders = db()->query(
    'SELECT o.*, u.email AS user_email, u.name AS user_name
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.id DESC LIMIT 200'
)->fetchAll();

$users = db()->query('SELECT id, email, name, phone, role, created_at FROM users ORDER BY id DESC')->fetchAll();

$statusLabels = [
    'new'      => 'Новый',
    'paid'     => 'Оплачен',
    'shipping' => 'В доставке',
    'done'     => 'Выполнен',
    'canceled' => 'Отменён',
];
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Админ-панель — <?= e($cfg['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/global.css" />
  <link rel="stylesheet" href="css/admin.css" />
</head>
<body>
  <div class="bg-decor">
    <div class="circle circle-amber"></div>
    <div class="circle circle-red"></div>
    <div class="circle circle-cyan"></div>
  </div>

  <header class="admin-header" id="siteHeader">
    <div class="container header-wrapper">
      <a href="index.php" class="logo">
        <h1><?= e($cfg['name']) ?></h1>
        <span class="logo-underline"></span>
      </a>
      <h2 class="panel-title">Административная панель</h2>
      <a href="php/logout.php" class="btn btn-login">Выйти</a>
    </div>
  </header>

  <div class="admin-wrapper">
    <nav class="admin-nav" id="adminNav">
      <ul>
        <li class="nav-item <?= $tab === 'dashboard' ? 'active' : '' ?>"><a href="admin.php?tab=dashboard">Дашборд</a></li>
        <li class="nav-item <?= $tab === 'products'  ? 'active' : '' ?>"><a href="admin.php?tab=products">Товары</a></li>
        <li class="nav-item <?= $tab === 'orders'    ? 'active' : '' ?>"><a href="admin.php?tab=orders">Заказы</a></li>
        <li class="nav-item <?= $tab === 'users'     ? 'active' : '' ?>"><a href="admin.php?tab=users">Пользователи</a></li>
      </ul>
    </nav>

    <main class="admin-content">
      <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
      <?php if ($ok):  ?><div class="alert alert-success"><?= e($ok)  ?></div><?php endif; ?>

      <?php if ($tab === 'dashboard'): ?>
        <section class="admin-section active">
          <h3>Обзор</h3>
          <div class="stats-grid">
            <div class="stat-card"><div class="stat-content"><span class="stat-number"><?= $stats['products'] ?></span><span class="stat-label">товаров</span></div></div>
            <div class="stat-card"><div class="stat-content"><span class="stat-number"><?= $stats['orders']   ?></span><span class="stat-label">заказов</span></div></div>
            <div class="stat-card"><div class="stat-content"><span class="stat-number"><?= $stats['users']    ?></span><span class="stat-label">пользователей</span></div></div>
            <div class="stat-card"><div class="stat-content"><span class="stat-number"><?= money((float) $stats['revenue']) ?></span><span class="stat-label">оборот</span></div></div>
          </div>
        </section>

      <?php elseif ($tab === 'products'): ?>
        <section class="admin-section active">
          <h3>Управление товарами</h3>

          <details class="admin-form">
            <summary>Добавить товар</summary>
            <form method="post" action="php/admin_handler.php" class="row g-2 mt-3">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
              <input type="hidden" name="action" value="product_save" />
              <input type="hidden" name="tab" value="products" />
              <div class="col-md-4"><input class="form-control" name="title" placeholder="Название" required></div>
              <div class="col-md-2">
                <select class="form-select" name="category_id" required>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2"><input class="form-control" name="country" placeholder="🇯🇵 Япония"></div>
              <div class="col-md-1"><input class="form-control" type="number" step="0.01" name="price" placeholder="Цена" required></div>
              <div class="col-md-1"><input class="form-control" type="number" name="stock" placeholder="Кол-во" value="20"></div>
              <div class="col-md-1"><input class="form-control" type="number" step="0.1" name="rating" placeholder="Оценка" value="5"></div>
              <div class="col-md-1"><label class="form-check-label"><input type="checkbox" name="is_active" checked> Активен</label></div>
              <div class="col-md-6"><input class="form-control" name="image" value="img/cat-grocery.png" placeholder="Путь к изображению"></div>
              <div class="col-md-6"><input class="form-control" name="description" placeholder="Описание"></div>
              <div class="col-12"><button class="btn btn-cta" type="submit">Добавить товар</button></div>
            </form>
          </details>

          <div class="table-responsive mt-3">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>ID</th><th>Название</th><th>Категория</th><th>Цена</th><th>Кол-во</th><th>Активен</th><th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($products as $p): ?>
                  <tr>
                    <td><?= (int) $p['id'] ?></td>
                    <td>
                      <form method="post" action="php/admin_handler.php" class="d-flex gap-1">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                        <input type="hidden" name="action" value="product_save" />
                        <input type="hidden" name="tab" value="products" />
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                        <input type="hidden" name="slug" value="<?= e($p['slug']) ?>" />
                        <input type="hidden" name="image" value="<?= e($p['image']) ?>" />
                        <input type="hidden" name="description" value="<?= e($p['description']) ?>" />
                        <input type="hidden" name="country" value="<?= e($p['country']) ?>" />
                        <input type="hidden" name="rating" value="<?= e($p['rating']) ?>" />
                        <input class="form-control form-control-sm" name="title" value="<?= e($p['title']) ?>" />
                        <select class="form-select form-select-sm" name="category_id">
                          <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === (int) $p['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                          <?php endforeach; ?>
                        </select>
                        <input class="form-control form-control-sm" style="width:90px" type="number" step="0.01" name="price" value="<?= e($p['price']) ?>" />
                        <input class="form-control form-control-sm" style="width:70px" type="number" name="stock" value="<?= (int) $p['stock'] ?>" />
                        <label class="form-check-label"><input type="checkbox" name="is_active" <?= $p['is_active'] ? 'checked' : '' ?>></label>
                        <button class="btn btn-sm btn-outline" type="submit">💾</button>
                      </form>
                    </td>
                    <td><?= e($p['category_name']) ?></td>
                    <td><?= e($p['price']) ?></td>
                    <td><?= (int) $p['stock'] ?></td>
                    <td><?= $p['is_active'] ? 'да' : 'нет' ?></td>
                    <td>
                      <form method="post" action="php/admin_handler.php" onsubmit="return confirm('Удалить товар?')">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                        <input type="hidden" name="action" value="product_delete" />
                        <input type="hidden" name="tab" value="products" />
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                        <button class="btn btn-sm btn-danger" type="submit">×</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

      <?php elseif ($tab === 'orders'): ?>
        <section class="admin-section active">
          <h3>Заказы</h3>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>№</th><th>Дата</th><th>Покупатель</th><th>Сумма</th><th>Доставка</th><th>Оплата</th><th>Статус</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orders as $o): ?>
                  <tr>
                    <td>#<?= (int) $o['id'] ?></td>
                    <td><?= e($o['created_at']) ?></td>
                    <td><?= e($o['user_name']) ?><br><small><?= e($o['user_email']) ?></small></td>
                    <td><?= money((float) $o['total']) ?></td>
                    <td><?= $o['delivery'] === 'pickup' ? 'Самовывоз' : 'Курьер' ?></td>
                    <td><?= $o['payment'] === 'cash' ? 'Наличные' : 'Карта' ?></td>
                    <td>
                      <form method="post" action="php/admin_handler.php" class="d-flex gap-1">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                        <input type="hidden" name="action" value="order_status" />
                        <input type="hidden" name="tab" value="orders" />
                        <input type="hidden" name="id" value="<?= (int) $o['id'] ?>" />
                        <select class="form-select form-select-sm" name="status">
                          <?php foreach ($statusLabels as $key => $lbl): ?>
                            <option value="<?= e($key) ?>" <?= $o['status'] === $key ? 'selected' : '' ?>><?= e($lbl) ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-outline">OK</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

      <?php elseif ($tab === 'users'): ?>
        <section class="admin-section active">
          <h3>Пользователи</h3>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>ID</th><th>Имя</th><th>E-mail</th><th>Телефон</th><th>Роль</th><th>Регистрация</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                  <tr>
                    <td><?= (int) $u['id'] ?></td>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone']) ?></td>
                    <td>
                      <form method="post" action="php/admin_handler.php" class="d-flex gap-1">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                        <input type="hidden" name="action" value="user_role" />
                        <input type="hidden" name="tab" value="users" />
                        <input type="hidden" name="id" value="<?= (int) $u['id'] ?>" />
                        <select class="form-select form-select-sm" name="role">
                          <option value="user"  <?= $u['role'] === 'user'  ? 'selected' : '' ?>>user</option>
                          <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                        </select>
                        <button class="btn btn-sm btn-outline">OK</button>
                      </form>
                    </td>
                    <td><?= e($u['created_at']) ?></td>
                    <td>
                      <form method="post" action="php/admin_handler.php" onsubmit="return confirm('Удалить пользователя?')">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
                        <input type="hidden" name="action" value="user_delete" />
                        <input type="hidden" name="tab" value="users" />
                        <input type="hidden" name="id" value="<?= (int) $u['id'] ?>" />
                        <button class="btn btn-sm btn-danger">×</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="js/site.js"></script>
</body>
</html>

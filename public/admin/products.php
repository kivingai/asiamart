<?php
require_once __DIR__ . '/_layout.php';
$pdo = db();
$action = $_GET['action'] ?? 'list';

function slug_from(string $s): string {
    $map = ['а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya'];
    $s = mb_strtolower($s);
    $s = strtr($s, $map);
    $s = preg_replace('~[^a-z0-9]+~u', '-', $s);
    $s = trim($s, '-');
    return $s ?: ('p-' . bin2hex(random_bytes(3)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $op = $_POST['op'] ?? '';

    if ($op === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'category_id'=> (int)($_POST['category_id'] ?? 0),
            'slug'       => slug_from(trim($_POST['slug'] ?? '') ?: ($_POST['name'] ?? '')),
            'name'       => trim($_POST['name'] ?? ''),
            'short_desc' => trim($_POST['short_desc'] ?? ''),
            'description'=> trim($_POST['description'] ?? ''),
            'price'      => (float)$_POST['price'],
            'old_price'  => $_POST['old_price'] !== '' ? (float)$_POST['old_price'] : null,
            'stock'      => (int)$_POST['stock'],
            'country'    => trim($_POST['country'] ?? ''),
            'brand'      => trim($_POST['brand'] ?? ''),
            'weight'     => trim($_POST['weight'] ?? ''),
            'image'      => trim($_POST['image'] ?? ''),
            'is_featured'=> isset($_POST['is_featured']) ? 1 : 0,
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ];
        if (!empty($_FILES['image_file']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $fname = '/uploads/p_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                move_uploaded_file($_FILES['image_file']['tmp_name'], __DIR__ . '/..' . $fname);
                $data['image'] = $fname;
            }
        }
        if ($data['name'] === '' || $data['price'] <= 0) {
            flash_set('Имя и цена обязательны.', 'error');
        } else {
            try {
                if ($id) {
                    $sql = 'UPDATE products SET ' . implode(',', array_map(fn($k) => "$k=:$k", array_keys($data))) . ' WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $data['id'] = $id;
                    $stmt->execute($data);
                    flash_set('Товар обновлён.');
                } else {
                    $sql = 'INSERT INTO products (' . implode(',', array_keys($data)) . ') VALUES (:' . implode(',:', array_keys($data)) . ')';
                    $pdo->prepare($sql)->execute($data);
                    flash_set('Товар создан.');
                }
            } catch (PDOException $e) {
                flash_set('Ошибка БД: ' . $e->getMessage(), 'error');
            }
        }
        header('Location: /admin/products.php'); exit;
    }
    if ($op === 'delete') {
        $id = (int)$_POST['id'];
        $used = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE product_id=?');
        $used->execute([$id]);
        if ((int)$used->fetchColumn() > 0) {
            $pdo->prepare('UPDATE products SET is_active=0 WHERE id=?')->execute([$id]);
            flash_set('Товар уже есть в заказах: он скрыт из каталога, но история заказов сохранена.', 'info');
        } else {
            $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
            flash_set('Товар удалён.');
        }
        header('Location: /admin/products.php'); exit;
    }
    if ($op === 'toggle') {
        $pdo->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ?')->execute([(int)$_POST['id']]);
        flash_set('Статус изменён.');
        header('Location: /admin/products.php'); exit;
    }
}

if ($action === 'edit' || $action === 'add') {
    $p = ['id'=>0,'category_id'=>0,'name'=>'','slug'=>'','short_desc'=>'','description'=>'','price'=>'','old_price'=>'','stock'=>0,'country'=>'','brand'=>'','weight'=>'','image'=>'','is_featured'=>0,'is_active'=>1];
    if ($action === 'edit') {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id=?');
        $stmt->execute([(int)$_GET['id']]);
        $p = $stmt->fetch() ?: $p;
    }
    $cats = all_categories();
    admin_header('products', $action === 'edit' ? 'Редактирование' : 'Новый товар');
    ?>

    <div class="admin-page-head">
      <div>
        <div class="admin-eyebrow"><span class="dot"></span>商 · PRODUCT · <?= $action === 'edit' ? 'EDIT' : 'NEW' ?></div>
        <h1 class="admin-page-title">
          <?= $action === 'edit' ? 'Редактирование <em>товара</em>' : 'Новый <em>товар</em>' ?><span class="kanji">商</span>
        </h1>
      </div>
      <div class="admin-page-actions">
        <a href="/admin/products.php" class="admin-btn admin-btn-ghost">← Назад</a>
      </div>
    </div>

    <div class="admin-section">
      <form method="post" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="op" value="save">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

        <div class="field">
          <label>Название товара</label>
          <input type="text" name="name" required value="<?= e($p['name']) ?>">
        </div>

        <div class="row">
          <div class="field">
            <label>Категория</label>
            <select name="category_id" required>
              <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id']==$p['category_id']?'selected':'' ?>><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Slug (URL)</label>
            <input type="text" name="slug" value="<?= e($p['slug']) ?>" placeholder="оставьте пустым для авто">
          </div>
        </div>

        <div class="field">
          <label>Краткое описание</label>
          <input type="text" name="short_desc" maxlength="250" value="<?= e($p['short_desc']) ?>">
        </div>

        <div class="field">
          <label>Полное описание</label>
          <textarea name="description" rows="5"><?= e($p['description']) ?></textarea>
        </div>

        <div class="row cols-3">
          <div class="field"><label>Цена, ₽</label><input type="number" name="price" min="0" step="0.01" required value="<?= e($p['price']) ?>"></div>
          <div class="field"><label>Старая цена</label><input type="number" name="old_price" min="0" step="0.01" value="<?= e($p['old_price']) ?>"></div>
          <div class="field"><label>На складе</label><input type="number" name="stock" min="0" value="<?= (int)$p['stock'] ?>"></div>
        </div>

        <div class="row cols-3">
          <div class="field"><label>Страна</label><input type="text" name="country" value="<?= e($p['country']) ?>"></div>
          <div class="field"><label>Бренд</label><input type="text" name="brand" value="<?= e($p['brand']) ?>"></div>
          <div class="field"><label>Вес / объём</label><input type="text" name="weight" value="<?= e($p['weight']) ?>"></div>
        </div>

        <div class="row">
          <div class="field">
            <label>Картинка — путь или URL</label>
            <input type="text" name="image" value="<?= e($p['image']) ?>" placeholder="/assets/img/products/...">
          </div>
          <div class="field">
            <label>Или загрузить файл</label>
            <input type="file" name="image_file" accept="image/*">
          </div>
        </div>
        <?php if ($p['image']): ?>
          <div style="display:flex;gap:14px;align-items:center;padding:14px;background:var(--rg-cream);border-radius:10px;border:1px solid var(--rg-line);">
            <img src="<?= e($p['image']) ?>" style="width:80px;height:80px;border-radius:8px;object-fit:cover;border:1px solid var(--rg-line);">
            <div style="font-family:'DM Sans',sans-serif;font-size:12px;color:var(--rg-mute);">Текущее изображение</div>
          </div>
        <?php endif; ?>

        <div class="row">
          <label class="checkbox">
            <input type="checkbox" name="is_active" <?= $p['is_active']?'checked':'' ?>>
            <span>Активен (виден в каталоге)</span>
          </label>
          <label class="checkbox">
            <input type="checkbox" name="is_featured" <?= $p['is_featured']?'checked':'' ?>>
            <span>★ Хит продаж (показать на главной)</span>
          </label>
        </div>

        <div class="form-actions">
          <button class="admin-btn" type="submit">💾 Сохранить →</button>
          <a href="/admin/products.php">Отмена</a>
        </div>
      </form>
    </div>

    <?php
    admin_footer();
    exit;
}

// === список ===
$q   = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$sql = 'SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE 1=1';
$params = [];
if ($q !== '')   { $sql .= ' AND p.name LIKE ?'; $params[] = "%$q%"; }
if ($cat)        { $sql .= ' AND p.category_id = ?'; $params[] = $cat; }
$sql .= ' ORDER BY p.id DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll();
$all_cats = all_categories();

admin_header('products', 'Товары');
?>

<div class="admin-page-head">
  <div>
    <div class="admin-eyebrow"><span class="dot"></span>商 · PRODUCTS · <?= count($rows) ?></div>
    <h1 class="admin-page-title">Управление <em>товарами</em><span class="kanji">商</span></h1>
  </div>
  <div class="admin-page-actions">
    <a href="?action=add" class="admin-btn">+ Добавить товар</a>
  </div>
</div>

<form method="get" class="admin-filter">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="🔍 Поиск по названию...">
  <a href="?" class="chip <?= $cat===0?'is-active':'' ?>">Все</a>
  <?php foreach ($all_cats as $c): ?>
    <a href="?cat=<?= $c['id'] ?><?= $q?'&q='.urlencode($q):'' ?>" class="chip <?= $cat===(int)$c['id']?'is-active':'' ?>"><?= e($c['name']) ?></a>
  <?php endforeach; ?>
  <?php if ($q || $cat): ?>
    <button type="submit" class="chip">Применить</button>
  <?php endif; ?>
</form>

<div class="admin-section">
  <?php if (!$rows): ?>
    <p style="color:var(--rg-mute);text-align:center;padding:32px;font-family:'DM Sans',sans-serif;">Товары не найдены.</p>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th></th>
          <th>Название</th>
          <th>Категория</th>
          <th>Цена</th>
          <th>Склад</th>
          <th>Статус</th>
          <th style="text-align:right;">Действия</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td style="width:60px;">
            <div class="name-thumb"><img src="<?= e($r['image'] ?: '/assets/img/placeholder.png') ?>" alt=""></div>
          </td>
          <td>
            <div class="name-title"><?= e($r['name']) ?></div>
            <div class="name-sub"><?= e($r['slug']) ?></div>
          </td>
          <td><?= e($r['cat_name']) ?></td>
          <td>
            <span class="price"><?= price((float)$r['price']) ?></span>
            <?php if($r['old_price']): ?>
              <div style="font-family:'DM Sans',sans-serif;font-size:11px;color:var(--rg-mute);text-decoration:line-through;"><?= price((float)$r['old_price']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($r['stock'] > 0): ?>
              <span style="font-family:'DM Sans',sans-serif;font-size:13px;color:var(--rg-ink);"><?= $r['stock'] ?> шт</span>
            <?php else: ?>
              <span class="pill cancelled">нет в наличии</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if($r['is_active']): ?>
              <span class="pill active">Активен</span>
            <?php else: ?>
              <span class="pill hidden">Скрыт</span>
            <?php endif; ?>
            <?php if($r['is_featured']): ?>
              <div style="margin-top:6px;"><span class="pill admin">★ Хит</span></div>
            <?php endif; ?>
          </td>
          <td>
            <div class="row-actions">
              <a href="/product.php?id=<?= $r['id'] ?>" target="_blank" class="icon-act" title="Посмотреть на сайте">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <a href="?action=edit&id=<?= $r['id'] ?>" class="icon-act" title="Редактировать">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="m18.5 2.5 3 3L12 15l-4 1 1-4z"/></svg>
              </a>
              <form method="post" style="display:inline;" onsubmit="return confirm('Скрыть/показать товар?')">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="op" value="toggle">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="icon-act" title="Скрыть/показать">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 12a9 9 0 0 1 15-6.7l3 3M21 12a9 9 0 0 1-15 6.7l-3-3"/><path d="M21 3v6h-6M3 21v-6h6"/></svg>
                </button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Удалить «<?= e($r['name']) ?>» навсегда?')">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="op" value="delete">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="icon-act danger" title="Удалить">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="m19 6-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                </button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>

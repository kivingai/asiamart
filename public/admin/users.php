<?php
require_once __DIR__ . '/_layout.php';
$pdo = db();
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $op = $_POST['op'] ?? '';
    $id = (int)$_POST['id'];

    if ($id === (int)$me['id']) {
        flash_set('Нельзя менять самого себя.', 'error');
        header('Location: /admin/users.php'); exit;
    }

    if ($op === 'role') {
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
        $pdo->prepare('UPDATE users SET role=? WHERE id=?')->execute([$role, $id]);
        flash_set("Роль изменена на «{$role}».");
    }
    if ($op === 'delete') {
        $orders = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id=?');
        $orders->execute([$id]);
        if ((int)$orders->fetchColumn() > 0) {
            flash_set('У пользователя есть заказы, удаление отклонено. Историю покупок нужно сохранить.', 'error');
        } else {
            $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
            flash_set('Пользователь удалён.');
        }
    }
    header('Location: /admin/users.php'); exit;
}

$q = trim($_GET['q'] ?? '');
$role_filter = $_GET['role'] ?? '';
$sql = 'SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id) AS orders_cnt,
        (SELECT COALESCE(SUM(o.total),0) FROM orders o WHERE o.user_id=u.id AND o.status<>"cancelled") AS spent
        FROM users u WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (u.email LIKE ? OR u.name LIKE ?)';
    $params[] = "%$q%"; $params[] = "%$q%";
}
if (in_array($role_filter, ['admin','user'])) {
    $sql .= ' AND u.role=?';
    $params[] = $role_filter;
}
$sql .= ' ORDER BY u.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

admin_header('users', 'Пользователи');
?>

<div class="admin-page-head">
  <div>
    <div class="admin-eyebrow"><span class="dot"></span>客 · USERS · <?= count($users) ?></div>
    <h1 class="admin-page-title">Управление <em>клиентами</em><span class="kanji">客</span></h1>
  </div>
</div>

<form method="get" class="admin-filter">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="🔍 Поиск по email или имени...">
  <a href="?<?= $q?'q='.urlencode($q):'' ?>" class="chip <?= $role_filter===''?'is-active':'' ?>">Все</a>
  <a href="?role=admin<?= $q?'&q='.urlencode($q):'' ?>" class="chip <?= $role_filter==='admin'?'is-active':'' ?>">Админы</a>
  <a href="?role=user<?= $q?'&q='.urlencode($q):'' ?>" class="chip <?= $role_filter==='user'?'is-active':'' ?>">Клиенты</a>
</form>

<div class="admin-section">
  <?php if (!$users): ?>
    <p style="color:var(--rg-mute);text-align:center;padding:32px;font-family:'DM Sans',sans-serif;">Пользователи не найдены.</p>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>№</th>
          <th>Имя</th>
          <th>Email</th>
          <th>Регистрация</th>
          <th>Заказов</th>
          <th>Потрачено</th>
          <th>Роль</th>
          <th style="text-align:right;"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): $isMe = (int)$u['id']===(int)$me['id']; ?>
        <tr>
          <td><span class="num">#<?= $u['id'] ?></span></td>
          <td>
            <div class="name-cell">
              <div class="name-thumb" style="background:linear-gradient(135deg, var(--rg-vermilion), #8c2516);color:var(--rg-paper);display:flex;align-items:center;justify-content:center;font-family:'Fraunces',serif;font-style:italic;font-size:18px;border:none;">
                <?= e(mb_substr($u['name'] ?: 'A', 0, 1)) ?>
              </div>
              <div>
                <div class="name-title"><?= e($u['name']) ?><?= $isMe?' <span style="color:var(--rg-mute);font-size:11px;font-style:italic;">— вы</span>':'' ?></div>
              </div>
            </div>
          </td>
          <td style="color:var(--rg-ink-2);font-size:13px;"><?= e($u['email']) ?></td>
          <td style="color:var(--rg-mute);font-size:12px;"><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['orders_cnt'] > 0): ?>
              <span style="font-family:'Fraunces',serif;font-size:16px;color:var(--rg-ink);"><?= $u['orders_cnt'] ?></span>
              <span style="color:var(--rg-mute);font-size:11px;">шт</span>
            <?php else: ?>
              <span style="color:var(--rg-mute);font-size:12px;">—</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($u['spent'] > 0): ?>
              <span class="price"><?= price((float)$u['spent']) ?></span>
            <?php else: ?>
              <span style="color:var(--rg-mute);font-size:12px;">—</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($u['role'] === 'admin'): ?>
              <span class="pill admin">★ Админ</span>
            <?php else: ?>
              <span class="pill user">Клиент</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="row-actions">
              <?php if (!$isMe): ?>
                <form method="post" style="display:inline;" onsubmit="return confirm('Сделать <?= $u['role']==='admin'?'обычным клиентом':'администратором' ?>?')">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="op" value="role">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="role" value="<?= $u['role']==='admin'?'user':'admin' ?>">
                  <button type="submit" class="icon-act" title="<?= $u['role']==='admin'?'Снять админа':'Сделать админом' ?>">
                    <?php if ($u['role']==='admin'): ?>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    <?php else: ?>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9 12 2"/></svg>
                    <?php endif; ?>
                  </button>
                </form>
                <form method="post" style="display:inline;" onsubmit="return confirm('Удалить пользователя <?= e($u['email']) ?>?')">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="op" value="delete">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button type="submit" class="icon-act danger" title="Удалить пользователя">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="m19 6-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                  </button>
                </form>
              <?php else: ?>
                <span style="color:var(--rg-mute);font-size:11px;font-style:italic;">это вы</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>

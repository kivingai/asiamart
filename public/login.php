<?php
require_once __DIR__ . '/../includes/helpers.php';

if (current_user()) {
    header('Location: /profile.php');
    exit;
}

$mode = $_GET['mode'] ?? 'login';
$errors = [];
$old = ['email' => '', 'name' => ''];

function merge_guest_cart_into_user(int $user_id): void {
    $pdo = db();
    $sid = session_id();
    if (!$sid) return;
    $stmt = $pdo->prepare('SELECT product_id, quantity FROM cart_items WHERE session_id = ?');
    $stmt->execute([$sid]);
    $rows = $stmt->fetchAll();
    foreach ($rows as $r) {
        $upd = $pdo->prepare('
            INSERT INTO cart_items (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
        ');
        $upd->execute([$user_id, (int)$r['product_id'], (int)$r['quantity']]);
    }
    $pdo->prepare('DELETE FROM cart_items WHERE session_id = ?')->execute([$sid]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $mode = $_POST['mode'] ?? 'login';
    $email = trim($_POST['email'] ?? '');
    $pwd = $_POST['password'] ?? '';
    $old['email'] = $email;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Неверный email.';
    }

    if ($mode === 'register') {
        $name = trim($_POST['name'] ?? '');
        $old['name'] = $name;
        if (mb_strlen($name) < 2) $errors[] = 'Имя слишком короткое.';
        if (mb_strlen($pwd) < 6) $errors[] = 'Пароль должен быть не менее 6 символов.';
        if (!$errors) {
            $chk = db()->prepare('SELECT id FROM users WHERE email = ?');
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $errors[] = 'Email уже зарегистрирован.';
            } else {
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                $ins = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "user")');
                $ins->execute([$name, $email, $hash]);
                $uid = (int)db()->lastInsertId();
                $_SESSION['user_id'] = $uid;
                merge_guest_cart_into_user($uid);
                flash_set('Аккаунт создан. Добро пожаловать!');
                $next = $_POST['next'] ?: '/profile.php';
                header('Location: ' . $next); exit;
            }
        }
    } else {
        if (!$errors) {
            $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $u = $stmt->fetch();
            if ($u && password_verify($pwd, $u['password_hash'])) {
                $_SESSION['user_id'] = (int)$u['id'];
                merge_guest_cart_into_user((int)$u['id']);
                flash_set('С возвращением, ' . $u['name'] . '!');
                $next = $_POST['next'] ?: '/profile.php';
                header('Location: ' . $next); exit;
            } else {
                $errors[] = 'Неверный email или пароль.';
            }
        }
    }
}

$next = $_GET['next'] ?? $_POST['next'] ?? '';
$page_title = ($mode === 'register' ? 'Регистрация' : 'Вход') . ' — AsiaMart';
$current_page = 'login';
$page_class = 'login-ryokan';
require __DIR__ . '/../includes/header.php';
?>

<main class="login-main">
  <aside class="login-visual">
    <div>
      <div class="login-visual-eye">
        <span class="dot"></span>
        <span>会員 · MEMBERS ONLY · 2026</span>
      </div>
      <h2 class="login-visual-title">Соберите свой <em>азиатский</em> кабинет.</h2>
      <p class="login-visual-lead">История заказов, любимые товары, быстрое оформление и&nbsp;персональные предложения от&nbsp;команды AsiaMart.</p>
    </div>
  </aside>

  <section class="login-form-wrap">
    <div class="login-card">
      <nav class="login-tabs">
        <a href="?mode=login<?= $next ? '&next=' . urlencode($next) : '' ?>" class="<?= $mode === 'login' ? 'is-active' : '' ?>">Вход</a>
        <a href="?mode=register<?= $next ? '&next=' . urlencode($next) : '' ?>" class="<?= $mode === 'register' ? 'is-active' : '' ?>">Регистрация</a>
      </nav>

      <?php if ($mode === 'login'): ?>
        <div class="login-eyebrow"><span class="kanji">入</span> · WELCOME BACK</div>
        <h1 class="login-title">С <em>возвращением</em>.</h1>
        <p class="login-sub">Войдите, чтобы продолжить покупки и&nbsp;увидеть историю заказов.</p>
      <?php else: ?>
        <div class="login-eyebrow"><span class="kanji">新</span> · NEW MEMBER</div>
        <h1 class="login-title"><em>Создайте</em> аккаунт.</h1>
        <p class="login-sub">Регистрация займёт меньше минуты. Никакого спама — обещаем.</p>
      <?php endif; ?>

      <form class="login-form" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="mode" value="<?= e($mode) ?>">
        <input type="hidden" name="next" value="<?= e($next) ?>">

        <?php if ($errors): ?>
          <div class="form-err"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>

        <?php if ($mode === 'register'): ?>
          <div class="form-row">
            <label>Ваше имя</label>
            <input type="text" name="name" value="<?= e($old['name']) ?>" required>
          </div>
        <?php endif; ?>
        <div class="form-row">
          <label>Email</label>
          <input type="email" name="email" value="<?= e($old['email']) ?>" required autofocus>
        </div>
        <div class="form-row">
          <label>Пароль</label>
          <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn-submit">
          <?= $mode === 'login' ? 'Войти' : 'Создать аккаунт' ?>
          <span>→</span>
        </button>
      </form>

      <div class="login-foot">
        <?php if ($mode === 'login'): ?>
          Ещё нет аккаунта? <a href="?mode=register<?= $next ? '&next=' . urlencode($next) : '' ?>">Зарегистрироваться</a>
        <?php else: ?>
          Уже есть аккаунт? <a href="?mode=login<?= $next ? '&next=' . urlencode($next) : '' ?>">Войти</a>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

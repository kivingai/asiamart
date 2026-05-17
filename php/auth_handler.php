<?php
// Обработчик форм авторизации и регистрации.
// Принимает POST с полями action, email, password, name (для регистрации).

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/cart.php';

session_init();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/Bxodandreg.php');
}

if (!csrf_check($_POST['csrf'] ?? null)) {
    flash_set('auth_error', 'Ошибка проверки CSRF-токена. Обновите страницу и попробуйте снова.');
    redirect('/Bxodandreg.php');
}

$mode     = $_POST['mode'] ?? 'login';
$email    = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$name     = trim((string) ($_POST['name'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('auth_error', 'Введите корректный e-mail.');
    redirect('/Bxodandreg.php');
}

if ($mode === 'register') {
    if (mb_strlen($password) < 6) {
        flash_set('auth_error', 'Пароль должен содержать не менее 6 символов.');
        redirect('/Bxodandreg.php');
    }
    if ($name === '') {
        flash_set('auth_error', 'Укажите имя для регистрации.');
        redirect('/Bxodandreg.php');
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        flash_set('auth_error', 'Пользователь с таким e-mail уже существует.');
        redirect('/Bxodandreg.php');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = db()->prepare('INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, "user")');
    $stmt->execute([$email, $hash, $name]);
    $userId = (int) db()->lastInsertId();

    login_user($userId);
    cart_merge_session_to_user($userId);
    redirect('/profile.php');
}

// mode = login
$stmt = db()->prepare('SELECT id, password_hash, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    flash_set('auth_error', 'Неверный e-mail или пароль.');
    redirect('/Bxodandreg.php');
}

login_user((int) $user['id']);
cart_merge_session_to_user((int) $user['id']);

if ($user['role'] === 'admin') {
    redirect('/admin.php');
}
redirect('/profile.php');

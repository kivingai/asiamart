<?php
$page = 'auth';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/helpers.php';

$cfg   = app_config();
$error = flash_get('auth_error');
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Вход — <?= e($cfg['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/global.css" />
  <link rel="stylesheet" href="css/Bxodandreg.css" />
</head>
<body>
  <div class="bg-decor">
    <div class="circle circle-amber"></div>
    <div class="circle circle-red"></div>
    <div class="circle circle-cyan"></div>
  </div>

  <main class="auth-page">
    <div class="auth-card">
      <a href="index.php" class="auth-logo" id="logo">
        <h1><?= e($cfg['name']) ?></h1>
        <span class="logo-underline"></span>
      </a>

      <div class="auth-tabs">
        <button type="button" class="auth-tab active" id="loginToggle">Войти</button>
        <button type="button" class="auth-tab" id="registerToggle">Регистрация</button>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <form id="authForm" method="post" action="php/auth_handler.php" autocomplete="on">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
        <input type="hidden" name="mode" id="modeField" value="login" />

        <div class="form-group" id="nameGroup" hidden>
          <label for="name">Имя</label>
          <input id="name" type="text" name="name" class="form-control" placeholder="Иван Иванов" />
        </div>

        <div class="form-group">
          <label for="email">E-mail</label>
          <input id="email" type="email" name="email" class="form-control" required placeholder="you@example.com" />
        </div>

        <div class="form-group">
          <label for="password">Пароль</label>
          <input id="password" type="password" name="password" class="form-control" required placeholder="••••••" />
        </div>

        <div id="forgotPassword" class="auth-meta">
          <a href="#">Забыли пароль?</a>
        </div>

        <div id="termsGroup" class="auth-meta" hidden>
          <label class="form-check">
            <input type="checkbox" required /> Согласен с условиями использования
          </label>
        </div>

        <button type="submit" class="submit-btn">
          <span class="btn-text">Войти</span>
        </button>

        <p class="auth-switch">
          <span id="switchText">Нет аккаунта?</span>
          <button type="button" id="switchBtn" class="auth-switch-btn">Зарегистрироваться</button>
        </p>

        <p class="auth-demo">
          Демо-доступ:
          <br><strong>admin@asiamart.local</strong> / admin123
          <br><strong>user@asiamart.local</strong> / user12345
        </p>
      </form>
    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    jQuery(function ($) {
      var isLogin = true;
      function applyMode(login) {
        isLogin = login;
        $('#loginToggle').toggleClass('active', login);
        $('#registerToggle').toggleClass('active', !login);
        $('#nameGroup').attr('hidden', login ? '' : null);
        $('#termsGroup').attr('hidden', login ? '' : null);
        $('#forgotPassword').attr('hidden', login ? null : '');
        $('#modeField').val(login ? 'login' : 'register');
        $('.btn-text').text(login ? 'Войти' : 'Создать аккаунт');
        $('#switchText').text(login ? 'Нет аккаунта?' : 'Уже есть аккаунт?');
        $('#switchBtn').text(login ? 'Зарегистрироваться' : 'Войти');
        $('#name').prop('required', !login);
      }
      $('#loginToggle').on('click',    function () { applyMode(true); });
      $('#registerToggle').on('click', function () { applyMode(false); });
      $('#switchBtn').on('click',      function () { applyMode(!isLogin); });
    });
  </script>
</body>
</html>

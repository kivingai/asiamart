<?php
$page = 'support';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/php/cart.php';

$cfg = app_config();
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    // В демо-режиме просто показываем сообщение об успешной отправке.
    // На боевом сервере здесь можно подключить mail() или интеграцию с CRM.
    $sent = true;
}
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Поддержка — <?= e($cfg['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/global.css" />
  <link rel="stylesheet" href="css/sup.css" />
</head>
<body>
  <div class="bg-decor">
    <div class="circle circle-amber"></div>
    <div class="circle circle-red"></div>
    <div class="circle circle-cyan"></div>
  </div>

  <?php include __DIR__ . '/php/partials/header.php'; ?>

  <main class="support-page">
    <div class="container">
      <h2 class="sup-title">Поддержка</h2>
      <p class="sup-lead">Свяжитесь с нами любым удобным способом — мы на связи 24/7.</p>

      <div class="sup-grid">
        <div class="sup-card">
          <h3>Контакты</h3>
          <p><strong>E-mail:</strong> <?= e($cfg['email']) ?></p>
          <p><strong>Телефон:</strong> <?= e($cfg['phone']) ?></p>
          <p><strong>Время работы:</strong> ежедневно с 09:00 до 22:00 по МСК</p>
          <p><strong>Социальные сети:</strong></p>
          <ul>
            <li><a href="https://t.me/" target="_blank" rel="noopener">Telegram-канал</a></li>
            <li><a href="https://vk.com/" target="_blank" rel="noopener">Группа ВКонтакте</a></li>
            <li><a href="https://ok.ru/" target="_blank" rel="noopener">Страница в Одноклассниках</a></li>
          </ul>
        </div>

        <form class="sup-card sup-form" method="post" action="sup.php#contact">
          <h3 id="contact">Форма обратной связи</h3>
          <?php if ($sent): ?>
            <div class="alert alert-success">Спасибо! Ваше сообщение отправлено, мы свяжемся с вами в ближайшее время.</div>
          <?php endif; ?>
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
          <div class="form-group">
            <label>Имя</label>
            <input class="form-control" name="name" required />
          </div>
          <div class="form-group">
            <label>Контакт для ответа (e-mail или телефон)</label>
            <input class="form-control" name="contact" required />
          </div>
          <div class="form-group">
            <label>Сообщение</label>
            <textarea class="form-control" name="message" rows="4" required></textarea>
          </div>
          <button class="btn btn-cta" type="submit">Отправить</button>
        </form>

        <div class="sup-card">
          <h3>Часто задаваемые вопросы</h3>
          <div id="supAccordion">
            <h4>Сколько времени идёт доставка?</h4>
            <p>По Москве и Чебоксарам — 1–2 дня, в другие регионы России — 3–7 дней через СДЭК или Boxberry.</p>
            <h4>Можно ли оплатить при получении?</h4>
            <p>Да, при оформлении заказа выберите оплату наличными — курьер примет деньги на месте.</p>
            <h4>Что делать, если товар оказался повреждён?</h4>
            <p>Свяжитесь с нами в течение 24 часов — мы заменим товар или вернём деньги.</p>
            <h4>Как стать оптовым покупателем?</h4>
            <p>Напишите нам на <?= e($cfg['email']) ?> с темой «Опт» — оформим для вас отдельный прайс-лист.</p>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/php/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <script src="js/site.js"></script>
  <script>
    jQuery(function ($) {
      if ($.fn.accordion) {
        $('#supAccordion').accordion({ collapsible: true, active: false, heightStyle: 'content' });
      }
    });
  </script>
</body>
</html>

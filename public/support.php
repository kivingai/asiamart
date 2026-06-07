<?php
require_once __DIR__ . '/../includes/helpers.php';

$page_title = 'Поддержка — AsiaMart';
$page_description = 'Помощь, FAQ, контакты AsiaMart. Доставка, оплата, возврат.';
$current_page = 'support';
$page_class = 'support-ryokan';

$success = false;
$errors = [];
$form = ['name'=>'', 'email'=>'', 'topic'=>'order', 'message'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($form as $k => $_) {
        $form[$k] = trim($_POST[$k] ?? '');
    }
    if (!$form['name']) $errors[] = 'Укажите имя.';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Неверный email.';
    if (mb_strlen($form['message']) < 10) $errors[] = 'Сообщение должно быть не короче 10 символов.';
    if (!$errors) {
        $log = sprintf("[%s] %s <%s> /%s/: %s\n", date('c'), $form['name'], $form['email'], $form['topic'], $form['message']);
        @file_put_contents(__DIR__ . '/uploads/support.log', $log, FILE_APPEND);
        flash_set('Сообщение отправлено! Мы ответим в течение дня.');
        header('Location: /support.php#form'); exit;
    }
}

if (current_user()) {
    $form['name'] = current_user()['name'];
    $form['email'] = current_user()['email'];
}

require __DIR__ . '/../includes/header.php';
?>

<section class="sup-hero">
  <div class="sup-hero-inner">
    <div class="sup-eyebrow">
      <span class="dot"></span>
      <span>支援 · SUPPORT · 2026</span>
    </div>
    <h1 class="sup-title">Помощь и&nbsp;<em>поддержка<span class="kanji">手</span></em></h1>
    <p class="sup-lead">Ответы на&nbsp;популярные вопросы, контакты и&nbsp;форма обратной связи. Наша команда отвечает в&nbsp;течение часа в&nbsp;рабочее время.</p>
  </div>
</section>

<div class="container">
  <div class="sup-divider">
    <span class="line"></span>
    <span class="label"><span class="num">№01</span> <span class="kanji">連絡</span> КОНТАКТЫ</span>
    <span class="line"></span>
  </div>
</div>

<section class="sup-section">
  <div class="container">
    <div class="contact-grid">
      <div class="contact-card">
        <div class="contact-icon">@</div>
        <div class="contact-eyebrow">Email</div>
        <h3>hello@asiamart.ru</h3>
        <p>Заказы, возвраты, вопросы по&nbsp;ассортименту. Отвечаем за&nbsp;1–2&nbsp;часа.</p>
        <a href="mailto:hello@asiamart.ru">Написать письмо →</a>
      </div>
      <div class="contact-card">
        <div class="contact-icon">電</div>
        <div class="contact-eyebrow">Телефон</div>
        <h3>+7 800 555-АЗИЯ</h3>
        <p>Бесплатный звонок по&nbsp;России. Заказы по&nbsp;телефону и&nbsp;консультации.</p>
        <p class="contact-hours">Пн–Вс · 10:00–22:00 МСК</p>
      </div>
      <div class="contact-card">
        <div class="contact-icon">話</div>
        <div class="contact-eyebrow">Telegram & MAX</div>
        <h3>@asiamart_shop</h3>
        <p>Самый быстрый канал. Пришлите фото, скриншот&nbsp;— разберёмся.</p>
        <a href="#">Открыть чат →</a>
      </div>
    </div>
  </div>
</section>

<div class="container">
  <div class="sup-divider">
    <span class="line"></span>
    <span class="label"><span class="num">№02</span> <span class="kanji">問</span> ЧАСТЫЕ ВОПРОСЫ</span>
    <span class="line"></span>
  </div>
</div>

<section class="sup-section">
  <div class="container">
    <div class="faq-grid">
      <details class="faq-item" open>
        <summary>
          <span class="faq-num">01.</span>
          <span class="faq-q">Сколько идёт доставка по&nbsp;Москве и&nbsp;России?</span>
          <span class="faq-toggle">+</span>
        </summary>
        <div class="faq-a">По&nbsp;Москве и&nbsp;области — курьер в&nbsp;день заказа (при заказе до&nbsp;14:00) или на&nbsp;следующий день. По&nbsp;России&nbsp;— экспресс-доставкой через СДЭК и&nbsp;Почту России&nbsp;за 1–4&nbsp;рабочих дня. Заказы свыше&nbsp;5000&nbsp;₽ — доставка бесплатная.</div>
      </details>
      <details class="faq-item">
        <summary>
          <span class="faq-num">02.</span>
          <span class="faq-q">Как оплатить заказ?</span>
          <span class="faq-toggle">+</span>
        </summary>
        <div class="faq-a">Принимаем карты Visa / Mastercard / МИР, СБП по&nbsp;QR-коду, наличные при получении (только Москва). После оплаты вы получите чек на&nbsp;email.</div>
      </details>
      <details class="faq-item">
        <summary>
          <span class="faq-num">03.</span>
          <span class="faq-q">Можно ли вернуть товар?</span>
          <span class="faq-toggle">+</span>
        </summary>
        <div class="faq-a">Да, в&nbsp;течение&nbsp;14 дней, если товар не&nbsp;использовался и&nbsp;сохранил оригинальную упаковку. Скоропортящиеся товары (свежие морепродукты, охлаждённое мясо) возврату не&nbsp;подлежат, кроме случаев брака или&nbsp;нарушения холодной цепи.</div>
      </details>
      <details class="faq-item">
        <summary>
          <span class="faq-num">04.</span>
          <span class="faq-q">Откуда вы привозите товары?</span>
          <span class="faq-toggle">+</span>
        </summary>
        <div class="faq-a">5&nbsp;стран — Япония, Корея, Китай, Вьетнам, Таиланд. Работаем напрямую с&nbsp;производителями: Kikkoman, Nissin, Meiji, Pocky, Choya, Otsuka и&nbsp;десятками региональных брендов.</div>
      </details>
      <details class="faq-item">
        <summary>
          <span class="faq-num">05.</span>
          <span class="faq-q">Есть&nbsp;ли товары&nbsp;халяль / без глютена / веган?</span>
          <span class="faq-toggle">+</span>
        </summary>
        <div class="faq-a">Да, ассортимент маркируется в&nbsp;карточке товара. Большинство соусов, рисов и&nbsp;круп подходят. По&nbsp;конкретному товару&nbsp;— напишите, мы&nbsp;уточним состав у&nbsp;производителя.</div>
      </details>
      <details class="faq-item">
        <summary>
          <span class="faq-num">06.</span>
          <span class="faq-q">Как часто привозите новинки?</span>
          <span class="faq-toggle">+</span>
        </summary>
        <div class="faq-a">Новинки появляются каждые 2–4&nbsp;недели. Подпишитесь на&nbsp;рассылку или Telegram-канал, чтобы первыми узнавать о&nbsp;новых поступлениях.</div>
      </details>
    </div>
  </div>
</section>

<div class="container">
  <div class="sup-divider">
    <span class="line"></span>
    <span class="label"><span class="num">№03</span> <span class="kanji">便</span> ОБРАТНАЯ СВЯЗЬ</span>
    <span class="line"></span>
  </div>
</div>

<section class="sup-section" id="form">
  <div class="container">
    <div class="sup-form-wrap">
      <div class="sup-form-intro">
        <h3>Напишите&nbsp;<em>нам</em>.</h3>
        <p>Не&nbsp;нашли ответ в&nbsp;FAQ? Заполните форму&nbsp;— ответим на&nbsp;email в&nbsp;течение дня.</p>
        <div class="sup-form-meta">
          <div class="sup-form-meta-item">
            <span class="sup-form-meta-kanji">時</span>
            <span class="sup-form-meta-text">Пн–Вс · 10:00–22:00 МСК</span>
          </div>
          <div class="sup-form-meta-item">
            <span class="sup-form-meta-kanji">速</span>
            <span class="sup-form-meta-text">Среднее время ответа — 47&nbsp;минут</span>
          </div>
          <div class="sup-form-meta-item">
            <span class="sup-form-meta-kanji">秘</span>
            <span class="sup-form-meta-text">Ваши данные не&nbsp;передаются третьим лицам</span>
          </div>
        </div>
      </div>

      <form class="sup-form" method="post" action="#form">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <?php if ($errors): ?>
          <div class="flash flash-err" style="margin-bottom:1.5rem;background:#fef2f2;border-left:3px solid #b91c1c;padding:12px 16px;color:#991b1b;font-family:'DM Sans',sans-serif;font-size:14px"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>

        <div class="sup-form-row">
          <label>Ваше имя</label>
          <input type="text" name="name" value="<?= e($form['name']) ?>" required>
        </div>
        <div class="sup-form-row">
          <label>Email для ответа</label>
          <input type="email" name="email" value="<?= e($form['email']) ?>" required>
        </div>
        <div class="sup-form-row">
          <label>Тема обращения</label>
          <select name="topic">
            <option value="order" <?= $form['topic']==='order'?'selected':'' ?>>Вопрос по заказу</option>
            <option value="product" <?= $form['topic']==='product'?'selected':'' ?>>Вопрос по товару</option>
            <option value="return" <?= $form['topic']==='return'?'selected':'' ?>>Возврат / обмен</option>
            <option value="other" <?= $form['topic']==='other'?'selected':'' ?>>Другое</option>
          </select>
        </div>
        <div class="sup-form-row">
          <label>Сообщение</label>
          <textarea name="message" rows="5" required><?= e($form['message']) ?></textarea>
        </div>
        <button type="submit" class="btn-send">Отправить <span>→</span></button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

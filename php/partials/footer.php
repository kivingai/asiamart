<?php
$cfg = app_config();
?>
<footer class="site-footer">
  <div class="container footer-top">
    <div class="footer-col company">
      <h3><?= e($cfg['name']) ?></h3>
      <span class="logo-underline"></span>
      <p>
        Интернет-магазин премиальных продуктов из стран Азии.
        Мы привозим лапшу, соусы, чай и снеки напрямую от проверенных
        производителей из Японии, Кореи, Китая, Тайланда и Вьетнама.
      </p>
    </div>
    <div class="footer-col nav">
      <h4>Навигация</h4>
      <ul>
        <li><a href="index.php">Главная</a></li>
        <li><a href="katalog.php">Каталог</a></li>
        <li><a href="info.php">О магазине</a></li>
        <li><a href="sup.php">Поддержка</a></li>
        <li><a href="check.php">Доставка и оплата</a></li>
      </ul>
    </div>
    <div class="footer-col contacts">
      <h4>Контакты</h4>
      <a href="mailto:<?= e($cfg['email']) ?>" class="contact-link">
        <div class="icon-box gradient-amber">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" />
            <rect x="2" y="4" width="20" height="16" rx="2" />
          </svg>
        </div>
        <span><?= e($cfg['email']) ?></span>
      </a>
      <p class="social-title">Мы в соцсетях</p>
      <div class="social-links">
        <a href="https://t.me/" class="social-link" aria-label="Telegram" target="_blank" rel="noopener">
          <img src="img/telegram.png" alt="Telegram" />
        </a>
        <a href="https://vk.com/" class="social-link" aria-label="ВКонтакте" target="_blank" rel="noopener">
          <span class="social-text" aria-hidden="true">VK</span>
        </a>
        <a href="https://ok.ru/" class="social-link" aria-label="Одноклассники" target="_blank" rel="noopener">
          <span class="social-text" aria-hidden="true">OK</span>
        </a>
      </div>
    </div>
  </div>
  <div class="container footer-bottom">
    <p>© <?= date('Y') ?> <?= e($cfg['name']) ?>. Все права защищены.</p>
    <div class="legal-links">
      <a href="#">Политика конфиденциальности</a>
      <a href="#">Условия использования</a>
    </div>
  </div>
</footer>

<nav class="mobile-nav">
  <div class="mobile-nav-inner">
    <a href="index.php" class="mobile-item <?= ($page ?? '') === 'home' ? 'active' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
        <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
      </svg>
      <span>Главная</span>
    </a>
    <a href="katalog.php" class="mobile-item <?= ($page ?? '') === 'catalog' ? 'active' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M16 10a4 4 0 0 1-8 0"></path>
        <path d="M3.103 6.034h17.794"></path>
        <path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"></path>
      </svg>
      <span>Каталог</span>
    </a>
    <a href="cart.php" class="mobile-item <?= ($page ?? '') === 'cart' ? 'active' : '' ?>">
      <svg class="icon" viewBox="-5 -3 35 27" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="8" cy="21" r="1"></circle>
        <circle cx="19" cy="21" r="1"></circle>
        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
      </svg>
      <span>Корзина</span>
    </a>
    <a href="<?= current_user() ? 'profile.php' : 'Bxodandreg.php' ?>" class="mobile-item <?= ($page ?? '') === 'profile' ? 'active' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
      <span>Профиль</span>
    </a>
  </div>
</nav>

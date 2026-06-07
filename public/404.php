<?php
require_once __DIR__ . '/../includes/helpers.php';
http_response_code(404);

$page_title = '404 — Страница не найдена · AsiaMart';
$current_page = '';
$page_class = 'error-ryokan';
require __DIR__ . '/../includes/header.php';
?>

<main class="err-main">
  <div class="err-stamp-bg" aria-hidden="true">朱</div>
  <div class="err-inner">
    <div class="err-eyebrow">
      <span class="dot"></span>
      <span>迷子 · LOST PAGE · ERROR</span>
    </div>
    <div class="err-code"><em>4</em><span class="err-kanji">空</span><em>4</em></div>
    <h1 class="err-title">Страница <em>потерялась</em> в&nbsp;Азии.</h1>
    <p class="err-lead">Возможно, она переехала или&nbsp;никогда не&nbsp;существовала. Попробуйте начать с&nbsp;главной или&nbsp;откройте каталог.</p>
    <div class="err-actions">
      <a href="/" class="err-btn-primary">На главную <span>→</span></a>
      <a href="/katalog.php" class="err-btn-ghost">Открыть каталог</a>
      <a href="/support.php" class="err-btn-ghost">Поддержка</a>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

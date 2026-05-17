// Глобальные скрипты сайта AsiaMart.
// Скрытие/закрепление шапки при прокрутке, обновление счётчика корзины,
// jQuery UI tooltip-подсказки.
(function () {
  function toggleHeader() {
    var header = document.getElementById('siteHeader');
    if (!header) return;
    if (window.scrollY > 20) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }
  document.addEventListener('DOMContentLoaded', toggleHeader);
  window.addEventListener('scroll', toggleHeader);

  // Обновляет бейдж корзины.
  window.updateCartBadge = function (count) {
    var badge = document.getElementById('cartBadge');
    if (!badge) return;
    if (!count || count <= 0) {
      badge.setAttribute('hidden', '');
      badge.textContent = '0';
    } else {
      badge.removeAttribute('hidden');
      badge.textContent = String(count);
    }
  };

  // Универсальный AJAX-вызов корзины.
  // action: 'add' | 'update' | 'remove' | 'state' | 'clear'
  window.cartRequest = function (action, productId, qty) {
    var data = { action: action };
    if (productId !== undefined && productId !== null) data.product_id = productId;
    if (qty !== undefined && qty !== null) data.qty = qty;
    return jQuery.post('php/cart.php', data, null, 'json');
  };

  jQuery(function ($) {
    if ($.fn.tooltip) {
      $('[title]').tooltip({ track: true });
    }
  });
})();

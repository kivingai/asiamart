// Скрипт страницы корзины.
// Изменение количества, удаление, очистка — всё через AJAX и перезагрузка значений.
jQuery(function ($) {
  function moneyRu(value) {
    return Math.round(value).toLocaleString('ru-RU') + '\u20BD';
  }

  function applyState(state) {
    if (!state) return;
    window.updateCartBadge(state.count);
    if (!state.items.length) {
      window.location.reload();
      return;
    }
    var map = {};
    state.items.forEach(function (it) { map[it.product_id] = it; });
    $('.cart-item').each(function () {
      var $row = $(this);
      var pid = parseInt($row.attr('data-product-id'), 10);
      var it = map[pid];
      if (!it) {
        $row.remove();
        return;
      }
      $row.find('.qty-input').val(it.qty);
      $row.find('.ci-sum').text(moneyRu(it.qty * it.price));
    });
    $('#sumItems').text(moneyRu(state.total));
    // Перезагрузка для обновления доставки / итога
    // Достаточно простой пересборки на клиенте — но безопаснее перерендерить через reload
  }

  $(document).on('click', '.qty-btn', function () {
    var $row = $(this).closest('.cart-item');
    var pid = parseInt($row.attr('data-product-id'), 10);
    var $input = $row.find('.qty-input');
    var qty = parseInt($input.val(), 10) || 1;
    if ($(this).data('action') === 'inc') qty++;
    else qty = Math.max(1, qty - 1);
    $input.val(qty);
    window.cartRequest('update', pid, qty).done(function (state) {
      applyState(state);
      window.location.reload();
    });
  });

  $(document).on('change', '.qty-input', function () {
    var $row = $(this).closest('.cart-item');
    var pid = parseInt($row.attr('data-product-id'), 10);
    var qty = Math.max(1, parseInt($(this).val(), 10) || 1);
    $(this).val(qty);
    window.cartRequest('update', pid, qty).done(function () {
      window.location.reload();
    });
  });

  $(document).on('click', '.ci-remove', function () {
    var $row = $(this).closest('.cart-item');
    var pid = parseInt($row.attr('data-product-id'), 10);
    window.cartRequest('remove', pid).done(function () {
      window.location.reload();
    });
  });

  $('#cartClearBtn').on('click', function () {
    if (!confirm('Очистить корзину?')) return;
    window.cartRequest('clear').done(function () {
      window.location.reload();
    });
  });
});

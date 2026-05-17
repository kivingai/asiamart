// Скрипт каталога: AJAX «Добавить в корзину», автокомплит поиска (jQuery UI).

jQuery(function ($) {
  // Кнопка «Добавить в корзину»
  $(document).on('click', '.product-add', function (e) {
    e.preventDefault();
    var $card = $(this).closest('.product-card');
    var pid = parseInt($card.attr('data-product-id'), 10);
    if (!pid) return;
    window.cartRequest('add', pid, 1)
      .done(function (resp) {
        if (resp && resp.ok) {
          window.updateCartBadge(resp.count);
          showToast('Товар добавлен в корзину');
        }
      })
      .fail(function () {
        alert('Не удалось добавить товар. Попробуйте позже.');
      });
  });

  // Подробнее: jQuery UI dialog
  $(document).on('click', '[data-action="details"]', function (e) {
    e.preventDefault();
    var $card = $(this).closest('.product-card');
    var title = $card.find('.product-info h4').text();
    var desc  = $card.find('.product-info p').text();
    var price = $card.find('.product-price').text();
    var country = $card.find('.badge-country').text();
    $('#productDialog').attr('title', title);
    $('#productDialogBody').html(
      '<p><strong>Страна:</strong> ' + country + '</p>' +
      '<p>' + desc + '</p>' +
      '<p><strong>Цена:</strong> ' + price + '</p>'
    );
    $('#productDialog').dialog({
      modal: true,
      width: 420,
      buttons: {
        'В корзину': function () {
          var pid = parseInt($card.attr('data-product-id'), 10);
          window.cartRequest('add', pid, 1).done(function (resp) {
            if (resp && resp.ok) {
              window.updateCartBadge(resp.count);
              showToast('Товар добавлен в корзину');
            }
          });
          $(this).dialog('close');
        },
        'Закрыть': function () {
          $(this).dialog('close');
        }
      }
    });
  });

  // Автокомплит поиска: собираем заголовки из карточек и предлагаем подсказки.
  var titles = [];
  $('.product-card .product-info h4').each(function () {
    titles.push($(this).text().trim());
  });
  if ($.fn.autocomplete) {
    $('#searchInput').autocomplete({
      source: titles,
      minLength: 2,
      select: function (event, ui) {
        $('#searchInput').val(ui.item.value);
        $('#searchForm').trigger('submit');
      }
    });
  }

  // Простой тост
  function showToast(text) {
    var $t = $('<div class="toast-msg"></div>').text(text);
    $('body').append($t);
    setTimeout(function () { $t.addClass('show'); }, 10);
    setTimeout(function () { $t.removeClass('show'); setTimeout(function () { $t.remove(); }, 300); }, 2400);
  }
});

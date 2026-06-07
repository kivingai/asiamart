document.addEventListener('DOMContentLoaded', () => {
  const cartStore = window.CartStore;
  const cartContainer = document.getElementById('cartContainer');

  if (!cartStore || !cartContainer) return;

  const DELIVERY_THRESHOLD = 5000;
  const DELIVERY_PRICE = 490;

  const categoryLabelMap = {
    wine: 'Вино',
    cheese: 'Сыр',
    meat: 'Мясо',
    grocery: 'Бакалея'
  };

  const getCategoryLabel = (value) => {
    const key = String(value ?? '').trim().toLowerCase();
    return categoryLabelMap[key] || value || 'Товар';
  };

  const getDeliveryPrice = (subtotal) => {
    if (subtotal <= 0) return 0;
    return subtotal >= DELIVERY_THRESHOLD ? 0 : DELIVERY_PRICE;
  };

  const getItemsWord = (count) => {
    const abs = Math.abs(count) % 100;
    const last = abs % 10;

    if (abs > 10 && abs < 20) return 'товаров';
    if (last > 1 && last < 5) return 'товара';
    if (last === 1) return 'товар';
    return 'товаров';
  };

  const createEmptyMarkup = () => `
    <div class="empty-cart">
      <div class="empty-icon">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="8" cy="21" r="1"></circle>
          <circle cx="19" cy="21" r="1"></circle>
          <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
        </svg>
      </div>
      <h2>Корзина пока пустая</h2>
      <p>Добавьте товары из каталога, чтобы оформить заказ.</p>
      <a href="katalog.html" class="continue-btn" data-action="continue">
        <span>Перейти в каталог</span>
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14"></path>
          <path d="m12 5 7 7-7 7"></path>
        </svg>
      </a>
    </div>
  `;

  const createItemMarkup = (item) => `
    <article class="cart-item" data-item-id="${item.id}">
      <button class="remove-btn" type="button" data-action="remove" aria-label="Удалить товар">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
          <path d="M10 11v6"></path>
          <path d="M14 11v6"></path>
        </svg>
      </button>

      <div class="item-image">
        <img src="${item.image}" alt="${item.name}" loading="lazy" decoding="async" />
      </div>

      <div class="item-info">
        <h3>${item.name}</h3>
        <p>${getCategoryLabel(item.category)}</p>

        <div class="item-bottom">
          <div class="quantity-controls">
            <button type="button" data-action="decrease" ${item.quantity <= 1 ? 'disabled' : ''} aria-label="Уменьшить количество">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                <path d="M5 12h14"></path>
              </svg>
            </button>
            <span class="quantity">${item.quantity}</span>
            <button type="button" data-action="increase" aria-label="Увеличить количество">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                <path d="M12 5v14"></path>
                <path d="M5 12h14"></path>
              </svg>
            </button>
          </div>

          <div class="price-info">
            <p class="unit-price">${cartStore.formatPrice(item.price)} за шт.</p>
            <p class="total-price">${cartStore.formatPrice(item.price * item.quantity)}</p>
          </div>
        </div>
      </div>
    </article>
  `;

  const createFilledMarkup = (items) => {
    const totalQuantity = items.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const delivery = getDeliveryPrice(subtotal);
    const total = subtotal + delivery;

    return `
      <div class="cart-with-items">
        <section class="cart-items-section">
          <div class="cart-header-row">
            <h2>Товары в корзине</h2>
            <span>${totalQuantity} ${getItemsWord(totalQuantity)}</span>
            <button class="add-btn" type="button" data-action="clear">Очистить корзину</button>
          </div>

          <div class="cart-list">
            ${items.map((item) => createItemMarkup(item)).join('')}
          </div>
        </section>

        <aside class="cart-summary">
          <h3>Итог заказа</h3>

          <div class="summary-list">
            <div class="summary-row">
              <span>Товары</span>
              <span class="value">${cartStore.formatPrice(subtotal)}</span>
            </div>
            <div class="summary-row">
              <span>Доставка</span>
              <span class="value ${delivery === 0 ? 'free' : ''}">
                ${delivery === 0 ? 'Бесплатно' : cartStore.formatPrice(delivery)}
              </span>
            </div>
          </div>

          <div class="total-row">
            <span>Итого</span>
            <span>${cartStore.formatPrice(total)}</span>
          </div>

          <button class="checkout-btn" type="button" data-action="checkout">
            <span class="btn-content">
              <span>Оформить заказ</span>
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"></path>
                <path d="m12 5 7 7-7 7"></path>
              </svg>
            </span>
            <span class="overlay"></span>
          </button>

          <p class="secure-info">Безопасная оплата и защита персональных данных</p>
        </aside>
      </div>
    `;
  };

  const renderCart = () => {
    const items = cartStore.getItems();
    cartContainer.innerHTML = items.length ? createFilledMarkup(items) : createEmptyMarkup();
  };

  cartContainer.addEventListener('click', (event) => {
    const actionElement = event.target.closest('[data-action]');
    if (!actionElement) return;

    const action = actionElement.getAttribute('data-action');
    if (!action) return;

    if (action === 'continue') {
      window.location.href = 'katalog.html';
      return;
    }

    if (action === 'clear') {
      if (window.confirm('Очистить корзину полностью?')) {
        cartStore.clear();
        renderCart();
      }
      return;
    }

    if (action === 'checkout') {
      window.location.href = 'check.html';
      return;
    }

    const itemElement = actionElement.closest('[data-item-id]');
    if (!itemElement) return;
    const itemId = itemElement.getAttribute('data-item-id');
    if (!itemId) return;

    if (action === 'remove') {
      cartStore.removeItem(itemId);
      renderCart();
      return;
    }

    const currentQty = cartStore.getItemQuantity(itemId);
    if (action === 'increase') {
      cartStore.setQuantity(itemId, currentQty + 1);
      renderCart();
      return;
    }

    if (action === 'decrease') {
      cartStore.setQuantity(itemId, Math.max(1, currentQty - 1));
      renderCart();
    }
  });

  renderCart();
});

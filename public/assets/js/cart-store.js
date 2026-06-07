// AsiaMart cart store: server-backed cart via /api/cart.php.
(function() {
  const normalizeProduct = (productOrId) => {
    if (typeof productOrId === 'object' && productOrId !== null) {
      return productOrId;
    }
    return { id: productOrId, name: '' };
  };

  async function post(action, productId, qty) {
    const res = await fetch('/api/cart.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, product_id: productId, qty })
    });

    const data = await res.json().catch(() => ({
      ok: false,
      error: 'bad response'
    }));

    if (!res.ok && data.ok !== true) {
      data.ok = false;
    }

    return data;
  }

  function ensureHeaderBadge() {
    let badge = document.querySelector('.ry-cart-bubble');
    if (badge) return badge;

    const cartLink = document.querySelector('.ry-cart');
    if (!cartLink) return null;

    badge = document.createElement('span');
    badge.className = 'ry-cart-bubble';
    badge.setAttribute('data-cart-count', '');
    cartLink.appendChild(badge);
    return badge;
  }

  function updateBadge(count) {
    const nextCount = Math.max(0, Number(count) || 0);
    const selectors = '[data-cart-count], .ry-cart-bubble, .cart-badge';
    let badges = Array.from(document.querySelectorAll(selectors));

    if (nextCount > 0 && badges.length === 0) {
      const created = ensureHeaderBadge();
      if (created) badges = [created];
    }

    badges.forEach((badge) => {
      badge.textContent = String(nextCount);
      badge.style.display = nextCount > 0 ? '' : 'none';
      badge.setAttribute('aria-label', `${nextCount} товаров в корзине`);
    });
  }

  function toast(text) {
    let t = document.getElementById('asiamart-toast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'asiamart-toast';
      t.className = 'asiamart-toast';
      document.body.appendChild(t);
    }
    t.textContent = text;
    t.classList.add('show');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 2200);
  }

  async function add(productOrId, qty = 1) {
    const product = normalizeProduct(productOrId);
    const id = Number(product.id);
    const quantity = Math.max(1, Number(qty) || 1);

    if (!Number.isFinite(id) || id <= 0) return false;

    const result = await post('add', id, quantity);
    if (result.ok) {
      updateBadge(result.count);
      const name = product.name ? `«${product.name}» ` : '';
      toast(`${name}добавлен в корзину`);
      return true;
    }

    toast('Не удалось добавить товар');
    return false;
  }

  window.CartStore = {
    add,
    addItem(product, qty = 1) {
      return add(product, qty);
    },
    async setQuantity(productId, qty) {
      const result = await post('set', Number(productId), Math.max(0, Number(qty) || 0));
      if (result.ok) updateBadge(result.count);
      return result;
    },
    async removeItem(productId) {
      const result = await post('remove', Number(productId), 0);
      if (result.ok) updateBadge(result.count);
      return result;
    },
    getItemQuantity() {
      return 1;
    },
    updateBadge
  };
})();

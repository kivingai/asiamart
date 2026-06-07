// AsiaMart cart-store: серверная корзина через /api/cart.php
// Заменяет localStorage-вариант оригинала. Эмулирует тот же интерфейс,
// поэтому product-page.js и katalog.js работают без изменений.

(function() {
  async function post(action, productId, qty) {
    const res = await fetch('/api/cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, product_id: productId, qty })
    });
    return res.json();
  }

  // Обновить бейдж количества в шапке (если есть)
  function updateBadge(count) {
    document.querySelectorAll('[data-cart-count]').forEach(el => {
      if (count > 0) {
        el.textContent = count;
        el.style.display = '';
      } else {
        el.style.display = 'none';
      }
    });
  }

  // Маленький toast
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

  window.CartStore = {
    async addItem(product, qty = 1) {
      const id = Number(product.id);
      if (!Number.isFinite(id) || id <= 0) return false;
      const r = await post('add', id, qty);
      if (r.ok) {
        updateBadge(r.count);
        toast(`✓ «${product.name}» добавлен в корзину`);
        return true;
      }
      toast('Не удалось добавить товар');
      return false;
    },
    async setQuantity(productId, qty) {
      const r = await post('set', Number(productId), Math.max(0, Number(qty) || 0));
      if (r.ok) updateBadge(r.count);
      return r;
    },
    async removeItem(productId) {
      const r = await post('remove', Number(productId), 0);
      if (r.ok) updateBadge(r.count);
      return r;
    },
    getItemQuantity() { return 1; }, // совместимость с оригинальным product-page.js
  };
})();

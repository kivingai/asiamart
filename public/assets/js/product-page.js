document.addEventListener('DOMContentLoaded', () => {
  const cartStore = window.CartStore;
  if (!cartStore) return;

  const parsePrice = (rawText) => {
    const digits = String(rawText ?? '').replace(/[^\d]/g, '');
    const parsed = Number.parseInt(digits, 10);
    return Number.isFinite(parsed) ? parsed : 0;
  };

  const buildFallbackProduct = () => {
    const title = document.querySelector('.product-title')?.textContent?.trim();
    const category = document.querySelector('.product-category')?.textContent?.trim();
    const priceText = document.querySelector('.product-price')?.textContent ?? '';
    const image = document.querySelector('.image-card img')?.getAttribute('src') ?? '';
    const pathId = window.location.pathname.split('/').pop()?.replace('.html', '') || 'product';

    if (!title || !image) return null;

    return {
      id: pathId,
      name: title,
      category,
      image,
      price: parsePrice(priceText)
    };
  };

  const flashAddedState = (button, qty) => {
    const label = button.querySelector('span');
    if (!label) return;

    const originalText = label.textContent;
    label.textContent = `Добавлено (${qty})`;
    button.disabled = true;

    window.setTimeout(() => {
      label.textContent = originalText;
      button.disabled = false;
    }, 1200);
  };

  const buttons = document.querySelectorAll('.add-to-cart');
  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      const fallback = buildFallbackProduct();

      const product = {
        id: button.getAttribute('data-product-id') || fallback?.id,
        name: button.getAttribute('data-product-name') || fallback?.name,
        category: button.getAttribute('data-product-category') || fallback?.category,
        image: button.getAttribute('data-product-image') || fallback?.image,
        price:
          Number(button.getAttribute('data-product-price')) ||
          fallback?.price ||
          0
      };

      if (!product.id || !product.name || !product.image || product.price <= 0) {
        alert('Не удалось добавить товар в корзину.');
        return;
      }

      cartStore.addItem(product, 1);
      const qty = cartStore.getItemQuantity(String(product.id));
      flashAddedState(button, qty);
    });
  });
});

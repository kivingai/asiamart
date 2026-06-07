document.addEventListener('DOMContentLoaded', () => {
  const filterPanel = document.getElementById('filterPanel');
  const filterToggleBtn = document.getElementById('filterToggleBtn');
  const filterCloseBtn = document.getElementById('filterCloseBtn');
  const categoryList = document.getElementById('categoryList');
  const searchInput = document.getElementById('searchInput');
  const productGrid = document.getElementById('productGrid');
  const noProducts = document.getElementById('noProducts');
  const cartStore = window.CartStore;

  if (!categoryList || !productGrid) return;

  const products = [
    {
      id: 1,
      name: 'Chateau Margaux 2015',
      category: 'wine',
      price: 12500,
      rating: 4.9,
      image: 'img/cat-wine.jpg',
      country: '🇫🇷 Франция',
      description: 'Изысканное красное вино премиум класса'
    },
    {
      id: 2,
      name: 'Barolo DOCG 2018',
      category: 'wine',
      price: 8900,
      rating: 5.0,
      image: 'img/cat-wine.jpg',
      country: '🇮🇹 Италия',
      description: 'Элегантное вино из региона Пьемонт'
    },
    {
      id: 3,
      name: 'Chateau Petrus 2016',
      category: 'wine',
      price: 45000,
      rating: 5.0,
      image: 'img/cat-wine.jpg',
      country: '🇫🇷 Франция',
      description: 'Легендарное вино из Помероля'
    },
    {
      id: 4,
      name: 'Tignanello 2017',
      category: 'wine',
      price: 15800,
      rating: 4.8,
      image: 'img/cat-wine.jpg',
      country: '🇮🇹 Италия',
      description: 'Супертосканское вино мирового класса'
    },
    {
      id: 5,
      name: 'Пармезан Реджано 24 мес.',
      category: 'cheese',
      price: 2890,
      rating: 5.0,
      image: 'img/cat-cheese.jpg',
      country: '🇮🇹 Италия',
      description: 'Выдержанный пармезан премиум качества'
    },
    {
      id: 6,
      name: 'Камамбер де Норманди',
      category: 'cheese',
      price: 1450,
      rating: 4.8,
      image: 'img/cat-cheese.jpg',
      country: '🇫🇷 Франция',
      description: 'Мягкий сыр с белой плесенью'
    },
    {
      id: 7,
      name: 'Хамон Иберико',
      category: 'meat',
      price: 4200,
      rating: 5.0,
      image: 'img/cat-meat.jpg',
      country: '🇪🇸 Испания',
      description: 'Элитный испанский хамон'
    },
    {
      id: 8,
      name: 'Прошутто ди Парма',
      category: 'meat',
      price: 3500,
      rating: 4.9,
      image: 'img/cat-meat.jpg',
      country: '🇮🇹 Италия',
      description: 'Итальянская вяленая ветчина'
    },
    {
      id: 9,
      name: 'Оливковое масло Extra Virgin',
      category: 'grocery',
      price: 1890,
      rating: 4.7,
      image: 'img/cat-grocery.jpg',
      country: '🇬🇷 Греция',
      description: 'Первого холодного отжима'
    },
    {
      id: 10,
      name: 'Трюфельная паста',
      category: 'grocery',
      price: 2400,
      rating: 4.9,
      image: 'img/cat-grocery.jpg',
      country: '🇮🇹 Италия',
      description: 'Паста с черным трюфелем'
    },
    {
      id: 11,
      name: 'Бальзамический уксус 12 лет',
      category: 'grocery',
      price: 3200,
      rating: 5.0,
      image: 'img/cat-grocery.jpg',
      country: '🇮🇹 Италия',
      description: 'Выдержанный бальзамик из Модены'
    },
    {
      id: 12,
      name: 'Морская соль с трюфелем',
      category: 'grocery',
      price: 890,
      rating: 4.6,
      image: 'img/cat-grocery.jpg',
      country: '🇫🇷 Франция',
      description: 'Деликатесная соль с кусочками трюфеля'
    }
  ];

  const categories = [
    { id: 'all', name: 'Все товары', icon: '🛒' },
    { id: 'cheese', name: 'Сыр', icon: '🧀' },
    { id: 'wine', name: 'Вино', icon: '🍷' },
    { id: 'meat', name: 'Мясо', icon: '🥩' },
    { id: 'grocery', name: 'Бакалея', icon: '🫘' }
  ];

  let currentCategory = 'all';
  let searchQuery = '';
  let searchTimer = null;
  const categoryPageById = {
    wine: 'wine.html',
    cheese: 'cheese.html',
    meat: 'meat.html',
    grocery: 'grocery.html'
  };

  const productsById = new Map(
    products.map((product) => [
      String(product.id),
      {
        ...product,
        searchName: product.name.toLowerCase(),
        productPage: categoryPageById[product.category] || 'katalog.html'
      }
    ])
  );

  const setFilterPanelOpen = (isOpen) => {
    filterPanel?.classList.toggle('open', isOpen);
  };

  const renderCategories = () => {
    categoryList.innerHTML = categories
      .map(
        (cat) => `
      <button class="category-btn ${cat.id === currentCategory ? 'active' : ''}" data-id="${cat.id}" type="button">
        <span class="emoji">${cat.icon}</span>
        <span>${cat.name}</span>
      </button>`
      )
      .join('');
  };

  const renderProducts = () => {
    const filteredProducts = Array.from(productsById.values()).filter((product) => {
      const matchCategory =
        currentCategory === 'all' || product.category === currentCategory;
      const matchSearch = product.searchName.includes(searchQuery);
      return matchCategory && matchSearch;
    });

    if (filteredProducts.length === 0) {
      noProducts?.classList.remove('hidden');
      productGrid.innerHTML = '';
      return;
    }

    noProducts?.classList.add('hidden');

    productGrid.innerHTML = filteredProducts
      .map(
        (product) => `
      <div class="product-card" data-product-id="${product.id}" role="link" tabindex="0">
        <div class="product-image">
          <img src="${product.image}" alt="${product.name}" loading="lazy" decoding="async" />
          <div class="badge-country">${product.country}</div>
          <div class="badge-rating">
            ${product.rating.toFixed(1)}
            <svg class="star-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round">
              <polygon points="12 2 15 8.9 22 9.3 17 14 18.4 21 12 17.3 5.6 21 7 14 2 9.3 9 8.9 12 2" />
            </svg>
          </div>
          <div class="product-overlay">
            <button class="overlay-button" data-action="details" type="button">Подробнее</button>
          </div>
        </div>
        <div class="product-info">
          <h4>${product.name}</h4>
          <p>${product.description}</p>
        </div>
        <div class="product-bottom">
          <span class="product-price">${product.price.toLocaleString('ru-RU')}₽</span>
          <button class="product-add" aria-label="Добавить в корзину" data-action="add" type="button">
            <svg class="icon" viewBox="-5 -3 35 27" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="8" cy="21" r="1"></circle>
              <circle cx="19" cy="21" r="1"></circle>
              <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
            </svg>
          </button>
        </div>
      </div>`
      )
      .join('');
  };

  categoryList.addEventListener('click', (event) => {
    const button = event.target.closest('.category-btn');
    if (!button) return;

    const nextCategory = button.getAttribute('data-id');
    if (!nextCategory || nextCategory === currentCategory) return;

    currentCategory = nextCategory;
    renderCategories();
    renderProducts();

    if (window.innerWidth < 1024) {
      setFilterPanelOpen(false);
    }
  });

  productGrid.addEventListener('click', (event) => {
    const card = event.target.closest('[data-product-id]');
    if (!card) return;

    const product = productsById.get(card.getAttribute('data-product-id'));
    if (!product) return;

    const actionButton = event.target.closest('[data-action]');
    const action = actionButton?.getAttribute('data-action');

    if (action === 'add') {
      if (!cartStore) {
        alert('Корзина временно недоступна. Обновите страницу.');
        return;
      }

      cartStore.addItem({
        id: String(product.id),
        name: product.name,
        image: product.image,
        category: product.category,
        price: product.price
      });

      const qty = cartStore.getItemQuantity(String(product.id));
      alert(`Товар "${product.name}" добавлен в корзину (${qty} шт.).`);
      return;
    }

    window.location.href = product.productPage;
  });

  productGrid.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' && event.key !== ' ') return;

    const card = event.target.closest('.product-card[data-product-id]');
    if (!card || event.target.closest('[data-action="add"]')) return;

    const product = productsById.get(card.getAttribute('data-product-id'));
    if (!product) return;

    event.preventDefault();
    window.location.href = product.productPage;
  });

  searchInput?.addEventListener('input', (event) => {
    const value = event.target.value.trim().toLowerCase();
    if (value === searchQuery) return;

    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
      searchQuery = value;
      renderProducts();
    }, 120);
  });

  filterToggleBtn?.addEventListener('click', () => setFilterPanelOpen(true));
  filterCloseBtn?.addEventListener('click', () => setFilterPanelOpen(false));

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setFilterPanelOpen(false);
    }
  });

  renderCategories();
  renderProducts();
});


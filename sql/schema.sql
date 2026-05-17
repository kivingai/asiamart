-- AsiaMart database schema (MySQL 5.7+, utf8mb4)
-- Создание базы данных и пользователя — выполнять под root:
--   CREATE DATABASE IF NOT EXISTS asiamart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   CREATE USER IF NOT EXISTS 'asiamart'@'localhost' IDENTIFIED BY 'asiamart_pwd';
--   GRANT ALL ON asiamart.* TO 'asiamart'@'localhost';
--   FLUSH PRIVILEGES;
-- Затем подключиться к asiamart и выполнить этот файл.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name          VARCHAR(120) NOT NULL,
    phone         VARCHAR(30)  DEFAULT NULL,
    role          ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    slug  VARCHAR(60)  NOT NULL UNIQUE,
    name  VARCHAR(120) NOT NULL,
    icon  VARCHAR(20)  DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    sort  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    category_id  INT NOT NULL,
    title        VARCHAR(180) NOT NULL,
    slug         VARCHAR(180) NOT NULL UNIQUE,
    country      VARCHAR(120) NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    rating       DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    description  TEXT,
    image        VARCHAR(255) DEFAULT NULL,
    stock        INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_products_category (category_id),
    INDEX idx_products_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cart_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    qty        INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_user_product (user_id, product_id),
    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    status     ENUM('new','paid','shipping','done','canceled') NOT NULL DEFAULT 'new',
    total      DECIMAL(10,2) NOT NULL,
    address    VARCHAR(255) NOT NULL,
    delivery   ENUM('courier','pickup') NOT NULL DEFAULT 'courier',
    payment    ENUM('card','cash') NOT NULL DEFAULT 'card',
    phone      VARCHAR(30) DEFAULT NULL,
    note       TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    product_id INT NOT NULL,
    title      VARCHAR(180) NOT NULL,
    qty        INT NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- Тестовые данные
-- =====================================================================

INSERT INTO categories (slug, name, icon, image, sort) VALUES
    ('noodles', 'Лапша',            '🍜', 'img/cat-meat.png',    1),
    ('sauces',  'Соусы и приправы', '🥡', 'img/cat-grocery.png', 2),
    ('tea',     'Чай и напитки',    '🍵', 'img/cat-wine.png',    3),
    ('snacks',  'Снеки и сладости', '🍡', 'img/cat-cheese.png',  4);

INSERT INTO products (category_id, title, slug, country, price, rating, description, image, stock) VALUES
    -- Лапша
    ((SELECT id FROM categories WHERE slug='noodles'), 'Рамэн Nissin Tonkotsu',          'noodles-nissin-tonkotsu', '🇯🇵 Япония',  220.00, 4.9, 'Классическая лапша рамэн со вкусом тонкоцу — крем-бульон на свиных косточках.',       'img/cat-meat.png', 50),
    ((SELECT id FROM categories WHERE slug='noodles'), 'Удон Shimadaya',                 'noodles-udon-shimadaya',  '🇯🇵 Япония',  280.00, 4.7, 'Толстая пшеничная лапша удон, готовится за 1 минуту.',                                'img/cat-meat.png', 60),
    ((SELECT id FROM categories WHERE slug='noodles'), 'Лапша Соба гречневая Hakubaku',  'noodles-soba-hakubaku',   '🇯🇵 Япония',  320.00, 4.8, 'Тонкая гречневая лапша, традиционно подаётся холодной с соевым соусом.',              'img/cat-meat.png', 40),
    ((SELECT id FROM categories WHERE slug='noodles'), 'Рисовая лапша Pho',              'noodles-rice-pho',        '🇻🇳 Вьетнам', 180.00, 4.6, 'Тонкая рисовая лапша для супа Фо.',                                                   'img/cat-meat.png', 80),
    ((SELECT id FROM categories WHERE slug='noodles'), 'Лапша острая Samyang Buldak',    'noodles-samyang-buldak',  '🇰🇷 Корея',   190.00, 4.9, 'Знаменитая корейская острая лапша — для любителей огненного вкуса.',                  'img/cat-meat.png', 100),
    ((SELECT id FROM categories WHERE slug='noodles'), 'Фунчоза стеклянная',             'noodles-funchoza',        '🇨🇳 Китай',   140.00, 4.5, 'Тонкая прозрачная лапша из крахмала маша.',                                          'img/cat-meat.png', 70),
    -- Соусы и приправы
    ((SELECT id FROM categories WHERE slug='sauces'),  'Соевый соус Kikkoman',           'sauce-kikkoman',          '🇯🇵 Япония',  390.00, 4.9, 'Натурального брожения, классика японской кухни.',                                    'img/cat-grocery.png', 60),
    ((SELECT id FROM categories WHERE slug='sauces'),  'Паста мисо Hikari белая',        'sauce-miso-hikari',       '🇯🇵 Япония',  520.00, 4.8, 'Светлая мисо-паста для классического мисо-супа.',                                    'img/cat-grocery.png', 40),
    ((SELECT id FROM categories WHERE slug='sauces'),  'Соус устричный Lee Kum Kee',     'sauce-oyster-lkk',        '🇨🇳 Китай',   430.00, 4.7, 'Густой соус для жарки и заправки.',                                                  'img/cat-grocery.png', 50),
    ((SELECT id FROM categories WHERE slug='sauces'),  'Соус Sriracha острый',           'sauce-sriracha',          '🇹🇭 Тайланд', 280.00, 4.8, 'Перчёный соус из красного чили и чеснока — для всего.',                              'img/cat-grocery.png', 90),
    ((SELECT id FROM categories WHERE slug='sauces'),  'Паста гочуджан',                 'sauce-gochujang',         '🇰🇷 Корея',   460.00, 4.7, 'Острая ферментированная паста из красного перца.',                                   'img/cat-grocery.png', 35),
    ((SELECT id FROM categories WHERE slug='sauces'),  'Рыбный соус Nuoc Mam',           'sauce-nuocmam',           '🇻🇳 Вьетнам', 320.00, 4.6, 'Классический вьетнамский рыбный соус.',                                              'img/cat-grocery.png', 30),
    -- Чай и напитки
    ((SELECT id FROM categories WHERE slug='tea'),     'Зелёный чай Сенча',              'tea-sencha',              '🇯🇵 Япония',  650.00, 4.9, 'Премиальный листовой зелёный чай первого сбора.',                                    'img/cat-wine.png', 25),
    ((SELECT id FROM categories WHERE slug='tea'),     'Матча Marukyu Koyamaen',         'tea-matcha-koyamaen',     '🇯🇵 Япония',  1290.00, 5.0, 'Тонко смолотый зелёный чай матча высшего сорта.',                                   'img/cat-wine.png', 20),
    ((SELECT id FROM categories WHERE slug='tea'),     'Пуэр шу 5 лет',                  'tea-puer-shou',           '🇨🇳 Китай',   890.00, 4.8, 'Выдержанный тёмный пуэр.',                                                          'img/cat-wine.png', 30),
    ((SELECT id FROM categories WHERE slug='tea'),     'Улун Те Гуань Инь',              'tea-tieguanyin',          '🇨🇳 Китай',   780.00, 4.9, 'Бирюзовый улун с лёгкими цветочными нотами.',                                       'img/cat-wine.png', 28),
    ((SELECT id FROM categories WHERE slug='tea'),     'Кокосовая вода Cocomi',          'drink-coconut-water',     '🇹🇭 Тайланд', 220.00, 4.6, 'Натуральная кокосовая вода без сахара.',                                            'img/cat-wine.png', 80),
    ((SELECT id FROM categories WHERE slug='tea'),     'Чай ройбос с имбирём',           'tea-rooibos-ginger',      '🇮🇳 Индия',   420.00, 4.5, 'Травяной чай ройбос с натуральным имбирём.',                                        'img/cat-wine.png', 45),
    -- Снеки и сладости
    ((SELECT id FROM categories WHERE slug='snacks'),  'Моти с маття',                   'snack-mochi-matcha',      '🇯🇵 Япония',  290.00, 4.7, 'Японские рисовые пирожные с начинкой из крема матча.',                              'img/cat-cheese.png', 60),
    ((SELECT id FROM categories WHERE slug='snacks'),  'Дораяки с кремом',               'snack-dorayaki',          '🇯🇵 Япония',  220.00, 4.8, 'Два пышных блинчика с начинкой из крема — любимое лакомство Дораэмона.',           'img/cat-cheese.png', 50),
    ((SELECT id FROM categories WHERE slug='snacks'),  'Pocky шоколадный',               'snack-pocky-chocolate',   '🇯🇵 Япония',  150.00, 4.6, 'Хрустящие палочки в шоколадной глазури.',                                           'img/cat-cheese.png', 120),
    ((SELECT id FROM categories WHERE slug='snacks'),  'Морская капуста нори с маслом',  'snack-nori-snack',        '🇰🇷 Корея',   180.00, 4.7, 'Хрустящие листы нори со вкусом кунжутного масла — отличный перекус.',               'img/cat-cheese.png', 90),
    ((SELECT id FROM categories WHERE slug='snacks'),  'Печенье «удача» с предсказанием','snack-fortune-cookies',   '🇨🇳 Китай',   240.00, 4.5, 'Хрустящее печенье с предсказанием внутри.',                                         'img/cat-cheese.png', 70),
    ((SELECT id FROM categories WHERE slug='snacks'),  'Манго сушёное Tropic',           'snack-dried-mango',       '🇹🇭 Тайланд', 320.00, 4.8, 'Натуральное сушёное манго без сахара.',                                             'img/cat-cheese.png', 65);

-- Тестовые пользователи
-- Администратор: admin@asiamart.local / admin123
-- Пользователь:  user@asiamart.local  / user12345
INSERT INTO users (email, password_hash, name, phone, role) VALUES
    ('admin@asiamart.local', '$2y$10$Yv6md9GRYzi9MSCTi4zJtulOQGVvx9Lp7Jp0qzcye2segrHBNYloG', 'Администратор', '+7 (000) 000-00-00', 'admin'),
    ('user@asiamart.local',  '$2y$10$49v16K/ZWqASUthaYUHIve6C3suhNO5SAPBxksiVTVUT7P6QC9.cy', 'Демо Пользователь', '+7 (000) 123-45-67', 'user');

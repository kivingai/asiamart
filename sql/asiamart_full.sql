-- ============================================
-- AsiaMart — полный дамп базы данных (структура + данные)
-- 
-- Импорт через phpMyAdmin:
--   1. Откройте phpMyAdmin
--   2. Вкладка «Импорт» → выберите этот файл → «Вперёд»
-- 
-- Логины после импорта:
--   admin@asiamart.ru / admin123  — администратор
--   demo@asiamart.ru  / demo1234  — обычный пользователь
-- ============================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS `asiamart` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `asiamart`;

/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_product` (`user_id`,`product_id`),
  UNIQUE KEY `uq_user_product` (`user_id`,`product_id`),
  UNIQUE KEY `uq_session_product` (`session_id`,`product_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_session_id` (`session_id`),
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(64) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,'noodles','Лапша и рис','Японский рамен, удон, гречневая соба, вьетнамская рисовая лапша фо и премиальный жасминовый рис.','/assets/img/categories/cat_noodles.jpg',1),
(2,'drinks','Напитки','Традиционное саке, японские чаи матча и сенча, китайский улун и освежающие азиатские газировки.','/assets/img/categories/cat_drinks.jpg',2),
(3,'snacks','Снеки и сладости','Моти, дораяки, рисовые крекеры сэнбэй, нори и легендарные Pocky — азия любит сладкое.','/assets/img/categories/cat_snacks.jpg',3),
(4,'sauces','Соусы и специи','Соевый соус Kikkoman, мисо, гочуджан, sriracha, кунжутное масло — основа любой азиатской кухни.','/assets/img/categories/cat_sauces.jpg',4);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `fk_oi_product` (`product_id`),
  CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES
(1,1,1,'Рамен Тонкоцу',32.00,2),
(2,1,8,'Матча Uji Premium',145.00,2),
(3,1,13,'Моти ассорти 6 шт',75.00,2),
(4,2,1,'Рамен Тонкоцу',32.00,2),
(5,2,8,'Матча Uji Premium',145.00,1),
(6,2,13,'Моти ассорти 6 шт',75.00,3);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(40) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `status` enum('new','paid','shipped','delivered','cancelled') NOT NULL DEFAULT 'new',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customer_name` varchar(120) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `customer_email` varchar(190) NOT NULL,
  `city` varchar(120) NOT NULL DEFAULT '',
  `address` varchar(500) NOT NULL DEFAULT '',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_address` varchar(500) NOT NULL DEFAULT '',
  `payment_method` enum('card','sbp','cash') NOT NULL DEFAULT 'card',
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,'AM-260524-D3AB5',2,'paid',754.00,'Иван Тестов','+79991234567','test@asiamart.ru','Москва','ул. Тверская 1, кв. 5',504.00,250.00,'','card','Тест дипломки','2026-05-24 10:58:59'),
(2,'AM-260524-2D18E',2,'paid',684.00,'Иван Петров','+7 999 123-45-67','ivan@asiamart.ru','Москва','ул. Тверская, д. 10, кв. 5',434.00,250.00,'','card','Дипломный заказ','2026-05-24 11:01:32');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `short_desc` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `old_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `country` varchar(60) DEFAULT NULL,
  `brand` varchar(80) DEFAULT NULL,
  `weight` varchar(60) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `rating` decimal(2,1) NOT NULL DEFAULT 5.0,
  `reviews_count` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(1,1,'Рамен Тонкоцу','ramen-tonkotsu','Японский свиной рамен с насыщенным бульоном из костей','Классический японский рамен Тонкоцу — лапша в густом сливочно-белом бульоне, который варится из свиных костей более 12 часов. В наборе — лапша быстрого приготовления, концентрат бульона и приправы.',32.00,38.00,60,'Япония',NULL,'118 г','/assets/img/products/p_ramen_tonkotsu.jpg',1,4.5,38,1,'2026-05-24 07:40:58'),
(2,1,'Рамен Сёю','ramen-shoyu','Японский соевый рамен с прозрачным бульоном','Лёгкий рамен Сёю с янтарным бульоном на соевой основе. Тонкая пшеничная лапша, мягкий вкус, идеален для знакомства с японской кухней.',28.00,NULL,80,'Япония',NULL,'105 г','/assets/img/products/p_ramen_shoyu.jpg',0,4.7,81,1,'2026-05-24 07:40:58'),
(3,1,'Удон Kobe','udon-kobe','Толстая пшеничная лапша ручной выработки','Премиальный сушёный удон из Кобе. Толстые упругие нити, идеальные для горячих супов с бульоном даси или жареных блюд яки-удон.',45.00,NULL,45,'Япония',NULL,'250 г','/assets/img/products/p_udon.jpg',1,4.8,217,1,'2026-05-24 07:40:58'),
(4,1,'Соба гречневая','soba','Тонкая гречневая лапша, традиция с 1928 г.','Японская лапша соба из гречневой муки — серо-коричневые тонкие нити с ореховым вкусом. Подаётся горячей или холодной с соусом цуюй.',39.00,NULL,50,'Япония',NULL,'200 г','/assets/img/products/p_soba.jpg',0,4.6,216,1,'2026-05-24 07:40:58'),
(5,1,'Лапша Фо','pho','Вьетнамская рисовая лапша для супа фо','Плоская рисовая лапша из Вьетнама. Готовится за пару минут, легко впитывает ароматы бульона. Подходит для классического супа фо бо и фо га.',24.00,NULL,90,'Вьетнам',NULL,'500 г','/assets/img/products/p_pho.jpg',0,4.7,170,1,'2026-05-24 07:40:58'),
(6,1,'Жасминовый рис Premium','jasmine-rice','Длиннозёрный тайский рис с цветочным ароматом','Тайский жасминовый рис первого сорта (Khao Dawk Mali). Раскрывает нежный аромат жасмина при варке, идеальный гарнир к карри и стир-фрай.',55.00,65.00,70,'Таиланд',NULL,'1 кг','/assets/img/products/p_jasmine_rice.jpg',1,4.9,110,1,'2026-05-24 07:40:58'),
(7,2,'Саке Junmai Daiginjo','sake-junmai','Премиальное саке холодной выдержки 720 мл','Чистый рисовый саке категории Junmai Daiginjo из префектуры Ниигата. Полированный на 50% рис, мягкий фруктовый вкус. Подавать охлаждённым.',189.00,NULL,25,'Япония',NULL,'720 мл, 16%','/assets/img/products/p_sake.jpg',1,4.5,185,1,'2026-05-24 07:40:58'),
(8,2,'Матча Uji Premium','matcha-uji','Японский церемониальный зелёный чай в порошке','Матча высшего сорта из района Удзи (Киото). Ярко-зелёный порошок, насыщенный вкус с умами. Подходит для чайной церемонии и латте.',145.00,165.00,30,'Япония',NULL,'40 г','/assets/img/products/p_matcha.jpg',1,4.5,144,1,'2026-05-24 07:40:58'),
(9,2,'Сенча','sencha','Классический японский зелёный чай в листьях','Самый популярный японский зелёный чай — листовая сенча. Свежий травянистый вкус, лёгкая горчинка, лучше всего раскрывается при 70°C.',65.00,NULL,60,'Япония',NULL,'80 г','/assets/img/products/p_sencha.jpg',0,4.5,94,1,'2026-05-24 07:40:58'),
(10,2,'Улун Phoenix Dan Cong','oolong','Китайский улун с медовым послевкусием','Полуферментированный улун с горы Феникс. Цветочно-медовые ноты, ясный янтарный настой, выдерживает 5–6 заварок.',95.00,NULL,40,'Китай',NULL,'125 г','/assets/img/products/p_oolong.jpg',0,4.9,122,1,'2026-05-24 07:40:58'),
(11,2,'Юдзу газировка','yuzu-soda','Газированный напиток с японским цитрусом юдзу','Освежающая газировка из натурального сока юдзу — японского цитруса со вкусом, напоминающим грейпфрут и лайм. Без алкоголя.',18.00,NULL,120,'Япония',NULL,'750 мл','/assets/img/products/p_yuzu.jpg',0,4.7,101,1,'2026-05-24 07:40:58'),
(12,2,'Личи газировка','lychee-soda','Премиум газировка с натуральным личи','Лёгкий розовый напиток с ароматом свежего личи. Деликатный сладкий вкус, не приторный — идеален к острым блюдам.',16.00,NULL,150,'Тайвань',NULL,'330 мл','/assets/img/products/p_lychee.jpg',0,4.9,39,1,'2026-05-24 07:40:58'),
(13,3,'Моти ассорти 6 шт','mochi-6','Рисовые пирожные ручной работы 6 вкусов','Подарочный набор моти: сакура, матча, чёрный кунжут, манго, белый шоколад, личи. Мягкая рисовая оболочка и нежная начинка из пасты адзуки.',75.00,NULL,35,'Япония',NULL,'180 г','/assets/img/products/p_mochi.jpg',1,4.9,52,1,'2026-05-24 07:40:58'),
(14,3,'Pocky Клубника','pocky-strawberry','Бисквитные палочки в клубничной глазури','Хрустящие палочки Glico Pocky в розовой глазури с настоящими кусочками клубники. Культовый японский снек с 1966 года.',12.00,NULL,200,'Япония',NULL,'38 г','/assets/img/products/p_pocky_strawberry.jpg',0,4.6,72,1,'2026-05-24 07:40:58'),
(15,3,'Pocky Матча','pocky-matcha','Бисквитные палочки с зелёным чаем','Версия легендарных Pocky с глазурью из матча — не слишком сладко, чуть терпко, очень азиатски. Идеально к зелёному чаю.',13.00,NULL,180,'Япония',NULL,'70 г','/assets/img/products/p_pocky_matcha.jpg',0,4.9,128,1,'2026-05-24 07:40:58'),
(16,3,'Дораяки с адзуки','dorayaki','Японские мини-блины с пастой из красной фасоли','Два мягких блинчика-сэндвича с начинкой из сладкой пасты адзуки. Любимый десерт Дораэмона. Подавать комнатной температуры или слегка подогретыми.',28.00,NULL,60,'Япония',NULL,'2 шт × 65 г','/assets/img/products/p_dorayaki.jpg',0,4.6,197,1,'2026-05-24 07:40:58'),
(17,3,'Нори жареная Yamamoto','nori','Премиальные листы морской капусты 10 шт','Обжаренные листы нори от Yamamoto — золотой стандарт для домашних суши и онигири. Хрустящие, насыщенно-зелёные, с лёгким морским вкусом.',35.00,NULL,80,'Япония',NULL,'25 г, 10 листов','/assets/img/products/p_nori.jpg',1,4.6,218,1,'2026-05-24 07:40:58'),
(18,3,'Сэнбэй ассорти','senbei','Японские рисовые крекеры ручной обжарки','Набор сэнбэй разных видов: с нори, кунжутом, соевой глазурью. Хрустящие, солоновато-сладкие, отличная закуска к зелёному чаю или пиву.',38.00,NULL,55,'Япония',NULL,'160 г','/assets/img/products/p_senbei.jpg',0,4.8,189,1,'2026-05-24 07:40:58'),
(19,4,'Соевый соус Kikkoman','soy-sauce-kikkoman','Классический японский соевый соус 500 мл','Натурально сваренный соевый соус Kikkoman — эталон, известный с XVII века. Глубокий солёно-умами вкус, подходит для всего: от суши до маринадов.',42.00,NULL,150,'Япония',NULL,'500 мл','/assets/img/products/p_soy_sauce.jpg',1,4.8,55,1,'2026-05-24 07:40:58'),
(20,4,'Соус Терияки','teriyaki','Сладко-солёный соус для глазирования','Густой соус терияки на основе соевого соуса, мирина и сахара. Идеален для курицы, лосося и стир-фрай — даёт блестящую карамельную корочку.',36.00,NULL,90,'Япония',NULL,'300 мл','/assets/img/products/p_teriyaki.jpg',0,4.6,165,1,'2026-05-24 07:40:58'),
(21,4,'Мисо паста красная','miso-red','Ферментированная соевая паста для супов','Красная (ака) мисо — выдержанная паста с насыщенным солёным вкусом. Основа для супа мисо-сиру, маринадов для рыбы и заправок.',58.00,NULL,50,'Япония',NULL,'350 г','/assets/img/products/p_miso.jpg',1,4.9,68,1,'2026-05-24 07:40:58'),
(22,4,'Гочуджан','gochujang','Корейская острая ферментированная паста','Знаменитая корейская паста гочуджан — острая, слегка сладкая, со сложным ферментированным вкусом. Незаменима для пибимпапа и тогпокки.',49.00,NULL,65,'Корея',NULL,'500 г','/assets/img/products/p_gochujang.jpg',1,4.8,28,1,'2026-05-24 07:40:58'),
(23,4,'Sriracha Hot Chili','sriracha','Острый соус чили с чесноком','Культовый острый соус Huy Fong Sriracha — красная бутылка с зелёной крышкой и петухом. Универсальный острый соус ко всему.',32.00,NULL,110,'Тайланд/США',NULL,'482 г','/assets/img/products/p_sriracha.jpg',0,4.8,147,1,'2026-05-24 07:40:58'),
(24,4,'Кунжутное масло','sesame-oil','Нерафинированное масло жареного кунжута','Тёмное ароматное кунжутное масло — несколько капель в конце готовки превратят обычный салат или лапшу в азиатское блюдо. Только для заправки, не жарки.',48.00,NULL,70,'Япония',NULL,'500 мл','/assets/img/products/p_sesame_oil.jpg',0,4.8,159,1,'2026-05-24 07:40:58');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin@asiamart.ru','$2y$10$iSJ5Ir2f.opvKpyG9zSIbuqY8Xhg/8ALTCjfDesmtQfCUxOXH/bHG','Администратор','+48 000 000 000',NULL,'admin','2026-05-24 07:40:58'),
(2,'demo@asiamart.ru','$2y$10$CLz4Bn7eqhmcPyr7wh1GQOoToF9BSpOgGtkAnQfT7kXj7ZfS68TcC','Демо-пользователь','+48 111 222 333',NULL,'user','2026-05-24 07:40:58');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS=1;

-- AsiaMart — структура базы данных
-- MySQL / MariaDB, кодировка utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- =========================
-- Пользователи (покупатели + админы)
-- =========================
CREATE TABLE `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`         VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `name`          VARCHAR(100) NOT NULL,
  `phone`         VARCHAR(30)  DEFAULT NULL,
  `address`       VARCHAR(255) DEFAULT NULL,
  `role`          ENUM('user','admin') NOT NULL DEFAULT 'user',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Категории товаров
-- =========================
CREATE TABLE `categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(64)  NOT NULL,
  `name`        VARCHAR(120) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Товары
-- =========================
CREATE TABLE `products` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`  INT UNSIGNED NOT NULL,
  `name`         VARCHAR(200) NOT NULL,
  `slug`         VARCHAR(200) NOT NULL,
  `short_desc`   VARCHAR(500) DEFAULT NULL,
  `description`  TEXT         DEFAULT NULL,
  `price`        DECIMAL(10,2) NOT NULL DEFAULT 0,
  `old_price`    DECIMAL(10,2) DEFAULT NULL,
  `stock`        INT NOT NULL DEFAULT 0,
  `country`      VARCHAR(60)  DEFAULT NULL,
  `brand`        VARCHAR(80)  DEFAULT NULL,
  `weight`       VARCHAR(60)  DEFAULT NULL,
  `image`        VARCHAR(255) DEFAULT NULL,
  `is_featured`  TINYINT(1)   NOT NULL DEFAULT 0,
  `rating`       DECIMAL(2,1) NOT NULL DEFAULT 5.0,
  `reviews_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Корзина (для авторизованных)
-- =========================
CREATE TABLE `cart_items` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(128) DEFAULT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity`   INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_product` (`user_id`, `product_id`),
  UNIQUE KEY `uniq_session_product` (`session_id`, `product_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_session_id` (`session_id`),
  CONSTRAINT `fk_cart_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Заказы (имитация оплаты)
-- =========================
CREATE TABLE `orders` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number`    VARCHAR(40) NOT NULL,
  `user_id`         INT UNSIGNED DEFAULT NULL,
  `status`          ENUM('new','paid','shipped','delivered','cancelled') NOT NULL DEFAULT 'new',
  `total`           DECIMAL(10,2) NOT NULL DEFAULT 0,
  `customer_name`   VARCHAR(120) NOT NULL,
  `customer_phone`  VARCHAR(30)  NOT NULL,
  `customer_email`  VARCHAR(190) NOT NULL,
  `city`            VARCHAR(120) NOT NULL DEFAULT '',
  `address`         VARCHAR(500) NOT NULL DEFAULT '',
  `subtotal`        DECIMAL(10,2) NOT NULL DEFAULT 0,
  `delivery`        DECIMAL(10,2) NOT NULL DEFAULT 0,
  `shipping_address` VARCHAR(500) NOT NULL DEFAULT '',
  `payment_method`  ENUM('card','sbp','cash') NOT NULL DEFAULT 'card',
  `comment`         TEXT DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`    INT UNSIGNED NOT NULL,
  `product_id`  INT UNSIGNED NOT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `price`       DECIMAL(10,2) NOT NULL,
  `quantity`    INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `fk_oi_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Smart E-Commerce Database Schema
-- Import via phpMyAdmin into a database named: smart_ecommerce

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','user') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `address` TEXT NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `status` ENUM('pending','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

-- Seed users (passwords: admin123 / user123 — bcrypt hashes)
INSERT INTO `users` (`name`,`email`,`password`,`role`) VALUES
('Admin','admin@shop.com','$2b$10$ER5lvE7VtOVMEWhH6VgnU.BzMuiHsMb4CsoQviuycL36WFaP0XSiK','admin'),
('Demo User','user@shop.com','$2b$10$UFEOxQJTiDTbOVDjXYjy/OW/UhqDenQkOFmlH4qqI1hu.rRykMIve','user');

-- Seed categories
INSERT INTO `categories` (`name`) VALUES
('Electronics'),('Clothing'),('Books'),('Home & Kitchen'),('Sports');

-- Seed products
INSERT INTO `products` (`category_id`,`name`,`description`,`price`,`stock`,`image`) VALUES
(1,'Wireless Headphones','Bluetooth 5.0 over-ear headphones with noise cancellation.',79.99,25,NULL),
(1,'Smart Watch','Fitness tracker with heart-rate sensor and GPS.',129.50,15,NULL),
(1,'USB-C Charger 65W','Fast-charging GaN charger for laptops & phones.',34.00,40,NULL),
(2,'Cotton T-Shirt','Soft 100% cotton crew-neck t-shirt, multiple colors.',14.99,100,NULL),
(2,'Denim Jacket','Classic blue denim jacket, slim fit.',49.90,20,NULL),
(3,'Clean Code','A handbook of agile software craftsmanship by Robert C. Martin.',29.99,30,NULL),
(3,'The Pragmatic Programmer','Your journey to mastery, 20th anniversary edition.',32.50,18,NULL),
(4,'Stainless Steel Knife Set','5-piece kitchen knife set with wooden block.',45.00,12,NULL),
(4,'Electric Kettle 1.7L','Fast-boil cordless kettle with auto shut-off.',22.75,28,NULL),
(5,'Yoga Mat','Non-slip 6mm thick exercise mat.',19.99,50,NULL),
(5,'Football Size 5','Official match-quality football.',24.50,35,NULL),
(1,'Bluetooth Speaker','Portable waterproof speaker, 12-hour battery.',39.99,22,NULL);

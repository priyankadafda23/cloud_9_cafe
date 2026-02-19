-- Cloud 9 Cafe - Orders and Reservations Schema
-- Run this after 001_init_schema.sql

USE `cloud_9_cafe`;

SET NAMES utf8mb4;

-- ============================================
-- ORDERS TABLE
-- ============================================
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `order_number` VARCHAR(20) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `final_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `delivery_type` ENUM('dine_in', 'takeaway', 'delivery') NOT NULL DEFAULT 'dine_in',
  `delivery_address` TEXT DEFAULT NULL,
  `special_instructions` TEXT DEFAULT NULL,
  `ordered_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `confirmed_at` DATETIME DEFAULT NULL,
  `prepared_at` DATETIME DEFAULT NULL,
  `delivered_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_order_number` (`order_number`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_ordered_at` (`ordered_at`),
  CONSTRAINT `fk_orders_user_id_users_id`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ORDER ITEMS TABLE
-- ============================================
CREATE TABLE `order_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `menu_item_id` BIGINT UNSIGNED NOT NULL,
  `item_name` VARCHAR(120) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `special_requests` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order_id` (`order_id`),
  KEY `idx_order_items_menu_item_id` (`menu_item_id`),
  CONSTRAINT `fk_order_items_order_id_orders_id`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_menu_item_id_menu_items_id`
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- RESERVATIONS TABLE
-- ============================================
DROP TABLE IF EXISTS `reservations`;

CREATE TABLE `reservations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `reservation_code` VARCHAR(20) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending',
  `party_size` INT UNSIGNED NOT NULL DEFAULT 1,
  `reservation_date` DATE NOT NULL,
  `reservation_time` TIME NOT NULL,
  `table_number` VARCHAR(20) DEFAULT NULL,
  `special_requests` TEXT DEFAULT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(20) DEFAULT NULL,
  `customer_email` VARCHAR(190) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reservations_code` (`reservation_code`),
  KEY `idx_reservations_user_id` (`user_id`),
  KEY `idx_reservations_status` (`status`),
  KEY `idx_reservations_date` (`reservation_date`),
  CONSTRAINT `fk_reservations_user_id_users_id`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA (Optional - for testing)
-- ============================================

-- Insert sample orders (if users and menu items exist)
-- Note: These will only work if you have existing user_ids and menu_item_ids

-- Sample order 1
-- INSERT INTO `orders` (`user_id`, `order_number`, `status`, `total_amount`, `final_amount`, `payment_status`, `delivery_type`)
-- VALUES (1, 'ORD-20260001', 'delivered', 25.50, 25.50, 'paid', 'dine_in');

-- Sample reservation 1
-- INSERT INTO `reservations` (`user_id`, `reservation_code`, `status`, `party_size`, `reservation_date`, `reservation_time`, `customer_name`)
-- VALUES (1, 'RES-20260001', 'confirmed', 4, '2026-02-20', '19:00:00', 'John Doe');

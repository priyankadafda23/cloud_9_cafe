-- Cloud 9 Cafe - Initial database schema
-- Compatible with MySQL 8+ and common XAMPP MariaDB setups.

CREATE DATABASE IF NOT EXISTS `cloud_9_cafe`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `cloud_9_cafe`;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `menu_items`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `phone` VARCHAR(20) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_is_active` (`is_active`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menu_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(60) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `is_available` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_menu_items_category` (`category`),
  KEY `idx_menu_items_is_available` (`is_available`),
  KEY `idx_menu_items_created_by` (`created_by`),
  CONSTRAINT `fk_menu_items_created_by_users_id`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contact_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
  `replied_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_messages_user_id` (`user_id`),
  KEY `idx_contact_messages_status` (`status`),
  KEY `idx_contact_messages_created_at` (`created_at`),
  CONSTRAINT `fk_contact_messages_user_id_users_id`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

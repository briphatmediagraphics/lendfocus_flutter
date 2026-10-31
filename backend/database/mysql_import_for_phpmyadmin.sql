-- MySQL import file for phpMyAdmin
-- Save as: database/mysql_import_for_phpmyadmin.sql
-- WARNING: Review and modify (database name, existing tables) before importing.

SET FOREIGN_KEY_CHECKS = 0;

-- Support tickets
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_number` VARCHAR(64) NOT NULL UNIQUE,
  `user_id` BIGINT UNSIGNED NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'open',
  `priority` VARCHAR(32) NULL,
  `assigned_to` BIGINT UNSIGNED NULL,
  `extra` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`user_id`),
  INDEX (`status`),
  INDEX (`ticket_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support ticket replies / threads
CREATE TABLE IF NOT EXISTS `support_ticket_replies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `support_ticket_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `author_name` VARCHAR(255) NULL,
  `message` TEXT NOT NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`support_ticket_id`),
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Plots (map points / parcels)
CREATE TABLE IF NOT EXISTS `plots` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` BIGINT UNSIGNED NULL,
  `code` VARCHAR(64) NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'open',
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `coords` JSON NULL, -- expected: {"lat": ..., "lng": ...} or GeoJSON coordinate
  `position_x` DOUBLE NULL,
  `position_y` DOUBLE NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`project_id`),
  INDEX (`status`),
  INDEX (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings (reservations)
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plot_id` BIGINT UNSIGNED NULL,
  `project_id` BIGINT UNSIGNED NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `control_number` VARCHAR(64) NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'held', -- held/paid/released/cancelled
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(8) NULL,
  `held_until` DATETIME NULL,
  `stripe_session_id` VARCHAR(255) NULL,
  `stripe_payment_intent` VARCHAR(255) NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`plot_id`),
  INDEX (`user_id`),
  INDEX (`status`),
  INDEX (`control_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(8) NULL,
  `provider` VARCHAR(64) NULL,
  `provider_id` VARCHAR(255) NULL,
  `status` VARCHAR(32) NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`booking_id`),
  INDEX (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System notifications (in-app)
CREATE TABLE IF NOT EXISTS `system_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NULL,
  `recipients` JSON NULL, -- array of user IDs or ["admin"] etc.
  `data` JSON NULL,
  `read_at` DATETIME NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If your projects table already exists, add a map_shapes JSON column for persisted drawings.
-- The ALTER is performed only when the column does not already exist to avoid import errors in phpMyAdmin.
-- This uses a small runtime check against information_schema and executes an ALTER via a prepared statement when needed.

-- Note: phpMyAdmin may disable multiple-statement execution depending on configuration; if you see issues, run the ALTER manually or remove this block and run:
--   ALTER TABLE `projects` ADD COLUMN `map_shapes` JSON NULL;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'map_shapes');
SET @sql := IF(@exists = 0, 'ALTER TABLE `projects` ADD COLUMN `map_shapes` JSON NULL;', 'SELECT "map_shapes_already_exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

-- Sample data for quick testing (uncommented)
-- Insert a sample support ticket and a reply
INSERT INTO support_tickets (ticket_number, user_id, subject, message, status, priority, created_at, updated_at)
VALUES ('TCK-0001', NULL, 'Test: Map booking issue', 'User reports Unexpected token "<" when fetching calendar JSON', 'open', 'normal', NOW(), NOW());
SET @ticket_id := LAST_INSERT_ID();

INSERT INTO support_ticket_replies (support_ticket_id, user_id, author_name, message, created_at, updated_at)
VALUES (@ticket_id, NULL, 'Support Bot', 'Thanks — investigating. Please provide console logs.', NOW(), NOW());

-- Insert a sample plot and a held booking (held for 5 days)
INSERT INTO plots (project_id, code, status, price, coords, position_x, position_y, created_at, updated_at)
VALUES (NULL, 'P-001', 'held', 50000.00, JSON_OBJECT('lat', -6.7924, 'lng', 39.2083), 39.2083, -6.7924, NOW(), NOW());
SET @plot_id := LAST_INSERT_ID();

INSERT INTO bookings (plot_id, project_id, user_id, control_number, status, amount, currency, held_until, created_at, updated_at)
VALUES (@plot_id, NULL, NULL, CONCAT('BK-', LPAD(FLOOR(RAND()*9999),4,'0')), 'held', 50000.00, 'TZS', DATE_ADD(NOW(), INTERVAL 5 DAY), NOW(), NOW());
SET @booking_id := LAST_INSERT_ID();

INSERT INTO payments (booking_id, amount, currency, provider, provider_id, status, created_at, updated_at)
VALUES (@booking_id, 0.00, 'TZS', NULL, NULL, 'pending', NOW(), NOW());

-- Sample system notification referencing the booking
INSERT INTO system_notifications (title, body, recipients, data, created_at, updated_at)
VALUES ('Sample: Booking held', 'A sample booking has been created and is held for 5 days.', JSON_ARRAY(), JSON_OBJECT('booking_id', @booking_id), NOW(), NOW());

-- Notes:
-- 1) This file intentionally avoids strict foreign key constraints to keep import simple when 'users' or 'projects' tables may already exist with different structures.
-- 2) If your 'projects' table does not exist, either create it first or remove the ALTER TABLE line and add 'map_shapes' column later via migration.
-- 3) After import, run `php artisan migrate` locally to ensure Laravel migrations and models are in sync with the database.
-- 4) Backup your database before import.

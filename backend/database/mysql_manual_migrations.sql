-- Manual SQL for migration changes in LandFocus Laravel
-- Import this file directly into your MySQL/phpMyAdmin database.

SET FOREIGN_KEY_CHECKS = 0;

-- Add receipt file support to payments
ALTER TABLE `payments`
  ADD COLUMN IF NOT EXISTS `receipt_file` VARCHAR(255) DEFAULT NULL AFTER `reference`;

-- Create support tickets table if it does not exist
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` CHAR(36) NOT NULL,
  `ticket_number` VARCHAR(255) NOT NULL,
  `user_id` CHAR(36) DEFAULT NULL,
  `project_id` CHAR(36) DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
  `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `assigned_to` CHAR(36) DEFAULT NULL,
  `last_replied_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_tickets_ticket_number_unique` (`ticket_number`),
  KEY `support_tickets_user_id_index` (`user_id`),
  KEY `support_tickets_project_id_index` (`project_id`),
  KEY `support_tickets_assigned_to_index` (`assigned_to`),
  CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add rating/feedback table for projects
CREATE TABLE IF NOT EXISTS `project_feedback` (
  `id` CHAR(36) NOT NULL,
  `project_id` CHAR(36) NOT NULL,
  `user_id` CHAR(36) DEFAULT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `comments` TEXT DEFAULT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_feedback_project_id_index` (`project_id`),
  KEY `project_feedback_user_id_index` (`user_id`),
  CONSTRAINT `project_feedback_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_feedback_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add notification audit table for message/event delivery tracking
CREATE TABLE IF NOT EXISTS `notification_audit` (
  `id` CHAR(36) NOT NULL,
  `user_id` CHAR(36) DEFAULT NULL,
  `type` ENUM('email','sms','whatsapp','in_app') NOT NULL DEFAULT 'email',
  `event` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `sent_at` TIMESTAMP NULL DEFAULT NULL,
  `status` ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `error_message` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_audit_user_id_index` (`user_id`),
  CONSTRAINT `notification_audit_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add project fields: cover_image, total_plots, map_layout_type
ALTER TABLE `projects`
  ADD COLUMN IF NOT EXISTS `cover_image` VARCHAR(255) DEFAULT NULL AFTER `cover_path`,
  ADD COLUMN IF NOT EXISTS `total_plots` INT DEFAULT 0 AFTER `base_price_tzs`,
  ADD COLUMN IF NOT EXISTS `map_layout_type` ENUM('grid','coordinates') DEFAULT 'grid' AFTER `total_plots`;

-- Add plot position fields
ALTER TABLE `plots`
  ADD COLUMN IF NOT EXISTS `position_x` INT DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `position_y` INT DEFAULT NULL AFTER `position_x`;

SET FOREIGN_KEY_CHECKS = 1;

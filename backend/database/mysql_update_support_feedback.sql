-- Update schema for support, feedback, receipts, and notification audit
-- Import this file after your existing mysql_import.sql and mysql_update_plots.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Add receipt file support to payments
ALTER TABLE `payments`
  ADD COLUMN IF NOT EXISTS `receipt_file` varchar(255) DEFAULT NULL AFTER `reference`;

-- Add optional buyer user relationship to bookings if missing
ALTER TABLE `bookings`
  ADD COLUMN IF NOT EXISTS `user_id` char(36) DEFAULT NULL AFTER `sales_officer_id`;

-- Add inquiry support ticket fields if they are missing
ALTER TABLE `inquiries`
  ADD COLUMN IF NOT EXISTS `ticket_number` varchar(255) UNIQUE DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `user_id` char(36) DEFAULT NULL AFTER `ticket_number`,
  ADD COLUMN IF NOT EXISTS `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `admin_notes` text DEFAULT NULL AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `last_replied_at` timestamp NULL DEFAULT NULL AFTER `updated_at`,
  ADD COLUMN IF NOT EXISTS `assigned_to` char(36) DEFAULT NULL AFTER `admin_notes`;

-- Feedback / rating table
CREATE TABLE IF NOT EXISTS `project_feedback` (
  `id` char(36) NOT NULL,
  `project_id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `rating` tinyint unsigned NOT NULL DEFAULT 0,
  `comments` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_feedback_project_id_index` (`project_id`),
  KEY `project_feedback_user_id_index` (`user_id`),
  CONSTRAINT `project_feedback_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_feedback_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification audit log for email/SMS/whatsapp triggers
CREATE TABLE IF NOT EXISTS `notification_audit` (
  `id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `type` enum('email','sms','whatsapp','in_app') NOT NULL DEFAULT 'email',
  `event` varchar(255) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_audit_user_id_index` (`user_id`),
  CONSTRAINT `notification_audit_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional support tickets table for request tracking if you want a separate table
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` char(36) NOT NULL,
  `ticket_number` varchar(255) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `project_id` char(36) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `assigned_to` char(36) DEFAULT NULL,
  `last_replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_tickets_ticket_number_unique` (`ticket_number`),
  KEY `support_tickets_user_id_index` (`user_id`),
  KEY `support_tickets_project_id_index` (`project_id`),
  KEY `support_tickets_assigned_to_index` (`assigned_to`),
  CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

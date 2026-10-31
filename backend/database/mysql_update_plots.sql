-- Update schema for project/plot features
-- Import this file after your existing mysql_import.sql without modifying it.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `projects`
  ADD COLUMN IF NOT EXISTS `total_plots` int unsigned NOT NULL DEFAULT 0 AFTER `description`,
  ADD COLUMN IF NOT EXISTS `cover_image` varchar(255) DEFAULT NULL AFTER `cover_path`,
  ADD COLUMN IF NOT EXISTS `map_layout_type` enum('grid','coordinates') NOT NULL DEFAULT 'grid' AFTER `cover_image`;

ALTER TABLE `plots`
  ADD COLUMN IF NOT EXISTS `plot_number` varchar(255) DEFAULT NULL AFTER `project_id`,
  ADD COLUMN IF NOT EXISTS `position_x` int unsigned DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `position_y` int unsigned DEFAULT NULL AFTER `position_x`;

ALTER TABLE `plots`
  MODIFY COLUMN `status` enum('available','held','sold','reserved','open','booked','paid') NOT NULL DEFAULT 'available';

UPDATE `plots`
SET `plot_number` = `number`
WHERE `plot_number` IS NULL AND `number` IS NOT NULL;

ALTER TABLE `plots`
  ADD UNIQUE INDEX `plots_project_id_plot_number_unique` (`project_id`,`plot_number`);

SET FOREIGN_KEY_CHECKS = 1;

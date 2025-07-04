-- Simple Database Schema Update Script
-- Copy and paste this into your MySQL client (phpMyAdmin, MySQL Workbench, etc.)

-- Update Users Table
-- Add new columns if they don't exist
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `fullname` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `agency_id` int NOT NULL;

-- Update existing data
UPDATE `users` SET `email` = CONCAT(username, '@pcds2030.gov.my') WHERE (`email` = '' OR `email` IS NULL);
UPDATE `users` SET `fullname` = CONCAT('User ', username) WHERE `fullname` IS NULL;

-- Map agency_group_id to agency_id if agency_group_id exists
UPDATE `users` SET `agency_id` = 1 WHERE `agency_group_id` = 0;
UPDATE `users` SET `agency_id` = 2 WHERE `agency_group_id` = 1;
UPDATE `users` SET `agency_id` = 3 WHERE `agency_group_id` = 2;

-- Remove old columns (will fail if they don't exist, but that's OK)
ALTER TABLE `users` DROP COLUMN IF EXISTS `agency_name`;
ALTER TABLE `users` DROP COLUMN IF EXISTS `sector_id`;
ALTER TABLE `users` DROP COLUMN IF EXISTS `agency_group_id`;

-- Rename password to pw if password column exists
ALTER TABLE `users` CHANGE COLUMN IF EXISTS `password` `pw` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

-- Add indexes and constraints
ALTER TABLE `users` ADD UNIQUE KEY IF NOT EXISTS `username` (`username`);
ALTER TABLE `users` ADD UNIQUE KEY IF NOT EXISTS `email` (`email`);
ALTER TABLE `users` ADD KEY IF NOT EXISTS `agency_id` (`agency_id`);

-- Add foreign key constraint
ALTER TABLE `users` ADD CONSTRAINT IF NOT EXISTS `users_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`);

-- Update Programs Table
-- Add new columns if they don't exist
ALTER TABLE `programs` ADD COLUMN IF NOT EXISTS `users_assigned` int DEFAULT NULL;
ALTER TABLE `programs` ADD COLUMN IF NOT EXISTS `status` set('not-started','in-progress','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;
ALTER TABLE `programs` ADD COLUMN IF NOT EXISTS `targets_linked` int DEFAULT '0';

-- Map agency_group to agency_id if agency_group exists
UPDATE `programs` SET `agency_id` = 1 WHERE `agency_group` = 0;
UPDATE `programs` SET `agency_id` = 2 WHERE `agency_group` = 1;
UPDATE `programs` SET `agency_id` = 3 WHERE `agency_group` = 2;

-- Remove old columns
ALTER TABLE `programs` DROP COLUMN IF EXISTS `agency_group`;
ALTER TABLE `programs` DROP COLUMN IF EXISTS `sector_id`;
ALTER TABLE `programs` DROP COLUMN IF EXISTS `owner_agency_id`;
ALTER TABLE `programs` DROP COLUMN IF EXISTS `is_assigned`;
ALTER TABLE `programs` DROP COLUMN IF EXISTS `edit_permissions`;
ALTER TABLE `programs` DROP COLUMN IF EXISTS `status_indicator`;

-- Add indexes and constraints
ALTER TABLE `programs` ADD KEY IF NOT EXISTS `users_assigned` (`users_assigned`);
ALTER TABLE `programs` ADD CONSTRAINT IF NOT EXISTS `FK_programs_agency` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`);
ALTER TABLE `programs` ADD CONSTRAINT IF NOT EXISTS `FK_programs_users` FOREIGN KEY (`users_assigned`) REFERENCES `users` (`user_id`);

-- Show final table structures
DESCRIBE `users`;
DESCRIBE `programs`; 
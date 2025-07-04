-- Idempotent Database Schema Update Script
-- Updates old database schema to match new database schema
-- Safe to run multiple times

-- Start transaction for safety
START TRANSACTION;

-- Step 1: Remove foreign key constraints if they exist
SET @fk_name := (SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'agency_id' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1);
SET @sql := IF(@fk_name IS NOT NULL, CONCAT('ALTER TABLE `users` DROP FOREIGN KEY ', @fk_name), 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 2: Remove indexes and keys if they exist
SET @idx := (SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'agency_name' LIMIT 1);
SET @sql := IF(@idx IS NOT NULL, 'ALTER TABLE `users` DROP INDEX `agency_name`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx := (SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'agency_id' LIMIT 1);
SET @sql := IF(@idx IS NOT NULL, 'ALTER TABLE `users` DROP INDEX `agency_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 3: Add new columns if they do not exist
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'fullname');
SET @sql := IF(@col = 0, 'ALTER TABLE `users` ADD COLUMN `fullname` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'email');
SET @sql := IF(@col = 0, 'ALTER TABLE `users` ADD COLUMN `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'agency_id');
SET @sql := IF(@col = 0, 'ALTER TABLE `users` ADD COLUMN `agency_id` int NOT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 4: Update existing data to map agency_group_id to agency_id if columns exist
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'agency_group_id');
SET @col2 := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'agency_id');
SET @sql := IF(@col > 0 AND @col2 > 0, 'UPDATE `users` SET `agency_id` = 1 WHERE `agency_group_id` = 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := IF(@col > 0 AND @col2 > 0, 'UPDATE `users` SET `agency_id` = 2 WHERE `agency_group_id` = 1', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := IF(@col > 0 AND @col2 > 0, 'UPDATE `users` SET `agency_id` = 3 WHERE `agency_group_id` = 2', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 5: Generate emails for users that don't have them
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'email');
SET @sql := IF(@col > 0, 'UPDATE `users` SET `email` = CONCAT(username, "@pcds2030.gov.my") WHERE (`email` = "" OR `email` IS NULL)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 6: Generate fullnames for users that don't have them
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'fullname');
SET @sql := IF(@col > 0, 'UPDATE `users` SET `fullname` = CONCAT("User ", username) WHERE `fullname` IS NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 7: Remove columns that don't exist in new schema
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'agency_name');
SET @sql := IF(@col > 0, 'ALTER TABLE `users` DROP COLUMN `agency_name`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'sector_id');
SET @sql := IF(@col > 0, 'ALTER TABLE `users` DROP COLUMN `sector_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'agency_group_id');
SET @sql := IF(@col > 0, 'ALTER TABLE `users` DROP COLUMN `agency_group_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 8: Rename password column to pw if needed
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'password');
SET @col2 := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'pw');
SET @sql := IF(@col > 0 AND @col2 = 0, 'ALTER TABLE `users` CHANGE `password` `pw` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 9: Add new indexes and constraints if not exist
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'username');
SET @sql := IF(@idx = 0, 'ALTER TABLE `users` ADD UNIQUE KEY `username` (`username`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'email');
SET @sql := IF(@idx = 0, 'ALTER TABLE `users` ADD UNIQUE KEY `email` (`email`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'agency_id');
SET @sql := IF(@idx = 0, 'ALTER TABLE `users` ADD KEY `agency_id` (`agency_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 10: Add foreign key constraint to agency table if not exist
SET @fk := (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'agency_id' AND REFERENCED_TABLE_NAME = 'agency');
SET @sql := IF(@fk = 0, 'ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ========================================
-- UPDATE PROGRAMS TABLE
-- ========================================

-- Step 11: Add new columns to programs table if not exist
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'users_assigned');
SET @sql := IF(@col = 0, 'ALTER TABLE `programs` ADD COLUMN `users_assigned` int DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'status');
SET @sql := IF(@col = 0, 'ALTER TABLE `programs` ADD COLUMN `status` set(\'not-started\',\'in-progress\',\'completed\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'targets_linked');
SET @sql := IF(@col = 0, 'ALTER TABLE `programs` ADD COLUMN `targets_linked` int DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 12: Update programs table to map agency_group to agency_id if columns exist
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'agency_group');
SET @col2 := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'agency_id');
SET @sql := IF(@col > 0 AND @col2 > 0, 'UPDATE `programs` SET `agency_id` = 1 WHERE `agency_group` = 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := IF(@col > 0 AND @col2 > 0, 'UPDATE `programs` SET `agency_id` = 2 WHERE `agency_group` = 1', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := IF(@col > 0 AND @col2 > 0, 'UPDATE `programs` SET `agency_id` = 3 WHERE `agency_group` = 2', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 13: Remove old columns from programs table if they exist
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'agency_group');
SET @sql := IF(@col > 0, 'ALTER TABLE `programs` DROP COLUMN `agency_group`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'sector_id');
SET @sql := IF(@col > 0, 'ALTER TABLE `programs` DROP COLUMN `sector_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'owner_agency_id');
SET @sql := IF(@col > 0, 'ALTER TABLE `programs` DROP COLUMN `owner_agency_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'is_assigned');
SET @sql := IF(@col > 0, 'ALTER TABLE `programs` DROP COLUMN `is_assigned`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'edit_permissions');
SET @sql := IF(@col > 0, 'ALTER TABLE `programs` DROP COLUMN `edit_permissions`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'status_indicator');
SET @sql := IF(@col > 0, 'ALTER TABLE `programs` DROP COLUMN `status_indicator`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Step 14: Add new indexes and constraints to programs table if not exist
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_NAME = 'programs' AND INDEX_NAME = 'users_assigned');
SET @sql := IF(@idx = 0, 'ALTER TABLE `programs` ADD KEY `users_assigned` (`users_assigned`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @fk := (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'agency_id' AND REFERENCED_TABLE_NAME = 'agency');
SET @sql := IF(@fk = 0, 'ALTER TABLE `programs` ADD CONSTRAINT `FK_programs_agency` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @fk := (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'programs' AND COLUMN_NAME = 'users_assigned' AND REFERENCED_TABLE_NAME = 'users');
SET @sql := IF(@fk = 0, 'ALTER TABLE `programs` ADD CONSTRAINT `FK_programs_users` FOREIGN KEY (`users_assigned`) REFERENCES `users` (`user_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Commit the transaction
COMMIT;

-- Verify the changes
DESCRIBE `users`;
DESCRIBE `programs`; 
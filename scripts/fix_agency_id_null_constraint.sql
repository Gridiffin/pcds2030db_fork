-- Fix agency_id NOT NULL constraint for admin users
-- This script allows agency_id to be NULL so admin users can be created without agency assignment

-- Step 1: Drop the existing foreign key constraint
ALTER TABLE `users` DROP FOREIGN KEY `users_ibfk_1`;

-- Step 2: Modify the agency_id column to allow NULL values
ALTER TABLE `users` MODIFY COLUMN `agency_id` int NULL;

-- Step 3: Re-add the foreign key constraint with NULL allowed
ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Step 4: Add an index for better performance (if not already present)
-- Note: This is optional as the foreign key constraint already creates an index
-- ALTER TABLE `users` ADD INDEX `idx_agency_id` (`agency_id`);

-- Verification queries (run these to confirm the changes):
-- SHOW CREATE TABLE users;
-- SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'agency_id'; 
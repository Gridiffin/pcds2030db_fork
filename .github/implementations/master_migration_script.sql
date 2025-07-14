-- =================================================================
-- PCDS2030 DATABASE MIGRATION SCRIPT
-- FROM: pcds2030_dashboard TO: pcds2030_db structure
-- =================================================================

-- Set up migration environment
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

-- =================================================================
-- PHASE 1: SIMPLE MIGRATIONS (Identical Tables)
-- =================================================================

-- These tables have identical structure between old and new databases
-- Only data migration is needed

-- 1.1 audit_logs - Structure already matches
-- No changes needed for structure

-- 1.2 initiatives - Structure already matches  
-- No changes needed for structure

-- 1.3 notifications - Structure already matches
-- No changes needed for structure

-- 1.4 program_outcome_links - Structure already matches
-- No changes needed for structure

-- 1.5 program_submissions - Structure already matches
-- No changes needed for structure

-- 1.6 reports - Structure already matches
-- No changes needed for structure

-- =================================================================
-- PHASE 2: CREATE NEW TABLES
-- =================================================================

-- 2.1 Create targets table (missing in old database)
DROP TABLE IF EXISTS `targets`;
CREATE TABLE `targets` (
  `target_id` int NOT NULL AUTO_INCREMENT,
  `target_name` varchar(255) NOT NULL,
  `status_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =================================================================
-- PHASE 3: COMPLEX STRUCTURAL MIGRATIONS
-- =================================================================

-- 3.1 outcomes_details - Major structural changes needed
-- OLD has: outcome_type, outcome_template_id, is_draft
-- NEW has: indicator_type, agency_id (with FK)

-- Add new columns to match new structure
ALTER TABLE `outcomes_details` 
ADD COLUMN `indicator_type` varchar(100) DEFAULT NULL AFTER `is_important`,
ADD COLUMN `agency_id` int DEFAULT NULL AFTER `indicator_type`;

-- Remove columns that don't exist in new structure
ALTER TABLE `outcomes_details` 
DROP COLUMN `outcome_type`,
DROP COLUMN `outcome_template_id`, 
DROP COLUMN `is_draft`;

-- Add foreign key constraint for agency_id
ALTER TABLE `outcomes_details` 
ADD CONSTRAINT `fk_outcomes_agency` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`);

-- Add missing index
ALTER TABLE `outcomes_details` 
ADD INDEX `fk_outcomes_agency` (`agency_id`);

-- Remove outdated indexes
ALTER TABLE `outcomes_details` 
DROP INDEX `idx_outcome_type`,
DROP INDEX `idx_type_draft`;

-- =================================================================
-- 3.2 program_attachments - Major structural changes needed
-- =================================================================

-- OLD has: submission_id, original_filename, stored_filename, mime_type, upload_date, description, created_at, updated_at
-- NEW has: file_name, uploaded_at, file_size(bigint)

-- First backup existing data structure
CREATE TABLE `program_attachments_migration_backup` AS SELECT * FROM `program_attachments`;

-- Remove foreign key constraints temporarily
ALTER TABLE `program_attachments` DROP FOREIGN KEY `program_attachments_ibfk_2`;

-- Modify columns to match new structure
ALTER TABLE `program_attachments` 
DROP COLUMN `submission_id`,
DROP COLUMN `original_filename`,
DROP COLUMN `stored_filename`, 
DROP COLUMN `mime_type`,
DROP COLUMN `upload_date`,
DROP COLUMN `description`,
DROP COLUMN `created_at`,
DROP COLUMN `updated_at`;

-- Add new columns  
ALTER TABLE `program_attachments`
ADD COLUMN `file_name` varchar(255) NOT NULL AFTER `program_id`,
MODIFY COLUMN `file_size` bigint NOT NULL;

-- Rename upload column
ALTER TABLE `program_attachments`
CHANGE COLUMN `uploaded_by` `uploaded_by` int NOT NULL,
ADD COLUMN `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `uploaded_by`;

-- Remove old indexes and add new ones
ALTER TABLE `program_attachments`
DROP INDEX `idx_file_type`,
DROP INDEX `idx_program_active`,
DROP INDEX `idx_submission_active`,
DROP INDEX `idx_upload_date`,
DROP INDEX `idx_uploaded_by`;

-- Add new indexes to match new structure
ALTER TABLE `program_attachments`
ADD INDEX `idx_is_active` (`is_active`),
ADD INDEX `program_id` (`program_id`),
ADD INDEX `uploaded_by` (`uploaded_by`);

-- =================================================================
-- 3.3 programs - Major structural changes needed  
-- =================================================================

-- OLD has: sector_id, owner_agency_id, start_date, end_date, is_assigned, edit_permissions, status_indicator, agency_id
-- NEW has: agency_id, users_assigned, status, targets_linked

-- Remove columns not in new structure
ALTER TABLE `programs`
DROP COLUMN `sector_id`,
DROP COLUMN `start_date`, 
DROP COLUMN `end_date`,
DROP COLUMN `is_assigned`,
DROP COLUMN `edit_permissions`,
DROP COLUMN `status_indicator`;

-- Rename owner_agency_id to agency_id if it exists separately
-- (Note: checking the structure, it seems agency_id already exists)
-- ALTER TABLE `programs` CHANGE COLUMN `owner_agency_id` `agency_id` int NOT NULL;
ALTER TABLE `programs` DROP COLUMN `owner_agency_id`;

-- Add new columns
ALTER TABLE `programs`
ADD COLUMN `users_assigned` int DEFAULT NULL AFTER `agency_id`,
ADD COLUMN `status` set('pending','in_progress','completed','on_hold') DEFAULT NULL AFTER `attachment_count`,
ADD COLUMN `targets_linked` int DEFAULT '0' AFTER `hold_point`;

-- Add foreign key constraints
ALTER TABLE `programs`
ADD CONSTRAINT `FK_programs_agency` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`),
ADD CONSTRAINT `FK_programs_users` FOREIGN KEY (`users_assigned`) REFERENCES `users` (`user_id`);

-- Update indexes
ALTER TABLE `programs`
DROP INDEX `owner_agency_id`,
DROP INDEX `sector_id`;

ALTER TABLE `programs`
ADD INDEX `agency_id` (`agency_id`),
ADD INDEX `users_assigned` (`users_assigned`);

-- =================================================================
-- 3.4 reporting_periods - Complete restructure needed
-- =================================================================

-- OLD structure: year, quarter, start_date, end_date, status, updated_at, is_standard_dates, created_at
-- NEW structure: year, period_type, period_number, start_date, end_date, status, updated_at, created_at

-- Backup existing data for conversion
CREATE TABLE `reporting_periods_migration_backup` AS SELECT * FROM `reporting_periods`;

-- Remove old columns
ALTER TABLE `reporting_periods`
DROP COLUMN `quarter`,
DROP COLUMN `is_standard_dates`;

-- Add new columns  
ALTER TABLE `reporting_periods`
ADD COLUMN `period_type` enum('quarter','half','yearly') NOT NULL DEFAULT 'quarter' AFTER `year`,
ADD COLUMN `period_number` int NOT NULL AFTER `period_type`;

-- Remove old indexes
ALTER TABLE `reporting_periods`
DROP INDEX `quarter_year_idx`,
DROP INDEX `year`,
DROP INDEX `year_quarter`,
DROP INDEX `year_quarter_unique`;

-- Add new indexes
ALTER TABLE `reporting_periods`
ADD INDEX `period_type_year_idx` (`period_type`, `year`),
ADD UNIQUE INDEX `year_period_unique` (`year`, `period_type`, `period_number`);

-- =================================================================
-- PHASE 4: DROP LEGACY TABLES
-- =================================================================

-- Drop tables that don't exist in new database structure
-- (These should be verified for code dependencies first)

-- 4.1 Drop legacy sector-based outcome tables
-- DROP TABLE IF EXISTS `sector_outcomes_data`;
-- DROP TABLE IF EXISTS `outcome_history`;
-- DROP TABLE IF EXISTS `metrics_details`;
-- DROP TABLE IF EXISTS `sectors`;

-- 4.2 Drop backup tables
DROP TABLE IF EXISTS `sector_outcomes_data_backup_2025_06_29_15_11_05`;
DROP TABLE IF EXISTS `sector_outcomes_data_backup_2025_06_30_04_59_21`;

-- =================================================================
-- PHASE 5: DATA TRANSFORMATION AND MIGRATION
-- =================================================================

-- 5.1 Transform reporting_periods data (quarter -> period_type/period_number)
-- This needs to be run after backing up the data
/*
UPDATE `reporting_periods` SET 
    `period_type` = 'quarter',
    `period_number` = (SELECT `quarter` FROM `reporting_periods_migration_backup` WHERE `reporting_periods_migration_backup`.`period_id` = `reporting_periods`.`period_id`)
WHERE `period_id` IN (SELECT `period_id` FROM `reporting_periods_migration_backup`);
*/

-- 5.2 Transform program_attachments data
-- Map existing file information to new structure
/*
UPDATE `program_attachments` pa
JOIN `program_attachments_migration_backup` pab ON pa.`attachment_id` = pab.`attachment_id`
SET 
    pa.`file_name` = COALESCE(pab.`original_filename`, pab.`stored_filename`, 'unknown_file'),
    pa.`uploaded_at` = COALESCE(pab.`upload_date`, pab.`created_at`, CURRENT_TIMESTAMP);
*/

-- =================================================================
-- PHASE 6: DATA VALIDATION
-- =================================================================

-- 6.1 Verify record counts match expectations
/*
SELECT 'audit_logs' as table_name, COUNT(*) as record_count FROM audit_logs
UNION ALL
SELECT 'initiatives', COUNT(*) FROM initiatives  
UNION ALL
SELECT 'notifications', COUNT(*) FROM notifications
UNION ALL
SELECT 'outcomes_details', COUNT(*) FROM outcomes_details
UNION ALL
SELECT 'program_attachments', COUNT(*) FROM program_attachments
UNION ALL
SELECT 'program_outcome_links', COUNT(*) FROM program_outcome_links
UNION ALL
SELECT 'program_submissions', COUNT(*) FROM program_submissions
UNION ALL
SELECT 'programs', COUNT(*) FROM programs
UNION ALL
SELECT 'reporting_periods', COUNT(*) FROM reporting_periods
UNION ALL
SELECT 'reports', COUNT(*) FROM reports
UNION ALL
SELECT 'targets', COUNT(*) FROM targets
UNION ALL
SELECT 'users', COUNT(*) FROM users
UNION ALL
SELECT 'agency', COUNT(*) FROM agency;
*/

-- 6.2 Verify foreign key relationships
/*
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = 'pcds2030_dashboard'
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;
*/

-- =================================================================
-- RESTORE ORIGINAL SETTINGS
-- =================================================================

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- =================================================================
-- MIGRATION COMPLETE
-- =================================================================

-- To execute this migration:
-- 1. Create full database backup first
-- 2. Run in development environment for testing
-- 3. Verify all data transformations are correct
-- 4. Test application functionality
-- 5. Only then run in production with proper downtime planning

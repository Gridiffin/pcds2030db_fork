-- =================================================================
-- PCDS2030 DATABASE MIGRATION ROLLBACK SCRIPT
-- Restores pcds2030_dashboard to original structure
-- =================================================================

-- IMPORTANT: This script should only be run if migration fails
-- and you need to restore the original database structure

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

-- =================================================================
-- PHASE 1: RESTORE COMPLEX TABLES FROM BACKUPS
-- =================================================================

-- 1.1 Restore reporting_periods structure
DROP TABLE IF EXISTS `reporting_periods`;
CREATE TABLE `reporting_periods` AS SELECT * FROM `reporting_periods_migration_backup`;

-- Recreate original structure
ALTER TABLE `reporting_periods`
ADD PRIMARY KEY (`period_id`),
MODIFY COLUMN `period_id` int NOT NULL AUTO_INCREMENT,
ADD INDEX `quarter_year_idx` (`quarter`, `year`),
ADD INDEX `year` (`year`, `quarter`),
ADD INDEX `year_quarter` (`year`, `quarter`),
ADD INDEX `year_quarter_unique` (`year`, `quarter`);

-- 1.2 Restore program_attachments structure  
DROP TABLE IF EXISTS `program_attachments`;
CREATE TABLE `program_attachments` AS SELECT * FROM `program_attachments_migration_backup`;

-- Recreate original structure and constraints
ALTER TABLE `program_attachments`
ADD PRIMARY KEY (`attachment_id`),
MODIFY COLUMN `attachment_id` int NOT NULL AUTO_INCREMENT,
ADD INDEX `idx_file_type` (`file_type`),
ADD INDEX `idx_program_active` (`program_id`, `is_active`),
ADD INDEX `idx_submission_active` (`submission_id`, `is_active`),
ADD INDEX `idx_upload_date` (`upload_date`),
ADD INDEX `idx_uploaded_by` (`uploaded_by`);

-- Add foreign key constraints
ALTER TABLE `program_attachments`
ADD CONSTRAINT `program_attachments_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
ADD CONSTRAINT `program_attachments_ibfk_2` FOREIGN KEY (`submission_id`) REFERENCES `program_submissions` (`submission_id`),
ADD CONSTRAINT `program_attachments_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

-- =================================================================
-- PHASE 2: RESTORE MODIFIED TABLES
-- =================================================================

-- 2.1 Restore outcomes_details to original structure
ALTER TABLE `outcomes_details`
DROP FOREIGN KEY `fk_outcomes_agency`,
DROP INDEX `fk_outcomes_agency`,
DROP COLUMN `indicator_type`,
DROP COLUMN `agency_id`;

-- Add back original columns
ALTER TABLE `outcomes_details`
ADD COLUMN `outcome_type` enum('simple','complex') NOT NULL DEFAULT 'simple' COMMENT 'Type of outcome: simple (value-based) or complex (table/chart-based)' AFTER `detail_name`,
ADD COLUMN `outcome_template_id` int NOT NULL DEFAULT '0' COMMENT 'References the outcome template/definition this record is based on' AFTER `outcome_type`,  
ADD COLUMN `is_draft` int NOT NULL AFTER `is_cumulative`;

-- Restore original indexes
ALTER TABLE `outcomes_details`
ADD INDEX `idx_outcome_type` (`outcome_type`),
ADD INDEX `idx_type_draft` (`outcome_type`, `is_draft`);

-- 2.2 Restore programs to original structure
-- Remove new constraints first
ALTER TABLE `programs`
DROP FOREIGN KEY `FK_programs_agency`,
DROP FOREIGN KEY `FK_programs_users`,
DROP INDEX `agency_id`,
DROP INDEX `users_assigned`;

-- Remove new columns
ALTER TABLE `programs`
DROP COLUMN `users_assigned`,
DROP COLUMN `status`,  
DROP COLUMN `targets_linked`;

-- Add back original columns
ALTER TABLE `programs`
ADD COLUMN `owner_agency_id` int NOT NULL AFTER `initiative_id`,
ADD COLUMN `sector_id` int NOT NULL AFTER `owner_agency_id`,
ADD COLUMN `start_date` date DEFAULT NULL AFTER `sector_id`,
ADD COLUMN `end_date` date DEFAULT NULL AFTER `start_date`,
ADD COLUMN `is_assigned` tinyint NOT NULL DEFAULT '1' AFTER `updated_at`,
ADD COLUMN `edit_permissions` text AFTER `is_assigned`,
ADD COLUMN `status_indicator` set('pending','in_progress','completed','on_hold') DEFAULT NULL AFTER `attachment_count`;

-- Restore original indexes  
ALTER TABLE `programs`
ADD INDEX `owner_agency_id` (`owner_agency_id`),
ADD INDEX `sector_id` (`sector_id`);

-- =================================================================
-- PHASE 3: RECREATE LEGACY TABLES
-- =================================================================

-- 3.1 Recreate sectors table
CREATE TABLE `sectors` (
  `sector_id` int NOT NULL AUTO_INCREMENT,
  `sector_name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`sector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3.2 Recreate metrics_details table
CREATE TABLE `metrics_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `detail_name` varchar(255) NOT NULL,
  `detail_json` longtext NOT NULL,
  `is_draft` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`detail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3.3 Recreate outcome_history table
CREATE TABLE `outcome_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `outcome_record_id` int NOT NULL,
  `metric_id` int NOT NULL,
  `data_json` longtext NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `changed_by` int NOT NULL,
  `change_description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `fk_outcome_history_record` (`outcome_record_id`),
  KEY `fk_outcome_history_user` (`changed_by`),
  CONSTRAINT `fk_outcome_history_record` FOREIGN KEY (`outcome_record_id`) REFERENCES `sector_outcomes_data` (`id`),
  CONSTRAINT `fk_outcome_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3.4 Recreate sector_outcomes_data table (complex structure)
CREATE TABLE `sector_outcomes_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `metric_id` int NOT NULL,
  `outcome_type` enum('simple','complex') NOT NULL DEFAULT 'complex' COMMENT 'Type of outcome: simple (value-based) or complex (table/chart-based)',
  `outcome_template_id` int NOT NULL DEFAULT '0' COMMENT 'References the outcome template/definition this record is based on',
  `display_config` json DEFAULT NULL COMMENT 'JSON configuration for display settings (charts, formatting, etc.)',
  `sector_id` int NOT NULL,
  `period_id` int DEFAULT NULL,
  `table_name` varchar(255) NOT NULL,
  `data_json` longtext NOT NULL,
  `is_draft` tinyint DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int DEFAULT NULL,
  `is_important` tinyint DEFAULT '0' COMMENT 'Flag for important outcomes used in slide reports',
  `table_structure_type` enum('monthly','quarterly','yearly','custom') DEFAULT 'monthly' COMMENT 'Type of table structure: monthly (default), quarterly, yearly, or custom',
  `row_config` json DEFAULT NULL COMMENT 'JSON configuration for custom row definitions',
  `column_config` json DEFAULT NULL COMMENT 'JSON configuration for enhanced column definitions with types and units',
  PRIMARY KEY (`id`),
  KEY `fk_period_id` (`period_id`),
  KEY `fk_submitted_by` (`submitted_by`),
  KEY `idx_outcome_type` (`outcome_type`),
  KEY `idx_sector_outcomes_important` (`is_important`,`sector_id`),
  KEY `idx_structure_sector_draft` (`table_structure_type`,`sector_id`,`is_draft`),
  KEY `idx_table_structure_type` (`table_structure_type`),
  KEY `idx_type_sector_draft` (`outcome_type`,`sector_id`,`is_draft`),
  KEY `metric_sector_draft` (`metric_id`,`sector_id`,`is_draft`),
  CONSTRAINT `fk_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =================================================================
-- PHASE 4: REMOVE NEW TABLES
-- =================================================================

-- Remove tables that were added during migration
DROP TABLE IF EXISTS `targets`;

-- =================================================================
-- PHASE 5: CLEANUP MIGRATION BACKUP TABLES
-- =================================================================

DROP TABLE IF EXISTS `reporting_periods_migration_backup`;
DROP TABLE IF EXISTS `program_attachments_migration_backup`;

-- =================================================================
-- PHASE 6: VERIFICATION
-- =================================================================

-- Verify table structure is restored
SHOW TABLES;

-- Check that critical original columns are restored
DESCRIBE `outcomes_details`;
DESCRIBE `programs`;
DESCRIBE `reporting_periods`;
DESCRIBE `program_attachments`;

-- =================================================================
-- RESTORE ORIGINAL SETTINGS
-- =================================================================

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- =================================================================
-- ROLLBACK COMPLETE
-- =================================================================

SELECT 'Database structure rolled back to original state' AS status;

-- SQL Script to Remove All Sector-Related Functionality
-- This script removes sector_id columns and related constraints from the database

-- =================================================================
-- PHASE 1: Remove sector_id from sector_outcomes_data table
-- =================================================================

-- Remove sector_id column from sector_outcomes_data table
ALTER TABLE `sector_outcomes_data` DROP COLUMN IF EXISTS `sector_id`;

-- Remove sector-related indexes and constraints
ALTER TABLE `sector_outcomes_data` 
DROP INDEX IF EXISTS `metric_sector_draft`,
DROP INDEX IF EXISTS `idx_sector_outcomes_important`,
DROP INDEX IF EXISTS `idx_type_sector_draft`,
DROP INDEX IF EXISTS `idx_structure_sector_draft`;

-- Add new unique constraint without sector_id
ALTER TABLE `sector_outcomes_data` 
ADD UNIQUE KEY `metric_draft` (`metric_id`, `is_draft`);

-- Add new indexes without sector_id
ALTER TABLE `sector_outcomes_data` 
ADD INDEX `idx_important` (`is_important`),
ADD INDEX `idx_outcome_type` (`outcome_type`),
ADD INDEX `idx_type_draft` (`outcome_type`, `is_draft`),
ADD INDEX `idx_table_structure_type` (`table_structure_type`),
ADD INDEX `idx_structure_draft` (`table_structure_type`, `is_draft`);

-- =================================================================
-- PHASE 2: Update any remaining sector references
-- =================================================================

-- Note: The following tables should already have sector_id removed:
-- - users table (already removed in previous migration)
-- - programs table (already removed in previous migration)

-- =================================================================
-- PHASE 3: Verify changes
-- =================================================================

-- Check that sector_id column is removed
SELECT 'sector_outcomes_data' as table_name, 
       COUNT(*) as sector_id_columns 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'pcds2030_db' 
  AND TABLE_NAME = 'sector_outcomes_data' 
  AND COLUMN_NAME = 'sector_id';

-- Show updated table structure
DESCRIBE sector_outcomes_data;

-- =================================================================
-- MIGRATION COMPLETE
-- ================================================================= 
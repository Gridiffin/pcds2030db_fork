-- Migration: Cleanup Legacy Outcomes Tables
-- Date: 2025-01-27
-- Description: Drop legacy sector_outcomes_data and outcomes_details tables after migration to new outcomes table

-- Drop legacy tables (only run after confirming no other references exist)
-- WARNING: This will permanently delete data from these tables

-- Drop sector_outcomes_data table and related backups
DROP TABLE IF EXISTS sector_outcomes_data;
DROP TABLE IF EXISTS sector_outcomes_data_backup_2025_06_29_15_11_05;
DROP TABLE IF EXISTS sector_outcomes_data_backup_2025_06_30_04_59_21;

-- Drop outcomes_details table
DROP TABLE IF EXISTS outcomes_details;

-- Drop outcome_history table if it only referenced sector_outcomes_data
-- (Keep if it's used for other purposes)
-- DROP TABLE IF EXISTS outcome_history;

-- Verify cleanup
SELECT 'Legacy outcomes tables dropped successfully' as status; 
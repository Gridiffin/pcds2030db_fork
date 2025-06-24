-- Migration: Add is_cumulative column to outcomes_details
-- Date: 2025-06-23
-- Description: Add is_cumulative flag to outcomes_details table for enhanced graphing

ALTER TABLE `outcomes_details` 
ADD COLUMN `is_cumulative` TINYINT(1) NOT NULL DEFAULT 0 AFTER `detail_json`,
ADD INDEX `idx_is_cumulative` (`is_cumulative`);

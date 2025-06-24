-- Migration: Remove pillar_id column from initiatives table
-- Date: 2025-06-24
-- Description: Remove unused pillar_id field from initiatives table

-- Drop the pillar_id column
ALTER TABLE `initiatives` DROP COLUMN `pillar_id`;

-- Migration: Add flexible table structure fields to sector_outcomes_data
-- Date: 2025-06-26
-- Description: Adds fields to support flexible row and column configurations beyond monthly data

-- Add new columns for flexible table structure
ALTER TABLE sector_outcomes_data 
ADD COLUMN table_structure_type ENUM('monthly', 'quarterly', 'yearly', 'custom') DEFAULT 'monthly' COMMENT 'Type of table structure: monthly (default), quarterly, yearly, or custom',
ADD COLUMN row_config JSON DEFAULT NULL COMMENT 'JSON configuration for custom row definitions',
ADD COLUMN column_config JSON DEFAULT NULL COMMENT 'JSON configuration for enhanced column definitions with types and units';

-- Create index for better performance on table structure type queries
CREATE INDEX idx_table_structure_type ON sector_outcomes_data(table_structure_type);
CREATE INDEX idx_structure_sector_draft ON sector_outcomes_data(table_structure_type, sector_id, is_draft);

-- Update existing records to have 'monthly' structure type and proper configs
UPDATE sector_outcomes_data 
SET table_structure_type = 'monthly',
    row_config = JSON_OBJECT(
        'type', 'monthly',
        'rows', JSON_ARRAY(
            JSON_OBJECT('id', 'January', 'label', 'January', 'type', 'data'),
            JSON_OBJECT('id', 'February', 'label', 'February', 'type', 'data'),
            JSON_OBJECT('id', 'March', 'label', 'March', 'type', 'data'),
            JSON_OBJECT('id', 'April', 'label', 'April', 'type', 'data'),
            JSON_OBJECT('id', 'May', 'label', 'May', 'type', 'data'),
            JSON_OBJECT('id', 'June', 'label', 'June', 'type', 'data'),
            JSON_OBJECT('id', 'July', 'label', 'July', 'type', 'data'),
            JSON_OBJECT('id', 'August', 'label', 'August', 'type', 'data'),
            JSON_OBJECT('id', 'September', 'label', 'September', 'type', 'data'),
            JSON_OBJECT('id', 'October', 'label', 'October', 'type', 'data'),
            JSON_OBJECT('id', 'November', 'label', 'November', 'type', 'data'),
            JSON_OBJECT('id', 'December', 'label', 'December', 'type', 'data')
        )
    )
WHERE table_structure_type IS NULL OR table_structure_type = 'monthly';

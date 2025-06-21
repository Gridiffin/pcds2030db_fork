-- Program Numbering Feature Migration
-- Date: 2025-06-22
-- Description: Add program_number column to programs table for easier identification and mapping

-- Add program_number column to programs table
ALTER TABLE programs 
ADD COLUMN program_number VARCHAR(20) NULL 
COMMENT 'Optional program identifier for easier mapping to initiatives (e.g., 31.1, 2.5.3)';

-- Add index on program_number for better search performance
CREATE INDEX idx_programs_program_number ON programs(program_number);

-- Update any existing test data (optional - only if needed)
-- UPDATE programs SET program_number = '1.1' WHERE program_id = 1;
-- UPDATE programs SET program_number = '1.2' WHERE program_id = 2;

-- Verify the changes
-- SELECT 
--     COLUMN_NAME, 
--     DATA_TYPE, 
--     IS_NULLABLE, 
--     COLUMN_DEFAULT,
--     COLUMN_COMMENT
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'programs' 
-- AND COLUMN_NAME = 'program_number';

-- Show indexes on programs table
-- SHOW INDEXES FROM programs WHERE Key_name LIKE '%program_number%';

-- Migration: Add initiative_id to programs table
-- Date: 2025-06-23
-- Description: Add foreign key initiative_id to programs table to link programs to initiatives

ALTER TABLE `programs` 
ADD COLUMN `initiative_id` INT NULL AFTER `program_number`,
ADD INDEX `idx_initiative_id` (`initiative_id`);

-- Note: Foreign key constraint will be added after we ensure data integrity
-- ALTER TABLE `programs` 
-- ADD CONSTRAINT `fk_programs_initiative` 
-- FOREIGN KEY (`initiative_id`) REFERENCES `initiatives`(`initiative_id`) 
-- ON DELETE SET NULL ON UPDATE CASCADE;

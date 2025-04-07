-- Migration to update status enum values in program_submissions table

-- Temporarily disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Create backup of existing data
-- CREATE TABLE IF NOT EXISTS program_submissions_backup LIKE program_submissions;
-- INSERT INTO program_submissions_backup SELECT * FROM program_submissions;

-- Update the enum values in the status column
ALTER TABLE program_submissions 
MODIFY COLUMN status ENUM('target-achieved', 'on-track-yearly', 'severe-delay', 'not-started') NOT NULL;

-- Convert existing values to new enum values
UPDATE program_submissions SET status = 'target-achieved' WHERE status = 'on-track';
UPDATE program_submissions SET status = 'on-track-yearly' WHERE status = 'delayed';
UPDATE program_submissions SET status = 'severe-delay' WHERE status = 'completed';
-- 'not-started' remains the same

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

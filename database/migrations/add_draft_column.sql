-- Add is_draft column to program_submissions table
ALTER TABLE `program_submissions` 
ADD COLUMN `is_draft` tinyint(1) NOT NULL DEFAULT 0 AFTER `updated_at`;

-- Add index for efficient querying of drafts
ALTER TABLE `program_submissions` 
ADD INDEX `idx_program_period_draft` (`program_id`, `period_id`, `is_draft`);

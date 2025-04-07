-- Direct replacement of program_submissions table with minimal structure
-- Most data will be stored in the content_json field

-- Drop existing table
DROP TABLE IF EXISTS program_submissions;

-- Create minimal table structure
CREATE TABLE `program_submissions` (
  `submission_id` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `status` enum('on-track','delayed','completed','not-started') NOT NULL,
  `content_json` text DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`submission_id`),
  KEY `program_id` (`program_id`),
  KEY `period_id` (`period_id`),
  KEY `submitted_by` (`submitted_by`),
  KEY `idx_status` (`status`)
);

-- Add foreign key constraints
ALTER TABLE program_submissions
  ADD CONSTRAINT `program_submissions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
  ADD CONSTRAINT `program_submissions_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  ADD CONSTRAINT `program_submissions_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`);

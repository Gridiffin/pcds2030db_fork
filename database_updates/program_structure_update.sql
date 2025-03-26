-- Program structure updates

-- Check for existing columns
SET @is_assigned_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'programs'
    AND COLUMN_NAME = 'is_assigned'
);

SET @created_by_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'programs'
    AND COLUMN_NAME = 'created_by'
);

SET @target_date_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'program_submissions'
    AND COLUMN_NAME = 'target_date'
);

SET @status_date_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'program_submissions'
    AND COLUMN_NAME = 'status_date'
);

SET @updated_at_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'program_submissions'
    AND COLUMN_NAME = 'updated_at'
);

-- Add columns conditionally using prepared statements
SET @add_is_assigned = IF(@is_assigned_exists = 0, 'ALTER TABLE programs ADD COLUMN is_assigned TINYINT(1) NOT NULL DEFAULT 1', 'SELECT 1');
SET @add_created_by = IF(@created_by_exists = 0, 'ALTER TABLE programs ADD COLUMN created_by INT NOT NULL DEFAULT 1', 'SELECT 1');
SET @add_target_date = IF(@target_date_exists = 0, 'ALTER TABLE program_submissions ADD COLUMN target_date DATE NULL AFTER target', 'SELECT 1');
SET @add_status_date = IF(@status_date_exists = 0, 'ALTER TABLE program_submissions ADD COLUMN status_date DATE NULL AFTER status', 'SELECT 1');
SET @add_updated_at = IF(@updated_at_exists = 0, 'ALTER TABLE program_submissions ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'SELECT 1');

-- Execute the statements
PREPARE stmt FROM @add_is_assigned;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt FROM @add_created_by;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt FROM @add_target_date;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt FROM @add_status_date;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

PREPARE stmt FROM @add_updated_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing records (only if needed)
UPDATE programs SET is_assigned = 1 WHERE is_assigned IS NULL;
UPDATE programs SET created_by = owner_agency_id WHERE created_by = 1;

-- Update existing submissions only if the columns were just added
SET @update_submission_dates = IF(@target_date_exists = 0 OR @status_date_exists = 0, 'UPDATE program_submissions SET target_date = IFNULL(target_date, submission_date), status_date = IFNULL(status_date, submission_date) WHERE target_date IS NULL OR status_date IS NULL', 'SELECT 1');
PREPARE stmt FROM @update_submission_dates;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

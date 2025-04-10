-- Add is_standard_dates column to reporting_periods table
ALTER TABLE reporting_periods ADD COLUMN is_standard_dates BOOLEAN DEFAULT 1;

-- Update existing periods to mark which ones follow standard quarter dates
UPDATE reporting_periods SET is_standard_dates = 
(
    CASE 
        WHEN (quarter = 1 AND start_date = CONCAT(year, '-01-01') AND end_date = CONCAT(year, '-03-31')) THEN 1
        WHEN (quarter = 2 AND start_date = CONCAT(year, '-04-01') AND end_date = CONCAT(year, '-06-30')) THEN 1
        WHEN (quarter = 3 AND start_date = CONCAT(year, '-07-01') AND end_date = CONCAT(year, '-09-30')) THEN 1
        WHEN (quarter = 4 AND start_date = CONCAT(year, '-10-01') AND end_date = CONCAT(year, '-12-31')) THEN 1
        ELSE 0
    END
);

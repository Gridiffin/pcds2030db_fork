-- Add period_id column to sector_metrics_data table
ALTER TABLE sector_metrics_data 
ADD COLUMN period_id INT NULL AFTER sector_id, 
ADD CONSTRAINT fk_period_id FOREIGN KEY (period_id) REFERENCES reporting_periods(period_id);

-- Update the existing records to use the current open period
UPDATE sector_metrics_data SET period_id = (
    SELECT period_id FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, quarter DESC LIMIT 1
) WHERE period_id IS NULL;
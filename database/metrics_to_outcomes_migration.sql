-- Migration script for changing "metrics" to "outcomes" in database tables
USE pcds2030_dashboard;

-- Create new tables
CREATE TABLE outcomes_details LIKE metrics_details;
INSERT INTO outcomes_details SELECT * FROM metrics_details;

CREATE TABLE sector_outcomes_data LIKE sector_metrics_data;
INSERT INTO sector_outcomes_data SELECT * FROM sector_metrics_data;

-- Update foreign key references if needed
-- For now, we'll keep both sets of tables until the transition is complete

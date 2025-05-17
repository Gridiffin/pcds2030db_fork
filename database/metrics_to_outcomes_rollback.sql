-- Rollback script for metrics to outcomes migration
USE pcds2030_dashboard;

-- If we need to restore the original tables (in case of data loss)
-- Note: This is only needed if the original tables were dropped or modified

-- 1. Clean up the new tables if necessary
DROP TABLE IF EXISTS outcomes_details;
DROP TABLE IF EXISTS sector_outcomes_data;

-- 2. Restore any modified data in the original tables if needed
-- (Only necessary if original tables were modified during migration)

-- 3. Remove compatibility files if desired
-- Note: This would be done manually by removing the files:
--   - includes/agencies/outcomes.php
--   - includes/admins/outcomes.php
--   - api/check_outcome.php
--   - api/get_outcome_data.php
--   - api/save_outcome_json.php
--   - assets/js/outcome-editor.js
--   - assets/js/charts/outcomes-chart.js

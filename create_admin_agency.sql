-- Add Agency ID 4 for Admin Users
-- Database: pcds2030_dashboard

USE pcds2030_dashboard;

INSERT INTO agency (agency_id, agency_name, created_at, updated_at) 
VALUES (4, 'ADMIN_AGENCY', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    agency_name = VALUES(agency_name),
    updated_at = NOW();

-- Verify the insertion
SELECT * FROM agency WHERE agency_id = 4; 
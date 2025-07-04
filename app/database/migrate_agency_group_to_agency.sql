-- Migration script: Convert agency_group to agency and update references
-- 1. Rename the table
RENAME TABLE agency_group TO agency;

-- 2. Rename columns in the agency table
ALTER TABLE agency
  CHANGE agency_group_id agency_id INT NOT NULL AUTO_INCREMENT,
  CHANGE group_name agency_name VARCHAR(255) NOT NULL;
-- (sector_id column is kept as is; remove if not needed)

-- 3. Update references in users table
ALTER TABLE users
  CHANGE agency_group_id agency_id INT NOT NULL;

-- 4. Update any foreign keys or indexes if needed (example shown, adjust as needed)
-- DROP FOREIGN KEY fk_users_agency_group_id; -- Uncomment and adjust if you have a foreign key
-- ALTER TABLE users ADD CONSTRAINT fk_users_agency_id FOREIGN KEY (agency_id) REFERENCES agency(agency_id);

-- 5. (Optional) Update any other tables referencing agency_group_id
-- Add similar ALTER TABLE statements for any other tables that reference agency_group_id

-- 6. (Optional) Review and update any triggers, procedures, or views that reference agency_group or agency_group_id

-- Always backup your database before running this script! 
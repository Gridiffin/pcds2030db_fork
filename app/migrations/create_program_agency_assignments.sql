-- Program Agency Assignments Migration
-- Creates table for managing which agencies can access which programs with different permission levels

-- Create the program_agency_assignments table
CREATE TABLE IF NOT EXISTS program_agency_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    agency_id INT NOT NULL,
    role ENUM('owner', 'editor', 'viewer') NOT NULL DEFAULT 'viewer',
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    notes TEXT NULL,
    
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE,
    FOREIGN KEY (agency_id) REFERENCES agency(agency_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(user_id),
    
    UNIQUE KEY unique_program_agency (program_id, agency_id),
    INDEX idx_program_id (program_id),
    INDEX idx_agency_id (agency_id),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);

-- Migrate existing programs to use the new assignment system
-- Set the original agency as 'owner' for each program
INSERT INTO program_agency_assignments (program_id, agency_id, role, assigned_by, notes)
SELECT 
    p.program_id,
    p.agency_id,
    'owner' as role,
    p.created_by as assigned_by,
    'Migrated from original program ownership' as notes
FROM programs p
WHERE p.agency_id IS NOT NULL
  AND p.is_deleted = 0
  AND NOT EXISTS (
      SELECT 1 FROM program_agency_assignments paa 
      WHERE paa.program_id = p.program_id AND paa.agency_id = p.agency_id
  );

-- Add indexes for better performance
CREATE INDEX idx_program_agency_role ON program_agency_assignments(program_id, agency_id, role);
CREATE INDEX idx_assignment_lookup ON program_agency_assignments(program_id, is_active);

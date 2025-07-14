-- User-Level Permissions Migration
-- Adds user-level permissions within agencies for programs

-- Add restrict_editors column to programs table
ALTER TABLE programs ADD COLUMN IF NOT EXISTS restrict_editors TINYINT(1) DEFAULT 0 
COMMENT 'Whether to restrict editing to specific users within the agency (0=all can edit, 1=only assigned users can edit)';

-- Create the program_user_assignments table
CREATE TABLE IF NOT EXISTS program_user_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('editor', 'viewer') NOT NULL DEFAULT 'viewer',
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    notes TEXT NULL,
    
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(user_id),
    
    UNIQUE KEY unique_program_user (program_id, user_id),
    INDEX idx_program_id (program_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);

-- Add performance indexes
CREATE INDEX IF NOT EXISTS idx_program_user_role ON program_user_assignments(program_id, user_id, role);
CREATE INDEX IF NOT EXISTS idx_user_assignment_lookup ON program_user_assignments(user_id, is_active);
CREATE INDEX IF NOT EXISTS idx_restrict_editors ON programs(restrict_editors);

-- Note: No automatic migration needed as this adds new functionality
-- Programs will default to restrict_editors = 0 (all agency users can edit)
-- User assignments will be managed through the application interface

-- Program Attachments Feature Database Migration
-- This migration adds file attachment support to the existing program management system

-- Create program_attachments table
CREATE TABLE program_attachments (
    attachment_id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    submission_id INT NULL COMMENT 'Links to specific submission if needed for version control',
    
    -- File information
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL COMMENT 'File size in bytes',
    file_type VARCHAR(100) NOT NULL COMMENT 'File extension',
    mime_type VARCHAR(100) NOT NULL,
    
    -- Upload metadata
    uploaded_by INT NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT COMMENT 'User-provided description of the file',
    
    -- Status and lifecycle
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Soft delete flag',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE,
    FOREIGN KEY (submission_id) REFERENCES program_submissions(submission_id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id),
    
    -- Indexes for performance
    INDEX idx_program_active (program_id, is_active),
    INDEX idx_submission_active (submission_id, is_active),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_upload_date (upload_date),
    INDEX idx_file_type (file_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add attachment counter to programs table (optional - for quick counts)
ALTER TABLE programs 
ADD COLUMN attachment_count INT DEFAULT 0 COMMENT 'Cached count of active attachments';

-- Create trigger to maintain attachment count (optional)
DELIMITER $$

CREATE TRIGGER tr_program_attachments_insert
    AFTER INSERT ON program_attachments
    FOR EACH ROW
BEGIN
    IF NEW.is_active = 1 THEN
        UPDATE programs 
        SET attachment_count = attachment_count + 1 
        WHERE program_id = NEW.program_id;
    END IF;
END$$

CREATE TRIGGER tr_program_attachments_update
    AFTER UPDATE ON program_attachments
    FOR EACH ROW
BEGIN
    -- Handle activation/deactivation
    IF OLD.is_active != NEW.is_active THEN
        IF NEW.is_active = 1 THEN
            UPDATE programs 
            SET attachment_count = attachment_count + 1 
            WHERE program_id = NEW.program_id;
        ELSE
            UPDATE programs 
            SET attachment_count = attachment_count - 1 
            WHERE program_id = NEW.program_id;
        END IF;
    END IF;
END$$

CREATE TRIGGER tr_program_attachments_delete
    AFTER DELETE ON program_attachments
    FOR EACH ROW
BEGIN
    IF OLD.is_active = 1 THEN
        UPDATE programs 
        SET attachment_count = attachment_count - 1 
        WHERE program_id = OLD.program_id;
    END IF;
END$$

DELIMITER ;

-- Insert initial audit log entry for this migration
INSERT INTO audit_logs (user_id, action, details, status, created_at) 
VALUES (1, 'database_migration', 'Added program_attachments table and related triggers for file attachment support', 'success', NOW());

-- Sample data for testing (remove after testing)
-- INSERT INTO program_attachments (program_id, original_filename, stored_filename, file_path, file_size, file_type, mime_type, uploaded_by, description)
-- VALUES (1, 'project_plan.pdf', '20241220_abc123_project_plan.pdf', '/uploads/programs/attachments/1/20241220_abc123_project_plan.pdf', 1024000, 'pdf', 'application/pdf', 1, 'Initial project planning document');

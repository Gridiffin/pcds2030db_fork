-- =====================================================
-- PCDS 2030 Dashboard - Program Logic Redesign Schema
-- =====================================================
-- This file contains the complete database schema for the
-- redesigned program management system.
-- 
-- Requirements:
-- 1. One initiative → many programs
-- 2. One program → many targets
-- 3. Enhanced status tracking and ratings
-- 4. Cross-period target support
-- 5. File attachment management
-- 6. Audit integration
-- =====================================================

-- Set foreign key checks off temporarily for clean recreation
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. PROGRAMS TABLE (Enhanced)
-- =====================================================
DROP TABLE IF EXISTS `programs`;
CREATE TABLE `programs` (
  `program_id` int(10) NOT NULL AUTO_INCREMENT,
  `program_name` varchar(255) NOT NULL,
  `program_number` varchar(20) DEFAULT NULL,
  `program_description` text DEFAULT NULL,
  `initiative_id` int(10) DEFAULT NULL,
  `agency_id` int(10) NOT NULL,
  `status_indicator` enum('not_started', 'in_progress', 'delayed', 'completed') DEFAULT 'not_started',
  `program_rating` enum('monthly_target_achieved', 'on_track_for_year', 'severe_delay', 'not_started') DEFAULT 'not_started',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `users_assigned` varchar(500) DEFAULT NULL COMMENT 'JSON array of user IDs assigned to this program',
  `created_by` int(10) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_rating_update` timestamp NULL DEFAULT NULL,
  `rating_updated_by` int(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `attachment_count` int(10) DEFAULT 0 COMMENT 'Cached count of active attachments',
  `targets_count` int(10) DEFAULT 0 COMMENT 'Cached count of active targets',
  PRIMARY KEY (`program_id`),
  KEY `idx_program_number` (`program_number`),
  KEY `idx_initiative_id` (`initiative_id`),
  KEY `idx_agency_id` (`agency_id`),
  KEY `idx_status_indicator` (`status_indicator`),
  KEY `idx_program_rating` (`program_rating`),
  KEY `idx_is_active` (`is_active`),
  KEY `fk_programs_rating_updater` (`rating_updated_by`),
  CONSTRAINT `FK_programs_agency` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `FK_programs_initiative` FOREIGN KEY (`initiative_id`) REFERENCES `initiatives` (`initiative_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_programs_rating_updater` FOREIGN KEY (`rating_updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. PROGRAM TARGETS TABLE
-- =====================================================
DROP TABLE IF EXISTS `program_targets`;
CREATE TABLE `program_targets` (
  `target_id` int(10) NOT NULL AUTO_INCREMENT,
  `program_id` int(10) NOT NULL,
  `target_number` int(10) NOT NULL,
  `target_description` text NOT NULL,
  `status_indicator` enum('not_started', 'in_progress', 'delayed', 'completed') DEFAULT 'not_started',
  `status_description` text DEFAULT NULL COMMENT 'Detailed status description',
  `remarks` text DEFAULT NULL COMMENT 'Target-specific remarks',
  `unit_of_measure` varchar(100) DEFAULT NULL,
  `baseline_value` decimal(15,2) DEFAULT NULL,
  `baseline_year` year DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `created_by` int(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_status_update` timestamp NULL DEFAULT NULL,
  `status_updated_by` int(10) DEFAULT NULL,
  PRIMARY KEY (`target_id`),
  UNIQUE KEY `unique_program_target` (`program_id`, `target_number`),
  KEY `idx_program_targets_program_id` (`program_id`),
  KEY `idx_program_targets_active` (`is_active`),
  KEY `idx_target_status` (`status_indicator`, `is_active`),
  KEY `idx_target_dates` (`start_date`, `end_date`, `completion_date`),
  KEY `fk_targets_status_updater` (`status_updated_by`),
  KEY `fk_targets_creator` (`created_by`),
  CONSTRAINT `program_targets_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_targets_status_updater` FOREIGN KEY (`status_updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_targets_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. TARGET PERIOD STATUS TABLE (Enhanced)
-- =====================================================
DROP TABLE IF EXISTS `target_period_status`;
CREATE TABLE `target_period_status` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `target_id` int(10) NOT NULL,
  `period_id` int(10) NOT NULL,
  `target_value` decimal(15,2) DEFAULT NULL,
  `actual_value` decimal(15,2) DEFAULT NULL,
  `status` enum('not_started', 'in_progress', 'completed', 'delayed', 'cancelled') DEFAULT 'not_started',
  `timeline_status` enum('on_track', 'at_risk', 'behind', 'completed') DEFAULT 'on_track',
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL COMMENT 'Period-specific remarks',
  `carry_forward` tinyint(1) DEFAULT 0 COMMENT 'Support for cross-period targets',
  `carry_forward_from_period` int(10) DEFAULT NULL COMMENT 'Previous period this was carried from',
  `carry_forward_to_period` int(10) DEFAULT NULL COMMENT 'Next period this will carry to',
  `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(10) DEFAULT NULL,
  `is_draft` tinyint(1) DEFAULT 1 COMMENT 'Whether this is a draft submission',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_target_period` (`target_id`, `period_id`),
  KEY `idx_target_period_target_id` (`period_id`),
  KEY `idx_target_period_period_id` (`period_id`),
  KEY `idx_target_period_status` (`status`),
  KEY `idx_target_period_timeline` (`timeline_status`),
  KEY `idx_target_period_carry_forward` (`carry_forward`),
  KEY `idx_target_period_draft` (`is_draft`),
  KEY `fk_target_period_updated_by` (`updated_by`),
  KEY `fk_carry_forward_from` (`carry_forward_from_period`),
  KEY `fk_carry_forward_to` (`carry_forward_to_period`),
  CONSTRAINT `target_period_status_ibfk_1` FOREIGN KEY (`target_id`) REFERENCES `program_targets` (`target_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `target_period_status_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_target_period_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_carry_forward_from` FOREIGN KEY (`carry_forward_from_period`) REFERENCES `reporting_periods` (`period_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_carry_forward_to` FOREIGN KEY (`carry_forward_to_period`) REFERENCES `reporting_periods` (`period_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. PROGRAM ATTACHMENTS TABLE (Enhanced)
-- =====================================================
DROP TABLE IF EXISTS `program_attachments`;
CREATE TABLE `program_attachments` (
  `attachment_id` int(10) NOT NULL AUTO_INCREMENT,
  `program_id` int(10) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL COMMENT 'Original uploaded filename',
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL COMMENT 'File description/notes',
  `uploaded_by` int(10) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT 1,
  `download_count` int(10) DEFAULT 0 COMMENT 'Track download statistics',
  `last_accessed` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`attachment_id`),
  KEY `idx_program_attachments_program_id` (`program_id`),
  KEY `idx_program_attachments_active` (`is_active`),
  KEY `idx_program_attachments_uploaded_by` (`uploaded_by`),
  KEY `idx_program_attachments_file_type` (`file_type`),
  CONSTRAINT `program_attachments_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `program_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. PROGRAM SUBMISSIONS TABLE (Enhanced)
-- =====================================================
DROP TABLE IF EXISTS `program_submissions`;
CREATE TABLE `program_submissions` (
  `submission_id` int(10) NOT NULL AUTO_INCREMENT,
  `program_id` int(10) NOT NULL,
  `period_id` int(10) NOT NULL,
  `submission_type` enum('draft', 'final') DEFAULT 'draft',
  `submission_data` longtext DEFAULT NULL COMMENT 'JSON data for flexible submission content',
  `submitted_by` int(10) NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `version_number` int(10) DEFAULT 1 COMMENT 'Track submission versions',
  `previous_submission_id` int(10) DEFAULT NULL COMMENT 'Link to previous version',
  `approval_status` enum('pending', 'approved', 'rejected', 'needs_revision') DEFAULT 'pending',
  `approved_by` int(10) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  PRIMARY KEY (`submission_id`),
  KEY `idx_program_submissions_program_period` (`program_id`, `period_id`),
  KEY `idx_program_submissions_period_id` (`period_id`),
  KEY `idx_program_submissions_submitted_by` (`submitted_by`),
  KEY `idx_program_submissions_type` (`submission_type`),
  KEY `idx_program_submissions_approval` (`approval_status`),
  KEY `fk_previous_submission` (`previous_submission_id`),
  KEY `fk_approved_by` (`approved_by`),
  CONSTRAINT `program_submissions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `program_submissions_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `program_submissions_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_previous_submission` FOREIGN KEY (`previous_submission_id`) REFERENCES `program_submissions` (`submission_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. TARGET STATUS HISTORY TABLE (Enhanced)
-- =====================================================
DROP TABLE IF EXISTS `target_status_history`;
CREATE TABLE `target_status_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `target_period_status_id` int(10) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_by` int(10) NOT NULL,
  `change_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `change_reason` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_target_history_target_period` (`target_period_status_id`),
  KEY `idx_target_history_field` (`field_name`),
  KEY `idx_target_history_user` (`changed_by`),
  KEY `idx_target_history_date` (`change_date`),
  CONSTRAINT `target_status_history_ibfk_1` FOREIGN KEY (`target_period_status_id`) REFERENCES `target_period_status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `target_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. PROGRAM OUTCOME LINKS TABLE (Enhanced)
-- =====================================================
DROP TABLE IF EXISTS `program_outcome_links`;
CREATE TABLE `program_outcome_links` (
  `link_id` int(10) NOT NULL AUTO_INCREMENT,
  `program_id` int(10) NOT NULL,
  `outcome_id` int(10) NOT NULL,
  `created_by` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `link_type` enum('primary', 'secondary', 'contributing') DEFAULT 'primary' COMMENT 'Type of outcome relationship',
  `weight` decimal(3,2) DEFAULT 1.00 COMMENT 'Weight of this program contribution to outcome',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`link_id`),
  UNIQUE KEY `unique_program_outcome` (`program_id`, `outcome_id`),
  KEY `idx_program_id` (`program_id`),
  KEY `idx_outcome_id` (`outcome_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_link_type` (`link_type`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_pol_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pol_outcome` FOREIGN KEY (`outcome_id`) REFERENCES `outcomes_details` (`detail_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pol_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- TRIGGERS FOR AUDIT LOGGING
-- =====================================================

-- Programs table triggers
DELIMITER $$
CREATE TRIGGER tr_programs_insert AFTER INSERT ON programs 
FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action, details, status, created_at)
    VALUES (NEW.created_by, 'create_program', 
            CONCAT('Created program: ', NEW.program_name, ' (ID: ', NEW.program_id, ')'), 
            'success', NOW());
END$$

CREATE TRIGGER tr_programs_update AFTER UPDATE ON programs 
FOR EACH ROW BEGIN
    DECLARE audit_id INT;
    INSERT INTO audit_logs (user_id, action, details, status, created_at)
    VALUES (IFNULL(NEW.rating_updated_by, NEW.created_by), 'update_program', 
            CONCAT('Updated program: ', NEW.program_name, ' (ID: ', NEW.program_id, ')'), 
            'success', NOW());
    
    SET audit_id = LAST_INSERT_ID();
    
    -- Track specific field changes
    IF OLD.program_name != NEW.program_name THEN
        INSERT INTO audit_field_changes (audit_log_id, field_name, field_type, old_value, new_value, change_type)
        VALUES (audit_id, 'program_name', 'text', OLD.program_name, NEW.program_name, 'modified');
    END IF;
    
    IF OLD.program_description != NEW.program_description THEN
        INSERT INTO audit_field_changes (audit_log_id, field_name, field_type, old_value, new_value, change_type)
        VALUES (audit_id, 'program_description', 'text', OLD.program_description, NEW.program_description, 'modified');
    END IF;
    
    IF OLD.status_indicator != NEW.status_indicator THEN
        INSERT INTO audit_field_changes (audit_log_id, field_name, field_type, old_value, new_value, change_type)
        VALUES (audit_id, 'status_indicator', 'enum', OLD.status_indicator, NEW.status_indicator, 'modified');
    END IF;
    
    IF OLD.program_rating != NEW.program_rating THEN
        INSERT INTO audit_field_changes (audit_log_id, field_name, field_type, old_value, new_value, change_type)
        VALUES (audit_id, 'program_rating', 'enum', OLD.program_rating, NEW.program_rating, 'modified');
    END IF;
END$$

CREATE TRIGGER tr_programs_delete AFTER DELETE ON programs 
FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action, details, status, created_at)
    VALUES (OLD.created_by, 'delete_program', 
            CONCAT('Deleted program: ', OLD.program_name, ' (ID: ', OLD.program_id, ')'), 
            'success', NOW());
END$$

-- Program targets table triggers
CREATE TRIGGER tr_program_targets_insert AFTER INSERT ON program_targets 
FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action, details, status, created_at)
    VALUES (NEW.created_by, 'create_target', 
            CONCAT('Created target: ', NEW.target_description, ' (ID: ', NEW.target_id, ', Program: ', NEW.program_id, ')'), 
            'success', NOW());
    
    -- Update target count cache
    UPDATE programs SET targets_count = targets_count + 1 WHERE program_id = NEW.program_id;
END$$

CREATE TRIGGER tr_program_targets_update AFTER UPDATE ON program_targets 
FOR EACH ROW BEGIN
    DECLARE audit_id INT;
    INSERT INTO audit_logs (user_id, action, details, status, created_at)
    VALUES (IFNULL(NEW.status_updated_by, NEW.created_by), 'update_target', 
            CONCAT('Updated target: ', NEW.target_description, ' (ID: ', NEW.target_id, ')'), 
            'success', NOW());
    
    SET audit_id = LAST_INSERT_ID();
    
    -- Track specific field changes
    IF OLD.target_description != NEW.target_description THEN
        INSERT INTO audit_field_changes (audit_log_id, field_name, field_type, old_value, new_value, change_type)
        VALUES (audit_id, 'target_description', 'text', OLD.target_description, NEW.target_description, 'modified');
    END IF;
    
    IF OLD.status_indicator != NEW.status_indicator THEN
        INSERT INTO audit_field_changes (audit_log_id, field_name, field_type, old_value, new_value, change_type)
        VALUES (audit_id, 'status_indicator', 'enum', OLD.status_indicator, NEW.status_indicator, 'modified');
    END IF;
    
    IF OLD.status_description != NEW.status_description THEN
        INSERT INTO audit_field_changes (audit_log_id, field_name, field_type, old_value, new_value, change_type)
        VALUES (audit_id, 'status_description', 'text', OLD.status_description, NEW.status_description, 'modified');
    END IF;
END$$

CREATE TRIGGER tr_program_targets_delete AFTER DELETE ON program_targets 
FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action, details, status, created_at)
    VALUES (OLD.created_by, 'delete_target', 
            CONCAT('Deleted target: ', OLD.target_description, ' (ID: ', OLD.target_id, ')'), 
            'success', NOW());
    
    -- Update target count cache
    UPDATE programs SET targets_count = targets_count - 1 WHERE program_id = OLD.program_id;
END$$

-- Program attachments table triggers
CREATE TRIGGER tr_program_attachments_insert AFTER INSERT ON program_attachments 
FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action, details, status, created_at)
    VALUES (NEW.uploaded_by, 'upload_attachment', 
            CONCAT('Uploaded file: ', NEW.file_name, ' (ID: ', NEW.attachment_id, ', Program: ', NEW.program_id, ')'), 
            'success', NOW());
    
    -- Update attachment count cache
    UPDATE programs SET attachment_count = attachment_count + 1 WHERE program_id = NEW.program_id;
END$$

CREATE TRIGGER tr_program_attachments_update AFTER UPDATE ON program_attachments 
FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action, details, status, created_at)
    VALUES (NEW.uploaded_by, 'update_attachment', 
            CONCAT('Updated file: ', NEW.file_name, ' (ID: ', NEW.attachment_id, ')'), 
            'success', NOW());
END$$

CREATE TRIGGER tr_program_attachments_delete AFTER DELETE ON program_attachments 
FOR EACH ROW BEGIN
    INSERT INTO audit_logs (user_id, action, details, status, created_at)
    VALUES (OLD.uploaded_by, 'delete_attachment', 
            CONCAT('Deleted file: ', OLD.file_name, ' (ID: ', OLD.attachment_id, ')'), 
            'success', NOW());
    
    -- Update attachment count cache
    UPDATE programs SET attachment_count = attachment_count - 1 WHERE program_id = OLD.program_id;
END$$

DELIMITER ;

-- =====================================================
-- INITIAL DATA AND INDEXES
-- =====================================================

-- Create additional indexes for performance
CREATE INDEX idx_programs_agency_status ON programs(agency_id, status_indicator);
CREATE INDEX idx_programs_initiative_status ON programs(initiative_id, status_indicator);
CREATE INDEX idx_targets_program_status ON program_targets(program_id, status_indicator, is_active);
CREATE INDEX idx_target_period_carry_forward ON target_period_status(carry_forward, period_id);
CREATE INDEX idx_program_submissions_latest ON program_submissions(program_id, period_id, version_number DESC);

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- View for program summary with counts
CREATE OR REPLACE VIEW v_program_summary AS
SELECT 
    p.program_id,
    p.program_name,
    p.program_number,
    p.program_description,
    p.status_indicator,
    p.program_rating,
    p.start_date,
    p.end_date,
    i.initiative_name,
    a.agency_name,
    u.fullname as created_by_name,
    p.created_at,
    p.targets_count,
    p.attachment_count,
    (SELECT COUNT(*) FROM program_targets pt WHERE pt.program_id = p.program_id AND pt.is_active = 1) as active_targets,
    (SELECT COUNT(*) FROM program_targets pt WHERE pt.program_id = p.program_id AND pt.status_indicator = 'completed') as completed_targets,
    (SELECT COUNT(*) FROM program_attachments pa WHERE pa.program_id = p.program_id AND pa.is_active = 1) as active_attachments
FROM programs p
LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
LEFT JOIN agency a ON p.agency_id = a.agency_id
LEFT JOIN users u ON p.created_by = u.user_id
WHERE p.is_active = 1;

-- View for target details with program info
CREATE OR REPLACE VIEW v_target_details AS
SELECT 
    pt.target_id,
    pt.program_id,
    pt.target_number,
    pt.target_description,
    pt.status_indicator,
    pt.status_description,
    pt.remarks,
    pt.start_date,
    pt.end_date,
    pt.completion_date,
    p.program_name,
    p.program_number,
    p.agency_id,
    a.agency_name,
    u.fullname as created_by_name,
    pt.created_at
FROM program_targets pt
JOIN programs p ON pt.program_id = p.program_id
LEFT JOIN agency a ON p.agency_id = a.agency_id
LEFT JOIN users u ON pt.created_by = u.user_id
WHERE pt.is_active = 1 AND p.is_active = 1;

-- View for cross-period targets
CREATE OR REPLACE VIEW v_cross_period_targets AS
SELECT DISTINCT
    pt.target_id,
    pt.target_description,
    pt.program_id,
    p.program_name,
    GROUP_CONCAT(DISTINCT rp.year, ' Q', rp.period_number ORDER BY rp.year, rp.period_number) as periods_involved,
    MIN(rp.start_date) as overall_start_date,
    MAX(rp.end_date) as overall_end_date
FROM program_targets pt
JOIN target_period_status tps ON pt.target_id = tps.target_id
JOIN reporting_periods rp ON tps.period_id = rp.period_id
JOIN programs p ON pt.program_id = p.program_id
WHERE tps.carry_forward = 1
GROUP BY pt.target_id
HAVING COUNT(DISTINCT tps.period_id) > 1;

-- =====================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- =====================================================

DELIMITER $$

-- Procedure to create a new program with targets
CREATE PROCEDURE CreateProgramWithTargets(
    IN p_program_name VARCHAR(255),
    IN p_program_description TEXT,
    IN p_initiative_id INT,
    IN p_agency_id INT,
    IN p_created_by INT,
    IN p_targets JSON
)
BEGIN
    DECLARE v_program_id INT;
    DECLARE v_target_count INT DEFAULT 0;
    DECLARE v_target_description TEXT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE cur CURSOR FOR 
        SELECT JSON_UNQUOTE(JSON_EXTRACT(p_targets, CONCAT('$[', v_target_count, '].description')));
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Insert program
    INSERT INTO programs (program_name, program_description, initiative_id, agency_id, created_by)
    VALUES (p_program_name, p_program_description, p_initiative_id, p_agency_id, p_created_by);
    
    SET v_program_id = LAST_INSERT_ID();
    
    -- Insert targets if provided
    IF p_targets IS NOT NULL THEN
        SET v_target_count = JSON_LENGTH(p_targets);
        WHILE v_target_count > 0 DO
            SET v_target_description = JSON_UNQUOTE(JSON_EXTRACT(p_targets, CONCAT('$[', v_target_count - 1, '].description')));
            
            INSERT INTO program_targets (program_id, target_number, target_description, created_by)
            VALUES (v_program_id, v_target_count, v_target_description, p_created_by);
            
            SET v_target_count = v_target_count - 1;
        END WHILE;
    END IF;
    
    -- Commit transaction
    COMMIT;
    
    -- Return the new program ID
    SELECT v_program_id as program_id;
    
END$$

-- Procedure to update target status with history tracking
CREATE PROCEDURE UpdateTargetStatus(
    IN p_target_id INT,
    IN p_period_id INT,
    IN p_status ENUM('not_started', 'in_progress', 'completed', 'delayed', 'cancelled'),
    IN p_progress_percentage DECIMAL(5,2),
    IN p_notes TEXT,
    IN p_updated_by INT,
    IN p_change_reason VARCHAR(255)
)
BEGIN
    DECLARE v_old_status VARCHAR(50);
    DECLARE v_old_progress DECIMAL(5,2);
    DECLARE v_status_id INT;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Get current values
    SELECT status, progress_percentage, id INTO v_old_status, v_old_progress, v_status_id
    FROM target_period_status 
    WHERE target_id = p_target_id AND period_id = p_period_id;
    
    -- Update status
    UPDATE target_period_status 
    SET status = p_status, 
        progress_percentage = p_progress_percentage,
        notes = p_notes,
        updated_by = p_updated_by,
        updated_date = NOW()
    WHERE target_id = p_target_id AND period_id = p_period_id;
    
    -- Record history if status changed
    IF v_old_status != p_status THEN
        INSERT INTO target_status_history (target_period_status_id, field_name, old_value, new_value, changed_by, change_reason)
        VALUES (v_status_id, 'status', v_old_status, p_status, p_updated_by, p_change_reason);
    END IF;
    
    -- Record history if progress changed
    IF v_old_progress != p_progress_percentage THEN
        INSERT INTO target_status_history (target_period_status_id, field_name, old_value, new_value, changed_by, change_reason)
        VALUES (v_status_id, 'progress_percentage', CAST(v_old_progress AS CHAR), CAST(p_progress_percentage AS CHAR), p_updated_by, p_change_reason);
    END IF;
    
    -- Commit transaction
    COMMIT;
    
END$$

DELIMITER ;

-- =====================================================
-- GRANT PERMISSIONS (Adjust as needed for your setup)
-- =====================================================

-- Create indexes for better performance on large datasets
-- These will be helpful when the system grows
CREATE INDEX idx_audit_logs_program_actions ON audit_logs(action) WHERE action LIKE '%program%' OR action LIKE '%target%';
CREATE INDEX idx_audit_field_changes_program_fields ON audit_field_changes(field_name) WHERE field_name IN ('status_indicator', 'program_rating', 'target_description');

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================
SELECT 'Program logic redesign schema created successfully!' as STATUS;
SELECT 'Tables created: programs, program_targets, target_period_status, program_attachments, program_submissions, target_status_history, program_outcome_links' as TABLES;
SELECT 'Views created: v_program_summary, v_target_details, v_cross_period_targets' as VIEWS;
SELECT 'Procedures created: CreateProgramWithTargets, UpdateTargetStatus' as PROCEDURES;
SELECT 'Triggers created for audit logging on all major tables' as TRIGGERS;

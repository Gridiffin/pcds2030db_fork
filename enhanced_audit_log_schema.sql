-- Enhanced Audit Log Schema for Field-Level Tracking
-- Database: pcds2030_dashboard

USE pcds2030_dashboard;

-- Create table for field-level audit tracking
CREATE TABLE IF NOT EXISTS `audit_field_changes` (
  `change_id` int NOT NULL AUTO_INCREMENT,
  `audit_log_id` int NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_type` varchar(50) DEFAULT 'text' COMMENT 'text, number, date, boolean, json, etc.',
  `old_value` text,
  `new_value` text,
  `change_type` enum('added','modified','removed') NOT NULL DEFAULT 'modified',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`change_id`),
  KEY `idx_audit_log_id` (`audit_log_id`),
  KEY `idx_field_name` (`field_name`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_audit_field_changes_log` FOREIGN KEY (`audit_log_id`) REFERENCES `audit_logs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Add index to audit_logs table for better performance
ALTER TABLE `audit_logs` ADD INDEX `idx_entity_operation` (`action`, `created_at`);

-- Create view for easier querying of audit logs with field changes
CREATE OR REPLACE VIEW `audit_logs_with_changes` AS
SELECT 
    al.id,
    al.user_id,
    al.action,
    al.details,
    al.ip_address,
    al.status,
    al.created_at,
    u.username,
    u.fullname,
    a.agency_name,
    COUNT(afc.change_id) as field_changes_count,
    GROUP_CONCAT(
        CONCAT(afc.field_name, ':', afc.change_type, ':', 
               COALESCE(afc.old_value, 'NULL'), '->', 
               COALESCE(afc.new_value, 'NULL')
        ) SEPARATOR '|'
    ) as field_changes_summary
FROM audit_logs al
LEFT JOIN users u ON al.user_id = u.user_id
LEFT JOIN agency a ON u.agency_id = a.agency_id
LEFT JOIN audit_field_changes afc ON al.id = afc.audit_log_id
GROUP BY al.id, al.user_id, al.action, al.details, al.ip_address, al.status, al.created_at, u.username, u.fullname, a.agency_name;

-- Insert sample data to test the schema (optional)
-- INSERT INTO audit_logs (user_id, action, details, ip_address, status, created_at) 
-- VALUES (1, 'test_field_tracking', 'Testing field-level audit tracking', '127.0.0.1', 'success', NOW());

-- INSERT INTO audit_field_changes (audit_log_id, field_name, field_type, old_value, new_value, change_type)
-- VALUES (LAST_INSERT_ID(), 'program_name', 'text', 'Old Program Name', 'New Program Name', 'modified'),
--        (LAST_INSERT_ID(), 'status', 'text', 'in-progress', 'completed', 'modified'),
--        (LAST_INSERT_ID(), 'description', 'text', NULL, 'New description added', 'added'); 
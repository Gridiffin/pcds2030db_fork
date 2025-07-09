-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table pcds2030_dashboard.agency
CREATE TABLE IF NOT EXISTS `agency` (
  `agency_id` int NOT NULL AUTO_INCREMENT,
  `agency_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_dashboard.agency: ~4 rows (approximately)
INSERT INTO `agency` (`agency_id`, `agency_name`, `created_at`, `updated_at`) VALUES
	(1, 'STIDC', '2025-07-04 10:00:00', '2025-07-04 10:00:00'),
	(2, 'SFC', '2025-07-04 10:00:00', '2025-07-04 10:00:00'),
	(3, 'FDS', '2025-07-04 10:00:00', '2025-07-04 10:00:00'),
	(4, 'admin', '2025-07-05 15:16:11', '2025-07-05 15:16:11');

-- Dumping structure for table pcds2030_dashboard.audit_field_changes
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_dashboard.audit_field_changes: ~48 rows (approximately)
INSERT INTO `audit_field_changes` (`change_id`, `audit_log_id`, `field_name`, `field_type`, `old_value`, `new_value`, `change_type`, `created_at`) VALUES
	(1, 5, 'program_name', 'text', NULL, 'Test Program', 'added', '2025-07-05 15:34:01'),
	(2, 5, 'description', 'text', NULL, 'This is a test program', 'added', '2025-07-05 15:34:01'),
	(3, 5, 'status', 'text', NULL, 'active', 'added', '2025-07-05 15:34:01'),
	(4, 5, 'budget', 'integer', NULL, '100000', 'added', '2025-07-05 15:34:01'),
	(5, 5, 'start_date', 'date', NULL, '2024-01-01', 'added', '2025-07-05 15:34:01'),
	(6, 6, 'program_name', 'text', 'Test Program', 'Updated Test Program', 'modified', '2025-07-05 15:34:01'),
	(7, 6, 'description', 'text', 'This is a test program', 'This is an updated test program', 'modified', '2025-07-05 15:34:01'),
	(8, 6, 'status', 'text', 'active', 'completed', 'modified', '2025-07-05 15:34:01'),
	(9, 6, 'budget', 'integer', '100000', '150000', 'modified', '2025-07-05 15:34:01'),
	(10, 6, 'end_date', 'date', NULL, '2024-12-31', 'added', '2025-07-05 15:34:01'),
	(11, 7, 'program_name', 'text', 'Updated Test Program', NULL, 'removed', '2025-07-05 15:34:01'),
	(12, 7, 'description', 'text', 'This is an updated test program', NULL, 'removed', '2025-07-05 15:34:01'),
	(13, 7, 'status', 'text', 'completed', NULL, 'removed', '2025-07-05 15:34:01'),
	(14, 7, 'budget', 'integer', '150000', NULL, 'removed', '2025-07-05 15:34:01'),
	(15, 7, 'start_date', 'date', '2024-01-01', NULL, 'removed', '2025-07-05 15:34:01'),
	(16, 7, 'end_date', 'date', '2024-12-31', NULL, 'removed', '2025-07-05 15:34:01'),
	(17, 10, 'program_name', 'text', NULL, 'Enhanced Forestry Program', 'added', '2025-07-05 15:38:27'),
	(18, 10, 'description', 'text', NULL, 'This is a test program for enhanced audit logging', 'added', '2025-07-05 15:38:27'),
	(19, 10, 'status', 'text', NULL, 'active', 'added', '2025-07-05 15:38:27'),
	(20, 10, 'budget', 'integer', NULL, '100000', 'added', '2025-07-05 15:38:27'),
	(21, 10, 'start_date', 'date', NULL, '2024-01-01', 'added', '2025-07-05 15:38:27'),
	(22, 11, 'program_name', 'text', 'Enhanced Forestry Program', 'Updated Enhanced Forestry Program', 'modified', '2025-07-05 15:38:27'),
	(23, 11, 'description', 'text', 'This is a test program for enhanced audit logging', 'This is an updated test program for enhanced audit logging', 'modified', '2025-07-05 15:38:27'),
	(24, 11, 'status', 'text', 'active', 'completed', 'modified', '2025-07-05 15:38:27'),
	(25, 11, 'budget', 'integer', '100000', '150000', 'modified', '2025-07-05 15:38:27'),
	(26, 11, 'end_date', 'date', NULL, '2024-12-31', 'added', '2025-07-05 15:38:27'),
	(27, 12, 'program_name', 'text', 'Updated Enhanced Forestry Program', NULL, 'removed', '2025-07-05 15:38:27'),
	(28, 12, 'description', 'text', 'This is an updated test program for enhanced audit logging', NULL, 'removed', '2025-07-05 15:38:27'),
	(29, 12, 'status', 'text', 'completed', NULL, 'removed', '2025-07-05 15:38:27'),
	(30, 12, 'budget', 'integer', '150000', NULL, 'removed', '2025-07-05 15:38:27'),
	(31, 12, 'start_date', 'date', '2024-01-01', NULL, 'removed', '2025-07-05 15:38:27'),
	(32, 12, 'end_date', 'date', '2024-12-31', NULL, 'removed', '2025-07-05 15:38:27'),
	(33, 14, 'program_name', 'text', NULL, 'Enhanced Forestry Program', 'added', '2025-07-05 15:39:09'),
	(34, 14, 'description', 'text', NULL, 'This is a test program for enhanced audit logging', 'added', '2025-07-05 15:39:09'),
	(35, 14, 'status', 'text', NULL, 'active', 'added', '2025-07-05 15:39:09'),
	(36, 14, 'budget', 'integer', NULL, '100000', 'added', '2025-07-05 15:39:09'),
	(37, 14, 'start_date', 'date', NULL, '2024-01-01', 'added', '2025-07-05 15:39:09'),
	(38, 15, 'program_name', 'text', 'Enhanced Forestry Program', 'Updated Enhanced Forestry Program', 'modified', '2025-07-05 15:39:09'),
	(39, 15, 'description', 'text', 'This is a test program for enhanced audit logging', 'This is an updated test program for enhanced audit logging', 'modified', '2025-07-05 15:39:09'),
	(40, 15, 'status', 'text', 'active', 'completed', 'modified', '2025-07-05 15:39:09'),
	(41, 15, 'budget', 'integer', '100000', '150000', 'modified', '2025-07-05 15:39:09'),
	(42, 15, 'end_date', 'date', NULL, '2024-12-31', 'added', '2025-07-05 15:39:09'),
	(43, 16, 'program_name', 'text', 'Updated Enhanced Forestry Program', NULL, 'removed', '2025-07-05 15:39:09'),
	(44, 16, 'description', 'text', 'This is an updated test program for enhanced audit logging', NULL, 'removed', '2025-07-05 15:39:09'),
	(45, 16, 'status', 'text', 'completed', NULL, 'removed', '2025-07-05 15:39:09'),
	(46, 16, 'budget', 'integer', '150000', NULL, 'removed', '2025-07-05 15:39:09'),
	(47, 16, 'start_date', 'date', '2024-01-01', NULL, 'removed', '2025-07-05 15:39:09'),
	(48, 16, 'end_date', 'date', '2024-12-31', NULL, 'removed', '2025-07-05 15:39:09');

-- Dumping structure for table pcds2030_dashboard.audit_logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(128) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` varchar(16) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`),
  KEY `user_id` (`user_id`),
  KEY `idx_entity_operation` (`action`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_dashboard.audit_logs: ~46 rows (approximately)
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `status`, `created_at`) VALUES
	(1, 12, 'create_program_failed', 'Program Name: adasdas | Error: Unknown column \'start_date\' in \'field list\'', '127.0.0.1', 'failure', '2025-07-05 13:02:34'),
	(2, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-07-05 13:04:08'),
	(3, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-07-05 13:05:25'),
	(4, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-07-05 23:04:50'),
	(5, 1, 'create_program', 'Entity ID: 999', '127.0.0.1', 'success', '2025-07-05 23:34:01'),
	(6, 1, 'update_program', 'Entity ID: 999', '127.0.0.1', 'success', '2025-07-05 23:34:01'),
	(7, 1, 'delete_program', 'Entity ID: 999', '127.0.0.1', 'success', '2025-07-05 23:34:01'),
	(8, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 7 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-05 23:34:28'),
	(9, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 8 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-05 23:38:09'),
	(10, 1, 'create_program', 'Created new program: Enhanced Forestry Program', '0.0.0.0', 'success', '2025-07-05 23:38:27'),
	(11, 1, 'update_program', 'Updated program: Updated Enhanced Forestry Program', '0.0.0.0', 'success', '2025-07-05 23:38:27'),
	(12, 1, 'delete_program', 'Deleted program: Updated Enhanced Forestry Program', '0.0.0.0', 'success', '2025-07-05 23:38:27'),
	(13, 1, 'update_program', 'Updated program: ID: 888 | Changes: {"status":"active"}', '0.0.0.0', 'success', '2025-07-05 23:38:27'),
	(14, 1, 'create_program', 'Created new program: Enhanced Forestry Program', '127.0.0.1', 'success', '2025-07-05 23:39:09'),
	(15, 1, 'update_program', 'Updated program: Updated Enhanced Forestry Program', '127.0.0.1', 'success', '2025-07-05 23:39:09'),
	(16, 1, 'delete_program', 'Deleted program: Updated Enhanced Forestry Program', '127.0.0.1', 'success', '2025-07-05 23:39:09'),
	(17, 1, 'update_program', 'Updated program: ID: 888 | Changes: {"status":"active"}', '127.0.0.1', 'success', '2025-07-05 23:39:09'),
	(18, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 17 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-05 23:39:27'),
	(19, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 18 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-05 23:43:43'),
	(20, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 19 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-05 23:52:10'),
	(21, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 20 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-05 23:59:06'),
	(22, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 21 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:08:29'),
	(23, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 22 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:14:34'),
	(24, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 23 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:28:25'),
	(25, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 24 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:37:38'),
	(26, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 25 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:38:24'),
	(27, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 26 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:39:29'),
	(28, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 27 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:41:40'),
	(29, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 28 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:42:32'),
	(30, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 29 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:44:17'),
	(31, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 19 total records). Filters: Date from: 2025-06-05, Date to: 2025-07-05, User: admin', '127.0.0.1', 'success', '2025-07-06 00:44:43'),
	(32, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 21 total records). Filters: Date from: 2025-06-05, Date to: 2025-07-05', '127.0.0.1', 'success', '2025-07-06 00:44:50'),
	(33, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 2 total records). Filters: Date from: 2025-06-05, Date to: 2025-07-05, User: stidc', '127.0.0.1', 'success', '2025-07-06 00:45:03'),
	(34, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 33 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:48:10'),
	(35, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 21 total records). Filters: Date from: 2025-06-05, Date to: 2025-07-05', '127.0.0.1', 'success', '2025-07-06 00:48:17'),
	(36, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 35 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 00:56:47'),
	(37, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 36 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-07-06 01:00:19'),
	(38, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-07-06 09:43:02'),
	(39, 1, 'toggle_period_status', 'Changed status of period: quarter 1 2025 (ID: 1) from open to closed', '127.0.0.1', 'success', '2025-07-06 09:54:35'),
	(40, 1, 'create_period', 'Created reporting period: Yearly 1 2025 (ID: 17, Status: closed)', '127.0.0.1', 'success', '2025-07-06 10:51:09'),
	(41, 1, 'delete_period', 'Exception during period deletion (ID: 17). Error: Unknown column \'id\' in \'field list\'', '127.0.0.1', 'failure', '2025-07-06 10:59:31'),
	(42, 1, 'delete_period', 'Deleted reporting period: yearly 1 2025 (ID: 17, Status: closed)', '127.0.0.1', 'success', '2025-07-06 11:05:28'),
	(43, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '127.0.0.1', 'failure', '2025-07-06 19:02:41'),
	(44, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-07-06 19:02:47'),
	(45, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-07-06 21:56:25'),
	(46, 12, 'login_success', 'Username: user', '127.0.0.1', 'success', '2025-07-06 21:56:31');

-- Dumping structure for view pcds2030_dashboard.audit_logs_with_changes
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `audit_logs_with_changes` (
	`id` INT(10) NOT NULL,
	`user_id` INT(10) NOT NULL,
	`action` VARCHAR(128) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`details` TEXT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`ip_address` VARCHAR(45) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`status` VARCHAR(16) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`created_at` DATETIME NULL,
	`username` VARCHAR(100) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`fullname` VARCHAR(200) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`agency_name` VARCHAR(255) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`field_changes_count` BIGINT(19) NOT NULL,
	`field_changes_summary` TEXT NULL COLLATE 'utf8mb4_0900_ai_ci'
) ENGINE=MyISAM;

-- Dumping structure for table pcds2030_dashboard.initiatives
CREATE TABLE IF NOT EXISTS `initiatives` (
  `initiative_id` int NOT NULL AUTO_INCREMENT,
  `initiative_name` varchar(255) NOT NULL,
  `initiative_number` varchar(20) DEFAULT NULL,
  `initiative_description` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`initiative_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_initiative_number` (`initiative_number`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_dashboard.initiatives: ~4 rows (approximately)
INSERT INTO `initiatives` (`initiative_id`, `initiative_name`, `initiative_number`, `initiative_description`, `start_date`, `end_date`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
	(1, 'PCDS2030 Conservation Initiative', 'PCDS-CI-001', 'Primary conservation initiative for PCDS2030 strategic planning', NULL, NULL, 1, 1, '2025-06-23 14:16:15', '2025-06-23 14:16:15'),
	(2, 'inititative 1', '1', 'descirption of initative 1', '2025-06-17', '2025-06-24', 1, 1, '2025-06-24 02:03:01', '2025-07-06 13:55:06'),
	(3, 'Achieve world class recognition for biodiversity conservation and protected areas management', '31', 'description', '2021-01-01', '2030-12-31', 1, 1, '2025-06-24 07:11:35', '2025-06-24 07:11:35'),
	(4, 'something here', '12', '123', '2025-06-30', '2025-07-31', 1, 1, '2025-06-30 01:54:36', '2025-06-30 01:54:36');

-- Dumping structure for table pcds2030_dashboard.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'update',
  `read_status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_dashboard.notifications: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.outcomes_details
CREATE TABLE IF NOT EXISTS `outcomes_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `detail_name` varchar(255) NOT NULL,
  `display_config` json DEFAULT NULL COMMENT 'JSON configuration for display settings (charts, formatting, etc.)',
  `detail_json` longtext NOT NULL,
  `is_cumulative` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_important` tinyint DEFAULT '0' COMMENT 'Flag for important outcomes used in slide reports',
  `indicator_type` varchar(100) DEFAULT NULL,
  `agency_id` int DEFAULT NULL,
  PRIMARY KEY (`detail_id`),
  KEY `fk_outcomes_agency` (`agency_id`),
  KEY `idx_is_cumulative` (`is_cumulative`),
  KEY `idx_outcomes_details_important` (`is_important`),
  CONSTRAINT `fk_outcomes_agency` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_dashboard.outcomes_details: ~3 rows (approximately)
INSERT INTO `outcomes_details` (`detail_id`, `detail_name`, `display_config`, `detail_json`, `is_cumulative`, `created_at`, `updated_at`, `is_important`, `indicator_type`, `agency_id`) VALUES
	(19, 'TPA Protection & Biodiversity Conservation Programs (incl. community-based initiatives', NULL, '{\r\n  "layout_type": "simple",\r\n  "items": [\r\n    {\r\n      "value": "32",\r\n      "description": "On-going programs and initiatives by SFC (as of Sept 2024)"\r\n    }\r\n  ]\r\n}', 0, '2025-05-07 19:33:42', '2025-06-26 01:00:50', 1, 'conservation', 2),
	(21, 'Certification of FMU & FPMU', NULL, '{"layout_type":"simple","items":[{"value":"56.7%","description":"1,703,164 ha Certified (May 2025)"},{"value":"71.5%","description":"127,311 ha Certified (May 2025)"}]}', 0, '2025-05-07 19:40:32', '2025-06-28 01:22:53', 1, 'certification', 3),
	(39, 'Obtain world recognition for sustainable management practices and conservation effort', NULL, '{"layout_type": "comparison", "items": [{"label": "SDGP UNESCO Global Geopark", "value": "50%", "description": "(as of Sept 2024)"}, {"label": "Niah NP UNESCO World Heritage Site", "value": "100%", "description": "(as of Sept 2024)"}]}', 0, '2025-05-08 16:59:53', '2025-06-26 01:00:50', 1, 'recognition', 2);

-- Dumping structure for table pcds2030_dashboard.programs
CREATE TABLE IF NOT EXISTS `programs` (
  `program_id` int NOT NULL AUTO_INCREMENT,
  `initiative_id` int NOT NULL,
  `program_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `program_description` text COLLATE utf8mb4_general_ci,
  `agency_id` int NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`program_id`),
  KEY `initiative_id` (`initiative_id`),
  KEY `agency_id` (`agency_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`initiative_id`) REFERENCES `initiatives` (`initiative_id`),
  CONSTRAINT `programs_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`),
  CONSTRAINT `programs_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.programs: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.program_attachments
CREATE TABLE IF NOT EXISTS `program_attachments` (
  `attachment_id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` int DEFAULT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `uploaded_by` int NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`attachment_id`),
  KEY `submission_id` (`submission_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `program_attachments_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `program_submissions` (`submission_id`) ON DELETE CASCADE,
  CONSTRAINT `program_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.program_attachments: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.program_submissions
CREATE TABLE IF NOT EXISTS `program_submissions` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `period_id` int NOT NULL,
  `is_draft` tinyint(1) DEFAULT '1',
  `is_submitted` tinyint(1) DEFAULT '0',
  `status_indicator` enum('not_started','in_progress','completed','delayed') COLLATE utf8mb4_general_ci DEFAULT 'not_started',
  `rating` enum('monthly_target_achieved','on_track_for_year','severe_delay','not_started') COLLATE utf8mb4_general_ci DEFAULT 'not_started',
  `description` text COLLATE utf8mb4_general_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `submitted_by` int DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`submission_id`),
  KEY `program_id` (`program_id`),
  KEY `period_id` (`period_id`),
  KEY `submitted_by` (`submitted_by`),
  CONSTRAINT `program_submissions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  CONSTRAINT `program_submissions_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`) ON DELETE CASCADE,
  CONSTRAINT `program_submissions_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.program_submissions: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.program_targets
CREATE TABLE IF NOT EXISTS `program_targets` (
  `target_id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int NOT NULL,
  `target_description` text COLLATE utf8mb4_general_ci,
  `status_indicator` enum('not_started','in_progress','completed','delayed') COLLATE utf8mb4_general_ci DEFAULT 'not_started',
  `status_description` text COLLATE utf8mb4_general_ci,
  `remarks` text COLLATE utf8mb4_general_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`target_id`),
  KEY `submission_id` (`submission_id`),
  CONSTRAINT `program_targets_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `program_submissions` (`submission_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.program_targets: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.program_user_assignments
CREATE TABLE IF NOT EXISTS `program_user_assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('editor','viewer') COLLATE utf8mb4_general_ci DEFAULT 'editor',
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `program_id` (`program_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `program_user_assignments_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  CONSTRAINT `program_user_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.program_user_assignments: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.reporting_periods
CREATE TABLE IF NOT EXISTS `reporting_periods` (
  `period_id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `period_type` enum('quarter','half','yearly') NOT NULL DEFAULT 'quarter',
  `period_number` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`period_id`),
  KEY `period_type_year_idx` (`period_type`,`year`),
  KEY `year_period_unique` (`year`,`period_type`,`period_number`),
  CONSTRAINT `chk_valid_period_numbers` CHECK ((((`period_type` = _utf8mb4'quarter') and (`period_number` between 1 and 4)) or ((`period_type` = _utf8mb4'half') and (`period_number` between 1 and 2)) or ((`period_type` = _utf8mb4'yearly') and (`period_number` = 1))))
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_dashboard.reporting_periods: ~0 rows (approximately)
INSERT INTO `reporting_periods` (`period_id`, `year`, `period_type`, `period_number`, `start_date`, `end_date`, `status`, `updated_at`, `created_at`) VALUES
	(1, 2025, 'quarter', 1, '2025-01-01', '2025-03-31', 'closed', '2025-07-06 01:54:35', '2025-04-17 02:54:12'),
	(2, 2025, 'quarter', 2, '2025-04-01', '2025-06-30', 'closed', '2025-07-01 00:29:54', '2025-04-17 02:54:12'),
	(3, 2025, 'quarter', 3, '2025-07-01', '2025-09-30', 'open', '2025-07-01 00:29:54', '2025-04-17 02:54:12'),
	(4, 2025, 'quarter', 4, '2025-10-01', '2025-12-31', 'closed', '2025-06-24 06:00:13', '2025-04-17 02:54:12'),
	(10, 2024, 'quarter', 2, '2024-04-01', '2024-06-30', 'closed', '2025-06-24 06:00:13', '2025-04-17 02:54:12'),
	(11, 2025, 'half', 1, '2025-01-01', '2025-06-30', 'closed', '2025-07-01 00:29:54', '2025-05-18 13:13:23'),
	(12, 2025, 'half', 2, '2025-07-01', '2025-12-31', 'open', '2025-07-01 00:29:54', '2025-05-18 13:13:23');

-- Dumping structure for table pcds2030_dashboard.reports
CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `period_id` int NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `description` text,
  `pdf_path` varchar(255) NOT NULL,
  `pptx_path` varchar(255) NOT NULL,
  `generated_by` int NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_public` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`report_id`),
  KEY `generated_by` (`generated_by`),
  KEY `period_id` (`period_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_dashboard.reports: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.sector_outcomes_data
CREATE TABLE IF NOT EXISTS `sector_outcomes_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `metric_id` int NOT NULL,
  `outcome_type` enum('simple','complex') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'complex' COMMENT 'Type of outcome: simple (value-based) or complex (table/chart-based)',
  `outcome_template_id` int NOT NULL DEFAULT '0' COMMENT 'References the outcome template/definition this record is based on',
  `display_config` json DEFAULT NULL COMMENT 'JSON configuration for display settings (charts, formatting, etc.)',
  `sector_id` int NOT NULL,
  `period_id` int DEFAULT NULL,
  `table_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_draft` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int DEFAULT NULL,
  `is_important` tinyint(1) DEFAULT '0' COMMENT 'Flag for important outcomes used in slide reports',
  `table_structure_type` enum('monthly','quarterly','yearly','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'monthly' COMMENT 'Type of table structure: monthly (default), quarterly, yearly, or custom',
  `row_config` json DEFAULT NULL COMMENT 'JSON configuration for custom row definitions',
  `column_config` json DEFAULT NULL COMMENT 'JSON configuration for enhanced column definitions with types and units',
  PRIMARY KEY (`id`),
  UNIQUE KEY `metric_sector_draft` (`metric_id`,`sector_id`,`is_draft`),
  KEY `fk_period_id` (`period_id`),
  KEY `fk_submitted_by` (`submitted_by`),
  KEY `idx_sector_outcomes_important` (`is_important`,`sector_id`),
  KEY `idx_outcome_type` (`outcome_type`),
  KEY `idx_type_sector_draft` (`outcome_type`,`sector_id`,`is_draft`),
  KEY `idx_table_structure_type` (`table_structure_type`),
  KEY `idx_structure_sector_draft` (`table_structure_type`,`sector_id`,`is_draft`),
  CONSTRAINT `fk_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.sector_outcomes_data: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `pw` varchar(255) NOT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `agency_id` int NOT NULL,
  `role` enum('admin','agency','focal') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint DEFAULT '1',
  PRIMARY KEY (`user_id`),
  KEY `agency_id` (`agency_id`),
  KEY `email` (`email`),
  KEY `username` (`username`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_dashboard.users: ~0 rows (approximately)
INSERT INTO `users` (`user_id`, `username`, `pw`, `fullname`, `email`, `agency_id`, `role`, `created_at`, `updated_at`, `is_active`) VALUES
	(1, 'admin', '$2y$10$XsLEVjglthHpji3ONspyx.0YT/BxTe.fzDAUK4Jydg7oC8uQnc3V.', 'System Administrator', 'admin@pcds2030.gov.my', 4, 'admin', '2025-03-25 01:31:15', '2025-07-05 15:16:34', 1),
	(2, 'stidc1', '$2y$10$nQCMzJPe8xSV0F0uxFebeeNtFJnsCegdRJE7GEjpBmONWn/msBfI6', 'STIDC User 1', 'stidc1@stidc.gov.my', 1, 'focal', '2025-05-23 06:27:42', '2025-06-20 08:24:39', 1),
	(3, 'stidc2', '$2y$10$CNwb1EyKtXTU5GUlUg2Gx.7LVzWfCx822.REFoZzJYGTpvvfn2Xl.', 'STIDC User 2', 'stidc2@stidc.gov.my', 1, 'agency', '2025-05-23 06:28:07', '2025-05-23 06:28:07', 1),
	(4, 'stidc3', '$2y$10$GVVGb8qjco0WLrRLP7fSfONnblHVLyn8iidYe9Lvjrmwnaek.ycQG', 'STIDC User 3', 'stidc3@stidc.gov.my', 1, 'agency', '2025-05-23 06:28:38', '2025-05-23 06:28:38', 1),
	(5, 'sfc1', '$2y$10$SAn3DrSjO44o3jmamV56oOEIzNn2.ZZW.nrqhW.gqVGsCCwNqgxvi', 'SFC User 1', 'sfc1@sfc.gov.my', 2, 'focal', '2025-05-23 06:30:05', '2025-06-20 08:24:41', 1),
	(6, 'sfc2', '$2y$10$OpqdjpMR8/VPFT7FrVJTzuWpMRx5dtefXxXmPmTm5xQTRjYFnvr2m', 'SFC User 2', 'sfc2@sfc.gov.my', 2, 'agency', '2025-05-23 06:30:25', '2025-05-23 06:30:25', 1),
	(7, 'sfc3', '$2y$10$60AL8k9k5iAR6SlAWBooBOctJzbl2XBV6fVLw6ZhsfyhEfIIr7UkW', 'SFC User 3', 'sfc3@sfc.gov.my', 2, 'agency', '2025-05-23 06:30:51', '2025-05-23 06:30:51', 1),
	(8, 'fds1', '$2y$10$bua8hVx2q0f3cWjXr/2TVefQnh.51LMX4Fyfz3.zWDJGMyuUxEBpq', 'FDS User 1', 'fds1@fds.gov.my', 3, 'focal', '2025-05-23 06:31:31', '2025-06-20 08:24:43', 1),
	(9, 'fds2', '$2y$10$WWnKHgaCDo14MVBDogRpUOhu2sIHWkSfRC4NWuih9R3Uda/BrzSz.', 'FDS User 2', 'fds2@fds.gov.my', 3, 'focal', '2025-05-23 06:31:48', '2025-06-20 08:24:44', 1),
	(10, 'fds3', '$2y$10$3NE/RJmmL/98cmD4nffKJOcZxtl7Pu4q71P8QNgGVQMBeo.mAmTzG', 'FDS User 3', 'fds3@fds.gov.my', 3, 'focal', '2025-05-23 06:32:05', '2025-06-20 08:24:46', 1),
	(12, 'user', '$2y$10$/Z6xCsE7OknP.4HBT5CdBuWDZK5VNMf7MqwmGusJ0SM8xxaGQKdq2', 'Test User', 'user@test.gov.my', 1, 'focal', '2025-03-25 07:42:27', '2025-06-21 16:11:50', 1),
	(44, 'admin2', '$2y$10$XsLEVjglthHpji3ONspyx.0YT/BxTe.fzDAUK4Jydg7oC8uQnc3V.', 'Second Admin', 'admin2@pcds2030.gov.my', 1, 'admin', '2025-07-04 06:38:58', '2025-07-04 06:38:58', 1),
	(45, 'superadmin', '$2y$10$XsLEVjglthHpji3ONspyx.0YT/BxTe.fzDAUK4Jydg7oC8uQnc3V.', 'Super Admin', 'superadmin@pcds2030.gov.my', 1, 'admin', '2025-07-04 06:47:20', '2025-07-04 06:47:20', 1);

-- Dumping structure for view pcds2030_dashboard.audit_logs_with_changes
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `audit_logs_with_changes`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `audit_logs_with_changes` AS select `al`.`id` AS `id`,`al`.`user_id` AS `user_id`,`al`.`action` AS `action`,`al`.`details` AS `details`,`al`.`ip_address` AS `ip_address`,`al`.`status` AS `status`,`al`.`created_at` AS `created_at`,`u`.`username` AS `username`,`u`.`fullname` AS `fullname`,`a`.`agency_name` AS `agency_name`,count(`afc`.`change_id`) AS `field_changes_count`,group_concat(concat(`afc`.`field_name`,':',`afc`.`change_type`,':',coalesce(`afc`.`old_value`,'NULL'),'->',coalesce(`afc`.`new_value`,'NULL')) separator '|') AS `field_changes_summary` from (((`audit_logs` `al` left join `users` `u` on((`al`.`user_id` = `u`.`user_id`))) left join `agency` `a` on((`u`.`agency_id` = `a`.`agency_id`))) left join `audit_field_changes` `afc` on((`al`.`id` = `afc`.`audit_log_id`))) group by `al`.`id`,`al`.`user_id`,`al`.`action`,`al`.`details`,`al`.`ip_address`,`al`.`status`,`al`.`created_at`,`u`.`username`,`u`.`fullname`,`a`.`agency_name`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

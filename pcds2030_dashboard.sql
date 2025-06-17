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

-- Dumping structure for table pcds2030_dashboard.agency_group
CREATE TABLE IF NOT EXISTS `agency_group` (
  `agency_group_id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sector_id` int NOT NULL,
  PRIMARY KEY (`agency_group_id`),
  KEY `sector_id` (`sector_id`),
  CONSTRAINT `agency_group_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.agency_group: ~3 rows (approximately)
INSERT INTO `agency_group` (`agency_group_id`, `group_name`, `sector_id`) VALUES
	(0, 'STIDC', 1),
	(1, 'SFC', 1),
	(2, 'FDS', 1);

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
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.audit_logs: ~77 rows (approximately)
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `status`, `created_at`) VALUES
	(1, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 0 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 15:56:10'),
	(2, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 1 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 15:56:26'),
	(3, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-05 15:57:14'),
	(4, 12, 'login_success', 'Email: user', '127.0.0.1', 'success', '2025-06-05 15:57:19'),
	(5, 12, 'create_program', 'Program Name: asfdasfds | Program ID: 173', '127.0.0.1', 'success', '2025-06-05 15:57:40'),
	(6, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-05 15:58:13'),
	(7, 1, 'login_success', 'Email: admin', '127.0.0.1', 'success', '2025-06-05 15:58:17'),
	(8, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 7 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 15:58:19'),
	(9, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 8 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 15:58:26'),
	(10, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 9 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 16:05:35'),
	(11, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 10 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 16:06:00'),
	(12, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 11 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 16:08:10'),
	(13, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 12 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 16:10:24'),
	(14, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 13 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 16:15:36'),
	(15, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 14 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 16:19:40'),
	(16, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 15 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 16:28:06'),
	(17, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 16 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 16:28:43'),
	(18, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 17 total records). Filters: No filters applied', '127.0.0.1', 'success', '2025-06-05 16:44:16'),
	(19, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-05 16:44:24'),
	(20, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-05 17:03:17'),
	(21, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-06 13:01:41'),
	(22, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 13:02:57'),
	(23, 12, 'login_success', 'Username: user', '127.0.0.1', 'success', '2025-06-06 13:03:15'),
	(24, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 13:26:45'),
	(25, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-06 13:26:49'),
	(26, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 13:47:45'),
	(27, 12, 'login_success', 'Username: user', '127.0.0.1', 'success', '2025-06-06 13:47:51'),
	(28, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 13:50:52'),
	(29, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-06 13:50:57'),
	(30, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 13:52:43'),
	(31, 12, 'login_success', 'Username: user', '127.0.0.1', 'success', '2025-06-06 13:52:50'),
	(32, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 14:16:05'),
	(33, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-06 14:16:09'),
	(34, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 14:35:38'),
	(35, 12, 'login_success', 'Username: user', '127.0.0.1', 'success', '2025-06-06 14:35:44'),
	(36, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 14:46:36'),
	(37, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-06 14:46:40'),
	(38, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 15:10:09'),
	(39, 12, 'login_success', 'Username: user', '127.0.0.1', 'success', '2025-06-06 15:10:17'),
	(40, 12, 'create_program', 'Program Name: Furniture Park | Program ID: 174', '127.0.0.1', 'success', '2025-06-06 15:10:30'),
	(41, 12, 'update_program', 'Program Name: Furniture Park | Program ID: 174', '127.0.0.1', 'success', '2025-06-06 15:10:59'),
	(42, 12, 'create_program', 'Program Name: adadsa | Program ID: 175', '127.0.0.1', 'success', '2025-06-06 15:15:46'),
	(43, 12, 'update_program', 'Program Name: adadsa | Program ID: 175', '127.0.0.1', 'success', '2025-06-06 15:15:54'),
	(44, 12, 'program_submit_no_prior_submission', 'Program submission failed - no prior submission or draft found to validate content (Program ID: 174, Period ID: 2)', '127.0.0.1', 'failure', '2025-06-06 15:19:44'),
	(45, 12, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 174) draft saved for period 2', '127.0.0.1', 'success', '2025-06-06 15:19:59'),
	(46, 12, 'program_submitted', 'Program successfully submitted (Program ID: 174, Period ID: 2)', '127.0.0.1', 'success', '2025-06-06 15:20:02'),
	(47, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 15:20:38'),
	(48, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-06 15:20:47'),
	(49, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 15:28:00'),
	(50, 12, 'login_success', 'Username: user', '127.0.0.1', 'success', '2025-06-06 15:28:09'),
	(51, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 15:30:07'),
	(52, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-06 15:30:11'),
	(53, 1, 'admin_unsubmit_program', 'Program: Unknown Program | Program ID: 174 | Period ID: 2', '127.0.0.1', 'success', '2025-06-06 15:35:13'),
	(54, 1, 'unsubmit_program', 'Program ID: 174, Period ID: 2', '127.0.0.1', 'success', '2025-06-06 15:35:13'),
	(55, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 15:35:19'),
	(56, 12, 'login_success', 'Username: user', '127.0.0.1', 'success', '2025-06-06 15:35:24'),
	(57, 12, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 174) draft saved for period 2', '127.0.0.1', 'success', '2025-06-06 15:42:51'),
	(58, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 15:47:30'),
	(59, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-06 15:47:41'),
	(60, 1, 'admin_resubmit_program', 'Program: Unknown Program | Program ID: 174 | Period ID: 2', '127.0.0.1', 'success', '2025-06-06 15:47:47'),
	(61, 1, 'resubmit_program', 'Program ID: 174, Period ID: 2. Submission resubmitted.', '127.0.0.1', 'success', '2025-06-06 15:47:47'),
	(62, 1, 'Array', '', '127.0.0.1', 'success', '2025-06-06 15:47:47'),
	(63, 1, 'delete_program', 'Program Name: asfdasfds | Program ID: 173 | Owner: testagency', '127.0.0.1', 'success', '2025-06-06 15:48:24'),
	(64, 1, 'admin_unsubmit_program', 'Program: Unknown Program | Program ID: 174 | Period ID: 2', '127.0.0.1', 'success', '2025-06-06 15:51:45'),
	(65, 1, 'unsubmit_program', 'Program ID: 174, Period ID: 2', '127.0.0.1', 'success', '2025-06-06 15:51:45'),
	(66, 1, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 15:51:46'),
	(67, 12, 'login_success', 'Username: user', '127.0.0.1', 'success', '2025-06-06 15:51:55'),
	(68, 12, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 174) draft saved for period 2', '127.0.0.1', 'success', '2025-06-06 15:52:02'),
	(69, 12, 'program_submitted', 'Program successfully submitted (Program ID: 174, Period ID: 2)', '127.0.0.1', 'success', '2025-06-06 15:52:04'),
	(70, 12, 'logout', 'User logged out', '127.0.0.1', 'success', '2025-06-06 15:52:08'),
	(71, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '127.0.0.1', 'failure', '2025-06-06 15:52:12'),
	(72, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '127.0.0.1', 'failure', '2025-06-06 15:52:15'),
	(73, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-06 15:52:17'),
	(74, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2 2025\' for Forestry - Q2 2025 (ID: 312, File: Forestry_Q2-2025_20250606082526.pptx, Size: 7 bytes)', '127.0.0.1', 'success', '2025-06-06 16:25:26'),
	(75, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-17 11:51:38'),
	(76, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-17 16:03:05'),
	(77, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-17 16:13:22');

-- Dumping structure for table pcds2030_dashboard.metrics_details
CREATE TABLE IF NOT EXISTS `metrics_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `detail_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `detail_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_draft` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`detail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.metrics_details: ~3 rows (approximately)
INSERT INTO `metrics_details` (`detail_id`, `detail_name`, `detail_json`, `is_draft`, `created_at`, `updated_at`) VALUES
	(19, 'TPA Protection & Biodiversity Conservation Programs (incl. community-based initiatives', '{\r\n  "layout_type": "simple",\r\n  "items": [\r\n    {\r\n      "value": "32",\r\n      "description": "On-going programs and initiatives by SFC (as of Sept 2024)"\r\n    }\r\n  ]\r\n}', 0, '2025-05-07 19:33:42', '2025-05-14 02:13:32'),
	(21, 'Certification of FMU & FPMU', '{\n  "layout_type": "comparison",\n  "items": [\n    {\n      "label": "FMU",\n      "value": "78%",\n      "description": "2,327,221 ha Certified (Sept 2024)"\n    },\n    {\n      "label": "FPMU",\n      "value": "69%",\n      "description": "122,800 ha Certified (Sept 2024)"\n    }\n  ]\n}', 0, '2025-05-07 19:40:32', '2025-05-14 02:05:29'),
	(39, 'Obtain world recognition for sustainable management practices and conservation effort', '{"layout_type": "comparison", "items": [{"label": "SDGP UNESCO Global Geopark", "value": "50%", "description": "(as of Sept 2024)"}, {"label": "Niah NP UNESCO World Heritage Site", "value": "100%", "description": "(as of Sept 2024)"}]}', 0, '2025-05-08 16:59:53', '2025-05-14 02:02:40');

-- Dumping structure for table pcds2030_dashboard.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'update',
  `read_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.notifications: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.outcomes_details
CREATE TABLE IF NOT EXISTS `outcomes_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `detail_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `detail_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_draft` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`detail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.outcomes_details: ~3 rows (approximately)
INSERT INTO `outcomes_details` (`detail_id`, `detail_name`, `detail_json`, `is_draft`, `created_at`, `updated_at`) VALUES
	(19, 'TPA Protection & Biodiversity Conservation Programs (incl. community-based initiatives', '{\r\n  "layout_type": "simple",\r\n  "items": [\r\n    {\r\n      "value": "32",\r\n      "description": "On-going programs and initiatives by SFC (as of Sept 2024)"\r\n    }\r\n  ]\r\n}', 0, '2025-05-07 19:33:42', '2025-05-14 02:13:32'),
	(21, 'Certification of FMU & FPMU', '{\n  "layout_type": "comparison",\n  "items": [\n    {\n      "label": "FMU",\n      "value": "78%",\n      "description": "2,327,221 ha Certified (Sept 2024)"\n    },\n    {\n      "label": "FPMU",\n      "value": "69%",\n      "description": "122,800 ha Certified (Sept 2024)"\n    }\n  ]\n}', 0, '2025-05-07 19:40:32', '2025-05-14 02:05:29'),
	(39, 'Obtain world recognition for sustainable management practices and conservation effort', '{"layout_type": "comparison", "items": [{"label": "SDGP UNESCO Global Geopark", "value": "50%", "description": "(as of Sept 2024)"}, {"label": "Niah NP UNESCO World Heritage Site", "value": "100%", "description": "(as of Sept 2024)"}]}', 0, '2025-05-08 16:59:53', '2025-05-14 02:02:40');

-- Dumping structure for table pcds2030_dashboard.outcome_history
CREATE TABLE IF NOT EXISTS `outcome_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `outcome_record_id` int NOT NULL,
  `metric_id` int NOT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` int NOT NULL,
  `change_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `fk_outcome_history_user` (`changed_by`),
  KEY `fk_outcome_history_record` (`outcome_record_id`),
  CONSTRAINT `fk_outcome_history_record` FOREIGN KEY (`outcome_record_id`) REFERENCES `sector_outcomes_data` (`id`),
  CONSTRAINT `fk_outcome_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.outcome_history: ~3 rows (approximately)
INSERT INTO `outcome_history` (`history_id`, `outcome_record_id`, `metric_id`, `data_json`, `action_type`, `status`, `changed_by`, `change_description`, `created_at`) VALUES
	(1, 21, 8, '{\r\n  "columns": ["2022", "2023", "2024", "2025", "2026"],\r\n  "units": {\r\n    "2022": "Ha",\r\n    "2023": "Ha",\r\n    "2024": "Ha",\r\n    "2025": "Ha"\r\n  },\r\n  "data": {\r\n    "January": {\r\n      "2022": 787.01,\r\n      "2023": 1856.37,\r\n      "2024": 3146.60,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "February": {\r\n      "2022": 912.41,\r\n      "2023": 3449.94,\r\n      "2024": 6660.50,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "March": {\r\n      "2022": 513.04,\r\n      "2023": 2284.69,\r\n      "2024": 3203.80,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "April": {\r\n      "2022": 428.18,\r\n      "2023": 1807.69,\r\n      "2024": 1871.50,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "May": {\r\n      "2022": 485.08,\r\n      "2023": 3255.80,\r\n      "2024": 2750.20,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "June": {\r\n      "2022": 1277.90,\r\n      "2023": 3120.66,\r\n      "2024": 3396.30,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "July": {\r\n      "2022": 745.15,\r\n      "2023": 2562.38,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "August": {\r\n      "2022": 762.69,\r\n      "2023": 2474.93,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "September": {\r\n      "2022": 579.09,\r\n      "2023": 3251.93,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "October": {\r\n      "2022": 676.27,\r\n      "2023": 3086.64,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "November": {\r\n      "2022": 2012.35,\r\n      "2023": 3081.63,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "December": {\r\n      "2022": 1114.64,\r\n      "2023": 3240.14,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    }\r\n  }\r\n}', 'resubmit', 'submitted', 1, 'Outcome resubmitted by admin', '2025-06-04 06:11:05'),
	(2, 20, 7, '{"columns":["2022","2023","2024","2025","2026"],"units":{"2022":"RM","2023":"RM","2024":"RM","2025":"RM"},"data":{"January":{"2022":408531176.77,"2023":263569916.63,"2024":276004972.69,"2025":null,"2026":0},"February":{"2022":239761718.38,"2023":226356164.3,"2024":191530929.47,"2025":null,"2026":0},"March":{"2022":394935606.46,"2023":261778295.29,"2024":214907671.7,"2025":null,"2026":0},"April":{"2022":400891037.27,"2023":215771835.07,"2024":232014272.14,"2025":null,"2026":0},"May":{"2022":345725679.36,"2023":324280067.64,"2024":324627750.87,"2025":null,"2026":0},"June":{"2022":268966198.26,"2023":235560482.89,"2024":212303812.34,"2025":null,"2026":0},"July":{"2022":359792973.34,"2023":244689028.37,"2024":274788036.68,"2025":null,"2026":0},"August":{"2022":310830376.16,"2023":344761866.36,"2024":210420404.31,"2025":null,"2026":0},"September":{"2022":318990291.52,"2023":210214202.2,"2024":191837139,"2025":null,"2026":0},"October":{"2022":304693148.3,"2023":266639022.25,"2024":null,"2025":null,"2026":0},"November":{"2022":303936172.09,"2023":296062485.55,"2024":null,"2025":null,"2026":0},"December":{"2022":289911760.38,"2023":251155864.77,"2024":null,"2025":null,"2026":0}}}', 'resubmit', 'submitted', 1, 'Outcome resubmitted by admin', '2025-06-04 06:11:08'),
	(3, 21, 8, '{\r\n  "columns": ["2022", "2023", "2024", "2025", "2026"],\r\n  "units": {\r\n    "2022": "Ha",\r\n    "2023": "Ha",\r\n    "2024": "Ha",\r\n    "2025": "Ha"\r\n  },\r\n  "data": {\r\n    "January": {\r\n      "2022": 787.01,\r\n      "2023": 1856.37,\r\n      "2024": 3146.60,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "February": {\r\n      "2022": 912.41,\r\n      "2023": 3449.94,\r\n      "2024": 6660.50,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "March": {\r\n      "2022": 513.04,\r\n      "2023": 2284.69,\r\n      "2024": 3203.80,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "April": {\r\n      "2022": 428.18,\r\n      "2023": 1807.69,\r\n      "2024": 1871.50,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "May": {\r\n      "2022": 485.08,\r\n      "2023": 3255.80,\r\n      "2024": 2750.20,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "June": {\r\n      "2022": 1277.90,\r\n      "2023": 3120.66,\r\n      "2024": 3396.30,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "July": {\r\n      "2022": 745.15,\r\n      "2023": 2562.38,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "August": {\r\n      "2022": 762.69,\r\n      "2023": 2474.93,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "September": {\r\n      "2022": 579.09,\r\n      "2023": 3251.93,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "October": {\r\n      "2022": 676.27,\r\n      "2023": 3086.64,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "November": {\r\n      "2022": 2012.35,\r\n      "2023": 3081.63,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "December": {\r\n      "2022": 1114.64,\r\n      "2023": 3240.14,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    }\r\n  }\r\n}', 'submit', 'submitted', 1, 'Outcome submitted by admin', '2025-06-04 06:14:53');

-- Dumping structure for table pcds2030_dashboard.programs
CREATE TABLE IF NOT EXISTS `programs` (
  `program_id` int NOT NULL AUTO_INCREMENT,
  `program_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_agency_id` int NOT NULL,
  `sector_id` int NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_assigned` tinyint(1) NOT NULL DEFAULT '1',
  `edit_permissions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`program_id`),
  KEY `owner_agency_id` (`owner_agency_id`),
  KEY `sector_id` (`sector_id`)
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.programs: ~6 rows (approximately)
INSERT INTO `programs` (`program_id`, `program_name`, `owner_agency_id`, `sector_id`, `start_date`, `end_date`, `created_at`, `updated_at`, `is_assigned`, `edit_permissions`, `created_by`) VALUES
	(165, 'Forest Conservation Initiative', 12, 1, '2025-01-01', '2025-12-31', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
	(166, 'Sustainable Timber Management Program', 12, 1, '2025-02-01', '2026-01-31', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
	(167, 'Reforestation and Restoration Project', 12, 1, '2025-01-15', '2027-01-15', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
	(168, 'Wildlife Habitat Protection Scheme', 12, 1, '2025-03-01', '2025-11-30', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
	(169, 'Forest Research & Development Initiative', 12, 1, '2025-01-01', '2026-12-31', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
	(174, 'Furniture Park', 12, 1, '2025-05-30', '2025-06-06', '2025-06-06 07:10:30', '2025-06-06 07:52:02', 0, NULL, 1);

-- Dumping structure for table pcds2030_dashboard.program_submissions
CREATE TABLE IF NOT EXISTS `program_submissions` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `period_id` int NOT NULL,
  `submitted_by` int NOT NULL,
  `content_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `submission_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_draft` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`submission_id`),
  KEY `program_id` (`program_id`),
  KEY `period_id` (`period_id`),
  KEY `submitted_by` (`submitted_by`),
  KEY `idx_program_period_draft` (`program_id`,`period_id`,`is_draft`),
  CONSTRAINT `program_submissions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  CONSTRAINT `program_submissions_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  CONSTRAINT `program_submissions_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=160 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.program_submissions: ~12 rows (approximately)
INSERT INTO `program_submissions` (`submission_id`, `program_id`, `period_id`, `submitted_by`, `content_json`, `submission_date`, `updated_at`, `is_draft`) VALUES
	(141, 165, 11, 12, '  {\n    "rating": "on-track-yearly",\n    "brief_description": "Electric vehicle charging network",\n    "targets": [\n      {\n        "target_text": "Install 50 charging stations",\n        "status_description": "28 operational, 12 in progress"\n      }\n    ]\n  }', '2025-06-03 10:50:34', '2025-06-03 11:30:43', 0),
	(142, 165, 12, 12, '  {\n    "rating": "not-started",\n    "brief_description": "Urban beekeeping initiative",\n    "targets": [\n      {\n        "target_text": "Establish 10 apiaries",\n        "status_description": "Awaiting council approval"\n      }\n    ]\n  }', '2025-06-03 10:50:34', '2025-06-03 11:30:00', 0),
	(143, 166, 11, 12, '  {\n    "rating": "target-achieved",\n    "brief_description": "School recycling education drive",\n    "targets": [\n      {\n        "target_text": "Reach 1,000 students",\n        "status_description": "1,250 students participated"\n      }\n    ]\n  }', '2025-06-03 10:50:34', '2025-06-03 11:30:11', 0),
	(144, 166, 12, 12, '  {\n    "rating": "on-track-yearly",\n    "brief_description": "Water conservation project",\n    "targets": [\n      {\n        "target_text": "Reduce consumption by 30% annually",\n        "status_description": "18% reduction achieved halfway"\n      }\n    ]\n  }', '2025-06-03 10:50:34', '2025-06-03 11:30:22', 0),
	(145, 167, 11, 12, '  {\n    "rating": "target-achieved",\n    "brief_description": "Community clean-up initiative for World Oceans Day",\n    "targets": [\n      {\n        "target_text": "Collect 500kg of plastic waste",\n        "status_description": "520kg collected successfully"\n      }\n    ]\n  }', '2025-06-03 10:50:56', '2025-06-03 11:28:21', 0),
	(146, 167, 12, 12, '  {\n    "rating":"severe-delay",\n    "brief_description": "Wildlife sanctuary construction project",\n    "targets": [\n      {\n        "target_text": "Complete Phase 1 by June",\n        "status_description": "Permitting delays, only 20% completed"\n      }\n    ]\n  }', '2025-06-03 10:50:56', '2025-06-04 03:47:35', 0),
	(147, 168, 11, 12, '  {\n    "rating": "not-started",\n    "brief_description": "Green roof installation project",\n    "targets": [\n      {\n        "target_text": "Cover 2,000 sqm of roof space",\n        "status_description": "Funding not yet secured"\n      }\n    ]\n  }', '2025-06-03 10:50:56', '2025-06-03 11:30:53', 0),
	(148, 168, 12, 12, '{\n    "rating": "on-track-yearly",\n    "brief_description": "Renewable energy adoption program",\n    "targets": [\n      {\n        "target_text": "Install 200 solar panels by year-end",\n        "status_description": "110 installed as of Q2"\n      }\n    ]\n  }', '2025-06-03 10:50:56', '2025-06-03 11:28:57', 0),
	(149, 169, 11, 12, '  {\n    "rating": "target-achieved",\n    "brief_description": "Community garden development",\n    "targets": [\n      {\n        "target_text": "Establish 15 vegetable plots",\n        "status_description": "18 plots created and planted"\n      }\n    ]\n  }', '2025-06-03 10:51:06', '2025-06-03 11:31:00', 0),
	(150, 169, 12, 12, '  {\n    "rating": "severe-delay",\n    "brief_description": "Coral reef restoration program",\n    "targets": [\n      {\n        "target_text": "Transplant 5,000 coral fragments",\n        "status_description": "Only 800 transplanted due to storms"\n      }\n    ]\n  }', '2025-06-03 10:51:06', '2025-06-03 11:30:32', 0),
	(157, 174, 11, 12, '{"target":"Completion of design, survey and soil investigation.","status_description":"Pending updates from land and survey on survey status of the lot","brief_description":"description saja"}', '2025-06-06 07:10:30', '2025-06-06 07:10:59', 1),
	(159, 174, 2, 12, '{"rating":"target-achieved","targets":[{"target_text":"Completion of design, survey and soil investigation.","status_description":"Pending updates from land and survey on survey status of the lot"}],"remarks":"","brief_description":"descripttsssss","program_name":"Furniture Park"}', '2025-06-06 07:52:04', '2025-06-06 07:52:04', 0);

-- Dumping structure for table pcds2030_dashboard.reporting_periods
CREATE TABLE IF NOT EXISTS `reporting_periods` (
  `period_id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `quarter` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_standard_dates` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`period_id`),
  UNIQUE KEY `year` (`year`,`quarter`),
  UNIQUE KEY `year_quarter_unique` (`year`,`quarter`),
  UNIQUE KEY `year_quarter` (`year`,`quarter`),
  KEY `quarter_year_idx` (`quarter`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.reporting_periods: ~7 rows (approximately)
INSERT INTO `reporting_periods` (`period_id`, `year`, `quarter`, `start_date`, `end_date`, `status`, `updated_at`, `is_standard_dates`, `created_at`) VALUES
	(1, 2025, 1, '2025-01-01', '2025-03-31', 'closed', '2025-04-15 01:45:45', 1, '2025-04-17 02:54:12'),
	(2, 2025, 2, '2025-04-01', '2025-06-30', 'open', '2025-04-17 02:58:41', 1, '2025-04-17 02:54:12'),
	(3, 2025, 3, '2025-07-01', '2025-09-30', 'closed', '2025-04-17 02:37:02', 1, '2025-04-17 02:54:12'),
	(4, 2025, 4, '2025-10-01', '2025-12-31', 'closed', '2025-04-17 02:34:40', 1, '2025-04-17 02:54:12'),
	(10, 2024, 2, '2024-04-01', '2024-06-30', 'closed', '2025-04-17 02:58:36', 1, '2025-04-17 02:54:12'),
	(11, 2025, 5, '2025-01-01', '2025-06-30', 'open', '2025-05-18 13:16:02', 1, '2025-05-18 13:13:23'),
	(12, 2025, 6, '2025-07-01', '2025-12-31', 'closed', '2025-05-18 13:13:23', 1, '2025-05-18 13:13:23');

-- Dumping structure for table pcds2030_dashboard.reports
CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `period_id` int NOT NULL,
  `report_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `pdf_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pptx_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `generated_by` int NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`report_id`),
  KEY `period_id` (`period_id`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=313 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.reports: ~2 rows (approximately)
INSERT INTO `reports` (`report_id`, `period_id`, `report_name`, `description`, `pdf_path`, `pptx_path`, `generated_by`, `generated_at`, `is_public`) VALUES
	(301, 2, 'Forestry Report - Q2 2025', '', '', 'pptx/Forestry_Q2-2025_20250521030906.pptx', 1, '2025-05-21 01:09:06', 0),
	(312, 2, 'Forestry Report - Q2 2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250606082526.pptx', 1, '2025-06-06 08:25:26', 0);

-- Dumping structure for table pcds2030_dashboard.sectors
CREATE TABLE IF NOT EXISTS `sectors` (
  `sector_id` int NOT NULL AUTO_INCREMENT,
  `sector_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`sector_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.sectors: ~1 rows (approximately)
INSERT INTO `sectors` (`sector_id`, `sector_name`, `description`) VALUES
	(1, 'Forestry', 'Forestry sector including timber and forest resources');

-- Dumping structure for table pcds2030_dashboard.sector_outcomes_data
CREATE TABLE IF NOT EXISTS `sector_outcomes_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `metric_id` int NOT NULL,
  `sector_id` int NOT NULL,
  `period_id` int DEFAULT NULL,
  `table_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_draft` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `metric_sector_draft` (`metric_id`,`sector_id`,`is_draft`),
  KEY `fk_period_id` (`period_id`),
  KEY `fk_submitted_by` (`submitted_by`),
  CONSTRAINT `fk_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `sector_outcomes_data_chk_1` CHECK (json_valid(`data_json`))
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.sector_outcomes_data: ~2 rows (approximately)
INSERT INTO `sector_outcomes_data` (`id`, `metric_id`, `sector_id`, `period_id`, `table_name`, `data_json`, `is_draft`, `created_at`, `updated_at`, `submitted_by`) VALUES
	(20, 7, 1, 2, 'TIMBER EXPORT VALUE (RM)', '{"columns":["2022","2023","2024","2025","2026"],"units":{"2022":"RM","2023":"RM","2024":"RM","2025":"RM"},"data":{"January":{"2022":408531176.77,"2023":263569916.63,"2024":276004972.69,"2025":null,"2026":0},"February":{"2022":239761718.38,"2023":226356164.3,"2024":191530929.47,"2025":null,"2026":0},"March":{"2022":394935606.46,"2023":261778295.29,"2024":214907671.7,"2025":null,"2026":0},"April":{"2022":400891037.27,"2023":215771835.07,"2024":232014272.14,"2025":null,"2026":0},"May":{"2022":345725679.36,"2023":324280067.64,"2024":324627750.87,"2025":null,"2026":0},"June":{"2022":268966198.26,"2023":235560482.89,"2024":212303812.34,"2025":null,"2026":0},"July":{"2022":359792973.34,"2023":244689028.37,"2024":274788036.68,"2025":null,"2026":0},"August":{"2022":310830376.16,"2023":344761866.36,"2024":210420404.31,"2025":null,"2026":0},"September":{"2022":318990291.52,"2023":210214202.2,"2024":191837139,"2025":null,"2026":0},"October":{"2022":304693148.3,"2023":266639022.25,"2024":null,"2025":null,"2026":0},"November":{"2022":303936172.09,"2023":296062485.55,"2024":null,"2025":null,"2026":0},"December":{"2022":289911760.38,"2023":251155864.77,"2024":null,"2025":null,"2026":0}}}', 0, '2025-04-27 03:45:15', '2025-06-17 08:06:47', 35),
	(21, 8, 1, 2, 'TOTAL DEGRADED AREA', '{\r\n  "columns": ["2022", "2023", "2024", "2025", "2026"],\r\n  "units": {\r\n    "2022": "Ha",\r\n    "2023": "Ha",\r\n    "2024": "Ha",\r\n    "2025": "Ha"\r\n  },\r\n  "data": {\r\n    "January": {\r\n      "2022": 787.01,\r\n      "2023": 1856.37,\r\n      "2024": 3146.60,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "February": {\r\n      "2022": 912.41,\r\n      "2023": 3449.94,\r\n      "2024": 6660.50,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "March": {\r\n      "2022": 513.04,\r\n      "2023": 2284.69,\r\n      "2024": 3203.80,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "April": {\r\n      "2022": 428.18,\r\n      "2023": 1807.69,\r\n      "2024": 1871.50,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "May": {\r\n      "2022": 485.08,\r\n      "2023": 3255.80,\r\n      "2024": 2750.20,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "June": {\r\n      "2022": 1277.90,\r\n      "2023": 3120.66,\r\n      "2024": 3396.30,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "July": {\r\n      "2022": 745.15,\r\n      "2023": 2562.38,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "August": {\r\n      "2022": 762.69,\r\n      "2023": 2474.93,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "September": {\r\n      "2022": 579.09,\r\n      "2023": 3251.93,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "October": {\r\n      "2022": 676.27,\r\n      "2023": 3086.64,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "November": {\r\n      "2022": 2012.35,\r\n      "2023": 3081.63,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    },\r\n    "December": {\r\n      "2022": 1114.64,\r\n      "2023": 3240.14,\r\n      "2024": null,\r\n      "2025": null,\r\n      "2026": 0\r\n    }\r\n  }\r\n}', 0, '2025-05-13 23:25:38', '2025-06-17 08:06:55', 38);

-- Dumping structure for table pcds2030_dashboard.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `agency_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','agency') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sector_id` int DEFAULT NULL,
  `agency_group_id` int NOT NULL COMMENT '0-STIDC\r\n1-SFC\r\n2-FDS',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `agency_name` (`agency_name`),
  KEY `sector_id` (`sector_id`),
  KEY `agency_id` (`agency_group_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`),
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`agency_group_id`) REFERENCES `agency_group` (`agency_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.users: ~11 rows (approximately)
INSERT INTO `users` (`user_id`, `username`, `password`, `agency_name`, `role`, `sector_id`, `agency_group_id`, `created_at`, `updated_at`, `is_active`) VALUES
	(1, 'admin', '$2y$10$bPQQFeR4PbcueCgmV7/2Au.HWCjWH8v8ox.R.MxMfk4qXXHi3uPw6', 'Ministry of Natural Resources and Urban Development', 'admin', NULL, 0, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
	(12, 'user', '$2y$10$/Z6xCsE7OknP.4HBT5CdBuWDZK5VNMf7MqwmGusJ0SM8xxaGQKdq2', 'testagency', 'agency', 1, 0, '2025-03-25 07:42:27', '2025-05-05 06:41:55', 1),
	(35, 'stidc1', '$2y$10$nQCMzJPe8xSV0F0uxFebeeNtFJnsCegdRJE7GEjpBmONWn/msBfI6', 'stidc1', 'agency', 1, 0, '2025-05-23 06:27:42', '2025-05-23 06:27:42', 1),
	(36, 'stidc2', '$2y$10$CNwb1EyKtXTU5GUlUg2Gx.7LVzWfCx822.REFoZzJYGTpvvfn2Xl.', 'stidc2', 'agency', 1, 0, '2025-05-23 06:28:07', '2025-05-23 06:28:07', 1),
	(37, 'stidc3', '$2y$10$GVVGb8qjco0WLrRLP7fSfONnblHVLyn8iidYe9Lvjrmwnaek.ycQG', 'stidc3', 'agency', 1, 0, '2025-05-23 06:28:38', '2025-05-23 06:28:38', 1),
	(38, 'sfc1', '$2y$10$SAn3DrSjO44o3jmamV56oOEIzNn2.ZZW.nrqhW.gqVGsCCwNqgxvi', 'sfc1', 'agency', 1, 1, '2025-05-23 06:30:05', '2025-05-23 06:30:05', 1),
	(39, 'sfc2', '$2y$10$OpqdjpMR8/VPFT7FrVJTzuWpMRx5dtefXxXmPmTm5xQTRjYFnvr2m', 'sfc2', 'agency', 1, 1, '2025-05-23 06:30:25', '2025-05-23 06:30:25', 1),
	(40, 'sfc3', '$2y$10$60AL8k9k5iAR6SlAWBooBOctJzbl2XBV6fVLw6ZhsfyhEfIIr7UkW', 'sfc3', 'agency', 1, 1, '2025-05-23 06:30:51', '2025-05-23 06:30:51', 1),
	(41, 'fds1', '$2y$10$bua8hVx2q0f3cWjXr/2TVefQnh.51LMX4Fyfz3.zWDJGMyuUxEBpq', 'fds1', 'agency', 1, 2, '2025-05-23 06:31:31', '2025-05-23 06:31:31', 1),
	(42, 'fds2', '$2y$10$WWnKHgaCDo14MVBDogRpUOhu2sIHWkSfRC4NWuih9R3Uda/BrzSz.', 'fds2', 'agency', 1, 2, '2025-05-23 06:31:48', '2025-05-23 06:31:48', 1),
	(43, 'fds3', '$2y$10$3NE/RJmmL/98cmD4nffKJOcZxtl7Pu4q71P8QNgGVQMBeo.mAmTzG', 'fds3', 'agency', 1, 2, '2025-05-23 06:32:05', '2025-05-23 06:32:05', 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

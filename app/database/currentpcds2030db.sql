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

-- Dumping structure for table pcds2030_db.agency
CREATE TABLE IF NOT EXISTS `agency` (
  `agency_id` int NOT NULL AUTO_INCREMENT,
  `agency_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_db.agency: ~3 rows (approximately)
INSERT INTO `agency` (`agency_id`, `agency_name`, `created_at`, `updated_at`) VALUES
	(1, 'STIDC', '2025-07-04 10:00:00', '2025-07-04 10:00:00'),
	(2, 'SFC', '2025-07-04 10:00:00', '2025-07-04 10:00:00'),
	(3, 'FDS', '2025-07-04 10:00:00', '2025-07-04 10:00:00');

-- Dumping structure for table pcds2030_db.audit_logs
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_db.audit_logs: ~0 rows (approximately)

-- Dumping structure for table pcds2030_db.initiatives
CREATE TABLE IF NOT EXISTS `initiatives` (
  `initiative_id` int NOT NULL AUTO_INCREMENT,
  `initiative_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `initiative_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initiative_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`initiative_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_initiative_number` (`initiative_number`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_db.initiatives: ~3 rows (approximately)
INSERT INTO `initiatives` (`initiative_id`, `initiative_name`, `initiative_number`, `initiative_description`, `start_date`, `end_date`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
	(1, 'PCDS2030 Conservation Initiative', 'PCDS-CI-001', 'Primary conservation initiative for PCDS2030 strategic planning', NULL, NULL, 1, 1, '2025-06-23 14:16:15', '2025-06-23 14:16:15'),
	(2, 'inititative 1', '1', 'descirption of initative 1', '2025-06-17', '2025-06-24', 1, 1, '2025-06-24 02:03:01', '2025-06-24 02:03:01'),
	(3, 'Achieve world class recognition for biodiversity conservation and protected areas management', '31', 'description', '2021-01-01', '2030-12-31', 1, 1, '2025-06-24 07:11:35', '2025-06-24 07:11:35'),
	(4, 'something here', '12', '123', '2025-06-30', '2025-07-31', 1, 1, '2025-06-30 01:54:36', '2025-06-30 01:54:36');

-- Dumping structure for table pcds2030_db.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'update',
  `read_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_db.notifications: ~0 rows (approximately)

-- Dumping structure for table pcds2030_db.outcomes_details
CREATE TABLE IF NOT EXISTS `outcomes_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `detail_name` varchar(255) NOT NULL,
  `display_config` json DEFAULT NULL COMMENT 'JSON configuration for display settings (charts, formatting, etc.)',
  `detail_json` longtext NOT NULL,
  `is_cumulative` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_important` tinyint(1) DEFAULT '0' COMMENT 'Flag for important outcomes used in slide reports',
  `indicator_type` varchar(100) DEFAULT NULL,
  `agency_id` int DEFAULT NULL,
  PRIMARY KEY (`detail_id`),
  KEY `idx_is_cumulative` (`is_cumulative`),
  KEY `idx_outcomes_details_important` (`is_important`),
  KEY `fk_outcomes_agency` (`agency_id`),
  CONSTRAINT `fk_outcomes_agency` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_db.outcomes_details: ~3 rows (approximately)
INSERT INTO `outcomes_details` (`detail_id`, `detail_name`, `display_config`, `detail_json`, `is_cumulative`, `created_at`, `updated_at`, `is_important`, `indicator_type`, `agency_id`) VALUES
	(19, 'TPA Protection & Biodiversity Conservation Programs (incl. community-based initiatives', NULL, '{\r\n  "layout_type": "simple",\r\n  "items": [\r\n    {\r\n      "value": "32",\r\n      "description": "On-going programs and initiatives by SFC (as of Sept 2024)"\r\n    }\r\n  ]\r\n}', 0, '2025-05-07 19:33:42', '2025-06-26 01:00:50', 1, 'conservation', 2),
	(21, 'Certification of FMU & FPMU', NULL, '{"layout_type":"simple","items":[{"value":"56.7%","description":"1,703,164 ha Certified (May 2025)"},{"value":"71.5%","description":"127,311 ha Certified (May 2025)"}]}', 0, '2025-05-07 19:40:32', '2025-06-28 01:22:53', 1, 'certification', 3),
	(39, 'Obtain world recognition for sustainable management practices and conservation effort', NULL, '{"layout_type": "comparison", "items": [{"label": "SDGP UNESCO Global Geopark", "value": "50%", "description": "(as of Sept 2024)"}, {"label": "Niah NP UNESCO World Heritage Site", "value": "100%", "description": "(as of Sept 2024)"}]}', 0, '2025-05-08 16:59:53', '2025-06-26 01:00:50', 1, 'recognition', 2);

-- Dumping structure for table pcds2030_db.programs
CREATE TABLE IF NOT EXISTS `programs` (
  `program_id` int NOT NULL AUTO_INCREMENT,
  `program_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `program_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initiative_id` int DEFAULT NULL,
  `agency_id` int NOT NULL,
  `users_assigned` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int NOT NULL DEFAULT '1',
  `attachment_count` int DEFAULT '0' COMMENT 'Cached count of active attachments',
  `status` set('not-started','in-progress','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hold_point` json DEFAULT NULL,
  `targets_linked` int DEFAULT '0',
  PRIMARY KEY (`program_id`),
  KEY `agency_id` (`agency_id`),
  KEY `idx_program_number` (`program_number`),
  KEY `idx_initiative_id` (`initiative_id`),
  KEY `users_assigned` (`users_assigned`),
  CONSTRAINT `FK_programs_agency` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`),
  CONSTRAINT `FK_programs_users` FOREIGN KEY (`users_assigned`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=284 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_db.programs: ~4 rows (approximately)
INSERT INTO `programs` (`program_id`, `program_name`, `program_number`, `initiative_id`, `agency_id`, `users_assigned`, `created_at`, `updated_at`, `created_by`, `attachment_count`, `status`, `hold_point`, `targets_linked`) VALUES
	(176, 'Bamboo Industry Development', '', NULL, 1, 4, '2025-06-18 01:33:33', '2025-07-02 02:06:07', 1, 0, NULL, NULL, 2),
	(177, 'Bamboo Industry Developement 2026', NULL, NULL, 2, 5, '2025-06-18 01:34:45', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(181, 'Niah Unesco', NULL, NULL, 2, 5, '2025-06-18 01:35:28', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(182, 'Research and Development for KURSI PUSAKA in UNIMAS', NULL, NULL, 1, 3, '2025-06-18 01:35:47', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(183, 'Pusat Latihan Perkayuan PUSAKA Tanjung Manis', NULL, NULL, 1, 2, '2025-06-18 01:36:13', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(184, 'Obtaining UNESCO recognition for Sarawak Delta Geopark', NULL, NULL, 3, 8, '2025-06-18 01:36:19', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(185, 'Bamboo Industry Development', NULL, NULL, 3, 8, '2025-06-18 01:36:24', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(187, 'Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak', NULL, NULL, 3, 8, '2025-06-18 01:37:10', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(189, 'Furniture Park', '', NULL, 1, 2, '2025-06-18 01:37:53', '2025-07-02 02:31:59', 1, 0, NULL, NULL, 0),
	(193, 'Proposed Implementation of Forest Landscape Restoration Throughout Sarawak', NULL, NULL, 3, 8, '2025-06-18 01:52:41', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(194, 'Strengthening Forest Enforcement Through Advancing the Technology and Equipments', NULL, NULL, 3, 8, '2025-06-18 01:52:50', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(195, 'Quantifying Forest Carbon Stock in Sarawak', NULL, NULL, 3, 10, '2025-06-18 02:11:12', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(196, 'Applied R&D to develop commercially viable high value products from planted timber species', '', NULL, 1, 3, '2025-06-18 02:16:34', '2025-07-03 03:21:48', 1, 0, NULL, NULL, 0),
	(197, 'Implementation of Sarawak Young Designers programme', NULL, NULL, 1, 3, '2025-06-18 02:41:00', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(198, 'Integrated wildlife conservation and management in Sarawak', NULL, NULL, 2, 5, '2025-06-19 01:18:33', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(199, 'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks', NULL, NULL, 2, 5, '2025-06-19 01:18:36', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(200, 'System Tagang', NULL, NULL, 2, 5, '2025-06-19 01:18:40', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(201, 'Identify potential TPA to be managed by Managing Agent', NULL, NULL, 2, 5, '2025-06-19 01:18:40', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(202, 'To certify 20 sites under IUCN Green List of Protected and Conserved Areas', NULL, NULL, 2, 5, '2025-06-19 01:22:36', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(203, 'Establishment of Rainforest/Nature Discovery Centre', NULL, NULL, 2, 5, '2025-06-19 01:33:55', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(205, 'Development of Boardwalk & One Stop Centre at North Gate of Bukit Lima Nature Reserve', NULL, NULL, 2, 5, '2025-06-19 01:42:04', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(206, 'Achieve world class recognition for biodiversity conservation & protected areas management', NULL, NULL, 2, 5, '2025-06-19 01:58:16', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(207, 'To develop and upgrade facilities at selected manned and unmanned TPAs', NULL, NULL, 2, 5, '2025-06-19 02:18:56', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(209, 'Landscape Rehabilitation Programs', NULL, NULL, 2, 5, '2025-06-19 02:43:43', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0),
	(211, 'Turtle Conservation Project', NULL, NULL, 2, 5, '2025-06-19 02:54:04', '2025-07-01 08:23:09', 1, 0, NULL, NULL, 0);

-- Dumping structure for table pcds2030_db.program_attachments
CREATE TABLE IF NOT EXISTS `program_attachments` (
  `attachment_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint NOT NULL,
  `file_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` int NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`attachment_id`),
  KEY `program_id` (`program_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `program_attachments_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  CONSTRAINT `program_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_db.program_attachments: ~0 rows (approximately)

-- Dumping structure for table pcds2030_db.program_outcome_links
CREATE TABLE IF NOT EXISTS `program_outcome_links` (
  `link_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `outcome_id` int NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`link_id`),
  UNIQUE KEY `unique_program_outcome` (`program_id`,`outcome_id`),
  KEY `idx_program_id` (`program_id`),
  KEY `idx_outcome_id` (`outcome_id`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_pol_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pol_outcome` FOREIGN KEY (`outcome_id`) REFERENCES `outcomes_details` (`detail_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pol_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_db.program_outcome_links: ~2 rows (approximately)
INSERT INTO `program_outcome_links` (`link_id`, `program_id`, `outcome_id`, `created_by`, `created_at`) VALUES
	(1, 176, 19, 1, '2025-06-25 07:09:33'),
	(3, 176, 21, 1, '2025-06-25 07:24:23');

-- Dumping structure for table pcds2030_db.program_submissions
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
) ENGINE=InnoDB AUTO_INCREMENT=437 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_db.program_submissions: ~0 rows (approximately)

-- Dumping structure for table pcds2030_db.reporting_periods
CREATE TABLE IF NOT EXISTS `reporting_periods` (
  `period_id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `period_type` enum('quarter','half','yearly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'quarter',
  `period_number` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`period_id`),
  UNIQUE KEY `year_period_unique` (`year`,`period_type`,`period_number`),
  KEY `period_type_year_idx` (`period_type`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_db.reporting_periods: ~7 rows (approximately)
INSERT INTO `reporting_periods` (`period_id`, `year`, `period_type`, `period_number`, `start_date`, `end_date`, `status`, `updated_at`, `created_at`) VALUES
	(1, 2025, 'quarter', 1, '2025-01-01', '2025-03-31', 'open', '2025-06-24 06:00:13', '2025-04-17 02:54:12'),
	(2, 2025, 'quarter', 2, '2025-04-01', '2025-06-30', 'closed', '2025-07-01 00:29:54', '2025-04-17 02:54:12'),
	(3, 2025, 'quarter', 3, '2025-07-01', '2025-09-30', 'open', '2025-07-01 00:29:54', '2025-04-17 02:54:12'),
	(4, 2025, 'quarter', 4, '2025-10-01', '2025-12-31', 'closed', '2025-06-24 06:00:13', '2025-04-17 02:54:12'),
	(10, 2024, 'quarter', 2, '2024-04-01', '2024-06-30', 'closed', '2025-06-24 06:00:13', '2025-04-17 02:54:12'),
	(11, 2025, 'half', 1, '2025-01-01', '2025-06-30', 'closed', '2025-07-01 00:29:54', '2025-05-18 13:13:23'),
	(12, 2025, 'half', 2, '2025-07-01', '2025-12-31', 'open', '2025-07-01 00:29:54', '2025-05-18 13:13:23');

-- Dumping structure for table pcds2030_db.reports
CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `period_id` int NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `description` text,
  `pdf_path` varchar(255) NOT NULL,
  `pptx_path` varchar(255) NOT NULL,
  `generated_by` int NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`report_id`),
  KEY `period_id` (`period_id`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=360 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_db.reports: ~0 rows (approximately)

-- Dumping structure for table pcds2030_db.targets
CREATE TABLE IF NOT EXISTS `targets` (
  `target_id` int NOT NULL AUTO_INCREMENT,
  `target_name` varchar(255) NOT NULL,
  `status_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table pcds2030_db.targets: ~0 rows (approximately)

-- Dumping structure for table pcds2030_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pw` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `agency_id` int NOT NULL,
  `role` enum('admin','agency','focal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `agency_id` (`agency_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_db.users: ~0 rows (approximately)
INSERT INTO `users` (`user_id`, `username`, `pw`, `fullname`, `email`, `agency_id`, `role`, `created_at`, `updated_at`, `is_active`) VALUES
	(1, 'admin', '$2y$10$XsLEVjglthHpji3ONspyx.0YT/BxTe.fzDAUK4Jydg7oC8uQnc3V.', 'System Administrator', 'admin@pcds2030.gov.my', 1, 'admin', '2025-03-25 01:31:15', '2025-06-24 02:20:34', 1),
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

-- Dumping structure for trigger pcds2030_db.tr_program_attachments_delete
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tr_program_attachments_delete` AFTER DELETE ON `program_attachments` FOR EACH ROW BEGIN
    IF OLD.is_active = 1 THEN
        UPDATE programs 
        SET attachment_count = attachment_count - 1 
        WHERE program_id = OLD.program_id;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger pcds2030_db.tr_program_attachments_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tr_program_attachments_insert` AFTER INSERT ON `program_attachments` FOR EACH ROW BEGIN
    IF NEW.is_active = 1 THEN
        UPDATE programs 
        SET attachment_count = attachment_count + 1 
        WHERE program_id = NEW.program_id;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger pcds2030_db.tr_program_attachments_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tr_program_attachments_update` AFTER UPDATE ON `program_attachments` FOR EACH ROW BEGIN
    IF OLD.is_active = 1 AND NEW.is_active = 0 THEN
        UPDATE programs 
        SET attachment_count = attachment_count - 1 
        WHERE program_id = NEW.program_id;
    ELSEIF OLD.is_active = 0 AND NEW.is_active = 1 THEN
        UPDATE programs 
        SET attachment_count = attachment_count + 1 
        WHERE program_id = NEW.program_id;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for pcds2030_dashboard
CREATE DATABASE IF NOT EXISTS `pcds2030_dashboard` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `pcds2030_dashboard`;

-- Dumping structure for table pcds2030_dashboard.agency_group
CREATE TABLE IF NOT EXISTS `agency_group` (
  `agency_group_id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `sector_id` int NOT NULL,
  PRIMARY KEY (`agency_group_id`),
  KEY `sector_id` (`sector_id`),
  CONSTRAINT `agency_group_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.agency_group: ~3 rows (approximately)
REPLACE INTO `agency_group` (`agency_group_id`, `group_name`, `sector_id`) VALUES
	(0, 'STIDC', 1),
	(1, 'SFC', 1),
	(2, 'FDS', 1);

-- Dumping structure for table pcds2030_dashboard.metrics_details
CREATE TABLE IF NOT EXISTS `metrics_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `detail_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `detail_json` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `is_draft` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`detail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.metrics_details: ~3 rows (approximately)
REPLACE INTO `metrics_details` (`detail_id`, `detail_name`, `detail_json`, `is_draft`, `created_at`, `updated_at`) VALUES
	(19, 'TPA Protection & Biodiversity Conservation Programs (incl. community-based initiatives', '{\r\n  "layout_type": "simple",\r\n  "items": [\r\n    {\r\n      "value": "32",\r\n      "description": "On-going programs and initiatives by SFC (as of Sept 2024)"\r\n    }\r\n  ]\r\n}', 0, '2025-05-07 19:33:42', '2025-05-14 02:13:32'),
	(21, 'Certification of FMU & FPMU', '{\n  "layout_type": "comparison",\n  "items": [\n    {\n      "label": "FMU",\n      "value": "78%",\n      "description": "2,327,221 ha Certified (Sept 2024)"\n    },\n    {\n      "label": "FPMU",\n      "value": "69%",\n      "description": "122,800 ha Certified (Sept 2024)"\n    }\n  ]\n}', 0, '2025-05-07 19:40:32', '2025-05-14 02:05:29'),
	(39, 'Obtain world recognition for sustainable management practices and conservation effort', '{"layout_type": "comparison", "items": [{"label": "SDGP UNESCO Global Geopark", "value": "50%", "description": "(as of Sept 2024)"}, {"label": "Niah NP UNESCO World Heritage Site", "value": "100%", "description": "(as of Sept 2024)"}]}', 0, '2025-05-08 16:59:53', '2025-05-14 02:02:40');

-- Dumping structure for table pcds2030_dashboard.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'update',
  `read_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.notifications: ~0 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.outcomes_details
CREATE TABLE IF NOT EXISTS `outcomes_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `detail_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `detail_json` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `is_draft` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`detail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.outcomes_details: ~3 rows (approximately)
REPLACE INTO `outcomes_details` (`detail_id`, `detail_name`, `detail_json`, `is_draft`, `created_at`, `updated_at`) VALUES
	(19, 'TPA Protection & Biodiversity Conservation Programs (incl. community-based initiatives', '{\r\n  "layout_type": "simple",\r\n  "items": [\r\n    {\r\n      "value": "32",\r\n      "description": "On-going programs and initiatives by SFC (as of Sept 2024)"\r\n    }\r\n  ]\r\n}', 0, '2025-05-07 19:33:42', '2025-05-14 02:13:32'),
	(21, 'Certification of FMU & FPMU', '{\n  "layout_type": "comparison",\n  "items": [\n    {\n      "label": "FMU",\n      "value": "78%",\n      "description": "2,327,221 ha Certified (Sept 2024)"\n    },\n    {\n      "label": "FPMU",\n      "value": "69%",\n      "description": "122,800 ha Certified (Sept 2024)"\n    }\n  ]\n}', 0, '2025-05-07 19:40:32', '2025-05-14 02:05:29'),
	(39, 'Obtain world recognition for sustainable management practices and conservation effort', '{"layout_type": "comparison", "items": [{"label": "SDGP UNESCO Global Geopark", "value": "50%", "description": "(as of Sept 2024)"}, {"label": "Niah NP UNESCO World Heritage Site", "value": "100%", "description": "(as of Sept 2024)"}]}', 0, '2025-05-08 16:59:53', '2025-05-14 02:02:40');

-- Dumping structure for table pcds2030_dashboard.programs
CREATE TABLE IF NOT EXISTS `programs` (
  `program_id` int NOT NULL AUTO_INCREMENT,
  `program_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_agency_id` int NOT NULL,
  `sector_id` int NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_assigned` tinyint(1) NOT NULL DEFAULT '1',
  `edit_permissions` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`program_id`),
  KEY `owner_agency_id` (`owner_agency_id`),
  KEY `sector_id` (`sector_id`)
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.programs: ~1 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.program_submissions
CREATE TABLE IF NOT EXISTS `program_submissions` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `period_id` int NOT NULL,
  `submitted_by` int NOT NULL,
  `content_json` text COLLATE utf8mb4_unicode_ci,
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
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.program_submissions: ~1 rows (approximately)

-- Dumping structure for table pcds2030_dashboard.reporting_periods
CREATE TABLE IF NOT EXISTS `reporting_periods` (
  `period_id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `quarter` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
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
REPLACE INTO `reporting_periods` (`period_id`, `year`, `quarter`, `start_date`, `end_date`, `status`, `updated_at`, `is_standard_dates`, `created_at`) VALUES
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
  `report_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `pdf_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `pptx_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `generated_by` int NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`report_id`),
  KEY `period_id` (`period_id`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=312 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table pcds2030_dashboard.reports: ~1 rows (approximately)
REPLACE INTO `reports` (`report_id`, `period_id`, `report_name`, `description`, `pdf_path`, `pptx_path`, `generated_by`, `generated_at`, `is_public`) VALUES
	(301, 2, 'Forestry Report - Q2 2025', '', '', 'pptx/Forestry_Q2-2025_20250521030906.pptx', 1, '2025-05-21 01:09:06', 0);

-- Dumping structure for table pcds2030_dashboard.sectors
CREATE TABLE IF NOT EXISTS `sectors` (
  `sector_id` int NOT NULL AUTO_INCREMENT,
  `sector_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`sector_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.sectors: ~1 rows (approximately)
REPLACE INTO `sectors` (`sector_id`, `sector_name`, `description`) VALUES
	(1, 'Forestry', 'Forestry sector including timber and forest resources');

-- Dumping structure for table pcds2030_dashboard.sector_outcomes_data
CREATE TABLE IF NOT EXISTS `sector_outcomes_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `metric_id` int NOT NULL,
  `sector_id` int NOT NULL,
  `period_id` int DEFAULT NULL,
  `table_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_draft` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `metric_sector_draft` (`metric_id`,`sector_id`,`is_draft`),
  KEY `fk_period_id` (`period_id`),
  CONSTRAINT `sector_outcomes_data_chk_1` CHECK (json_valid(`data_json`))
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.sector_outcomes_data: ~1 rows (approximately)
REPLACE INTO `sector_outcomes_data` (`id`, `metric_id`, `sector_id`, `period_id`, `table_name`, `data_json`, `is_draft`, `created_at`, `updated_at`) VALUES
	(57, 1, 1, NULL, '321', '{\n    "title": "321",\n    "description": "12",\n    "base_year": 2024,\n    "target_year": 2029,\n    "measurement_frequency": "quarterly",\n    "unit_of_measurement": "123",\n    "data_source": "123",\n    "methodology": ""\n}', 1, '2025-05-26 03:35:26', '2025-05-27 07:09:39');

-- Dumping structure for table pcds2030_dashboard.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agency_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','agency') COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pcds2030_dashboard.users: ~12 rows (approximately)
REPLACE INTO `users` (`user_id`, `username`, `password`, `agency_name`, `role`, `sector_id`, `agency_group_id`, `created_at`, `updated_at`, `is_active`) VALUES
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

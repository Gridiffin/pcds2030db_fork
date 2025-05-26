-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 08:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pcds2030_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `agency_group`
--

CREATE TABLE `agency_group` (
  `id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `sector_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agency_group`
--

INSERT INTO `agency_group` (`id`, `group_name`, `sector_id`) VALUES
(0, 'STIDC', 1),
(1, 'SFC', 1),
(2, 'FDS', 1);

-- --------------------------------------------------------

--
-- Table structure for table `metrics_details`
--

CREATE TABLE `metrics_details` (
  `detail_id` int(11) NOT NULL,
  `detail_name` varchar(255) NOT NULL,
  `detail_json` longtext NOT NULL,
  `is_draft` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `metrics_details`
--

INSERT INTO `metrics_details` (`detail_id`, `detail_name`, `detail_json`, `is_draft`, `created_at`, `updated_at`) VALUES
(19, 'TPA Protection & Biodiversity Conservation Programs (incl. community-based initiatives', '{\r\n  \"layout_type\": \"simple\",\r\n  \"items\": [\r\n    {\r\n      \"value\": \"32\",\r\n      \"description\": \"On-going programs and initiatives by SFC (as of Sept 2024)\"\r\n    }\r\n  ]\r\n}', 0, '2025-05-07 19:33:42', '2025-05-14 02:13:32'),
(21, 'Certification of FMU & FPMU', '{\n  \"layout_type\": \"comparison\",\n  \"items\": [\n    {\n      \"label\": \"FMU\",\n      \"value\": \"78%\",\n      \"description\": \"2,327,221 ha Certified (Sept 2024)\"\n    },\n    {\n      \"label\": \"FPMU\",\n      \"value\": \"69%\",\n      \"description\": \"122,800 ha Certified (Sept 2024)\"\n    }\n  ]\n}', 0, '2025-05-07 19:40:32', '2025-05-14 02:05:29'),
(39, 'Obtain world recognition for sustainable management practices and conservation effort', '{\"layout_type\": \"comparison\", \"items\": [{\"label\": \"SDGP UNESCO Global Geopark\", \"value\": \"50%\", \"description\": \"(as of Sept 2024)\"}, {\"label\": \"Niah NP UNESCO World Heritage Site\", \"value\": \"100%\", \"description\": \"(as of Sept 2024)\"}]}', 0, '2025-05-08 16:59:53', '2025-05-14 02:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'update',
  `read_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `action_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outcomes_details`
--

CREATE TABLE `outcomes_details` (
  `detail_id` int(11) NOT NULL,
  `detail_name` varchar(255) NOT NULL,
  `detail_json` longtext NOT NULL,
  `is_draft` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outcomes_details`
--

INSERT INTO `outcomes_details` (`detail_id`, `detail_name`, `detail_json`, `is_draft`, `created_at`, `updated_at`) VALUES
(19, 'TPA Protection & Biodiversity Conservation Programs (incl. community-based initiatives', '{\r\n  \"layout_type\": \"simple\",\r\n  \"items\": [\r\n    {\r\n      \"value\": \"32\",\r\n      \"description\": \"On-going programs and initiatives by SFC (as of Sept 2024)\"\r\n    }\r\n  ]\r\n}', 0, '2025-05-07 19:33:42', '2025-05-14 02:13:32'),
(21, 'Certification of FMU & FPMU', '{\n  \"layout_type\": \"comparison\",\n  \"items\": [\n    {\n      \"label\": \"FMU\",\n      \"value\": \"78%\",\n      \"description\": \"2,327,221 ha Certified (Sept 2024)\"\n    },\n    {\n      \"label\": \"FPMU\",\n      \"value\": \"69%\",\n      \"description\": \"122,800 ha Certified (Sept 2024)\"\n    }\n  ]\n}', 0, '2025-05-07 19:40:32', '2025-05-14 02:05:29'),
(39, 'Obtain world recognition for sustainable management practices and conservation effort', '{\"layout_type\": \"comparison\", \"items\": [{\"label\": \"SDGP UNESCO Global Geopark\", \"value\": \"50%\", \"description\": \"(as of Sept 2024)\"}, {\"label\": \"Niah NP UNESCO World Heritage Site\", \"value\": \"100%\", \"description\": \"(as of Sept 2024)\"}]}', 0, '2025-05-08 16:59:53', '2025-05-14 02:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `owner_agency_id` int(11) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_assigned` tinyint(1) NOT NULL DEFAULT 1,
  `edit_permissions` text DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 1,
  `agency_owner` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`, `description`, `owner_agency_id`, `sector_id`, `start_date`, `end_date`, `created_at`, `updated_at`, `is_assigned`, `edit_permissions`, `created_by`, `agency_owner`) VALUES
(98, 'qwe', 'qwe', 12, 1, NULL, NULL, '2025-05-26 01:38:51', '2025-05-26 01:38:51', 0, NULL, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `program_submissions`
--

CREATE TABLE `program_submissions` (
  `submission_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `status` enum('target-achieved','on-track-yearly','severe-delay','not-started') NOT NULL,
  `content_json` text DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_draft` tinyint(1) NOT NULL DEFAULT 0,
  `agency_owner` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program_submissions`
--

INSERT INTO `program_submissions` (`submission_id`, `program_id`, `period_id`, `submitted_by`, `status`, `content_json`, `submission_date`, `updated_at`, `is_draft`, `agency_owner`) VALUES
(77, 98, 2, 12, 'target-achieved', '{\"target\":\"\",\"achievement\":\"\",\"status_text\":\"\",\"rating\":\"target-achieved\",\"remarks\":\"\",\"targets\":[],\"content\":{\"targets\":[{\"target_text\":\"\",\"status_description\":\"\",\"target_value\":null,\"achievement_value\":null}],\"achievement\":\"\",\"status\":\"target-achieved\",\"status_text\":\"\"}}', '2025-05-26 01:38:51', '2025-05-26 01:38:51', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `reporting_periods`
--

CREATE TABLE `reporting_periods` (
  `period_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `quarter` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_standard_dates` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reporting_periods`
--

INSERT INTO `reporting_periods` (`period_id`, `year`, `quarter`, `start_date`, `end_date`, `status`, `updated_at`, `is_standard_dates`, `created_at`) VALUES
(1, 2025, 1, '2025-01-01', '2025-03-31', 'closed', '2025-04-15 01:45:45', 1, '2025-04-17 02:54:12'),
(2, 2025, 2, '2025-04-01', '2025-06-30', 'open', '2025-04-17 02:58:41', 1, '2025-04-17 02:54:12'),
(3, 2025, 3, '2025-07-01', '2025-09-30', 'closed', '2025-04-17 02:37:02', 1, '2025-04-17 02:54:12'),
(4, 2025, 4, '2025-10-01', '2025-12-31', 'closed', '2025-04-17 02:34:40', 1, '2025-04-17 02:54:12'),
(10, 2024, 2, '2024-04-01', '2024-06-30', 'closed', '2025-04-17 02:58:36', 1, '2025-04-17 02:54:12'),
(11, 2025, 5, '2025-01-01', '2025-06-30', 'open', '2025-05-18 13:16:02', 1, '2025-05-18 13:13:23'),
(12, 2025, 6, '2025-07-01', '2025-12-31', 'closed', '2025-05-18 13:13:23', 1, '2025-05-18 13:13:23');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `pptx_path` varchar(255) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_public` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `period_id`, `report_name`, `description`, `pdf_path`, `pptx_path`, `generated_by`, `generated_at`, `is_public`) VALUES
(301, 2, 'Forestry Report - Q2 2025', '', '', 'pptx/Forestry_Q2-2025_20250521030906.pptx', 1, '2025-05-21 01:09:06', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors` (
  `sector_id` int(11) NOT NULL,
  `sector_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sectors`
--

INSERT INTO `sectors` (`sector_id`, `sector_name`, `description`) VALUES
(1, 'Forestry', 'Forestry sector including timber and forest resources');

-- --------------------------------------------------------

--
-- Table structure for table `sector_outcomes_data`
--

CREATE TABLE `sector_outcomes_data` (
  `id` int(11) NOT NULL,
  `metric_id` int(11) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `period_id` int(11) DEFAULT NULL,
  `table_name` varchar(255) NOT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data_json`)),
  `is_draft` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sector_outcomes_data`
--

INSERT INTO `sector_outcomes_data` (`id`, `metric_id`, `sector_id`, `period_id`, `table_name`, `data_json`, `is_draft`, `created_at`, `updated_at`) VALUES
(57, 1, 1, NULL, '123', '{\"columns\":[\"123\"],\"units\":[],\"data\":{\"January\":{\"123\":123123}}}', 1, '2025-05-26 03:35:26', '2025-05-26 03:35:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `agency_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','agency') NOT NULL,
  `sector_id` int(11) DEFAULT NULL,
  `agency_id` int(255) NOT NULL COMMENT '0-STIDC\r\n1-SFC\r\n2-FDS',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `agency_name`, `role`, `sector_id`, `agency_id`, `created_at`, `updated_at`, `is_active`) VALUES
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agency_group`
--
ALTER TABLE `agency_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sector_id` (`sector_id`);

--
-- Indexes for table `metrics_details`
--
ALTER TABLE `metrics_details`
  ADD PRIMARY KEY (`detail_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `outcomes_details`
--
ALTER TABLE `outcomes_details`
  ADD PRIMARY KEY (`detail_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_id`),
  ADD KEY `owner_agency_id` (`owner_agency_id`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `agency_owner` (`agency_owner`),
  ADD KEY `agency_owner_2` (`agency_owner`),
  ADD KEY `agency_owner_3` (`agency_owner`);

--
-- Indexes for table `program_submissions`
--
ALTER TABLE `program_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `period_id` (`period_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_program_period_draft` (`program_id`,`period_id`,`is_draft`),
  ADD KEY `agency_owner` (`agency_owner`);

--
-- Indexes for table `reporting_periods`
--
ALTER TABLE `reporting_periods`
  ADD PRIMARY KEY (`period_id`),
  ADD UNIQUE KEY `year` (`year`,`quarter`),
  ADD UNIQUE KEY `year_quarter_unique` (`year`,`quarter`),
  ADD UNIQUE KEY `year_quarter` (`year`,`quarter`),
  ADD KEY `quarter_year_idx` (`quarter`,`year`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `period_id` (`period_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`sector_id`);

--
-- Indexes for table `sector_outcomes_data`
--
ALTER TABLE `sector_outcomes_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `metric_sector_draft` (`metric_id`,`sector_id`,`is_draft`),
  ADD KEY `fk_period_id` (`period_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `agency_name` (`agency_name`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agency_group`
--
ALTER TABLE `agency_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `metrics_details`
--
ALTER TABLE `metrics_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outcomes_details`
--
ALTER TABLE `outcomes_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `program_submissions`
--
ALTER TABLE `program_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `reporting_periods`
--
ALTER TABLE `reporting_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=312;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `sector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sector_outcomes_data`
--
ALTER TABLE `sector_outcomes_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agency_group`
--
ALTER TABLE `agency_group`
  ADD CONSTRAINT `agency_group_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`owner_agency_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `programs_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`),
  ADD CONSTRAINT `programs_ibfk_3` FOREIGN KEY (`agency_owner`) REFERENCES `users` (`agency_id`);

--
-- Constraints for table `program_submissions`
--
ALTER TABLE `program_submissions`
  ADD CONSTRAINT `program_submissions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `program_submissions_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  ADD CONSTRAINT `program_submissions_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `agency_group` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

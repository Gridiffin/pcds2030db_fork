-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 04:39 AM
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
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_status` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_by` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`, `description`, `owner_agency_id`, `sector_id`, `start_date`, `end_date`, `created_at`, `updated_at`, `is_assigned`, `edit_permissions`, `created_by`) VALUES
(23, 'popo', 'popopipipop', 12, 2, '2025-04-01', '2025-04-08', '2025-04-08 08:26:23', '2025-04-08 08:26:23', 0, NULL, 12),
(25, 'test', 'test', 12, 2, '2025-04-03', '2025-04-03', '2025-04-10 02:56:45', '2025-04-10 02:56:45', 0, NULL, 12),
(26, 'qwer', 'qwer', 12, 2, '2025-01-01', '2025-02-10', '2025-04-10 03:17:59', '2025-04-10 03:17:59', 0, NULL, 12),
(38, 'cuba', 'cayb', 12, 2, '2025-04-15', '2025-04-17', '2025-04-16 07:22:38', '2025-04-16 07:22:38', 1, '{\"edit_permissions\":[\"target\",\"status\",\"status_text\",\"description\"],\"default_values\":[]}', 1),
(40, 'draft1', '', 12, 2, '0000-00-00', '0000-00-00', '2025-04-21 03:01:54', '2025-04-21 03:01:54', 0, NULL, 12),
(41, 'draft2', '', 12, 2, '0000-00-00', '0000-00-00', '2025-04-21 03:02:05', '2025-04-21 03:02:05', 0, NULL, 12);

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
  `is_draft` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program_submissions`
--

INSERT INTO `program_submissions` (`submission_id`, `program_id`, `period_id`, `submitted_by`, `status`, `content_json`, `submission_date`, `updated_at`, `is_draft`) VALUES
(23, 23, 2, 12, 'target-achieved', '{\"target\":\"target\",\"status_date\":\"2025-04-08\",\"status_text\":\"achievement\"}', '2025-04-08 08:26:23', '2025-04-08 08:26:23', 0),
(25, 25, 2, 12, 'target-achieved', '{\"target\":\"test\",\"status_date\":\"2025-04-10\",\"status_text\":\"test\"}', '2025-04-10 02:56:45', '2025-04-10 02:56:45', 0),
(26, 26, 2, 12, 'severe-delay', '{\"target\":\"qwer\",\"status_date\":\"2025-04-10\",\"status_text\":\"qwer\"}', '2025-04-10 03:17:59', '2025-04-10 03:17:59', 0),
(31, 40, 2, 12, 'not-started', '{\"target\":\"target\",\"status_date\":\"2025-04-21\",\"status_text\":\"\",\"achievement\":\"\",\"remarks\":\"\"}', '2025-04-21 03:01:54', '2025-04-21 03:01:54', 1),
(32, 41, 2, 12, 'not-started', '{\"target\":\"target\",\"status_date\":\"2025-04-21\",\"status_text\":\"\",\"achievement\":\"\",\"remarks\":\"\"}', '2025-04-21 03:02:05', '2025-04-21 03:02:05', 1);

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
(10, 2024, 2, '2024-04-01', '2024-06-30', 'closed', '2025-04-17 02:58:36', 1, '2025-04-17 02:54:12');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `pptx_path` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'Forestry', 'Forestry sector including timber and forest resources'),
(2, 'Land', 'Land development and management'),
(3, 'Environment', 'Environmental protection and management'),
(4, 'Natural Resources', 'Management of natural resources'),
(5, 'Urban Development', 'Urban planning and development');

-- --------------------------------------------------------

--
-- Table structure for table `sector_metrics_definition`
--

CREATE TABLE `sector_metrics_definition` (
  `metric_id` int(11) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_unit` varchar(50) DEFAULT NULL,
  `metric_type` enum('numeric','percentage','text') NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `permission_type` enum('creator_only','selected_agencies','all_sector') NOT NULL DEFAULT 'creator_only',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sector_metrics_draft`
--

CREATE TABLE `sector_metrics_draft` (
  `id` int(11) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `metric_id` int(11) NOT NULL,
  `month` varchar(20) NOT NULL COMMENT 'Month of the metric',
  `year` int(4) NOT NULL COMMENT 'Year of the metric',
  `column_title` varchar(255) NOT NULL COMMENT 'Title of the column',
  `table_content` text NOT NULL COMMENT 'Content of the table',
  `time_added` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Time when the record was added'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sector_metrics_submitted`
--

CREATE TABLE `sector_metrics_submitted` (
  `id` int(11) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `metric_id` int(11) NOT NULL,
  `month` varchar(20) NOT NULL COMMENT 'Month of the metric',
  `column_title` varchar(255) NOT NULL COMMENT 'Title of the column',
  `table_content` text NOT NULL COMMENT 'Content of the table',
  `time_added` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Time when the record was added'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sector_metrics_submitted`
--

INSERT INTO `sector_metrics_submitted` (`id`, `table_name`, `sector_id`, `metric_id`, `month`, `column_title`, `table_content`, `time_added`) VALUES
(44, 'something', 2, 1, 'January', '022', '0.001', '2025-04-25 02:06:51'),
(45, 'something', 2, 1, 'January', 'this', '123', '2025-04-25 02:06:51'),
(46, 'something', 0, 1, 'January', 'click here to auvaname', '0', '2025-04-25 02:11:37');

-- --------------------------------------------------------

--
-- Table structure for table `sector_metric_permissions`
--

CREATE TABLE `sector_metric_permissions` (
  `permission_id` int(11) NOT NULL,
  `metric_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sector_metric_values`
--

CREATE TABLE `sector_metric_values` (
  `value_id` int(11) NOT NULL,
  `metric_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `last_edited_by` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `numeric_value` decimal(15,2) DEFAULT NULL,
  `text_value` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sector_metric_value_history`
--

CREATE TABLE `sector_metric_value_history` (
  `history_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `numeric_value` decimal(15,2) DEFAULT NULL,
  `text_value` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `agency_name`, `role`, `sector_id`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'admin', '$2y$10$bPQQFeR4PbcueCgmV7/2Au.HWCjWH8v8ox.R.MxMfk4qXXHi3uPw6', 'Ministry of Natural Resources and Urban Development', 'admin', NULL, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(3, 'land_survey', '$2y$10$p1aSIN6bsbUlMqXyd17TMO8ZY8wJeQkVZjiKjjkhaN3mYYR5bJhBS', 'Land and Survey Department', 'agency', 2, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(4, 'nreb', '$2y$10$9i/6yu1uT3qT2v23Wx/H/.3B5XHHGqcsc6bqN09jOS9RNj/5xXvoa', 'Natural Resources and Environment Board', 'agency', 3, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(5, 'sfc', '$2y$10$lhVSzcJ/epOb2ce27OVUH.bmOPGsOPw38c/tnjFdcGl0XDjp4qtfG', 'Sarawak Forestry Corporation', 'agency', 1, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(6, 'lcda', '$2y$10$QxyxZHPAzKcmQVjo1uiN7uP9ApdTpfoMwavT0bmmrGAIxiS5vAwTi', 'Land Custody and Development Authority', 'agency', 2, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(12, 'user', '$2y$10$/Z6xCsE7OknP.4HBT5CdBuWDZK5VNMf7MqwmGusJ0SM8xxaGQKdq2', 'testagency', 'agency', 2, '2025-03-25 07:42:27', '2025-04-09 06:14:43', 1),
(15, 'testadmin', '$2y$10$JQaXUGYMej1nriu6lgYQXOvCjrfiGKRhFgqMe0kaBf./g.38b/eom', '', 'admin', NULL, '2025-04-11 06:12:58', '2025-04-17 01:34:13', 1),
(16, 'test3', '$2y$10$c6NUe40VWysBKupPkbkod.0q2BcpaU2/NeOzFQNFdCU2/lAplyXyG', '', 'admin', NULL, '2025-04-11 06:25:19', '2025-04-17 01:33:48', 0),
(17, 'admin2', '$2y$10$tkzE2DHMABmf5IiQ4.2VU.Mkg4laogdzEvlNSmcoz8tSu35Cx/wwO', '', 'admin', NULL, '2025-04-16 07:40:43', '2025-04-17 03:03:44', 1),
(18, 'testadmin2', '$2y$10$0Z56YV.wuWhmAHQGipoyZ.ZZag0jieeRpczfsvQvmZTPSuSvNk/5O', NULL, 'admin', NULL, '2025-04-17 00:41:59', '2025-04-17 00:41:59', 1),
(19, 'speed', '$2y$10$HkY31pXS.sqweZK8YLM1MuhuLRKogaDHExbneEY8p.gP3jO2iIXBq', 'Agensiii', 'agency', 2, '2025-04-17 00:43:54', '2025-04-17 00:43:54', 1),
(20, 'role1', '$2y$10$h71HKURH6FI0/K/KnTaI9eMfH9vnhEJoRTc1Sw1sLljSQvrdiQr4u', 'thisisanotheruserwithdifferentrole', 'agency', 3, '2025-04-25 00:48:59', '2025-04-25 00:48:59', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_id`),
  ADD KEY `owner_agency_id` (`owner_agency_id`),
  ADD KEY `sector_id` (`sector_id`);

--
-- Indexes for table `program_submissions`
--
ALTER TABLE `program_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `period_id` (`period_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_program_period_draft` (`program_id`,`period_id`,`is_draft`);

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
-- Indexes for table `sector_metrics_definition`
--
ALTER TABLE `sector_metrics_definition`
  ADD PRIMARY KEY (`metric_id`),
  ADD UNIQUE KEY `sector_id` (`sector_id`,`metric_name`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `sector_metrics_draft`
--
ALTER TABLE `sector_metrics_draft`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `metric_id` (`metric_id`);

--
-- Indexes for table `sector_metrics_submitted`
--
ALTER TABLE `sector_metrics_submitted`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sector_metric_permissions`
--
ALTER TABLE `sector_metric_permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `metric_agency` (`metric_id`,`agency_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Indexes for table `sector_metric_values`
--
ALTER TABLE `sector_metric_values`
  ADD PRIMARY KEY (`value_id`),
  ADD UNIQUE KEY `metric_period` (`metric_id`,`period_id`),
  ADD KEY `agency_id` (`agency_id`),
  ADD KEY `period_id` (`period_id`),
  ADD KEY `last_edited_by` (`last_edited_by`);

--
-- Indexes for table `sector_metric_value_history`
--
ALTER TABLE `sector_metric_value_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `value_id` (`value_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `sector_id` (`sector_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `program_submissions`
--
ALTER TABLE `program_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `reporting_periods`
--
ALTER TABLE `reporting_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `sector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sector_metrics_definition`
--
ALTER TABLE `sector_metrics_definition`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sector_metrics_draft`
--
ALTER TABLE `sector_metrics_draft`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `sector_metrics_submitted`
--
ALTER TABLE `sector_metrics_submitted`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `sector_metric_permissions`
--
ALTER TABLE `sector_metric_permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sector_metric_values`
--
ALTER TABLE `sector_metric_values`
  MODIFY `value_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sector_metric_value_history`
--
ALTER TABLE `sector_metric_value_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `programs_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`);

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
-- Constraints for table `sector_metrics_definition`
--
ALTER TABLE `sector_metrics_definition`
  ADD CONSTRAINT `sector_metrics_definition_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`),
  ADD CONSTRAINT `sector_metrics_definition_ibfk_2` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sector_metric_permissions`
--
ALTER TABLE `sector_metric_permissions`
  ADD CONSTRAINT `sector_metric_permissions_ibfk_1` FOREIGN KEY (`metric_id`) REFERENCES `sector_metrics_definition` (`metric_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sector_metric_permissions_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `sector_metric_values`
--
ALTER TABLE `sector_metric_values`
  ADD CONSTRAINT `sector_metric_values_ibfk_1` FOREIGN KEY (`metric_id`) REFERENCES `sector_metrics_definition` (`metric_id`),
  ADD CONSTRAINT `sector_metric_values_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sector_metric_values_ibfk_3` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  ADD CONSTRAINT `sector_metric_values_ibfk_4` FOREIGN KEY (`last_edited_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sector_metric_value_history`
--
ALTER TABLE `sector_metric_value_history`
  ADD CONSTRAINT `sector_metric_value_history_ibfk_1` FOREIGN KEY (`value_id`) REFERENCES `sector_metric_values` (`value_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sector_metric_value_history_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

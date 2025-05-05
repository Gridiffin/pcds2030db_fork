-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 02:42 AM
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
(42, 'program 1', 'hello', 12, 2, NULL, NULL, '2025-04-25 03:46:44', '2025-04-25 03:46:44', 1, '{\"edit_permissions\":[\"target\",\"status\",\"status_text\",\"description\",\"timeline\"],\"default_values\":[]}', 1),
(47, 'program A', '', 12, 2, '0000-00-00', '0000-00-00', '2025-04-25 06:50:06', '2025-04-25 06:50:06', 0, NULL, 12),
(48, 'try lagi', 'ggeege', 12, 2, NULL, NULL, '2025-04-25 07:19:25', '2025-04-25 07:19:25', 1, '{\"edit_permissions\":[\"target\",\"status\",\"status_text\",\"description\",\"timeline\"],\"default_values\":[]}', 1),
(49, 'try draft', '', 12, 2, NULL, NULL, '2025-04-25 07:21:55', '2025-04-25 07:21:55', 1, '{\"edit_permissions\":[\"target\",\"status\",\"status_text\",\"description\"],\"default_values\":[]}', 1),
(50, 'zani', '', 12, 2, '2025-04-18', '2025-04-25', '2025-04-26 06:21:40', '2025-04-26 06:21:40', 1, '{\"edit_permissions\":[\"target\",\"status\",\"status_text\",\"description\",\"timeline\"],\"default_values\":[]}', 1),
(51, 'eheheh', '', 12, 2, '0000-00-00', '0000-00-00', '2025-04-26 06:27:30', '2025-04-26 06:27:30', 0, NULL, 12),
(52, 'paofpao', '', 12, 2, '0000-00-00', '0000-00-00', '2025-04-26 06:44:57', '2025-04-26 06:44:57', 0, NULL, 12),
(53, 'gagaga', 'hshsd', 12, 2, '2025-04-18', '2025-04-25', '2025-04-26 07:12:54', '2025-04-26 07:13:03', 0, NULL, 12);

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
(37, 42, 2, 12, 'target-achieved', '{\"target\":\"target\",\"status_date\":\"2025-04-25\",\"status_text\":\"\",\"achievement\":\"\",\"remarks\":\"\"}', '2025-04-25 04:25:21', '2025-04-25 04:25:21', 0),
(38, 47, 2, 12, 'on-track-yearly', '{\"target\":\"target A\",\"status_date\":\"2025-04-25\",\"status_text\":\"\"}', '2025-04-25 06:50:06', '2025-04-25 06:50:21', 0),
(39, 48, 2, 12, 'target-achieved', '{\"target\":\"tageeaehjk\",\"status_date\":\"2025-04-25\",\"status_text\":\"\",\"achievement\":\"\",\"remarks\":\"\"}', '2025-04-25 07:19:51', '2025-04-25 07:19:51', 0),
(40, 49, 2, 12, 'target-achieved', '{\"target\":\"targetee\",\"status_date\":\"2025-04-25\",\"status_text\":\"\",\"achievement\":\"\",\"remarks\":\"\"}', '2025-04-25 07:22:16', '2025-04-25 07:23:25', 0),
(41, 50, 2, 12, 'target-achieved', '{\"target\":\"helo\",\"status_date\":\"2025-04-26\",\"status_text\":\"\",\"achievement\":\"\",\"remarks\":\"\"}', '2025-04-26 06:23:32', '2025-04-26 06:43:02', 0),
(42, 51, 2, 12, 'not-started', '{\"target\":\"eheh\",\"status_date\":\"2025-04-26\",\"status_text\":\"\",\"achievement\":\"\",\"remarks\":\"\"}', '2025-04-26 06:27:30', '2025-04-26 06:44:33', 0),
(43, 52, 2, 12, 'severe-delay', '{\"target\":\"agogsjuig\",\"status_date\":\"2025-04-26\",\"status_text\":\"\",\"achievement\":\"\",\"remarks\":\"\"}', '2025-04-26 06:44:57', '2025-04-26 06:45:10', 0),
(44, 53, 2, 12, 'target-achieved', '{\"target\":\"agaga\",\"status_date\":\"2025-04-26\",\"status_text\":\"hjafafafa\",\"achievement\":\"\",\"remarks\":\"\",\"status\":\"target-achieved\"}', '2025-04-26 07:12:54', '2025-04-26 07:13:20', 0);

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
  `report_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `pptx_path` varchar(255) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_public` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `sector_metrics_data`
--

CREATE TABLE `sector_metrics_data` (
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
-- Dumping data for table `sector_metrics_data`
--

INSERT INTO `sector_metrics_data` (`id`, `metric_id`, `sector_id`, `period_id`, `table_name`, `data_json`, `is_draft`, `created_at`, `updated_at`) VALUES
(13, 4, 2, 2, 'table A', '{\"columns\":[\"column A\"],\"data\":{\"January\":{\"column A\":12000},\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 0, '2025-04-27 10:24:19', '2025-04-28 02:13:24'),
(14, 6, 2, 2, 'table C', '{\"columns\":[\"column c2\",\"column c1\"],\"data\":{\"January\":{\"column c1\":1500},\"February\":{\"column c2\":100000},\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 1, '2025-04-27 10:33:02', '2025-04-28 02:13:24'),
(20, 7, 2, 2, 'TIMBER EXPORT VALUE (RM)', '{\"columns\": [\"2022\", \"2023\", \"2024\", \"2025\"], \"units\": {\"2022\": \"RM\", \"2023\": \"RM\", \"2024\": \"RM\", \"2025\": \"RM\"}, \"data\": {\"January\": {\"2022\": 408531176.77, \"2023\": 263569916.63, \"2024\": 276004972.69, \"2025\": null}, \"February\": {\"2022\": 239761718.38, \"2023\": 226356164.30, \"2024\": 191530929.47, \"2025\": null}, \"March\": {\"2022\": 394935606.46, \"2023\": 261778295.29, \"2024\": 214907671.70, \"2025\": null}, \"April\": {\"2022\": 400891037.27, \"2023\": 215771835.07, \"2024\": 232014272.14, \"2025\": null}, \"May\": {\"2022\": 345725679.36, \"2023\": 324280067.64, \"2024\": 324627750.87, \"2025\": null}, \"June\": {\"2022\": 268966198.26, \"2023\": 235560482.89, \"2024\": 212303812.34, \"2025\": null}, \"July\": {\"2022\": 359792973.34, \"2023\": 244689028.37, \"2024\": 274788036.68, \"2025\": null}, \"August\": {\"2022\": 310830376.16, \"2023\": 344761866.36, \"2024\": 210420404.31, \"2025\": null}, \"September\": {\"2022\": 318990291.52, \"2023\": 210214202.20, \"2024\": 191837139.00, \"2025\": null}, \"October\": {\"2022\": 304693148.30, \"2023\": 266639022.25, \"2024\": null, \"2025\": null}, \"November\": {\"2022\": 303936172.09, \"2023\": 296062485.55, \"2024\": null, \"2025\": null}, \"December\": {\"2022\": 289911760.38, \"2023\": 251155864.77, \"2024\": null, \"2025\": null}}}', 0, '2025-04-27 11:45:15', '2025-04-28 02:13:24'),
(21, 5, 2, 2, 'table B', '{\"columns\":[\"hello0\",\"hello \"],\"data\":{\"January\":{\"hello0\":15,\"hello \":1},\"February\":{\"hello \":300,\"hello0\":456},\"March\":{\"hello \":500},\"April\":{\"hello \":0.07},\"May\":{\"hello \":123},\"June\":{\"hello \":0.56},\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]},\"units\":{\"hello \":\"RM\",\"hello0\":\"Ha\"}}', 0, '2025-04-28 02:43:34', '2025-04-28 02:43:34');

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
-- Indexes for table `sector_metrics_data`
--
ALTER TABLE `sector_metrics_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `metric_sector_draft` (`metric_id`,`sector_id`,`is_draft`),
  ADD KEY `fk_period_id` (`period_id`);

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
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `program_submissions`
--
ALTER TABLE `program_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
-- AUTO_INCREMENT for table `sector_metrics_data`
--
ALTER TABLE `sector_metrics_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
-- Constraints for table `sector_metrics_data`
--
ALTER TABLE `sector_metrics_data`
  ADD CONSTRAINT `fk_period_id` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

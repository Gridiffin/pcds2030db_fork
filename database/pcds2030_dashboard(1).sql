-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2025 at 08:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`, `description`, `owner_agency_id`, `sector_id`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 'Program 1', 'fhbsfbsh', 12, 2, '0000-00-00', '0000-00-00', '2025-03-26 03:03:36', '2025-03-26 03:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `program_submissions`
--

CREATE TABLE `program_submissions` (
  `submission_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `target` text DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `achievement` text DEFAULT NULL,
  `achievement_date` date DEFAULT NULL,
  `status` enum('on-track','delayed','completed','not-started') NOT NULL,
  `status_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_draft` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `status` enum('open','closed') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reporting_periods`
--

INSERT INTO `reporting_periods` (`period_id`, `year`, `quarter`, `start_date`, `end_date`, `status`) VALUES
(1, 2025, 1, '2025-01-01', '2025-03-31', 'open'),
(2, 2025, 2, '2025-04-01', '2025-06-30', 'closed'),
(3, 2025, 3, '2025-07-01', '2025-09-30', 'closed'),
(4, 2025, 4, '2025-10-01', '2025-12-31', 'closed');

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
  `period_id` int(11) NOT NULL,
  `numeric_value` decimal(15,2) DEFAULT NULL,
  `text_value` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
(2, 'forestry_dept', '$2y$10$JNUxFe7AFI8Q/.CYuXP3KeWl4urP.KEdqkCNyuPer9IZC6bOc.xJy', 'Forestry Department', 'agency', 1, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(3, 'land_survey', '$2y$10$p1aSIN6bsbUlMqXyd17TMO8ZY8wJeQkVZjiKjjkhaN3mYYR5bJhBS', 'Land and Survey Department', 'agency', 2, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(4, 'nreb', '$2y$10$9i/6yu1uT3qT2v23Wx/H/.3B5XHHGqcsc6bqN09jOS9RNj/5xXvoa', 'Natural Resources and Environment Board', 'agency', 3, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(5, 'sfc', '$2y$10$lhVSzcJ/epOb2ce27OVUH.bmOPGsOPw38c/tnjFdcGl0XDjp4qtfG', 'Sarawak Forestry Corporation', 'agency', 1, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(6, 'lcda', '$2y$10$QxyxZHPAzKcmQVjo1uiN7uP9ApdTpfoMwavT0bmmrGAIxiS5vAwTi', 'Land Custody and Development Authority', 'agency', 2, '2025-03-25 01:31:15', '2025-03-25 01:31:15', 1),
(12, 'user', '$2y$10$/Z6xCsE7OknP.4HBT5CdBuWDZK5VNMf7MqwmGusJ0SM8xxaGQKdq2', 'testagency', 'agency', 2, '2025-03-25 07:42:27', '2025-03-25 07:42:27', 1);

--
-- Indexes for dumped tables
--

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
  ADD KEY `period_id` (`period_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `idx_program_period_draft` (`program_id`,`period_id`,`is_draft`);

--
-- Indexes for table `reporting_periods`
--
ALTER TABLE `reporting_periods`
  ADD PRIMARY KEY (`period_id`),
  ADD UNIQUE KEY `year` (`year`,`quarter`);

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
-- Indexes for table `sector_metric_values`
--
ALTER TABLE `sector_metric_values`
  ADD PRIMARY KEY (`value_id`),
  ADD KEY `metric_id` (`metric_id`),
  ADD KEY `agency_id` (`agency_id`),
  ADD KEY `period_id` (`period_id`);

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
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `program_submissions`
--
ALTER TABLE `program_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reporting_periods`
--
ALTER TABLE `reporting_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `sector_metric_values`
--
ALTER TABLE `sector_metric_values`
  MODIFY `value_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `program_submissions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
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
-- Constraints for table `sector_metric_values`
--
ALTER TABLE `sector_metric_values`
  ADD CONSTRAINT `sector_metric_values_ibfk_1` FOREIGN KEY (`metric_id`) REFERENCES `sector_metrics_definition` (`metric_id`),
  ADD CONSTRAINT `sector_metric_values_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sector_metric_values_ibfk_3` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

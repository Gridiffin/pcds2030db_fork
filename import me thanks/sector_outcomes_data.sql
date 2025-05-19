-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 10:18 AM
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
(40, 7, 1, 2, 'TIMBER EXPORT VALUE (RM)', '{\"columns\":[\"2022\",\"2023\",\"2024\",\"2025\",\"2026\"],\"units\":{\"2022\":\"RM\",\"2023\":\"RM\",\"2024\":\"RM\",\"2025\":\"RM\"},\"data\":{\"January\":{\"2022\":408531176.77,\"2023\":263569916.63,\"2024\":276004972.69,\"2025\":null,\"2026\":7},\"February\":{\"2022\":239761718.38,\"2023\":226356164.3,\"2024\":191530929.47,\"2025\":null,\"2026\":0},\"March\":{\"2022\":394935606.46,\"2023\":261778295.29,\"2024\":214907671.7,\"2025\":null,\"2026\":0},\"April\":{\"2022\":400891037.27,\"2023\":215771835.07,\"2024\":232014272.14,\"2025\":null,\"2026\":0},\"May\":{\"2022\":345725679.36,\"2023\":324280067.64,\"2024\":324627750.87,\"2025\":null,\"2026\":0},\"June\":{\"2022\":268966198.26,\"2023\":235560482.89,\"2024\":212303812.34,\"2025\":null,\"2026\":0},\"July\":{\"2022\":359792973.34,\"2023\":244689028.37,\"2024\":274788036.68,\"2025\":null,\"2026\":0},\"August\":{\"2022\":310830376.16,\"2023\":344761866.36,\"2024\":210420404.31,\"2025\":null,\"2026\":0},\"September\":{\"2022\":318990291.52,\"2023\":210214202.2,\"2024\":191837139,\"2025\":null,\"2026\":0},\"October\":{\"2022\":304693148.3,\"2023\":266639022.25,\"2024\":null,\"2025\":null,\"2026\":0},\"November\":{\"2022\":303936172.09,\"2023\":296062485.55,\"2024\":null,\"2025\":null,\"2026\":0},\"December\":{\"2022\":289911760.38,\"2023\":251155864.77,\"2024\":null,\"2025\":null,\"2026\":0}}}', 0, '2025-05-13 01:05:12', '2025-05-13 02:54:18'),
(48, 8, 1, NULL, 'Table_8', '{\"columns\":[\"qwr\"],\"units\":[],\"data\":{\"January\":{\"qwr\":123}}}', 1, '2025-05-19 08:03:57', '2025-05-19 08:03:57'),
(49, 9, 1, NULL, 'Table_9', '{\"columns\":[\"test\"],\"units\":{\"test\":\"123\"},\"data\":{\"January\":{\"test\":123}}}', 1, '2025-05-19 08:09:51', '2025-05-19 08:09:53'),
(50, 10, 1, NULL, 'test draft', '{\"columns\":[\"test\"],\"data\":{\"January\":{\"test\":0.12},\"February\":{\"test\":123},\"March\":{\"test\":0},\"April\":{\"test\":0},\"May\":{\"test\":0},\"June\":{\"test\":0},\"July\":{\"test\":0},\"August\":{\"test\":0},\"September\":{\"test\":0},\"October\":{\"test\":0},\"November\":{\"test\":0},\"December\":{\"test\":0}},\"units\":{\"test\":\"r\"}}', 1, '2025-05-19 08:16:54', '2025-05-19 08:17:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sector_outcomes_data`
--
ALTER TABLE `sector_outcomes_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `metric_sector_draft` (`metric_id`,`sector_id`,`is_draft`),
  ADD KEY `fk_period_id` (`period_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sector_outcomes_data`
--
ALTER TABLE `sector_outcomes_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sector_outcomes_data`
--
ALTER TABLE `sector_outcomes_data`
  ADD CONSTRAINT `fk_period_id` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

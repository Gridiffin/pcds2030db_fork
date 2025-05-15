-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 08:50 AM
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
(60, 'Implementation of Forest Landscape Restoration Throughout Sarawak', 'Description', 12, 1, '2025-05-15', '2026-06-19', '2025-05-15 01:54:04', '2025-05-15 01:54:04', 0, NULL, 12),
(61, 'Conservation & Protection of Wetlands & Watershed Whitin Heart of Borneo Sarawak (HoB)', 'Description 2', 12, 1, '2025-05-15', '2027-05-28', '2025-05-15 02:02:01', '2025-05-15 02:02:01', 0, NULL, 12),
(68, 'Quantifying Forest Carbon Stock in Sarawak', 'Description here', 12, 1, '2025-05-01', '2029-05-01', '2025-05-15 06:14:20', '2025-05-15 06:14:20', 0, NULL, 12),
(69, 'Bamkboo Industry Development', 'Desc', 12, 1, '2025-05-15', '2025-06-05', '2025-05-15 06:28:42', '2025-05-15 06:28:42', 0, NULL, 12),
(70, 'Furniture Park', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-15 06:29:44', '2025-05-15 06:29:44', 0, NULL, 12),
(71, 'Sarawak Delta Geopark (SDGp) UNESCO Global Geopark', '', 12, 1, '2025-05-15', '2025-05-15', '2025-05-15 06:32:46', '2025-05-15 06:32:46', 0, NULL, 12),
(72, 'Stengthenining protection for selected Totally Protected Areas(TPA)', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-15 06:36:12', '2025-05-15 06:36:12', 0, NULL, 12),
(73, 'Development and upgrading of integrated facilities of 20 TPA', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-15 06:38:36', '2025-05-15 06:38:36', 0, NULL, 12),
(74, 'Strengthening Forest Enforcement', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-15 06:40:13', '2025-05-15 06:40:13', 0, NULL, 12),
(75, 'Niah NP UNESCO World Heritage Site (WHS)', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-15 06:41:05', '2025-05-15 06:41:05', 0, NULL, 12);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`owner_agency_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `programs_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

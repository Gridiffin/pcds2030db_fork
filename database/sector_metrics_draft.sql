-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 10:03 AM
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

--
-- Dumping data for table `sector_metrics_draft`
--

INSERT INTO `sector_metrics_draft` (`id`, `table_name`, `sector_id`, `metric_id`, `month`, `year`, `column_title`, `table_content`, `time_added`) VALUES
(21, '', 2, 0, 'January', 0, 'new col', '1234', '2025-04-21 06:45:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sector_metrics_draft`
--
ALTER TABLE `sector_metrics_draft`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `metric_id` (`metric_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sector_metrics_draft`
--
ALTER TABLE `sector_metrics_draft`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

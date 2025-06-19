-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 19, 2025 at 11:50 AM
-- Server version: 5.7.44-cll-lve
-- PHP Version: 8.3.22

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
  `agency_group_id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `sector_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `agency_group`
--

INSERT INTO `agency_group` (`agency_group_id`, `group_name`, `sector_id`) VALUES
(0, 'STIDC', 1),
(1, 'SFC', 1),
(2, 'FDS', 1);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(128) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` varchar(16) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `audit_logs`
--

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
(77, 1, 'login_success', 'Username: admin', '127.0.0.1', 'success', '2025-06-17 16:13:22'),
(78, 38, 'login_success', 'Username: sfc1', '115.133.77.235', 'success', '2025-06-17 23:01:04'),
(79, 38, 'logout', 'User logged out', '115.133.77.235', 'success', '2025-06-17 23:03:03'),
(80, 12, 'login_success', 'Username: user', '115.133.77.235', 'success', '2025-06-17 23:03:15'),
(81, 12, 'logout', 'User logged out', '115.133.77.235', 'success', '2025-06-17 23:06:34'),
(82, 12, 'login_success', 'Username: user', '203.106.127.66', 'success', '2025-06-17 23:10:50'),
(83, 12, 'logout', 'User logged out', '203.106.127.66', 'success', '2025-06-17 23:11:21'),
(84, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '203.106.127.66', 'failure', '2025-06-17 23:11:26'),
(85, 1, 'login_success', 'Username: admin', '203.106.127.66', 'success', '2025-06-17 23:11:31'),
(86, 0, 'login_failure', 'Username: wani | Reason: User not found', '115.164.74.18', 'failure', '2025-06-18 07:37:30'),
(87, 0, 'login_failure', 'Username: aileeskf | Reason: User not found', '115.164.201.20', 'failure', '2025-06-18 09:02:24'),
(88, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 09:14:04'),
(89, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 09:14:11'),
(90, 1, 'logout', 'User logged out', '27.125.242.98', 'success', '2025-06-18 09:16:48'),
(91, 12, 'login_success', 'Username: user', '27.125.242.98', 'success', '2025-06-18 09:17:34'),
(92, 12, 'logout', 'User logged out', '27.125.242.98', 'success', '2025-06-18 09:23:12'),
(93, 12, 'login_success', 'Username: user', '113.210.106.59', 'success', '2025-06-18 09:26:39'),
(94, 35, 'login_failure', 'Username: stidc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:11'),
(95, 41, 'login_failure', 'Username: fds1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:15'),
(96, 35, 'login_failure', 'Username: stidc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:17'),
(97, 38, 'login_failure', 'Username: sfc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:18'),
(98, 35, 'login_failure', 'Username: stidc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:23'),
(99, 41, 'login_failure', 'Username: fds1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:26'),
(100, 38, 'login_failure', 'Username: sfc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:29'),
(101, 42, 'login_failure', 'Username: fds2 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:36'),
(102, 38, 'login_failure', 'Username: sfc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:37'),
(103, 35, 'login_failure', 'Username: stidc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:38'),
(104, 35, 'login_failure', 'Username: stidc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:39'),
(105, 35, 'login_failure', 'Username: stidc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:39'),
(106, 12, 'logout', 'User logged out', '113.210.106.59', 'success', '2025-06-18 09:27:44'),
(107, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-18 09:27:46'),
(108, 41, 'login_failure', 'Username: fds1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:46'),
(109, 35, 'login_failure', 'Username: stidc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:53'),
(110, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-18 09:27:55'),
(111, 41, 'login_failure', 'Username: fds1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:27:55'),
(112, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 09:27:57'),
(113, 0, 'login_failure', 'Username: stidc8 | Reason: User not found', '210.186.48.30', 'failure', '2025-06-18 09:27:57'),
(114, 36, 'login_failure', 'Username: stidc2 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:28:08'),
(115, 36, 'login_failure', 'Username: stidc2 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:28:14'),
(116, 41, 'login_failure', 'Username: fds1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:28:18'),
(117, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-18 09:28:23'),
(118, 37, 'login_failure', 'Username: stidc3 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 09:28:24'),
(119, 36, 'login_success', 'Username: stidc2', '210.186.48.30', 'success', '2025-06-18 09:28:24'),
(120, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 09:28:26'),
(121, 35, 'login_success', 'Username: stidc1', '210.186.48.30', 'success', '2025-06-18 09:28:28'),
(122, 37, 'login_success', 'Username: stidc3', '210.186.48.30', 'success', '2025-06-18 09:28:29'),
(123, 0, 'login_failure', 'Username: fds10 | Reason: User not found', '115.164.179.115', 'failure', '2025-06-18 09:28:35'),
(124, 0, 'login_failure', 'Username: stidc8 | Reason: User not found', '210.186.48.30', 'failure', '2025-06-18 09:28:36'),
(125, 37, 'login_success', 'Username: stidc3', '210.186.48.30', 'success', '2025-06-18 09:28:37'),
(126, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 09:28:38'),
(127, 43, 'login_failure', 'Username: fds3 | Reason: Invalid password', '115.164.201.20', 'failure', '2025-06-18 09:28:45'),
(128, 35, 'login_success', 'Username: stidc1', '210.186.48.30', 'success', '2025-06-18 09:28:55'),
(129, 43, 'login_success', 'Username: fds3', '115.164.179.115', 'success', '2025-06-18 09:29:08'),
(130, 35, 'login_success', 'Username: stidc1', '210.186.48.30', 'success', '2025-06-18 09:29:09'),
(131, 37, 'login_success', 'Username: stidc3', '210.186.48.30', 'success', '2025-06-18 09:29:42'),
(132, 0, 'login_failure', 'Username: marconsj | Reason: User not found', '210.186.48.30', 'failure', '2025-06-18 09:29:45'),
(133, 35, 'login_success', 'Username: stidc1', '210.186.48.30', 'success', '2025-06-18 09:29:57'),
(134, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 09:30:16'),
(135, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 09:30:17'),
(136, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 09:30:25'),
(137, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 09:30:30'),
(138, 1, 'login_success', 'Username: admin', '210.186.48.30', 'success', '2025-06-18 09:30:50'),
(139, 12, 'login_success', 'Username: user', '113.210.106.59', 'success', '2025-06-18 09:31:44'),
(140, 37, 'create_program', 'Program Name: Establish Bamboo Plantation and Develop Bamboo-based Industry | Program ID: 176', '210.186.48.30', 'success', '2025-06-18 09:33:33'),
(141, 38, 'create_program', 'Program Name: Bambpp | Program ID: 177', '210.186.48.30', 'success', '2025-06-18 09:34:45'),
(142, 41, 'create_program', 'Program Name: enforcement | Program ID: 178', '210.186.48.30', 'success', '2025-06-18 09:34:48'),
(143, 12, 'create_program', 'Program Name: Bamboo Industry Development | Program ID: 179', '113.210.106.59', 'success', '2025-06-18 09:34:54'),
(144, 43, 'create_program', 'Program Name: Bamboo Industry Development | Program ID: 180', '115.164.179.115', 'success', '2025-06-18 09:35:04'),
(145, 35, 'login_success', 'Username: stidc1', '210.186.48.30', 'success', '2025-06-18 09:35:22'),
(146, 38, 'create_program', 'Program Name: My Unesco | Program ID: 181', '210.186.48.30', 'success', '2025-06-18 09:35:28'),
(147, 36, 'create_program', 'Program Name: Research and Development KURSI PUSAKA | Program ID: 182', '210.186.48.30', 'success', '2025-06-18 09:35:47'),
(148, 35, 'create_program', 'Program Name: Pusat Latihan Perkayuan PUSAKA Tanjung Manis | Program ID: 183', '210.186.48.30', 'success', '2025-06-18 09:36:14'),
(149, 41, 'create_program', 'Program Name: Bamboo Industry Development | Program ID: 184', '210.186.48.30', 'success', '2025-06-18 09:36:19'),
(150, 41, 'create_program', 'Program Name: Bamboo Industry Development | Program ID: 185', '210.186.48.30', 'success', '2025-06-18 09:36:24'),
(151, 35, 'create_program', 'Program Name: Bamboo Industry | Program ID: 186', '210.186.48.30', 'success', '2025-06-18 09:37:08'),
(152, 41, 'create_program', 'Program Name: Conservation and Protection | Program ID: 187', '210.186.48.30', 'success', '2025-06-18 09:37:10'),
(153, 41, 'create_program', 'Program Name: bamboo | Program ID: 188', '210.186.48.30', 'success', '2025-06-18 09:37:10'),
(154, 35, 'create_program', 'Program Name: Furniture | Program ID: 189', '210.186.48.30', 'success', '2025-06-18 09:37:53'),
(155, 37, 'login_success', 'Username: stidc3', '210.186.48.30', 'success', '2025-06-18 09:40:47'),
(156, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 09:43:09'),
(157, 37, 'create_program', 'Program Name: Establish Furniture Park in Tanjung Manis | Program ID: 190', '210.186.48.30', 'success', '2025-06-18 09:43:35'),
(158, 37, 'login_success', 'Username: stidc3', '183.171.115.61', 'success', '2025-06-18 09:43:53'),
(159, 37, 'login_success', 'Username: stidc3', '58.26.203.187', 'success', '2025-06-18 09:44:04'),
(160, 43, 'update_program', 'Program Name: Bamboo Industry Development | Program ID: 180', '115.164.201.20', 'success', '2025-06-18 09:45:36'),
(161, 35, 'update_program', 'Program Name: Pusat Latihan Perkayuan PUSAKA Tanjung Manis | Program ID: 183', '210.186.48.30', 'success', '2025-06-18 09:45:53'),
(162, 43, 'create_program', 'Program Name: ssdd | Program ID: 191', '115.164.179.115', 'success', '2025-06-18 09:46:26'),
(163, 37, 'logout', 'User logged out', '58.26.203.187', 'success', '2025-06-18 09:46:43'),
(164, 37, 'update_program', 'Program Name: Establish Furniture Park in Tanjung Manis | Program ID: 190', '210.186.48.30', 'success', '2025-06-18 09:46:44'),
(165, 37, 'program_submit_no_prior_submission', 'Program submission failed - no prior submission or draft found to validate content (Program ID: 190, Period ID: 2)', '210.186.48.30', 'failure', '2025-06-18 09:46:57'),
(166, 37, 'logout', 'User logged out', '183.171.115.61', 'success', '2025-06-18 09:47:05'),
(167, 35, 'outcome_created', 'Created outcome \'Repair and Maintenance of the Workshop\' (Metric ID: 9) for sector 1', '210.186.48.30', 'success', '2025-06-18 09:47:50'),
(168, 38, 'create_program', 'Program Name: SFC Program 1 | Program ID: 192', '210.186.48.30', 'success', '2025-06-18 09:48:03'),
(169, 37, 'login_failure', 'Username: stidc3 | Reason: Invalid password', '1.9.222.66', 'failure', '2025-06-18 09:48:35'),
(170, 0, 'login_failure', 'Username: user1234 | Reason: User not found', '1.9.222.66', 'failure', '2025-06-18 09:49:05'),
(171, 35, 'program_draft_saved', 'Program \'Pusat Latihan Perkayuan PUSAKA Tanjung Manis\' (ID: 183) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 09:49:56'),
(172, 37, 'login_success', 'Username: stidc3', '58.26.203.187', 'success', '2025-06-18 09:50:30'),
(173, 36, 'update_program', 'Program Name: Research and Development for KURSI PUSAKA in UNIMAS | Program ID: 182', '210.186.48.30', 'success', '2025-06-18 09:51:03'),
(174, 1, 'login_success', 'Username: admin', '27.125.242.138', 'success', '2025-06-18 09:51:43'),
(175, 1, 'toggle_period_status', 'Changed status of period: Q5 2025 (ID: 11) from open to closed', '27.125.242.138', 'success', '2025-06-18 09:51:58'),
(176, 38, 'program_submit_no_prior_submission', 'Program submission failed - no prior submission or draft found to validate content (Program ID: 192, Period ID: 2)', '210.186.48.30', 'failure', '2025-06-18 09:52:09'),
(177, 37, 'update_program', 'Program Name: Bamboo Industry Development | Program ID: 176', '210.186.48.30', 'success', '2025-06-18 09:52:27'),
(178, 41, 'create_program', 'Program Name: Proposed Implementation of Forest Landscape Restoration Throughout Sarawak | Program ID: 193', '210.186.48.30', 'success', '2025-06-18 09:52:41'),
(179, 37, 'program_draft_saved', 'Program \'Establish Furniture Park in Tanjung Manis\' (ID: 190) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 09:52:43'),
(180, 41, 'create_program', 'Program Name: STRENGTHENING FOREST ENFORCEMENT THROUGH ADVANCING THE TECHNOLOGY AND EQUIPMENTS | Program ID: 194', '210.186.48.30', 'success', '2025-06-18 09:52:50'),
(181, 37, 'delete_program', 'Program Name: Establish Furniture Park in Tanjung Manis | Program ID: 190', '210.186.48.30', 'success', '2025-06-18 09:56:13'),
(182, 35, 'update_program', 'Program Name: RESEARCH AND DEVELOPMENT FOR KURSI PUSAKA UNIMAS | Program ID: 186', '210.186.48.30', 'success', '2025-06-18 09:56:20'),
(183, 38, 'program_draft_saved', 'Program \'SFC Program 1\' (ID: 192) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 09:57:08'),
(184, 38, 'program_submit_no_prior_submission', 'Program submission failed - no prior submission or draft found to validate content (Program ID: 177, Period ID: 2)', '210.186.48.30', 'failure', '2025-06-18 09:57:36'),
(185, 43, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 180) draft saved for period 2', '115.164.201.20', 'success', '2025-06-18 09:57:44'),
(186, 35, 'program_draft_saved', 'Program \'RESEARCH AND DEVELOPMENT FOR KURSI PUSAKA UNIMAS\' (ID: 186) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 09:57:52'),
(187, 12, 'update_program', 'Program Name: Bamboo Industry Development | Program ID: 179', '113.210.106.59', 'success', '2025-06-18 09:58:27'),
(188, 1, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 09:58:27'),
(189, 12, 'login_success', 'Username: user', '27.125.242.114', 'success', '2025-06-18 09:58:34'),
(190, 35, 'update_program', 'Program Name: Furniture Park | Program ID: 189', '210.186.48.30', 'success', '2025-06-18 09:58:58'),
(191, 38, 'program_draft_saved', 'Program \'Bamboo Industry Developement 2026\' (ID: 177) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 09:59:00'),
(192, 38, 'program_submitted', 'Program successfully submitted (Program ID: 177, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 09:59:10'),
(193, 38, 'update_program', 'Program Name: Niah Unesco | Program ID: 181', '210.186.48.30', 'success', '2025-06-18 09:59:16'),
(194, 12, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 09:59:24'),
(195, 41, 'update_program', 'Program Name: The Conservation and Preservation of Geosites, Biosites and Cultural Sites within Sarawak Delta Geopark | Program ID: 184', '210.186.48.30', 'success', '2025-06-18 09:59:25'),
(196, 36, 'program_draft_saved', 'Program \'Research and Development for KURSI PUSAKA in UNIMAS\' (ID: 182) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 09:59:30'),
(197, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 09:59:33'),
(198, 38, 'program_draft_saved', 'Program \'Niah Unesco\' (ID: 181) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 09:59:45'),
(199, 1, 'outcome_status_change', 'Successfully unsubmitted (set to draft) outcome for metric ID: 9', '27.125.242.114', 'success', '2025-06-18 09:59:49'),
(200, 1, 'outcome_status_change', 'Successfully unsubmitted (set to draft) outcome for metric ID: 8', '27.125.242.114', 'success', '2025-06-18 09:59:56'),
(201, 38, 'program_submitted', 'Program successfully submitted (Program ID: 181, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 09:59:57'),
(202, 1, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 10:00:00'),
(203, 12, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 179) draft saved for period 2', '113.210.106.59', 'success', '2025-06-18 10:00:03'),
(204, 12, 'login_success', 'Username: user', '27.125.242.114', 'success', '2025-06-18 10:00:06'),
(205, 12, 'program_submitted', 'Program successfully submitted (Program ID: 179, Period ID: 2)', '113.210.106.59', 'success', '2025-06-18 10:00:19'),
(206, 38, 'program_submit_incomplete_content', 'Program submission failed - incomplete content (missing targets/rating) (Program ID: 192, Period ID: 2)', '210.186.48.30', 'failure', '2025-06-18 10:00:22'),
(207, 36, 'program_draft_saved', 'Program \'Research and Development for KURSI PUSAKA in UNIMAS\' (ID: 182) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:04:17'),
(208, 12, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 10:04:53'),
(209, 41, 'delete_program', 'Program Name: Certify Long Term Forest License Area and Forest Plantation | Program ID: 188', '210.186.48.30', 'success', '2025-06-18 10:05:15'),
(210, 38, 'program_submit_incomplete_content', 'Program submission failed - incomplete content (missing targets/rating) (Program ID: 192, Period ID: 2)', '210.186.48.30', 'failure', '2025-06-18 10:05:21'),
(211, 38, 'program_draft_saved', 'Program \'SFC Program 1\' (ID: 192) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:05:54'),
(212, 38, 'program_submitted', 'Program successfully submitted (Program ID: 192, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 10:05:59'),
(213, 35, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 189) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:07:11'),
(214, 12, 'login_success', 'Username: user', '27.125.242.114', 'success', '2025-06-18 10:07:13'),
(215, 38, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-18 10:07:53'),
(216, 39, 'login_success', 'Username: sfc2', '210.186.48.30', 'success', '2025-06-18 10:08:07'),
(217, 35, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 189) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:08:40'),
(218, 35, 'program_draft_saved', 'Program \'Pusat Latihan Perkayuan PUSAKA Tanjung Manis\' (ID: 183) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:09:12'),
(219, 43, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 180) draft saved for period 2', '115.164.179.115', 'success', '2025-06-18 10:09:35'),
(220, 35, 'program_draft_saved', 'Program \'RESEARCH AND DEVELOPMENT FOR KURSI PUSAKA UNIMAS\' (ID: 186) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:09:49'),
(221, 41, 'program_submit_no_prior_submission', 'Program submission failed - no prior submission or draft found to validate content (Program ID: 194, Period ID: 2)', '210.186.48.30', 'failure', '2025-06-18 10:10:09'),
(222, 35, 'program_draft_saved', 'Program \'Pusat Latihan Perkayuan PUSAKA Tanjung Manis\' (ID: 183) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:10:20'),
(223, 36, 'program_draft_saved', 'Program \'Research and Development for KURSI PUSAKA in UNIMAS\' (ID: 182) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:10:42'),
(224, 43, 'create_program', 'Program Name: Quantifying Forest Carbon Stock in Sarawak | Program ID: 195', '115.164.179.115', 'success', '2025-06-18 10:11:12'),
(225, 35, 'program_draft_saved', 'Program \'RESEARCH AND DEVELOPMENT FOR KURSI PUSAKA UNIMAS\' (ID: 186) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:12:48'),
(226, 35, 'program_draft_saved', 'Program \'RESEARCH AND DEVELOPMENT FOR KURSI PUSAKA UNIMAS\' (ID: 186) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:13:40'),
(227, 35, 'program_draft_saved', 'Program \'RESEARCH AND DEVELOPMENT FOR KURSI PUSAKA UNIMAS\' (ID: 186) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:14:08'),
(228, 36, 'program_draft_saved', 'Program \'Research and Development for KURSI PUSAKA in UNIMAS\' (ID: 182) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:16:16'),
(229, 36, 'create_program', 'Program Name: Applied R&D to develop commercially viable high value products from planted timber species | Program ID: 196', '210.186.48.30', 'success', '2025-06-18 10:16:34'),
(230, 35, 'program_submitted', 'Program successfully submitted (Program ID: 186, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 10:16:38'),
(231, 43, 'update_program', 'Program Name: Quantifying Forest Carbon Stock in Sarawak | Program ID: 195', '210.186.48.30', 'success', '2025-06-18 10:21:15'),
(232, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:22:10'),
(233, 41, 'program_draft_saved', 'Program \'Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak\' (ID: 187) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:22:18'),
(234, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 10:29:16'),
(235, 41, 'program_draft_saved', 'Program \'Strengthening Forest Enforcement Through Advancing the Technology and Equipments\' (ID: 194) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:33:07'),
(236, 35, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 189) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:33:31'),
(237, 35, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 189) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:33:59'),
(238, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:35:08'),
(239, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:35:27'),
(240, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:37:19'),
(241, 36, 'update_program', 'Program Name: Applied R&D to develop commercially viable high value products from planted timber species | Program ID: 196', '210.186.48.30', 'success', '2025-06-18 10:37:38'),
(242, 36, 'program_draft_saved', 'Program \'Applied R&D to develop commercially viable high value products from planted timber species\' (ID: 196) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:39:25'),
(243, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:40:55'),
(244, 36, 'create_program', 'Program Name: Implementation of Sarawak Young Designers (SayD’signers Sarawak) programme | Program ID: 197', '210.186.48.30', 'success', '2025-06-18 10:41:00'),
(245, 41, 'program_draft_saved', 'Program \'Strengthening Forest Enforcement Through Advancing the Technology and Equipments\' (ID: 194) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:44:48'),
(246, 35, 'login_failure', 'Username: stidc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 10:44:57'),
(247, 35, 'login_success', 'Username: stidc1', '210.186.48.30', 'success', '2025-06-18 10:45:04'),
(248, 12, 'login_success', 'Username: user', '27.125.242.114', 'success', '2025-06-18 10:45:22'),
(249, 41, 'delete_program', 'Program Name: enforcement | Program ID: 178', '210.186.48.30', 'success', '2025-06-18 10:45:32'),
(250, 41, 'program_draft_saved', 'Program \'Proposed Implementation of Forest Landscape Restoration Throughout Sarawak\' (ID: 193) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:45:48'),
(251, 41, 'program_draft_saved', 'Program \'Strengthening Forest Enforcement Through Advancing the Technology and Equipments\' (ID: 194) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:46:42'),
(252, 43, 'delete_program', 'Program Name: ssdd | Program ID: 191', '210.186.48.30', 'success', '2025-06-18 10:48:10'),
(253, 43, 'delete_program', 'Program Name: Bamboo Industry Development | Program ID: 180', '210.186.48.30', 'success', '2025-06-18 10:48:15'),
(254, 12, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 10:48:52'),
(255, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 10:48:57'),
(256, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 313, File: Forestry_Q2-2025_20250618024920.pptx, Size: 168,476 bytes)', '27.125.242.114', 'success', '2025-06-18 10:49:20'),
(257, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618024920.pptx (Size: 168476 bytes)', '27.125.242.114', 'success', '2025-06-18 10:49:24'),
(258, 35, 'program_draft_saved', 'Program \'Pusat Latihan Perkayuan PUSAKA Tanjung Manis\' (ID: 183) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:50:25'),
(259, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q1-2025\' for Forestry - Q1 2025 (ID: 314, File: Forestry_Q1-2025_20250618025043.pptx, Size: 144,723 bytes)', '27.125.242.114', 'success', '2025-06-18 10:50:43'),
(260, 1, 'file_download', 'File downloaded: Forestry_Q1-2025_20250618025043.pptx (Size: 144723 bytes)', '27.125.242.114', 'success', '2025-06-18 10:50:47'),
(261, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 315, File: Forestry_Q2-2025_20250618025206.pptx, Size: 144,763 bytes)', '27.125.242.114', 'success', '2025-06-18 10:52:06'),
(262, 35, 'program_draft_saved', 'Program \'Pusat Latihan Perkayuan PUSAKA Tanjung Manis\' (ID: 183) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:52:08'),
(263, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618025206.pptx (Size: 144763 bytes)', '27.125.242.114', 'success', '2025-06-18 10:52:10'),
(264, 41, 'outcome_created', 'Created outcome \'Sarawak Delta Geopark (SDGp)\' (Metric ID: 10) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-18 10:52:22'),
(265, 36, 'update_program', 'Program Name: Implementation of Sarawak Young Designers (SayD’signers Sarawak) programme | Program ID: 197', '210.186.48.30', 'success', '2025-06-18 10:52:27'),
(266, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:52:39'),
(267, 1, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 10:52:56'),
(268, 12, 'login_success', 'Username: user', '27.125.242.114', 'success', '2025-06-18 10:53:03'),
(269, 35, 'program_draft_saved', 'Program \'Pusat Latihan Perkayuan PUSAKA Tanjung Manis\' (ID: 183) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:53:05'),
(270, 12, 'outcome_submitted', 'Outcome \'TOTAL DEGRADED AREA\' (Metric ID: 8) submitted for sector 1', '27.125.242.114', 'success', '2025-06-18 10:53:12'),
(271, 12, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 10:53:59'),
(272, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 10:54:06'),
(273, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 316, File: Forestry_Q2-2025_20250618025425.pptx, Size: 145,046 bytes)', '27.125.242.114', 'success', '2025-06-18 10:54:25'),
(274, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618025425.pptx (Size: 145046 bytes)', '27.125.242.114', 'success', '2025-06-18 10:54:28'),
(275, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 317, File: Forestry_Q2-2025_20250618025454.pptx, Size: 145,123 bytes)', '27.125.242.114', 'success', '2025-06-18 10:54:54'),
(276, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618025454.pptx (Size: 145123 bytes)', '27.125.242.114', 'success', '2025-06-18 10:55:02'),
(277, 41, 'program_draft_saved', 'Program \'Strengthening Forest Enforcement Through Advancing the Technology and Equipments\' (ID: 194) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:56:31'),
(278, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 318, File: Forestry_Q2-2025_20250618025638.pptx, Size: 148,278 bytes)', '27.125.242.114', 'success', '2025-06-18 10:56:38'),
(279, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618025638.pptx (Size: 148278 bytes)', '27.125.242.114', 'success', '2025-06-18 10:56:40'),
(280, 36, 'program_draft_saved', 'Program \'Research and Development for KURSI PUSAKA in UNIMAS\' (ID: 182) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:58:04'),
(281, 41, 'outcome_updated', 'Updated outcome \'TOTAL DEGRADED AREA\' (Metric ID: 8) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-18 10:58:20'),
(282, 41, 'program_draft_saved', 'Program \'Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak\' (ID: 187) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:58:20'),
(283, 36, 'program_submitted', 'Program successfully submitted (Program ID: 182, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 10:58:28'),
(284, 36, 'program_submit_no_prior_submission', 'Program submission failed - no prior submission or draft found to validate content (Program ID: 197, Period ID: 2)', '210.186.48.30', 'failure', '2025-06-18 10:58:33'),
(285, 39, 'outcome_created', 'Created outcome \'TPA Bako\' (Metric ID: 11) for sector 1', '210.186.48.30', 'success', '2025-06-18 10:58:42'),
(286, 36, 'program_draft_saved', 'Program \'Implementation of Sarawak Young Designers (SayD’signers Sarawak) programme\' (ID: 197) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:58:59'),
(287, 36, 'program_submitted', 'Program successfully submitted (Program ID: 197, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 10:59:03'),
(288, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 319, File: Forestry_Q2-2025_20250618025905.pptx, Size: 147,995 bytes)', '27.125.242.114', 'success', '2025-06-18 10:59:05'),
(289, 36, 'program_submitted', 'Program successfully submitted (Program ID: 196, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 10:59:07'),
(290, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618025905.pptx (Size: 147995 bytes)', '27.125.242.114', 'success', '2025-06-18 10:59:08'),
(291, 41, 'outcome_submitted', 'Outcome \'TOTAL DEGRADED AREA\' (Metric ID: 8) submitted for sector 1', '210.186.48.30', 'success', '2025-06-18 10:59:35'),
(292, 41, 'program_draft_saved', 'Program \'Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak\' (ID: 187) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 10:59:37'),
(293, 41, 'program_submitted', 'Program successfully submitted (Program ID: 194, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 11:00:10'),
(294, 41, 'outcome_updated', 'Updated outcome \'Sarawak Delta Geopark (SDGp)\' (Metric ID: 10) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-18 11:00:10'),
(295, 41, 'program_draft_saved', 'Program \'The Conservation and Preservation of Geosites, Biosites and Cultural Sites within Sarawak Delta Geopark\' (ID: 184) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:01:09'),
(296, 1, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:01:18'),
(297, 12, 'login_success', 'Username: user', '27.125.242.114', 'success', '2025-06-18 11:01:49'),
(298, 12, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:02:04'),
(299, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 11:02:13'),
(300, 1, 'outcome_status_change', 'Successfully unsubmitted (set to draft) outcome for metric ID: 8', '27.125.242.114', 'success', '2025-06-18 11:02:23'),
(301, 1, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:02:26'),
(302, 12, 'login_success', 'Username: user', '27.125.242.114', 'success', '2025-06-18 11:02:49'),
(303, 39, 'outcome_created', 'Created outcome \'Total Bako Visitor For 2025\' (Metric ID: 12) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-18 11:03:18'),
(304, 35, 'login_success', 'Username: stidc1', '210.186.48.30', 'success', '2025-06-18 11:05:30'),
(305, 39, 'outcome_updated', 'Updated outcome \'Total Bako Visitor For 2025\' (Metric ID: 12) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-18 11:10:15'),
(306, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:10:27'),
(307, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:11:13'),
(308, 39, 'outcome_submitted', 'Outcome \'Total Bako Visitor For 2025\' (Metric ID: 12) submitted for sector 1', '210.186.48.30', 'success', '2025-06-18 11:12:54'),
(309, 35, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 189) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:14:35'),
(310, 41, 'program_submitted', 'Program successfully submitted (Program ID: 187, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 11:14:42'),
(311, 35, 'program_draft_saved', 'Program \'Pusat Latihan Perkayuan PUSAKA Tanjung Manis – Operation and maintenance grant (TMTTC)\' (ID: 183) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:16:18'),
(312, 35, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 189) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:16:22'),
(313, 41, 'program_draft_saved', 'Program \'Obtaining UNESCO recognition for Sarawak Delta Geopark\' (ID: 184) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:16:43'),
(314, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:16:44'),
(315, 35, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 189) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:17:03'),
(316, 37, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 176) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:17:03'),
(317, 12, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:17:05'),
(318, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 11:17:09'),
(319, 41, 'outcome_updated', 'Updated outcome \'TOTAL DEGRADED AREA\' (Metric ID: 8) for sector 1', '210.186.48.30', 'success', '2025-06-18 11:18:09'),
(320, 1, 'login_success', 'Username: admin', '210.186.48.30', 'success', '2025-06-18 11:19:32'),
(321, 41, 'program_draft_saved', 'Program \'Obtaining UNESCO recognition for Sarawak Delta Geopark\' (ID: 184) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:20:36'),
(322, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 320, File: Forestry_Q2-2025_20250618032122.pptx, Size: 184,047 bytes)', '210.186.48.30', 'success', '2025-06-18 11:21:22'),
(323, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618032122.pptx (Size: 184047 bytes)', '210.186.48.30', 'success', '2025-06-18 11:21:27'),
(324, 35, 'program_draft_saved', 'Program \'Furniture Park\' (ID: 189) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:22:10'),
(325, 1, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:24:39'),
(326, 12, 'login_success', 'Username: user', '27.125.242.114', 'success', '2025-06-18 11:24:46'),
(327, 37, 'program_submitted', 'Program successfully submitted (Program ID: 176, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 11:26:40'),
(328, 12, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:26:47'),
(329, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 11:26:53'),
(330, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 321, File: Forestry_Q2-2025_20250618032742.pptx, Size: 180,766 bytes)', '27.125.242.114', 'success', '2025-06-18 11:27:42'),
(331, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618032742.pptx (Size: 180766 bytes)', '27.125.242.114', 'success', '2025-06-18 11:27:47'),
(332, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 322, File: Forestry_Q2-2025_20250618032914.pptx, Size: 160,898 bytes)', '27.125.242.114', 'success', '2025-06-18 11:29:14'),
(333, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618032914.pptx (Size: 160898 bytes)', '27.125.242.114', 'success', '2025-06-18 11:29:19'),
(334, 0, 'program_submit_unauthorized', 'Unauthorized program submission attempt for program ID: ', '210.186.48.30', 'failure', '2025-06-18 11:29:25'),
(335, 0, 'program_submit_unauthorized', 'Unauthorized program submission attempt for program ID: ', '210.186.48.30', 'failure', '2025-06-18 11:29:28'),
(336, 35, 'program_submitted', 'Program successfully submitted (Program ID: 183, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 11:29:29'),
(337, 0, 'program_submit_unauthorized', 'Unauthorized program submission attempt for program ID: ', '210.186.48.30', 'failure', '2025-06-18 11:29:36'),
(338, 39, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-18 11:29:45'),
(339, 41, 'program_draft_saved', 'Program \'Proposed Implementation of Forest Landscape Restoration Throughout Sarawak\' (ID: 193) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:29:51'),
(340, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 11:29:57'),
(341, 43, 'login_success', 'Username: fds3', '210.186.48.30', 'success', '2025-06-18 11:30:02'),
(342, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 11:30:06'),
(343, 41, 'program_submitted', 'Program successfully submitted (Program ID: 193, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 11:30:10'),
(344, 35, 'program_submitted', 'Program successfully submitted (Program ID: 189, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 11:30:35'),
(345, 43, 'program_draft_saved', 'Program \'Quantifying Forest Carbon Stock in Sarawak\' (ID: 195) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:30:54'),
(346, 43, 'program_submitted', 'Program successfully submitted (Program ID: 195, Period ID: 2)', '210.186.48.30', 'success', '2025-06-18 11:30:58'),
(347, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 11:31:00'),
(348, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 11:31:21'),
(349, 1, 'login_success', 'Username: admin', '210.186.48.30', 'success', '2025-06-18 11:31:32'),
(350, 1, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:31:45'),
(351, 12, 'login_failure', 'Username: user | Reason: Invalid password', '27.125.242.114', 'failure', '2025-06-18 11:31:56'),
(352, 12, 'login_success', 'Username: user', '27.125.242.114', 'success', '2025-06-18 11:31:59'),
(353, 41, 'program_draft_saved', 'Program \'Obtaining UNESCO recognition for Sarawak Delta Geopark\' (ID: 184) draft saved for period 2', '210.186.48.30', 'success', '2025-06-18 11:33:07'),
(354, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 323, File: Forestry_Q2-2025_20250618033401.pptx, Size: 148,382 bytes)', '210.186.48.30', 'success', '2025-06-18 11:34:01'),
(355, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618033401.pptx (Size: 148382 bytes)', '210.186.48.30', 'success', '2025-06-18 11:34:30'),
(356, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618033401.pptx (Size: 148382 bytes)', '210.186.48.30', 'success', '2025-06-18 11:34:31'),
(357, 12, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:34:36'),
(358, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 11:34:41'),
(359, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 324, File: Forestry_Q2-2025_20250618033606.pptx, Size: 198,951 bytes)', '27.125.242.114', 'success', '2025-06-18 11:36:06'),
(360, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618033606.pptx (Size: 198951 bytes)', '27.125.242.114', 'success', '2025-06-18 11:36:10'),
(361, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 325, File: Forestry_Q2-2025_20250618033837.pptx, Size: 194,024 bytes)', '27.125.242.114', 'success', '2025-06-18 11:38:37'),
(362, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618033837.pptx (Size: 194024 bytes)', '27.125.242.114', 'success', '2025-06-18 11:38:39'),
(363, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 326, File: Forestry_Q2-2025_20250618033903.pptx, Size: 194,024 bytes)', '27.125.242.114', 'success', '2025-06-18 11:39:03'),
(364, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618033903.pptx (Size: 194024 bytes)', '27.125.242.114', 'success', '2025-06-18 11:39:05');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `status`, `created_at`) VALUES
(365, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 327, File: Forestry_Q2-2025_20250618033933.pptx, Size: 164,905 bytes)', '27.125.242.114', 'success', '2025-06-18 11:39:33'),
(366, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618033933.pptx (Size: 164905 bytes)', '27.125.242.114', 'success', '2025-06-18 11:39:37'),
(367, 1, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:39:56'),
(368, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 11:40:04'),
(369, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 328, File: Forestry_Q2-2025_20250618034028.pptx, Size: 164,905 bytes)', '27.125.242.114', 'success', '2025-06-18 11:40:28'),
(370, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618034028.pptx (Size: 164905 bytes)', '27.125.242.114', 'success', '2025-06-18 11:40:30'),
(371, 1, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-18 11:41:20'),
(372, 1, 'logout', 'User logged out', '27.125.242.114', 'success', '2025-06-18 11:41:43'),
(373, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '27.125.242.114', 'failure', '2025-06-18 11:41:52'),
(374, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '27.125.242.114', 'failure', '2025-06-18 11:41:56'),
(375, 1, 'login_success', 'Username: admin', '27.125.242.114', 'success', '2025-06-18 11:42:02'),
(376, 1, 'save_report', 'Successfully saved Report: \'Forestry Report - Q2-2025\' for Forestry - Q2 2025 (ID: 329, File: Forestry_Q2-2025_20250618034231.pptx, Size: 164,905 bytes)', '27.125.242.114', 'success', '2025-06-18 11:42:31'),
(377, 1, 'file_download', 'File downloaded: Forestry_Q2-2025_20250618034231.pptx (Size: 164905 bytes)', '27.125.242.114', 'success', '2025-06-18 11:42:34'),
(378, 1, 'logout', 'User logged out', '27.125.242.232', 'success', '2025-06-18 11:42:53'),
(379, 12, 'login_failure', 'Username: user | Reason: Invalid password', '27.125.242.232', 'failure', '2025-06-18 11:43:00'),
(380, 12, 'login_success', 'Username: user', '27.125.242.232', 'success', '2025-06-18 11:43:07'),
(381, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 11:45:30'),
(382, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-18 11:51:06'),
(383, 1, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-18 11:51:45'),
(384, 39, 'login_failure', 'Username: sfc2 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 11:56:14'),
(385, 39, 'login_success', 'Username: sfc2', '210.186.48.30', 'success', '2025-06-18 11:56:25'),
(386, 37, 'login_success', 'Username: stidc3', '58.26.203.187', 'success', '2025-06-18 11:56:45'),
(387, 35, 'login_failure', 'Username: stidc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-18 11:57:40'),
(388, 35, 'login_success', 'Username: stidc1', '210.186.48.30', 'success', '2025-06-18 11:57:51'),
(389, 37, 'logout', 'User logged out', '58.26.203.187', 'success', '2025-06-18 11:58:51'),
(390, 35, 'delete_program', 'Program Name: RESEARCH AND DEVELOPMENT FOR KURSI PUSAKA UNIMAS | Program ID: 186', '210.186.48.30', 'success', '2025-06-18 11:59:08'),
(391, 35, 'login_success', 'Username: stidc1', '210.186.48.30', 'success', '2025-06-18 11:59:53'),
(392, 12, 'logout', 'User logged out', '27.125.242.232', 'success', '2025-06-18 12:00:50'),
(393, 41, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-18 12:00:51'),
(394, 41, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-18 12:00:51'),
(395, 12, 'login_success', 'Username: user', '27.125.242.232', 'success', '2025-06-18 12:03:25'),
(396, 12, 'logout', 'User logged out', '27.125.242.232', 'success', '2025-06-18 12:06:04'),
(397, 41, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-18 12:09:04'),
(398, 37, 'login_success', 'Username: stidc3', '58.26.203.187', 'success', '2025-06-18 14:38:16'),
(399, 37, 'login_success', 'Username: stidc3', '58.26.203.187', 'success', '2025-06-18 16:27:42'),
(400, 37, 'logout', 'User logged out', '58.26.203.187', 'success', '2025-06-18 16:28:27'),
(401, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 08:56:50'),
(402, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 08:56:56'),
(403, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 08:57:00'),
(404, 1, 'login_success', 'Username: admin', '210.186.48.30', 'success', '2025-06-19 08:57:06'),
(405, 1, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-19 08:57:35'),
(406, 12, 'login_failure', 'Username: user | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 08:57:40'),
(407, 12, 'login_success', 'Username: user', '210.186.48.30', 'success', '2025-06-19 08:57:46'),
(408, 12, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-19 08:59:06'),
(409, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 09:07:12'),
(410, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 09:07:33'),
(411, 38, 'login_failure', 'Username: sfc1 | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 09:08:01'),
(412, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 09:08:10'),
(413, 41, 'login_success', 'Username: fds1', '210.186.48.30', 'success', '2025-06-19 09:08:15'),
(414, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 09:09:33'),
(415, 0, 'login_failure', 'Username: sfcuser1 | Reason: User not found', '210.186.48.30', 'failure', '2025-06-19 09:09:45'),
(416, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 09:10:32'),
(417, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 09:10:51'),
(418, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 09:10:52'),
(419, 0, 'login_failure', 'Username: sfc | Reason: User not found', '115.164.78.255', 'failure', '2025-06-19 09:10:53'),
(420, 38, 'login_success', 'Username: sfc1', '115.164.78.255', 'success', '2025-06-19 09:11:08'),
(421, 12, 'login_success', 'Username: user', '210.186.48.30', 'success', '2025-06-19 09:16:54'),
(422, 38, 'create_program', 'Program Name: EFT1 | Program ID: 198', '115.164.78.255', 'success', '2025-06-19 09:18:34'),
(423, 38, 'create_program', 'Program Name: Conduct Periodical | Program ID: 199', '210.186.48.30', 'success', '2025-06-19 09:18:36'),
(424, 38, 'create_program', 'Program Name: System Tagang | Program ID: 200', '210.186.48.30', 'success', '2025-06-19 09:18:40'),
(425, 38, 'create_program', 'Program Name: Identify potential TPA to be managed by Managing Agent | Program ID: 201', '210.186.48.30', 'success', '2025-06-19 09:18:40'),
(426, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 09:22:03'),
(427, 38, 'create_program', 'Program Name: Achieving state terget of 1 million ha of well- | Program ID: 202', '210.186.48.30', 'success', '2025-06-19 09:22:36'),
(428, 38, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-19 09:23:03'),
(429, 38, 'update_program', 'Program Name: Identify potential TPA to be managed by Managing Agent | Program ID: 201', '210.186.48.30', 'success', '2025-06-19 09:27:41'),
(430, 38, 'create_program', 'Program Name: Establishment of Rainforest/Nature Discovery Centre (SRDC NR and PNDC) | Program ID: 203', '210.186.48.30', 'success', '2025-06-19 09:33:55'),
(431, 12, 'create_program', 'Program Name: Bamboo Industry Development | Program ID: 204', '210.186.48.30', 'success', '2025-06-19 09:37:48'),
(432, 38, 'create_program', 'Program Name: Developement | Program ID: 205', '210.186.48.30', 'success', '2025-06-19 09:42:04'),
(433, 38, 'program_draft_saved', 'Program \'Identify potential TPA to be managed by Managing Agent\' (ID: 201) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:42:43'),
(434, 38, 'update_program', 'Program Name: 3. 5 . System Tagang Mulu NP | Program ID: 200', '210.186.48.30', 'success', '2025-06-19 09:44:00'),
(435, 38, 'update_program', 'Program Name: Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks | Program ID: 199', '210.186.48.30', 'success', '2025-06-19 09:44:04'),
(436, 12, 'update_program', 'Program Name: Bamboo Industry Development | Program ID: 204', '210.186.48.30', 'success', '2025-06-19 09:44:11'),
(437, 1, 'login_success', 'Username: admin', '210.186.48.30', 'success', '2025-06-19 09:45:05'),
(438, 38, 'program_submit_no_prior_submission', 'Program submission failed - no prior submission or draft found to validate content (Program ID: 200, Period ID: 2)', '210.186.48.30', 'failure', '2025-06-19 09:45:10'),
(439, 1, 'login_success', 'Username: admin', '210.186.48.30', 'success', '2025-06-19 09:45:14'),
(440, 12, 'program_draft_saved', 'Program \'Bamboo Industry Development\' (ID: 204) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:45:25'),
(441, 12, 'program_submitted', 'Program successfully submitted (Program ID: 204, Period ID: 2)', '210.186.48.30', 'success', '2025-06-19 09:45:41'),
(442, 38, 'program_draft_saved', 'Program \'3. 5 . System Tagang Mulu NP\' (ID: 200) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:46:53'),
(443, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 09:47:14'),
(444, 38, 'program_draft_saved', 'Program \'3. 5 . System Tagang Mulu NP\' (ID: 200) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:48:35'),
(445, 38, 'update_program', 'Program Name: Establishment of Rainforest/Nature Discovery Centre (SRDC NR and PNDC) | Program ID: 203', '210.186.48.30', 'success', '2025-06-19 09:48:45'),
(446, 38, 'program_draft_saved', 'Program \'To certify 20 sites under IUCN Green List of Protected and Conserved Areas\' (ID: 202) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:50:16'),
(447, 41, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-19 09:50:27'),
(448, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 09:51:28'),
(449, 38, 'program_draft_saved', 'Program \'Establishment of Rainforest/Nature Discovery Centre (SRDC NR and PNDC)\' (ID: 203) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:52:08'),
(450, 38, 'program_draft_saved', 'Program \'Identify potential TPA to be managed by Managing Agent\' (ID: 201) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:52:10'),
(451, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 09:53:16'),
(452, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 09:53:37'),
(453, 38, 'program_draft_saved', 'Program \'3. 5 . System Tagang\' (ID: 200) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:53:52'),
(454, 38, 'program_draft_saved', 'Program \'To certify 20 sites under IUCN Green List of Protected and Conserved Areas\' (ID: 202) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:57:41'),
(455, 38, 'create_program_failed', 'Program Name: Achieve world class recognition for biodiversity conservation & protected areas management (Niah NP as Unesco World Heritage Site, Bako NP and Lambir Hills NP as ASEAN Heritage Parks, and Kuala Lawas/Kuala Trusan and Limbang Mangrove National Park as East Asia-Australasian Flyway Network Sites) | Error: Data too long for column \'program_name\' at row 1', '210.186.48.30', 'failure', '2025-06-19 09:57:58'),
(456, 38, 'create_program', 'Program Name: Achieve world class recognition for biodiversity conservation & protected areas management (Niah NP as Unesco World Heritage Site) | Program ID: 206', '210.186.48.30', 'success', '2025-06-19 09:58:16'),
(457, 38, 'program_draft_saved', 'Program \'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\' (ID: 199) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:58:25'),
(458, 38, 'program_draft_saved', 'Program \'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\' (ID: 199) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 09:58:54'),
(459, 38, 'program_draft_saved', 'Program \'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\' (ID: 199) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 10:00:38'),
(460, 38, 'program_draft_saved', 'Program \'To certify 20 sites under IUCN Green List of Protected and Conserved Areas\' (ID: 202) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 10:04:07'),
(461, 38, 'update_program', 'Program Name: Achieve world class recognition for biodiversity conservation & protected areas management (Niah NP as Unesco World Heritage Site) | Program ID: 206', '210.186.48.30', 'success', '2025-06-19 10:05:22'),
(462, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 10:15:24'),
(463, 38, 'program_draft_saved', 'Program \'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\' (ID: 199) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 10:18:13'),
(464, 38, 'create_program', 'Program Name: To develop | Program ID: 207', '210.186.48.30', 'success', '2025-06-19 10:18:56'),
(465, 0, 'login_failure', 'Username: sfc123 | Reason: User not found', '210.186.48.30', 'failure', '2025-06-19 10:22:10'),
(466, 0, 'login_failure', 'Username: sfc123 | Reason: User not found', '210.186.48.30', 'failure', '2025-06-19 10:23:17'),
(467, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 10:23:29'),
(468, 38, 'update_program', 'Program Name: To develop and upgrade facilities at selected manned and unmanned TPAs. | Program ID: 207', '210.186.48.30', 'success', '2025-06-19 10:23:54'),
(469, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 10:27:00'),
(470, 37, 'login_success', 'Username: stidc3', '58.26.203.187', 'success', '2025-06-19 10:27:47'),
(471, 38, 'create_program', 'Program Name: Implementation of community engagement programs by engaging community participation in Biodiversity Conservation Projects (Landscape Rehabilitation Programs) | Program ID: 208', '210.186.48.30', 'success', '2025-06-19 10:27:51'),
(472, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 10:28:31'),
(473, 1, 'login_success', 'Username: admin', '210.186.48.30', 'success', '2025-06-19 10:28:37'),
(474, 37, 'logout', 'User logged out', '58.26.203.187', 'success', '2025-06-19 10:28:50'),
(475, 36, 'login_success', 'Username: stidc2', '58.26.203.187', 'success', '2025-06-19 10:28:56'),
(476, 36, 'logout', 'User logged out', '58.26.203.187', 'success', '2025-06-19 10:34:27'),
(477, 37, 'login_success', 'Username: stidc3', '58.26.203.187', 'success', '2025-06-19 10:34:30'),
(478, 1, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-19 10:37:38'),
(479, 12, 'login_success', 'Username: user', '210.186.48.30', 'success', '2025-06-19 10:37:44'),
(480, 38, 'program_draft_saved', 'Program \'Achieve world class recognition for biodiversity conservation & protected areas management (Niah NP as Unesco World Heritage Site)\' (ID: 206) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 10:37:54'),
(481, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 10:40:04'),
(482, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 10:43:10'),
(483, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 10:43:10'),
(484, 38, 'program_draft_saved', 'Program \'3. 5 . System Tagang\' (ID: 200) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 10:43:14'),
(485, 1, 'login_success', 'Username: admin', '210.186.48.30', 'success', '2025-06-19 10:43:15'),
(486, 38, 'create_program', 'Program Name: 3.5 | Program ID: 209', '210.186.48.30', 'success', '2025-06-19 10:43:43'),
(487, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 10:44:24'),
(488, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 10:46:07'),
(489, 38, 'program_draft_saved', 'Program \'To develop and upgrade facilities at selected manned and unmanned TPAs.\' (ID: 207) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 10:48:25'),
(490, 38, 'update_program', 'Program Name: 3.5 Landscape Rehabilitation Programs | Program ID: 209', '210.186.48.30', 'success', '2025-06-19 10:48:35'),
(491, 38, 'program_draft_saved', 'Program \'To certify 20 sites under IUCN Green List of Protected and Conserved Areas\' (ID: 202) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 10:49:41'),
(492, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 10:50:15'),
(493, 38, 'create_program', 'Program Name: Turtle Consercation Project | Program ID: 210', '210.186.48.30', 'success', '2025-06-19 10:50:54'),
(494, 38, 'program_draft_saved', 'Program \'To develop and upgrade facilities at selected manned and unmanned TPAs.\' (ID: 207) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 10:51:50'),
(495, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 10:51:58'),
(496, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 10:52:06'),
(497, 38, 'update_program', 'Program Name: Turtle Consercation Project | Program ID: 210', '210.186.48.30', 'success', '2025-06-19 10:53:33'),
(498, 38, 'create_program', 'Program Name: Implementation of community engagement programs by engaging community participation in Bodiversity Conservation Projects | Program ID: 211', '210.186.48.30', 'success', '2025-06-19 10:54:04'),
(499, 38, 'update_program', 'Program Name: Turtle Conservation Project | Program ID: 211', '210.186.48.30', 'success', '2025-06-19 10:57:04'),
(500, 38, 'create_program', 'Program Name: Feed the Wildlife Program | Program ID: 212', '210.186.48.30', 'success', '2025-06-19 10:57:37'),
(501, 12, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-19 11:00:08'),
(502, 1, 'login_failure', 'Username: ADMIN | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 11:00:14'),
(503, 1, 'login_success', 'Username: admin', '210.186.48.30', 'success', '2025-06-19 11:00:21'),
(504, 38, 'update_program', 'Program Name: Feed the Wildlife Program | Program ID: 212', '210.186.48.30', 'success', '2025-06-19 11:01:10'),
(505, 1, 'audit_log_access', 'Successfully accessed audit logs (Page 1, 25 per page, 504 total records). Filters: No filters applied', '210.186.48.30', 'success', '2025-06-19 11:01:35'),
(506, 38, 'program_draft_saved', 'Program \'3.5 Feed the Wildlife Program\' (ID: 212) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:01:40'),
(507, 1, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-19 11:02:10'),
(508, 38, 'program_draft_saved', 'Program \'3.5 Feed the Wildlife Program\' (ID: 212) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:02:14'),
(509, 12, 'login_success', 'Username: user', '210.186.48.30', 'success', '2025-06-19 11:02:16'),
(510, 38, 'program_draft_saved', 'Program \'3.5 Landscape Rehabilitation Programs\' (ID: 209) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:02:37'),
(511, 38, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-19 11:02:53'),
(512, 39, 'login_success', 'Username: sfc2', '210.186.48.30', 'success', '2025-06-19 11:03:08'),
(513, 38, 'create_program', 'Program Name: \"Non-Timber Forest Product (NTFP) BRO- project proposal submitted and approved.\" | Program ID: 213', '210.186.48.30', 'success', '2025-06-19 11:03:24'),
(514, 38, 'program_draft_saved', 'Program \'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\' (ID: 199) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:03:53'),
(515, 39, 'logout', 'User logged out', '210.186.48.30', 'success', '2025-06-19 11:03:55'),
(516, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 11:04:12'),
(517, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 11:04:24'),
(518, 1, 'login_failure', 'Username: admin | Reason: Invalid password', '210.186.48.30', 'failure', '2025-06-19 11:04:55'),
(519, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 11:05:23'),
(520, 38, 'update_program', 'Program Name: 3.5 Non-Timber Forest Product (NTFP)/Beyond Timber Carnival Program | Program ID: 213', '210.186.48.30', 'success', '2025-06-19 11:06:12'),
(521, 38, 'create_program', 'Program Name: \" Logistic services 1. Tanjung Datu - Project proposal submitted and approved 2.  Miri Sibuti Coral Reef NP- Commence FPIC and Socio economic Survey Phase II\" | Program ID: 214', '210.186.48.30', 'success', '2025-06-19 11:06:48'),
(522, 38, 'update_program', 'Program Name: Logistic services | Program ID: 214', '210.186.48.30', 'success', '2025-06-19 11:11:50'),
(523, 38, 'program_draft_saved', 'Program \'3.5 Logistic services\' (ID: 214) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:12:36'),
(524, 38, 'program_draft_saved', 'Program \'To certify 20 sites under IUCN Green List of Protected and Conserved Areas\' (ID: 202) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:12:44'),
(525, 38, 'create_program', 'Program Name: Totally Protected Areas | Program ID: 215', '210.186.48.30', 'success', '2025-06-19 11:13:18'),
(526, 38, 'update_program', 'Program Name: Totally Protected Areas Services | Program ID: 215', '210.186.48.30', 'success', '2025-06-19 11:16:43'),
(527, 38, 'program_submitted', 'Program successfully submitted (Program ID: 200, Period ID: 2)', '210.186.48.30', 'success', '2025-06-19 11:16:49'),
(528, 38, 'program_draft_saved', 'Program \'3.5 Totally Protected Areas Services\' (ID: 215) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:17:54'),
(529, 38, 'delete_program', 'Program Name: Landscape Rehabilitation Programs | Program ID: 208', '210.186.48.30', 'success', '2025-06-19 11:18:26'),
(530, 38, 'program_submitted', 'Program successfully submitted (Program ID: 207, Period ID: 2)', '210.186.48.30', 'success', '2025-06-19 11:18:58'),
(531, 38, 'program_draft_saved', 'Program \'Turtle Consercation Project\' (ID: 210) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:19:07'),
(532, 38, 'delete_program', 'Program Name: Turtle Consercation Project | Program ID: 210', '210.186.48.30', 'success', '2025-06-19 11:19:15'),
(533, 38, 'program_draft_saved', 'Program \'3.5 Turtle Conservation Project\' (ID: 211) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:21:20'),
(534, 38, 'program_draft_saved', 'Program \'3.5 Non-Timber Forest Product (NTFP)/Beyond Timber Carnival Program\' (ID: 213) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:21:49'),
(535, 38, 'login_success', 'Username: sfc1', '115.164.78.255', 'success', '2025-06-19 11:23:42'),
(536, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 11:24:25'),
(537, 38, 'outcome_created', 'Created outcome \'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\' (Metric ID: 13) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-19 11:24:34'),
(538, 38, 'outcome_updated', 'Updated outcome \'Repair and Maintenance of the Workshop\' (Metric ID: 9) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-19 11:24:50'),
(539, 38, 'outcome_updated', 'Updated outcome \'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\' (Metric ID: 13) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-19 11:25:02'),
(540, 38, 'outcome_created', 'Created outcome \'20 TPAs certify under  IUCN Green List of Protected and Conserved Areas\' (Metric ID: 14) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-19 11:31:59'),
(541, 38, 'outcome_updated', 'Updated outcome \'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\' (Metric ID: 13) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-19 11:32:00'),
(542, 38, 'outcome_updated', 'Updated outcome \'20 TPAs certified under  IUCN Green List of Protected and Conserved Areas\' (Metric ID: 14) for sector 1 as draft', '210.186.48.30', 'success', '2025-06-19 11:33:01'),
(543, 38, 'program_draft_saved', 'Program \'Integrated wildlife conservation and management in Sarawak\' (ID: 198) draft saved for period 2', '115.164.78.255', 'success', '2025-06-19 11:41:49'),
(544, 38, 'program_draft_saved', 'Program \'3.5 Feed the Wildlife Program\' (ID: 212) draft saved for period 2', '210.186.48.30', 'success', '2025-06-19 11:42:38'),
(545, 38, 'create_program', 'Program Name: Identify services / work / program for community participation in managing and protecting TPA | Program ID: 216', '210.186.48.30', 'success', '2025-06-19 11:45:17'),
(546, 38, 'login_success', 'Username: sfc1', '210.186.48.30', 'success', '2025-06-19 11:46:57');

-- --------------------------------------------------------

--
-- Table structure for table `metrics_details`
--

CREATE TABLE `metrics_details` (
  `detail_id` int(11) NOT NULL,
  `detail_name` varchar(255) NOT NULL,
  `detail_json` longtext NOT NULL,
  `is_draft` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `read_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `outcomes_details`
--

CREATE TABLE `outcomes_details` (
  `detail_id` int(11) NOT NULL,
  `detail_name` varchar(255) NOT NULL,
  `detail_json` longtext NOT NULL,
  `is_draft` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `outcomes_details`
--

INSERT INTO `outcomes_details` (`detail_id`, `detail_name`, `detail_json`, `is_draft`, `created_at`, `updated_at`) VALUES
(19, 'TPA Protection & Biodiversity Conservation Programs (incl. community-based initiatives', '{\r\n  \"layout_type\": \"simple\",\r\n  \"items\": [\r\n    {\r\n      \"value\": \"32\",\r\n      \"description\": \"On-going programs and initiatives by SFC (as of Sept 2024)\"\r\n    }\r\n  ]\r\n}', 0, '2025-05-07 19:33:42', '2025-05-14 02:13:32'),
(21, 'Certification of FMU & FPMU', '{\"layout_type\":\"simple\",\"items\":[{\"value\":\"56.7%\",\"description\":\"1,703,164 ha Certified (May 2025)\"},{\"value\":\"71.5%\",\"description\":\"127,311 ha Certified (May 2025)\"}]}', 0, '2025-05-07 19:40:32', '2025-06-18 02:19:29'),
(39, 'Obtain world recognition for sustainable management practices and conservation effort', '{\"layout_type\": \"comparison\", \"items\": [{\"label\": \"SDGP UNESCO Global Geopark\", \"value\": \"50%\", \"description\": \"(as of Sept 2024)\"}, {\"label\": \"Niah NP UNESCO World Heritage Site\", \"value\": \"100%\", \"description\": \"(as of Sept 2024)\"}]}', 0, '2025-05-08 16:59:53', '2025-05-14 02:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `outcome_history`
--

CREATE TABLE `outcome_history` (
  `history_id` int(11) NOT NULL,
  `outcome_record_id` int(11) NOT NULL,
  `metric_id` int(11) NOT NULL,
  `data_json` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `outcome_history`
--

INSERT INTO `outcome_history` (`history_id`, `outcome_record_id`, `metric_id`, `data_json`, `action_type`, `status`, `changed_by`, `change_description`, `created_at`) VALUES
(1, 21, 8, '{\r\n  \"columns\": [\"2022\", \"2023\", \"2024\", \"2025\", \"2026\"],\r\n  \"units\": {\r\n    \"2022\": \"Ha\",\r\n    \"2023\": \"Ha\",\r\n    \"2024\": \"Ha\",\r\n    \"2025\": \"Ha\"\r\n  },\r\n  \"data\": {\r\n    \"January\": {\r\n      \"2022\": 787.01,\r\n      \"2023\": 1856.37,\r\n      \"2024\": 3146.60,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"February\": {\r\n      \"2022\": 912.41,\r\n      \"2023\": 3449.94,\r\n      \"2024\": 6660.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"March\": {\r\n      \"2022\": 513.04,\r\n      \"2023\": 2284.69,\r\n      \"2024\": 3203.80,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"April\": {\r\n      \"2022\": 428.18,\r\n      \"2023\": 1807.69,\r\n      \"2024\": 1871.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"May\": {\r\n      \"2022\": 485.08,\r\n      \"2023\": 3255.80,\r\n      \"2024\": 2750.20,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"June\": {\r\n      \"2022\": 1277.90,\r\n      \"2023\": 3120.66,\r\n      \"2024\": 3396.30,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"July\": {\r\n      \"2022\": 745.15,\r\n      \"2023\": 2562.38,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"August\": {\r\n      \"2022\": 762.69,\r\n      \"2023\": 2474.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"September\": {\r\n      \"2022\": 579.09,\r\n      \"2023\": 3251.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"October\": {\r\n      \"2022\": 676.27,\r\n      \"2023\": 3086.64,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"November\": {\r\n      \"2022\": 2012.35,\r\n      \"2023\": 3081.63,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"December\": {\r\n      \"2022\": 1114.64,\r\n      \"2023\": 3240.14,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    }\r\n  }\r\n}', 'resubmit', 'submitted', 1, 'Outcome resubmitted by admin', '2025-06-04 06:11:05'),
(2, 20, 7, '{\"columns\":[\"2022\",\"2023\",\"2024\",\"2025\",\"2026\"],\"units\":{\"2022\":\"RM\",\"2023\":\"RM\",\"2024\":\"RM\",\"2025\":\"RM\"},\"data\":{\"January\":{\"2022\":408531176.77,\"2023\":263569916.63,\"2024\":276004972.69,\"2025\":null,\"2026\":0},\"February\":{\"2022\":239761718.38,\"2023\":226356164.3,\"2024\":191530929.47,\"2025\":null,\"2026\":0},\"March\":{\"2022\":394935606.46,\"2023\":261778295.29,\"2024\":214907671.7,\"2025\":null,\"2026\":0},\"April\":{\"2022\":400891037.27,\"2023\":215771835.07,\"2024\":232014272.14,\"2025\":null,\"2026\":0},\"May\":{\"2022\":345725679.36,\"2023\":324280067.64,\"2024\":324627750.87,\"2025\":null,\"2026\":0},\"June\":{\"2022\":268966198.26,\"2023\":235560482.89,\"2024\":212303812.34,\"2025\":null,\"2026\":0},\"July\":{\"2022\":359792973.34,\"2023\":244689028.37,\"2024\":274788036.68,\"2025\":null,\"2026\":0},\"August\":{\"2022\":310830376.16,\"2023\":344761866.36,\"2024\":210420404.31,\"2025\":null,\"2026\":0},\"September\":{\"2022\":318990291.52,\"2023\":210214202.2,\"2024\":191837139,\"2025\":null,\"2026\":0},\"October\":{\"2022\":304693148.3,\"2023\":266639022.25,\"2024\":null,\"2025\":null,\"2026\":0},\"November\":{\"2022\":303936172.09,\"2023\":296062485.55,\"2024\":null,\"2025\":null,\"2026\":0},\"December\":{\"2022\":289911760.38,\"2023\":251155864.77,\"2024\":null,\"2025\":null,\"2026\":0}}}', 'resubmit', 'submitted', 1, 'Outcome resubmitted by admin', '2025-06-04 06:11:08'),
(3, 21, 8, '{\r\n  \"columns\": [\"2022\", \"2023\", \"2024\", \"2025\", \"2026\"],\r\n  \"units\": {\r\n    \"2022\": \"Ha\",\r\n    \"2023\": \"Ha\",\r\n    \"2024\": \"Ha\",\r\n    \"2025\": \"Ha\"\r\n  },\r\n  \"data\": {\r\n    \"January\": {\r\n      \"2022\": 787.01,\r\n      \"2023\": 1856.37,\r\n      \"2024\": 3146.60,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"February\": {\r\n      \"2022\": 912.41,\r\n      \"2023\": 3449.94,\r\n      \"2024\": 6660.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"March\": {\r\n      \"2022\": 513.04,\r\n      \"2023\": 2284.69,\r\n      \"2024\": 3203.80,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"April\": {\r\n      \"2022\": 428.18,\r\n      \"2023\": 1807.69,\r\n      \"2024\": 1871.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"May\": {\r\n      \"2022\": 485.08,\r\n      \"2023\": 3255.80,\r\n      \"2024\": 2750.20,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"June\": {\r\n      \"2022\": 1277.90,\r\n      \"2023\": 3120.66,\r\n      \"2024\": 3396.30,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"July\": {\r\n      \"2022\": 745.15,\r\n      \"2023\": 2562.38,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"August\": {\r\n      \"2022\": 762.69,\r\n      \"2023\": 2474.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"September\": {\r\n      \"2022\": 579.09,\r\n      \"2023\": 3251.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"October\": {\r\n      \"2022\": 676.27,\r\n      \"2023\": 3086.64,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"November\": {\r\n      \"2022\": 2012.35,\r\n      \"2023\": 3081.63,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"December\": {\r\n      \"2022\": 1114.64,\r\n      \"2023\": 3240.14,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    }\r\n  }\r\n}', 'submit', 'submitted', 1, 'Outcome submitted by admin', '2025-06-04 06:14:53'),
(4, 22, 9, '{\"columns\":[],\"data\":{\"January\":[],\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 'unsubmit', 'draft', 1, 'Outcome unsubmitted by admin', '2025-06-18 01:59:49'),
(5, 21, 8, '{\r\n  \"columns\": [\"2022\", \"2023\", \"2024\", \"2025\", \"2026\"],\r\n  \"units\": {\r\n    \"2022\": \"Ha\",\r\n    \"2023\": \"Ha\",\r\n    \"2024\": \"Ha\",\r\n    \"2025\": \"Ha\"\r\n  },\r\n  \"data\": {\r\n    \"January\": {\r\n      \"2022\": 787.01,\r\n      \"2023\": 1856.37,\r\n      \"2024\": 3146.60,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"February\": {\r\n      \"2022\": 912.41,\r\n      \"2023\": 3449.94,\r\n      \"2024\": 6660.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"March\": {\r\n      \"2022\": 513.04,\r\n      \"2023\": 2284.69,\r\n      \"2024\": 3203.80,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"April\": {\r\n      \"2022\": 428.18,\r\n      \"2023\": 1807.69,\r\n      \"2024\": 1871.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"May\": {\r\n      \"2022\": 485.08,\r\n      \"2023\": 3255.80,\r\n      \"2024\": 2750.20,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"June\": {\r\n      \"2022\": 1277.90,\r\n      \"2023\": 3120.66,\r\n      \"2024\": 3396.30,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"July\": {\r\n      \"2022\": 745.15,\r\n      \"2023\": 2562.38,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"August\": {\r\n      \"2022\": 762.69,\r\n      \"2023\": 2474.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"September\": {\r\n      \"2022\": 579.09,\r\n      \"2023\": 3251.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"October\": {\r\n      \"2022\": 676.27,\r\n      \"2023\": 3086.64,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"November\": {\r\n      \"2022\": 2012.35,\r\n      \"2023\": 3081.63,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"December\": {\r\n      \"2022\": 1114.64,\r\n      \"2023\": 3240.14,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    }\r\n  }\r\n}', 'unsubmit', 'draft', 1, 'Outcome unsubmitted by admin', '2025-06-18 01:59:56'),
(6, 21, 8, '{\"columns\":[\"2022\",\"2023\",\"2024\",\"2025\",\"2026\"],\"data\":{\"January\":{\"2022\":787.01,\"2023\":1856.37,\"2024\":3572.12,\"2025\":0,\"2026\":0},\"February\":{\"2022\":912.41,\"2023\":3449.94,\"2024\":6911.42,\"2025\":0,\"2026\":0},\"March\":{\"2022\":513.04,\"2023\":2284.69,\"2024\":3565.31,\"2025\":0,\"2026\":0},\"April\":{\"2022\":428.18,\"2023\":1807.69,\"2024\":2243.09,\"2025\":0,\"2026\":0},\"May\":{\"2022\":485.08,\"2023\":3255.8,\"2024\":3190.19,\"2025\":0,\"2026\":0},\"June\":{\"2022\":1277.9,\"2023\":3120.66,\"2024\":3618.48,\"2025\":0,\"2026\":0},\"July\":{\"2022\":745.15,\"2023\":2562.38,\"2024\":1378.09,\"2025\":0,\"2026\":0},\"August\":{\"2022\":762.69,\"2023\":2474.93,\"2024\":1536.83,\"2025\":0,\"2026\":0},\"September\":{\"2022\":579.09,\"2023\":3251.93,\"2024\":1141.79,\"2025\":0,\"2026\":0},\"October\":{\"2022\":676.27,\"2023\":3086.64,\"2024\":1311.2,\"2025\":0,\"2026\":0},\"November\":{\"2022\":2012.35,\"2023\":3081.63,\"2024\":942.5,\"2025\":0,\"2026\":0},\"December\":{\"2022\":1114.64,\"2023\":3240.14,\"2024\":969,\"2025\":0,\"2026\":0}}}', 'unsubmit', 'draft', 1, 'Outcome unsubmitted by admin', '2025-06-18 03:02:23');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_id` int(11) NOT NULL,
  `program_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_agency_id` int(11) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_assigned` tinyint(1) NOT NULL DEFAULT '1',
  `edit_permissions` text COLLATE utf8mb4_unicode_ci,
  `created_by` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`, `owner_agency_id`, `sector_id`, `start_date`, `end_date`, `created_at`, `updated_at`, `is_assigned`, `edit_permissions`, `created_by`) VALUES
(165, 'Forest Conservation Initiative', 12, 1, '2025-01-01', '2025-12-31', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
(166, 'Sustainable Timber Management Program', 12, 1, '2025-02-01', '2026-01-31', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
(167, 'Reforestation and Restoration Project', 12, 1, '2025-01-15', '2027-01-15', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
(168, 'Wildlife Habitat Protection Scheme', 12, 1, '2025-03-01', '2025-11-30', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
(169, 'Forest Research & Development Initiative', 12, 1, '2025-01-01', '2026-12-31', '2025-06-03 10:50:10', '2025-06-03 10:50:10', 1, NULL, 12),
(174, 'Furniture Park', 12, 1, '2025-05-30', '2025-06-06', '2025-06-06 07:10:30', '2025-06-06 07:52:02', 0, NULL, 1),
(176, 'Bamboo Industry Development', 37, 1, '2025-01-01', '2030-12-31', '2025-06-18 01:33:33', '2025-06-18 03:17:03', 0, NULL, 1),
(177, 'Bamboo Industry Developement 2026', 38, 1, '2025-06-18', '2030-06-27', '2025-06-18 01:34:45', '2025-06-18 01:59:00', 0, NULL, 1),
(179, 'Bamboo Industry Development', 12, 1, '2025-06-18', '2028-06-29', '2025-06-18 01:34:54', '2025-06-18 02:00:02', 0, NULL, 1),
(181, 'Niah Unesco', 38, 1, '2025-06-18', '2028-10-17', '2025-06-18 01:35:28', '2025-06-18 01:59:45', 0, NULL, 1),
(182, 'Research and Development for KURSI PUSAKA in UNIMAS', 36, 1, '2025-01-01', '2025-12-31', '2025-06-18 01:35:47', '2025-06-18 02:58:04', 0, NULL, 1),
(183, 'Pusat Latihan Perkayuan PUSAKA Tanjung Manis – Operation and maintenance grant (TMTTC)', 35, 1, '2025-01-01', '2030-12-31', '2025-06-18 01:36:13', '2025-06-18 03:16:18', 0, NULL, 1),
(184, 'Obtaining UNESCO recognition for Sarawak Delta Geopark', 41, 1, '2025-06-18', '2026-12-31', '2025-06-18 01:36:19', '2025-06-18 03:33:07', 0, NULL, 1),
(185, 'Bamboo Industry Development', 41, 1, '2025-06-02', '2025-07-12', '2025-06-18 01:36:24', '2025-06-18 01:58:09', 0, NULL, 1),
(187, 'Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak', 41, 1, '2025-01-01', '2025-12-31', '2025-06-18 01:37:10', '2025-06-18 02:59:37', 0, NULL, 1),
(189, 'Furniture Park', 35, 1, '2025-01-01', '2030-12-31', '2025-06-18 01:37:53', '2025-06-18 03:22:10', 0, NULL, 1),
(192, 'SFC Program 1', 38, 1, '2020-01-01', '2025-01-01', '2025-06-18 01:48:03', '2025-06-18 02:05:54', 0, NULL, 1),
(193, 'Proposed Implementation of Forest Landscape Restoration Throughout Sarawak', 41, 1, '2021-01-01', '2025-12-31', '2025-06-18 01:52:41', '2025-06-18 03:29:51', 0, NULL, 1),
(194, 'Strengthening Forest Enforcement Through Advancing the Technology and Equipments', 41, 1, '2025-01-02', '2024-06-30', '2025-06-18 01:52:50', '2025-06-18 02:56:31', 0, NULL, 1),
(195, 'Quantifying Forest Carbon Stock in Sarawak', 43, 1, '2025-01-01', '2026-12-18', '2025-06-18 02:11:12', '2025-06-18 03:30:54', 0, NULL, 1),
(196, 'Applied R&D to develop commercially viable high value products from planted timber species', 36, 1, '2025-01-01', '2025-12-31', '2025-06-18 02:16:34', '2025-06-18 02:39:25', 0, NULL, 1),
(197, 'Implementation of Sarawak Young Designers (SayD’signers Sarawak) programme', 36, 1, '2025-01-01', '2025-12-31', '2025-06-18 02:41:00', '2025-06-18 02:58:59', 0, NULL, 1),
(198, 'Integrated wildlife conservation and management in Sarawak', 38, 1, '2025-01-19', '2026-02-28', '2025-06-19 01:18:33', '2025-06-19 03:41:49', 0, NULL, 1),
(199, 'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks', 38, 1, '2024-01-01', '2025-12-31', '2025-06-19 01:18:36', '2025-06-19 03:03:53', 0, NULL, 1),
(200, '3. 5 . System Tagang', 38, 1, '2023-12-01', '2028-12-30', '2025-06-19 01:18:40', '2025-06-19 02:43:14', 0, NULL, 1),
(201, 'Identify potential TPA to be managed by Managing Agent', 38, 1, '2025-06-01', '2025-12-31', '2025-06-19 01:18:40', '2025-06-19 01:52:10', 0, NULL, 1),
(202, 'To certify 20 sites under IUCN Green List of Protected and Conserved Areas', 38, 1, '2025-01-07', '2025-12-26', '2025-06-19 01:22:36', '2025-06-19 03:12:44', 0, NULL, 1),
(203, 'Establishment of Rainforest/Nature Discovery Centre (SRDC NR and PNDC)', 38, 1, '2024-01-01', '2030-12-24', '2025-06-19 01:33:55', '2025-06-19 01:52:08', 0, NULL, 1),
(204, 'Bamboo Industry Development', 12, 1, '2025-06-19', '2026-02-12', '2025-06-19 01:37:48', '2025-06-19 01:45:25', 0, NULL, 1),
(205, 'Developement of Boardwalk & One Stop Centre at North Gate of Bukit Lima Nature Reserve.', 38, 1, '2025-04-03', '2025-09-04', '2025-06-19 01:42:04', '2025-06-19 01:52:30', 0, NULL, 1),
(206, 'Achieve world class recognition for biodiversity conservation & protected areas management (Niah NP as Unesco World Heritage Site)', 38, 1, '2025-01-01', '2025-12-31', '2025-06-19 01:58:16', '2025-06-19 02:37:54', 0, NULL, 1),
(207, 'To develop and upgrade facilities at selected manned and unmanned TPAs.', 38, 1, '2025-01-03', '2025-12-01', '2025-06-19 02:18:56', '2025-06-19 02:51:50', 0, NULL, 1),
(209, '3.5 Landscape Rehabilitation Programs', 38, 1, '2021-01-20', '2030-12-30', '2025-06-19 02:43:43', '2025-06-19 03:02:37', 0, NULL, 1),
(211, '3.5 Turtle Conservation Project', 38, 1, '2023-01-01', '2030-12-31', '2025-06-19 02:54:04', '2025-06-19 03:21:20', 0, NULL, 1),
(212, '3.5 Feed the Wildlife Program', 38, 1, '2020-01-01', '2030-12-31', '2025-06-19 02:57:37', '2025-06-19 03:42:38', 0, NULL, 1),
(213, '3.5 Non-Timber Forest Product (NTFP)/Beyond Timber Carnival Program', 38, 1, '2021-01-01', '2030-12-31', '2025-06-19 03:03:24', '2025-06-19 03:21:49', 0, NULL, 1),
(214, '3.5 Logistic services', 38, 1, '2023-02-01', '2030-12-31', '2025-06-19 03:06:48', '2025-06-19 03:12:36', 0, NULL, 1),
(215, '3.5 Totally Protected Areas Services', 38, 1, '2025-01-01', '2025-01-01', '2025-06-19 03:13:18', '2025-06-19 03:17:54', 0, NULL, 1),
(216, '3.2 Management and Strengthening Protection for Selected Totally Protected Areas', 38, 1, NULL, NULL, '2025-06-19 03:45:17', '2025-06-19 03:48:43', 0, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `program_submissions`
--

CREATE TABLE `program_submissions` (
  `submission_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `content_json` text COLLATE utf8mb4_unicode_ci,
  `submission_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_draft` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program_submissions`
--

INSERT INTO `program_submissions` (`submission_id`, `program_id`, `period_id`, `submitted_by`, `content_json`, `submission_date`, `updated_at`, `is_draft`) VALUES
(141, 165, 11, 12, '  {\n    \"rating\": \"on-track-yearly\",\n    \"brief_description\": \"Electric vehicle charging network\",\n    \"targets\": [\n      {\n        \"target_text\": \"Install 50 charging stations\",\n        \"status_description\": \"28 operational, 12 in progress\"\n      }\n    ]\n  }', '2025-06-03 10:50:34', '2025-06-03 11:30:43', 0),
(142, 165, 12, 12, '  {\n    \"rating\": \"not-started\",\n    \"brief_description\": \"Urban beekeeping initiative\",\n    \"targets\": [\n      {\n        \"target_text\": \"Establish 10 apiaries\",\n        \"status_description\": \"Awaiting council approval\"\n      }\n    ]\n  }', '2025-06-03 10:50:34', '2025-06-03 11:30:00', 0),
(143, 166, 11, 12, '  {\n    \"rating\": \"target-achieved\",\n    \"brief_description\": \"School recycling education drive\",\n    \"targets\": [\n      {\n        \"target_text\": \"Reach 1,000 students\",\n        \"status_description\": \"1,250 students participated\"\n      }\n    ]\n  }', '2025-06-03 10:50:34', '2025-06-03 11:30:11', 0),
(144, 166, 12, 12, '  {\n    \"rating\": \"on-track-yearly\",\n    \"brief_description\": \"Water conservation project\",\n    \"targets\": [\n      {\n        \"target_text\": \"Reduce consumption by 30% annually\",\n        \"status_description\": \"18% reduction achieved halfway\"\n      }\n    ]\n  }', '2025-06-03 10:50:34', '2025-06-03 11:30:22', 0),
(145, 167, 11, 12, '  {\n    \"rating\": \"target-achieved\",\n    \"brief_description\": \"Community clean-up initiative for World Oceans Day\",\n    \"targets\": [\n      {\n        \"target_text\": \"Collect 500kg of plastic waste\",\n        \"status_description\": \"520kg collected successfully\"\n      }\n    ]\n  }', '2025-06-03 10:50:56', '2025-06-03 11:28:21', 0),
(146, 167, 12, 12, '  {\n    \"rating\":\"severe-delay\",\n    \"brief_description\": \"Wildlife sanctuary construction project\",\n    \"targets\": [\n      {\n        \"target_text\": \"Complete Phase 1 by June\",\n        \"status_description\": \"Permitting delays, only 20% completed\"\n      }\n    ]\n  }', '2025-06-03 10:50:56', '2025-06-04 03:47:35', 0),
(147, 168, 11, 12, '  {\n    \"rating\": \"not-started\",\n    \"brief_description\": \"Green roof installation project\",\n    \"targets\": [\n      {\n        \"target_text\": \"Cover 2,000 sqm of roof space\",\n        \"status_description\": \"Funding not yet secured\"\n      }\n    ]\n  }', '2025-06-03 10:50:56', '2025-06-03 11:30:53', 0),
(148, 168, 12, 12, '{\n    \"rating\": \"on-track-yearly\",\n    \"brief_description\": \"Renewable energy adoption program\",\n    \"targets\": [\n      {\n        \"target_text\": \"Install 200 solar panels by year-end\",\n        \"status_description\": \"110 installed as of Q2\"\n      }\n    ]\n  }', '2025-06-03 10:50:56', '2025-06-03 11:28:57', 0),
(149, 169, 11, 12, '  {\n    \"rating\": \"target-achieved\",\n    \"brief_description\": \"Community garden development\",\n    \"targets\": [\n      {\n        \"target_text\": \"Establish 15 vegetable plots\",\n        \"status_description\": \"18 plots created and planted\"\n      }\n    ]\n  }', '2025-06-03 10:51:06', '2025-06-03 11:31:00', 0),
(150, 169, 12, 12, '  {\n    \"rating\": \"severe-delay\",\n    \"brief_description\": \"Coral reef restoration program\",\n    \"targets\": [\n      {\n        \"target_text\": \"Transplant 5,000 coral fragments\",\n        \"status_description\": \"Only 800 transplanted due to storms\"\n      }\n    ]\n  }', '2025-06-03 10:51:06', '2025-06-03 11:30:32', 0),
(157, 174, 11, 12, '{\"target\":\"Completion of design, survey and soil investigation.\",\"status_description\":\"Pending updates from land and survey on survey status of the lot\",\"brief_description\":\"description saja\"}', '2025-06-06 07:10:30', '2025-06-06 07:10:59', 1),
(159, 174, 2, 12, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Completion of design, survey and soil investigation.\",\"status_description\":\"Pending updates from land and survey on survey status of the lot\"}],\"remarks\":\"\",\"brief_description\":\"descripttsssss\",\"program_name\":\"Furniture Park\"}', '2025-06-06 07:52:04', '2025-06-06 07:52:04', 0),
(161, 183, 11, 35, '{\"target\":\"Repair and Maintenance of the Workshop; Installation, repair and maintenance of the machinery in the workshop; Conduct human capital development training program; To develop new training programmes\",\"status_description\":\"In progress; In progress; 2; In progress\",\"brief_description\":\"Pusat Latihan Perkayuan PUSAKA telah ditubuhkan sejak tahun 1990 yang terletak di Kompleks Industri Perabot PUSAKA Kota Samarahan dan Bandar Baru Tanjung Manis, Mukah. Sejak ditubuhkan, pusat latihan telah melatih seramai lebih kurang 2,500 orang pelatih dalam bidang Pembuatan Perabot, Seni Ukir Kayu dan Pemeringkatan Kayu, di samping kursus-kursus teknikal berkaitan perkayuan dengan kerjasama penyedia latihan yang lain seperti WISDEC, FITEC, FRIM, UNIMAS, UPM dan lain-lain. Industri perkayuan merupakan salah satu penyumbang utama kepada ekonomi Sarawak, terutamanya melalui sektor eksport produk kayu seperti papan lapis, venir, kayu gergaji, dan produk hiliran bernilai tambah seperti perabot.\"}', '2025-06-18 01:45:53', '2025-06-18 01:45:53', 1),
(163, 183, 2, 35, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Repair and Maintenance of the Workshop\",\"status_description\":\"In progress\"},{\"target_text\":\"Installation, repair and maintenance of the machinery in the workshop\",\"status_description\":\"In progress\"},{\"target_text\":\"Conduct human capital development training program\",\"status_description\":\"2\"},{\"target_text\":\"To develop new training programmes\",\"status_description\":\"In progress\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Pusat Latihan Perkayuan PUSAKA Tanjung Manis\"}', '2025-06-18 03:29:29', '2025-06-18 03:29:29', 0),
(164, 182, 11, 36, '{\"target\":\"To develop and produce the furniture prototypes.; To conduct Research on Design As Catalyst To Develop The Fine Fabrics From Bamboo Textiles In Sarawak. (24 Months Project)\",\"status_description\":\"1. In the process of identifying vendor. 2. In the process of preparing details of prototype design.; In progress\",\"brief_description\":\"Funding relevant researches\"}', '2025-06-18 01:51:03', '2025-06-18 01:51:03', 1),
(165, 176, 2, 37, '{\"target\":\"To plant 750 ha of bamboo target cumulative for quarter 1 year 2025\",\"status_description\":\"in progress :\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(168, 192, 2, 38, '{\"rating\":\"not-started\",\"targets\":[],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"SFC Program 1\"}', '2025-06-18 02:05:59', '2025-06-18 02:05:59', 0),
(171, 179, 2, 12, '{\"target\":\"To produce 162,250 seedlings; To plant 375 ha of bamboo\",\"status_description\":\"15,415 seedlings produced; 3.6 ha area planted\",\"brief_description\":\"To develop bamboo industry\"}', '2025-06-18 02:00:19', '2025-06-18 02:00:19', 0),
(172, 189, 11, 35, '{\"target\":\"1 potential investor enggagement session\",\"status_description\":\"In Progress\"}', '2025-06-18 01:58:58', '2025-06-18 01:58:58', 1),
(173, 177, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"plan to plant 1000 hectar\",\"status_description\":\"in programess\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Bamboo Industry Developement 2026\"}', '2025-06-18 01:59:10', '2025-06-18 01:59:10', 0),
(174, 181, 11, 38, '{\"target\":\"t1 aaa; t2 bbb\",\"status_description\":\"50% certified; 100% certified\",\"brief_description\":\"world heritage site\"}', '2025-06-18 01:59:16', '2025-06-18 01:59:16', 1),
(175, 184, 11, 41, '{\"target\":\"To conserve geological Sites in Sarawak Delta Geopark.; To conserve biological Sites in Sarawak Delta Geopark.; To educate the public about the importance of geosites and biosites.; Cultural sites within Sarawak Delta Geopark are to be focused and improved to be the part of the focal tourism spots in Kuching.\",\"status_description\":\"Half of the listed geological sites have implemented conservation efforts in collaboration with relevant ministries and agencies as well as the community.; Still in the progress of identifying the better conservation efforts for biological sites.; In the midst of collaborating with the communities to promote and provide awareness of the existence of the geological and biological sites.; Cultural sites have been identified and efforts are being implemented gradually.\",\"brief_description\":\"To ensure the longevity and tourism aspects of the sites within Sarawak Delta Geopark.\"}', '2025-06-18 01:59:25', '2025-06-18 01:59:25', 1),
(176, 182, 2, 36, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To conduct UNIMAS Furniture Design Competition\",\"status_description\":\"Prize presentations were conducted during PUSAKA Monthly Assembly on 16th January 2025.\"},{\"target_text\":\"To develop and produce the furniture prototypes.\",\"status_description\":\"In progress - In the process of identifying vendor and preparing details of prototype design.\"},{\"target_text\":\"To fund potential relevant research\",\"status_description\":\"In progress\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Research and Development for KURSI PUSAKA in UNIMAS\"}', '2025-06-18 02:58:28', '2025-06-18 02:58:28', 0),
(177, 181, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"t1 aaa\",\"status_description\":\"50% certified\"},{\"target_text\":\"t2 bbb\",\"status_description\":\"100% certified\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Niah Unesco\"}', '2025-06-18 01:59:57', '2025-06-18 01:59:57', 0),
(178, 179, 2, 12, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"To produce 162,250 seedlings\",\"status_description\":\"completed\"},{\"target_text\":\"To plant 375 ha of bamboo\",\"status_description\":\"3.6 ha area planted\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 02:00:19', '2025-06-18 02:00:19', 0),
(179, 182, 2, 36, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To conduct UNIMAS Furniture Design Competition\",\"status_description\":\"Prize presentations were conducted during PUSAKA Monthly Assembly on 16th January 2025.\"},{\"target_text\":\"To develop and produce the furniture prototypes.\",\"status_description\":\"In progress - In the process of identifying vendor and preparing details of prototype design.\"},{\"target_text\":\"To fund potential relevant research\",\"status_description\":\"In progress - \\r\\n1. Study on Design As Catalyst To Develop The Fine Fabrics From Bamboo Textiles In Sarawak - MoA signed. Project expected to start in 2nd quarter.\\r\\n2. Development of Bamboo-based Activated Carbon via Chemical Activation for Water Filteration and Gas Adsorption - Project will commence subject to availability of fund.\"}],\"remarks\":\"\",\"brief_description\":\"Provide fundings for relevant researches conducted under KURSI PUSAKA.\",\"program_name\":\"Research and Development for KURSI PUSAKA in UNIMAS\"}', '2025-06-18 02:58:28', '2025-06-18 02:58:28', 0),
(180, 192, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"target 1\",\"status_description\":\"\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"SFC Program 1\"}', '2025-06-18 02:05:59', '2025-06-18 02:05:59', 0),
(181, 189, 2, 35, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"1 potential investor enggagement session\",\"status_description\":\"In Progress\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Furniture Park\"}', '2025-06-18 03:30:35', '2025-06-18 03:30:35', 0),
(182, 189, 2, 35, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"1 potential investor enggagement session\",\"status_description\":\"In Progress\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Furniture Park\"}', '2025-06-18 03:30:35', '2025-06-18 03:30:35', 0),
(183, 183, 2, 35, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To conduct at least 2 training (Half yearly)\",\"status_description\":\"In progress\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Pusat Latihan Perkayuan PUSAKA Tanjung Manis\"}', '2025-06-18 03:29:29', '2025-06-18 03:29:29', 0),
(186, 183, 2, 35, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To conduct at least 2 training (Half yearly)\",\"status_description\":\"In progress\"}],\"remarks\":\"\",\"brief_description\":\"Pusat Latihan Perkayuan PUSAKA telah ditubuhkan sejak tahun 1990 yang terletak di Kompleks Industri Perabot PUSAKA Kota Samarahan dan Bandar Baru Tanjung Manis, Mukah. Sejak ditubuhkan, pusat latihan telah melatih seramai lebih kurang 2,500 orang pelatih dalam bidang Pembuatan Perabot, Seni Ukir Kayu dan Pemeringkatan Kayu, di samping kursus-kursus teknikal berkaitan perkayuan dengan kerjasama penyedia latihan yang lain seperti WISDEC, FITEC, FRIM, UNIMAS, UPM dan lain-lain. Industri perkayuan merupakan salah satu penyumbang utama kepada ekonomi Sarawak, terutamanya melalui sektor eksport produk kayu seperti papan lapis, venir, kayu gergaji, dan produk hiliran bernilai tambah seperti perabot.\",\"program_name\":\"Pusat Latihan Perkayuan PUSAKA Tanjung Manis\"}', '2025-06-18 03:29:29', '2025-06-18 03:29:29', 0),
(187, 182, 2, 36, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To develop and produce the furniture prototypes.\",\"status_description\":\"In progress - In the process of identifying vendor and preparing details of prototype design.\"},{\"target_text\":\"To conduct Research on Design As Catalyst To Develop The Fine Fabrics From Bamboo Textiles In Sarawak. (24 Months Project)\",\"status_description\":\"In progress - MoA signed. Project expected to start in 2nd quarter.\"}],\"remarks\":\"\",\"brief_description\":\"Provide fundings for relevant researches conducted under KURSI PUSAKA.\",\"program_name\":\"Research and Development for KURSI PUSAKA in UNIMAS\"}', '2025-06-18 02:58:28', '2025-06-18 02:58:28', 0),
(191, 182, 2, 36, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To develop and produce the furniture prototypes.\",\"status_description\":\"In progress - In the process of identifying vendor and preparing details of prototype design.\"},{\"target_text\":\"To conduct Research on Design As Catalyst To Develop The Fine Fabrics From Bamboo Textiles In Sarawak. (24 Months Project)\",\"status_description\":\"In progress - MoA signed. Project expected to start in 2nd quarter.\"}],\"remarks\":\"\",\"brief_description\":\"Provide fundings for relevant researches conducted under KURSI PUSAKA.\",\"program_name\":\"Research and Development for KURSI PUSAKA in UNIMAS\"}', '2025-06-18 02:58:28', '2025-06-18 02:58:28', 0),
(192, 195, 11, 43, '{\"target\":\"1. Organize conference to monitor carbon permit and license holder status; 2. Prepare sebutharga and tender doc for car rental and other purchases.; 3. Conduct field survey to look for virgin peat swamp forest.; 4. Carry out sampling at Anap Muput FMU.; 5. Organize training on soil sustainability and management.; Conduct one carbon training at Netherland in June.; One sampling trip to Peat Swamp Forest at Sebuyau.\",\"status_description\":\"1. Conference was conducted at Miri on 25 & 26 Feb 2025.; 2. Meeting to discuss the spec after receiving document from bidders was conducted on 10 Apr. However, major mistake on the RDD transport rental document and need to retender.; 3. Recce was carrid out in Mar and potential site is at Kanowit.; postpone due to no transportation; Application to Datu was rejected; Postpone to Sept due to IDF event fall on 29\\/6; It was carried out on 8-21 May 2025\",\"brief_description\":\"Conduct inventory via ground truthing across Sarawak\"}', '2025-06-18 02:21:15', '2025-06-18 02:21:15', 1),
(193, 176, 2, 37, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\n5.75 hectares planted (Jan - June 2025)\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"\"}],\"remarks\":\"\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(194, 187, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Engagement session on project implementation of P.17 Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak (HoB)\",\"status_description\":\"One Engagement session on project implementation of P.17 Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak (HoB) have been done\"},{\"target_text\":\"Project planning meeting of P.17 on Conservation and Protection of Balleh Watershed, Kapit\",\"status_description\":\"Meeting on project planning and Human Capital Skills Program on Conservation and Protection of Balleh Watershed, Kapit\"},{\"target_text\":\"Field survey on Wetlands area for P.17 Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak (HoB)\",\"status_description\":\"Field survey activities for natural tourist attractions and tourism potential in the proposed Ramsar site area\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak\"}', '2025-06-18 03:14:42', '2025-06-18 03:14:42', 0),
(195, 194, 2, 41, '{\"rating\":\"not-started\",\"targets\":[],\"remarks\":\"\",\"brief_description\":\"Program on Forest Enforcement Integrated Operation, Forest Enforcement Officers Training and Forest Enforcement Awareness Programme\",\"program_name\":\"Strengthening Forest Enforcement Through Advancing the Technology and Equipments\"}', '2025-06-18 03:00:10', '2025-06-18 03:00:10', 0),
(196, 189, 2, 35, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"2 potential investor enggagement session by 1st half year\",\"status_description\":\"In Progress\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Furniture Park\"}', '2025-06-18 03:30:35', '2025-06-18 03:30:35', 0),
(197, 189, 2, 35, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"2 potential investor enggagement session by 1st half year\",\"status_description\":\"In Progress\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Furniture Park\"}', '2025-06-18 03:30:35', '2025-06-18 03:30:35', 0),
(198, 176, 2, 37, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\n5.75 hectares planted (Jan - June 2025)\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"in progress :\\r\\n- Site visit to Kompleks Industri Perabot PUSAKA Kuala Baram (KIPPKB)\\r\\n- Discussion on KIPPKB facilities via zoom meeting\"}],\"remarks\":\"\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(199, 176, 2, 37, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\n5.75 hectares planted (Jan - June 2025)\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"in progress :\\r\\n- Site visit to Kompleks Industri Perabot PUSAKA Kuala Baram (KIPPKB)\\r\\n- Discussion on KIPPKB facilities via zoom meeting\"}],\"remarks\":\"\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(200, 176, 2, 37, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\n5.75 hectares planted (Jan - June 2025)\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"in progress :\\r\\n- Site visit to Kompleks Industri Perabot PUSAKA Kuala Baram (KIPPKB)\\r\\n- Discussion on KIPPKB facilities via zoom meeting\"}],\"remarks\":\"\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(201, 196, 11, 36, '{\"target\":\"To conduct Research On The Potential Of Carbon Trading In Sustainable Bamboo Plantation In Sarawak (Continuation from 2024); To conduct Research On The Potential Commercialization Of Products From R&D Initiative - Bamboo Briquette Charcoal (1 Year Project); To conduct Research on Cross Laminated Bamboo Project: Physical And Mechanical Studies On The Performance Of Borax-treated And Densified Wild Buluh Beting (Gigantochloa levis) Sourced From Sabah And Sarawak. (Continuation from 2024, expected to Complete in 2nd Quarter 2025); To conduct Research on Development Of Bamboo Shoot Powder From Local Bamboo Farms. (Continuation from 2024, expected to Complete in 3rd Quarter 2025); To conduct Research On Establishing A Cost-effective Protocol For Micropropagation Of Bambusa Vulgaris. Research on Production of Bamboo Seedlings Through Tissue Culture (Continuation from 2024, expected to Complete in 4th Quarter 2025.\",\"status_description\":\"In progress - Samples collection and data analysis for Guadua angustifolia, Gigantochloa atter, Dendrocalamus asper dan Bambusa vulgaris bamboo species.; The research project will begin subject to the availability of funds. Procurement procedures will be initiated as soon as funds become available.; Testing of samples, data compilation and data analysis. Preliminary result, borax treatment reduced adhesive and mechanical performance due to deposits in bamboo pores.; Data analysis and pre-treatment selection for highest quality of bamboo shoot powder (BSP). The BSP will be used for further product development studies.; Establishment of optimal multiple roots induction protocols for mass propagation of in vitro Bambusa vulgaris plants.\",\"brief_description\":\"To develop commercially viable high value products from planted timber species.\"}', '2025-06-18 02:37:38', '2025-06-18 02:37:38', 1),
(202, 196, 2, 36, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To conduct Research On The Potential Of Carbon Trading In Sustainable Bamboo Plantation In Sarawak (Continuation from 2024)\",\"status_description\":\"In progress - Samples collection and data analysis for Guadua angustifolia, Gigantochloa atter, Dendrocalamus asper dan Bambusa vulgaris bamboo species.\"},{\"target_text\":\"To conduct Research On The Potential Commercialization Of Products From R&D Initiative - Bamboo Briquette Charcoal (1 Year Project)\",\"status_description\":\"In progress - The research project will begin subject to the availability of funds. Procurement procedures will be initiated as soon as funds become available.\"},{\"target_text\":\"To conduct Research on Cross Laminated Bamboo Project: Physical And Mechanical Studies On The Performance Of Borax-treated And Densified Wild Buluh Beting (Gigantochloa levis) Sourced From Sabah And Sarawak. (Continuation from 2024, expected to Complete in 2nd Quarter 2025)\",\"status_description\":\"In progress - Testing of samples, data compilation and data analysis. Preliminary result, borax treatment reduced adhesive and mechanical performance due to deposits in bamboo pores.\"},{\"target_text\":\"To conduct Research on Development Of Bamboo Shoot Powder From Local Bamboo Farms. (Continuation from 2024, expected to Complete in 3rd Quarter 2025)\",\"status_description\":\"In progress - Data analysis and pre-treatment selection for highest quality of bamboo shoot powder (BSP). The BSP will be used for further product development studies.\"},{\"target_text\":\"To conduct Research On Establishing A Cost-effective Protocol For Micropropagation Of Bambusa Vulgaris. Research on Production of Bamboo Seedlings Through Tissue Culture (Continuation from 2024, expected to Complete in 4th Quarter 2025.\",\"status_description\":\"In progress - Establishment of optimal multiple roots induction protocols for mass propagation of in vitro Bambusa vulgaris plants.\"}],\"remarks\":\"Several projects are continuation from 2024.\",\"brief_description\":\"To develop commercially viable high value products from planted timber species.\",\"program_name\":\"Applied R&D to develop commercially viable high value products from planted timber species\"}', '2025-06-18 02:59:07', '2025-06-18 02:59:07', 0),
(203, 176, 2, 37, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nJan - June 2025 = 5.75 hectares planted\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"in progress :\\r\\n- Site visit to Kompleks Industri Perabot PUSAKA Kuala Baram (KIPPKB)\\r\\n- Discussion on KIPPKB facilities via zoom meeting\"}],\"remarks\":\"Planting and Seedlings Production are continuation program.\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(204, 194, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"1. Processing of Tender document (waiver) on rental of land and river transport for forest enforcement operation - 5 tender documents at Sarikei, Sibu, Kapit, Bintulu and Miri Region\",\"status_description\":\"Completed\"},{\"target_text\":\"An integrated forest enforcement operation on combating illegal logging and encroachment within Permanent Forest Estates - 5 operation\",\"status_description\":\"in progress - 1 operation by HQ at LPF0043 Bintulu (Preventive and Enforcement Division, Forest Department Sarawak),\"}],\"remarks\":\"\",\"brief_description\":\"Program on Forest Enforcement Integrated Operation, Forest Enforcement Officers Training and Forest Enforcement Awareness Programme\",\"program_name\":\"Strengthening Forest Enforcement Through Advancing the Technology and Equipments\"}', '2025-06-18 03:00:10', '2025-06-18 03:00:10', 0),
(205, 193, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Cumulative of 50million trees planted and recorded in Penghijauan Malaysia\",\"status_description\":\"\"},{\"target_text\":\"15 CEPA\\/planting programs in collaboration with various stakeholders conducted\",\"status_description\":\"\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Proposed Implementation of Forest Landscape Restoration Throughout Sarawak\"}', '2025-06-18 03:30:10', '2025-06-18 03:30:10', 0),
(206, 194, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"1. Processing of Tender document (waiver) on rental of land and river transport for forest enforcement operation - 5 tender documents at Sarikei, Sibu, Kapit, Bintulu and Miri Region\",\"status_description\":\"Completed\"},{\"target_text\":\"An integrated forest enforcement operation on combating illegal logging and encroachment within Permanent Forest Estates - 5 operation\",\"status_description\":\"in progress - 1 operation by HQ at LPF0043 Bintulu (Preventive and Enforcement Division, Forest Department Sarawak),\"}],\"remarks\":\"\",\"brief_description\":\"Program on Forest Enforcement Integrated Operation, Forest Enforcement Officers Training and Forest Enforcement Awareness Programme\",\"program_name\":\"Strengthening Forest Enforcement Through Advancing the Technology and Equipments\"}', '2025-06-18 03:00:10', '2025-06-18 03:00:10', 0),
(207, 183, 2, 35, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To conduct at least 2 training (Half yearly)\",\"status_description\":\"In progress\"}],\"remarks\":\"\",\"brief_description\":\"Pusat Latihan Perkayuan PUSAKA telah ditubuhkan sejak tahun 1990 yang terletak di Kompleks Industri Perabot PUSAKA Kota Samarahan dan Bandar Baru Tanjung Manis, Mukah. Sejak ditubuhkan, pusat latihan telah melatih seramai lebih kurang 2,500 orang pelatih dalam bidang Pembuatan Perabot, Seni Ukir Kayu dan Pemeringkatan Kayu, di samping kursus-kursus teknikal berkaitan perkayuan dengan kerjasama penyedia latihan yang lain seperti WISDEC, FITEC, FRIM, UNIMAS, UPM dan lain-lain. Industri perkayuan merupakan salah satu penyumbang utama kepada ekonomi Sarawak, terutamanya melalui sektor eksport produk kayu seperti papan lapis, venir, kayu gergaji, dan produk hiliran bernilai tambah seperti perabot.\",\"program_name\":\"Pusat Latihan Perkayuan PUSAKA Tanjung Manis\"}', '2025-06-18 03:29:29', '2025-06-18 03:29:29', 0),
(208, 183, 2, 35, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To conduct at least 2 training (Half yearly)\",\"status_description\":\"In progress\"}],\"remarks\":\"\",\"brief_description\":\"Pusat Latihan Perkayuan PUSAKA telah ditubuhkan sejak tahun 1990 yang terletak di Kompleks Industri Perabot PUSAKA Kota Samarahan dan Bandar Baru Tanjung Manis, Mukah. Sejak ditubuhkan, pusat latihan telah melatih seramai lebih kurang 2,500 orang pelatih dalam bidang Pembuatan Perabot, Seni Ukir Kayu dan Pemeringkatan Kayu, di samping kursus-kursus teknikal berkaitan perkayuan dengan kerjasama penyedia latihan yang lain seperti WISDEC, FITEC, FRIM, UNIMAS, UPM dan lain-lain. Industri perkayuan merupakan salah satu penyumbang utama kepada ekonomi Sarawak, terutamanya melalui sektor eksport produk kayu seperti papan lapis, venir, kayu gergaji, dan produk hiliran bernilai tambah seperti perabot.\",\"program_name\":\"Pusat Latihan Perkayuan PUSAKA Tanjung Manis\"}', '2025-06-18 03:29:29', '2025-06-18 03:29:29', 0),
(209, 197, 11, 36, '{\"target\":\"Proposed a new SayD\'signers Sarawak program module\\/ syabllus; Identify the local universities\\/ insitutions to provide training program for SayD\'Signers; Searching for new batch of SayD\'Signers\",\"status_description\":\"In progress - SARADEC has reached out to UNIMAS to proposed a whole new furniture design training syabllus for the SayD\'signers program to be resume by Q1 2026.; In progress - SARADEC is currently reviewing a mock-up syllabus specially designed by UNIMAS for the SayD\'Signers program.; In progress - SARADEC is also awaiting the approval of financial decision on the cost of the SayD\'Signers program.\",\"brief_description\":\"To establish SayD\'signers Sarawak training programme.\"}', '2025-06-18 02:52:26', '2025-06-18 02:52:26', 1),
(210, 176, 2, 37, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nJan - June 2025 = 5.75 hectares planted\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"in progress :\\r\\n- Site visit to Kompleks Industri Perabot PUSAKA Kuala Baram (KIPPKB)\\r\\n- Discussion on KIPPKB facilities via zoom meeting\"}],\"remarks\":\"Planting and Seedlings Production are continuation program.\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(211, 183, 2, 35, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To conduct at least 2 training (Half yearly)\",\"status_description\":\"In progress\"}],\"remarks\":\"\",\"brief_description\":\"Pusat Latihan Perkayuan PUSAKA telah ditubuhkan sejak tahun 1990 yang terletak di Kompleks Industri Perabot PUSAKA Kota Samarahan dan Bandar Baru Tanjung Manis, Mukah. Sejak ditubuhkan, pusat latihan telah melatih seramai lebih kurang 2,500 orang pelatih dalam bidang Pembuatan Perabot, Seni Ukir Kayu dan Pemeringkatan Kayu, di samping kursus-kursus teknikal berkaitan perkayuan dengan kerjasama penyedia latihan yang lain seperti WISDEC, FITEC, FRIM, UNIMAS, UPM dan lain-lain. Industri perkayuan merupakan salah satu penyumbang utama kepada ekonomi Sarawak, terutamanya melalui sektor eksport produk kayu seperti papan lapis, venir, kayu gergaji, dan produk hiliran bernilai tambah seperti perabot.\",\"program_name\":\"Pusat Latihan Perkayuan PUSAKA Tanjung Manis\"}', '2025-06-18 03:29:29', '2025-06-18 03:29:29', 0),
(212, 194, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"1. Processing of Tender document (waiver) on rental of land and river transport for forest enforcement operation - 5 tender documents at Sarikei, Sibu, Kapit, Bintulu and Miri Region\",\"status_description\":\"Completed\"},{\"target_text\":\"An integrated forest enforcement operation on combating illegal logging and encroachment within Permanent Forest Estates - 5 operation\",\"status_description\":\"In progress - Three (3) integrated forest enforcement operation conducted on combating illegal logging and encroachment within Permanent Forest Estates\"},{\"target_text\":\"Training for forest enforcement officers - 5 training program\",\"status_description\":\"In progress - Three (3) trainings conducted  for forest enforcement officers\"},{\"target_text\":\"Awareness program on forest enforcement - 5 program\",\"status_description\":\"In progress - Two (2) awareness program on forest enforcement conducted\"}],\"remarks\":\"\",\"brief_description\":\"Program on Forest Enforcement Integrated Operation, Forest Enforcement Officers Training and Forest Enforcement Awareness Programme\",\"program_name\":\"Strengthening Forest Enforcement Through Advancing the Technology and Equipments\"}', '2025-06-18 03:00:10', '2025-06-18 03:00:10', 0),
(213, 182, 2, 36, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To develop and produce the furniture prototypes.\",\"status_description\":\"In progress - In the process of identifying vendor and preparing details of prototype design.\"},{\"target_text\":\"To conduct Research on Design As Catalyst To Develop The Fine Fabrics From Bamboo Textiles In Sarawak. (24 Months Project)\",\"status_description\":\"In progress - MoA signed. Project expected to start in 2nd quarter.\"}],\"remarks\":\"\",\"brief_description\":\"Provide fundings for relevant researches and programmes conducted under KURSI PUSAKA.\",\"program_name\":\"Research and Development for KURSI PUSAKA in UNIMAS\"}', '2025-06-18 02:58:28', '2025-06-18 02:58:28', 0),
(214, 187, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Engagement session on project implementation of P.17 Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak (HoB)\",\"status_description\":\"One Engagement session on project implementation of P.17 Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak (HoB) have been done\"},{\"target_text\":\"Project planning meeting of P.17 on Conservation and Protection of Balleh Watershed, Kapit\",\"status_description\":\"Meeting on project planning and Human Capital Skills Program on Conservation and Protection of Balleh Watershed, Kapit\"},{\"target_text\":\"Field survey on Wetlands area for P.17 Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak (HoB)\",\"status_description\":\"Field survey activities for natural tourist attractions and tourism potential in the proposed Ramsar site area\"},{\"target_text\":\"Discussion on CEPA Program for community in Paloh\\/Loba Pulau\",\"status_description\":\"One CEPA Program for community in Paloh have been approved\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak\"}', '2025-06-18 03:14:42', '2025-06-18 03:14:42', 0),
(215, 197, 2, 36, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Proposed a new SayD\'signers Sarawak program module\\/ syabllus\",\"status_description\":\"In progress - SARADEC has reached out to UNIMAS to proposed a whole new furniture design training syabllus for the SayD\'signers program to be resume by Q1 2026.\"},{\"target_text\":\"Identify the local universities\\/ insitutions to provide training program for SayD\'Signers\",\"status_description\":\"In progress - SARADEC is currently reviewing a mock-up syllabus specially designed by UNIMAS for the SayD\'Signers program.\"},{\"target_text\":\"Searching for new batch of SayD\'Signers\",\"status_description\":\"In progress - SARADEC is also awaiting the approval of financial decision on the cost of the SayD\'Signers program.\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Implementation of Sarawak Young Designers (SayD\\u2019signers Sarawak) programme\"}', '2025-06-18 02:59:03', '2025-06-18 02:59:03', 0),
(216, 187, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Engagement session on project implementation of P.17 Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak (HoB)\",\"status_description\":\"One Engagement session on project implementation of P.17 Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak (HoB) have been done\"},{\"target_text\":\"Project planning meeting of P.17 on Conservation and Protection of Balleh Watershed, Kapit\",\"status_description\":\"Meeting on project planning and Human Capital Skills Program on Conservation and Protection of Balleh Watershed, Kapit\"},{\"target_text\":\"Field survey on Wetlands area for P.17 Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak (HoB)\",\"status_description\":\"Field survey activities for natural tourist attractions and tourism potential in the proposed Ramsar site area\"},{\"target_text\":\"Discussion on CEPA Program for community in Paloh\\/Loba Pulau\",\"status_description\":\"One CEPA Program for community in Paloh have been approved and will be held on July\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Conservation and Protection of Wetlands and Watershed Within Heart of Borneo Sarawak\"}', '2025-06-18 03:14:42', '2025-06-18 03:14:42', 0),
(217, 184, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"To conserve geological Sites in Sarawak Delta Geopark.\",\"status_description\":\"Half of the listed geological sites have implemented conservation efforts in collaboration with relevant ministries and agencies as well as the community.\"},{\"target_text\":\"To conserve biological Sites in Sarawak Delta Geopark.\",\"status_description\":\"Still in the progress of identifying the better conservation efforts for biological sites.\"},{\"target_text\":\"To educate the public about the importance of geosites and biosites.\",\"status_description\":\"In the midst of collaborating with the communities to promote and provide awareness of the existence of the geological and biological sites.\"},{\"target_text\":\"Cultural sites within Sarawak Delta Geopark are to be focused and improved to be the part of the focal tourism spots in Kuching.\",\"status_description\":\"Cultural sites have been identified and efforts are being implemented gradually.\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"The Conservation and Preservation of Geosites, Biosites and Cultural Sites within Sarawak Delta Geopark\"}', '2025-06-18 03:01:09', '2025-06-18 03:01:09', 1),
(218, 176, 2, 37, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nJan - June 2025 = 5.75 hectares planted\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nJan-June 2025 = 2,879 seedlings produced\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"in progress :\\r\\n- Site visit to Kompleks Industri Perabot PUSAKA Kuala Baram (KIPPKB)\\r\\n- Discussion on KIPPKB facilities via zoom meeting\"}],\"remarks\":\"Planting and Seedlings Production are continuation program.\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(219, 176, 2, 37, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nJan - June 2025 = 5.75 hectares planted\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nJan-June 2025 = 2,879 seedlings produced\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"in progress :\\r\\n- Site visit to Kompleks Industri Perabot PUSAKA Kuala Baram (KIPPKB)\\r\\n- Discussion on KIPPKB facilities via zoom meeting\"}],\"remarks\":\"Planting and Seedlings Production are continuation program.\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(220, 189, 2, 35, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Submission of Budget for Development (Demak Laut) by 1st Half 2025\",\"status_description\":\"RM233,709.000.00 for full development of Infra (Clearing, Soil Treatment, Land Dev, Infra Build Up, Fencing, ETC)\"},{\"target_text\":\"Submission of Budget for Development (Tanjung Manis) by 1st Half 2025\",\"status_description\":\"RM14,000.000.00 for development of Infra (P&G,Fencing,11kv Sub,Soil Investigation,ETC)\"},{\"target_text\":\"RMK13 budget interview by 2nd Half 2025\",\"status_description\":\"Preparation for the budget Interview (expected in July)\"},{\"target_text\":\"2 potential investor enggagement session by 1st half year\",\"status_description\":\"\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Furniture Park\"}', '2025-06-18 03:30:35', '2025-06-18 03:30:35', 0),
(221, 183, 2, 35, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To conduct at least 2 training (Half yearly)\",\"status_description\":\"In progress\"}],\"remarks\":\"\",\"brief_description\":\"Pusat Latihan Perkayuan PUSAKA telah ditubuhkan sejak tahun 1990 yang terletak di Kompleks Industri Perabot PUSAKA Kota Samarahan dan Bandar Baru Tanjung Manis, Mukah. Sejak ditubuhkan, pusat latihan telah melatih seramai lebih kurang 2,500 orang pelatih dalam bidang Pembuatan Perabot, Seni Ukir Kayu dan Pemeringkatan Kayu, di samping kursus-kursus teknikal berkaitan perkayuan dengan kerjasama penyedia latihan yang lain seperti WISDEC, FITEC, FRIM, UNIMAS, UPM dan lain-lain. Industri perkayuan merupakan salah satu penyumbang utama kepada ekonomi Sarawak, terutamanya melalui sektor eksport produk kayu seperti papan lapis, venir, kayu gergaji, dan produk hiliran bernilai tambah seperti perabot.\",\"program_name\":\"Pusat Latihan Perkayuan PUSAKA Tanjung Manis \\u2013 Operation and maintenance grant (TMTTC)\"}', '2025-06-18 03:29:29', '2025-06-18 03:29:29', 0),
(222, 189, 2, 35, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Submission of Budget for Development (Demak Laut) by 1st Half 2025\",\"status_description\":\"RM233,709.000.00 for full development of Infra (Clearing, Soil Treatment, Land Dev, Infra Build Up, Fencing, ETC)\"},{\"target_text\":\"Submission of Budget for Development (Tanjung Manis) by 1st Half 2025\",\"status_description\":\"RM14,000.000.00 for development of Infra (P&G,Fencing,11kv Sub,Soil Investigation,ETC)\"},{\"target_text\":\"RMK13 budget interview by 2nd Half 2025\",\"status_description\":\"Preparation for the budget Interview (expected in July)\"},{\"target_text\":\"2 Potential investor enggagement session\",\"status_description\":\"at least 2 potential investor engaged for  both furniture park\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Furniture Park\"}', '2025-06-18 03:30:35', '2025-06-18 03:30:35', 0),
(223, 184, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Preparation for Self-Assessment\",\"status_description\":\"Discussion on pre-preparation for self-assessment based on the UNESCO Checklist\"},{\"target_text\":\"Organize a Self-Assessment\",\"status_description\":\"Organize a Self-Assessment Workshop based on the UNESCO Checklist with Agencies and Advisors of aSDUGGp\"},{\"target_text\":\"Geopark Community\",\"status_description\":\"Organize Geopark Community Capacity Empowerment Program\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Obtaining UNESCO recognition for Sarawak Delta Geopark\"}', '2025-06-18 03:16:43', '2025-06-18 03:16:43', 1),
(224, 176, 2, 37, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nJan - June 2025 = 5.75 hectares planted\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nSabal - 2,879 seedlings produced\\r\\nTg Manis - 134,764 seedlings produced\\r\\nTotal Jan - June 2025 = 137,643 seedlings produced\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"in progress :\\r\\n- Site visit to Kompleks Industri Perabot PUSAKA Kuala Baram (KIPPKB)\\r\\n- Discussion on KIPPKB facilities via zoom meeting\"}],\"remarks\":\"Planting and Seedlings Production are continuation program.\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0),
(225, 189, 2, 35, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Submission of Budget for Development (Demak Laut) by 1st Half 2025\",\"status_description\":\"RM233,709.000.00 for full development of Infra (Clearing, Soil Treatment, Land Dev, Infra Build Up, Fencing, ETC)\"},{\"target_text\":\"Submission of Budget for Development (Tanjung Manis) by 1st Half 2025\",\"status_description\":\"RM14,000.000.00 for development of Infra (P&G,Fencing,11kv Sub,Soil Investigation,ETC)\"},{\"target_text\":\"RMK13 budget interview by 2nd Half 2025\",\"status_description\":\"Preparation for the budget Interview (expected in July)\"},{\"target_text\":\"2 Potential investor enggagement session\",\"status_description\":\"at least 2 potential investor engaged for  both furniture park\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Furniture Park\"}', '2025-06-18 03:30:35', '2025-06-18 03:30:35', 0),
(226, 176, 2, 37, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To plant 750 ha of bamboo target cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nJan - June 2025 = 5.75 hectares planted\"},{\"target_text\":\"To produce 200,000 seedlings cumulative for half yearly 2025\",\"status_description\":\"in progress :\\r\\nSabal - 2,879 seedlings produced\\r\\nTg Manis - 134,764 seedlings produced\\r\\nTotal Jan - June 2025 = 137,643 seedlings produced\"},{\"target_text\":\"To proposed nursery plan, site identification, manpower and implementation planning (half yearly 2025)\",\"status_description\":\"in progress :\\r\\n- Site visit to Kompleks Industri Perabot PUSAKA Kuala Baram (KIPPKB)\\r\\n- Discussion on KIPPKB facilities via zoom meeting\"}],\"remarks\":\"Planting and Seedlings Production are continuation program.\",\"brief_description\":\"To establish Bamboo Plantation and Develop Bamboo-based Industry\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-18 03:26:40', '2025-06-18 03:26:40', 0);
INSERT INTO `program_submissions` (`submission_id`, `program_id`, `period_id`, `submitted_by`, `content_json`, `submission_date`, `updated_at`, `is_draft`) VALUES
(227, 184, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Preparation for Self-Assessment\",\"status_description\":\"Discussion on pre-preparation for self-assessment based on the UNESCO Checklist\"},{\"target_text\":\"Organize a Self-Assessment\",\"status_description\":\"Organize a Self-Assessment Workshop based on the UNESCO Checklist with Agencies and Advisors of aSDUGGp\"},{\"target_text\":\"Geopark Community\",\"status_description\":\"Organize Geopark Community Capacity Empowerment Program\"},{\"target_text\":\"Site Preparation\",\"status_description\":\"For all the geological and biological sites alongside the heritage program\"},{\"target_text\":\"Educational programs\",\"status_description\":\"Conduct education programs and capacity building for Geopark School\"},{\"target_text\":\"Geopark Partner\",\"status_description\":\"Conduct capacity building for Geopark Partner (community)\"},{\"target_text\":\"Mock Audit\",\"status_description\":\"Self-assessment through Mock Audit Session with experts\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Obtaining UNESCO recognition for Sarawak Delta Geopark\"}', '2025-06-18 03:20:36', '2025-06-18 03:20:36', 1),
(228, 189, 2, 35, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Submission of Budget for Development (Demak Laut) by 1st Half 2025\",\"status_description\":\"RM233,709.000.00 for full development of Infra (Clearing, Soil Treatment, Land Dev, Infra Build Up, Fencing, ETC) submitted to MUDeNR on 29 May 2025\"},{\"target_text\":\"Submission of Budget for Development (Tanjung Manis) by 1st Half 2025\",\"status_description\":\"RM14,000.000.00 for development of Infra (P&G,Fencing,11kv Sub,Soil Investigation,ETC) submitted to MUDeNR on 29 May 2025\"},{\"target_text\":\"RMK13 budget interview by 2nd Half 2025\",\"status_description\":\"Preparation for the budget Interview (expected in July)\"},{\"target_text\":\"2 Potential investor enggagement session\",\"status_description\":\"at least 2 potential investor engaged for  both furniture park\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Furniture Park\"}', '2025-06-18 03:30:35', '2025-06-18 03:30:35', 0),
(229, 193, 2, 41, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Cumulative of 50million trees planted and recorded in Penghijauan Malaysia\",\"status_description\":\"49,000,000 million trees planted\"},{\"target_text\":\"15 CEPA\\/planting programs in collaboration with various stakeholders conducted\",\"status_description\":\"15 CEPA\\/planting programs in collaboration with various stakeholders conducted\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Proposed Implementation of Forest Landscape Restoration Throughout Sarawak\"}', '2025-06-18 03:30:10', '2025-06-18 03:30:10', 0),
(230, 195, 2, 43, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"1. Organize conference to monitor carbon permit and license holder status\",\"status_description\":\"1. Conference was conducted at Miri on 25 & 26 Feb 2025.\"},{\"target_text\":\"2. Prepare sebutharga and tender doc for car rental and other purchases.\",\"status_description\":\"2. Meeting to discuss the spec after receiving document from bidders was conducted on 10 Apr. However, major mistake on the RDD transport rental document and need to retender.\"},{\"target_text\":\"3. Conduct field survey to look for virgin peat swamp forest.\",\"status_description\":\"3. Recce was carrid out in Mar and potential site is at Kanowit.\"},{\"target_text\":\"4. Carry out sampling at Anap Muput FMU.\",\"status_description\":\"postpone due to no transportation\"},{\"target_text\":\"5. Organize training on soil sustainability and management.\",\"status_description\":\"Application to Datu was rejected\"},{\"target_text\":\"Conduct one carbon training at Netherland in June.\",\"status_description\":\"Postpone to Sept due to IDF event fall on 29\\/6\"},{\"target_text\":\"One sampling trip to Peat Swamp Forest at Sebuyau.\",\"status_description\":\"It was carried out on 8-21 May 2025\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Quantifying Forest Carbon Stock in Sarawak\"}', '2025-06-18 03:30:58', '2025-06-18 03:30:58', 0),
(231, 184, 2, 41, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Preparation for Self-Assessment\",\"status_description\":\"Discussion on pre-preparation for self-assessment based on the UNESCO Checklist\"},{\"target_text\":\"Organize a Self-Assessment\",\"status_description\":\"Organize a Self-Assessment Workshop based on the UNESCO Checklist with Agencies and Advisors of aSDUGGp\"},{\"target_text\":\"Geopark Community\",\"status_description\":\"Organize Geopark Community Capacity Empowerment Program\"},{\"target_text\":\"Site Preparation\",\"status_description\":\"For all the geological and biological sites alongside the heritage program\"},{\"target_text\":\"Educational programs\",\"status_description\":\"Conduct education programs and capacity building for Geopark School\"},{\"target_text\":\"Geopark Partner\",\"status_description\":\"Conduct capacity building for Geopark Partner (community)\"},{\"target_text\":\"Mock Audit\",\"status_description\":\"Self-assessment through Mock Audit Session with experts\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Obtaining UNESCO recognition for Sarawak Delta Geopark\"}', '2025-06-18 03:33:07', '2025-06-18 03:33:07', 1),
(232, 201, 11, 38, '{\"target\":\"Draft Concession Agreement; Identify potential TPA\",\"status_description\":\"In progress; Not started\",\"brief_description\":\"To draft concession agreement and liaise with potential Managing Agent.\"}', '2025-06-19 01:27:41', '2025-06-19 01:27:41', 1),
(233, 201, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Concession agreement  (for Tg. Datu NP and Niah NP) between SFC-SSL signed\",\"status_description\":\"Not Achieved: \\r\\nConcession agreement will only be signed between SFC and SSL during the DUN tentatively in May 2025.\"},{\"target_text\":\"Sites handed over to SSL\",\"status_description\":\"In Progress\"},{\"target_text\":\"Selective tender for Bako concession process completed and successful operator identified\",\"status_description\":\"Not started\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Identify potential TPA to be managed by Managing Agent\"}', '2025-06-19 01:42:43', '2025-06-19 01:42:43', 1),
(234, 200, 11, 38, '{\"target\":\"To develope 8 locations Tagang in Sarawak surounding TPAMs\",\"status_description\":\"2 locations System Tagang developed with communites at Sg Entangor, Sedilu and SG Meluang , Mulu\",\"brief_description\":\"Facilitated communities surrounding TPAM to develop tagang system.\"}', '2025-06-19 01:44:00', '2025-06-19 01:44:00', 1),
(235, 199, 11, 38, '{\"target\":\"Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks by 2025\",\"status_description\":\"In progress\",\"brief_description\":\"The title of AHP aims to have the sites to have its ecological process and life support systems maintained, genetic diversity preserved, sustainable utilization species and ecosystem ensured and wilderness with scenic, cultural, educational, research, recreational and tourism values maintained.  One of the benefits of attaining AHP is to promote cooperative efforts among ASEAN members, crucial for the conservation and management of parks. This collaboration aims to facilitate the development and implementation of regional conservation strategies and complementary mechanisms, enhancing national efforts to implement conservation measures. This contributes to the improvement of the management of protected areas and their biodiversity, aligning with both regional and international standards.\"}', '2025-06-19 01:44:04', '2025-06-19 01:44:04', 1),
(236, 204, 11, 12, '{\"target\":\"To produce 162,500 seedlings; To plant 375 ha of bamboo\",\"status_description\":\"15,415 seedlings produced; 3.6 ha area planted\"}', '2025-06-19 01:44:11', '2025-06-19 01:44:11', 1),
(237, 204, 2, 12, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"To produce 162,250 seedlings\",\"status_description\":\"15,416 seedlings produced\"},{\"target_text\":\"To plant 375 ha of bamboo\",\"status_description\":\"3.6 ha area planted\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Bamboo Industry Development\"}', '2025-06-19 01:45:41', '2025-06-19 01:45:41', 0),
(238, 200, 2, 38, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"To develope 8 locations Tagang in Sarawak surounding TPAMs\",\"status_description\":\"2 locations System Tagang developed with communites at Sg Entangor, Sedilu and SG Meluang , Mulu\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"3. 5 . System Tagang Mulu NP\"}', '2025-06-19 03:16:49', '2025-06-19 03:16:49', 0),
(239, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading\",\"status_description\":\"Wildlife assessment proposal approved by Q1, and reconnaissance survey had been conducted to Gunung Gading on 10 June 2025\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 01:47:14', '2025-06-19 01:47:14', 1),
(240, 200, 2, 38, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"To develope 8 locations Tagang in Sarawak surounding TPAMs\",\"status_description\":\"2 locations System Tagang developed with communites at Sg Entangor, Sedilu and SG Meluang , Mulu\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"3. 5 . System Tagang Mulu NP\"}', '2025-06-19 03:16:49', '2025-06-19 03:16:49', 0),
(241, 203, 11, 38, '{\"target\":\"Meeting  with District Office \\/ Council- To identify relevant target group conducted\",\"status_description\":\"Unable to secure appointment date with relevant parties.\",\"brief_description\":\"Identify community development \\/participation program at SWC and PNR\"}', '2025-06-19 01:48:45', '2025-06-19 01:48:45', 1),
(242, 202, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Create\\/ready evidences needed to fulfil 20 indicators for Bako NP & Santubong NP\",\"status_description\":\"\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"To certify 20 sites under IUCN Green List of Protected and Conserved Areas\"}', '2025-06-19 01:50:16', '2025-06-19 01:50:16', 1),
(243, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading\",\"status_description\":\"Wildlife assessment proposal approved by Q1, and reconnaissance survey had been conducted to Gunung Gading on 10 June 2025\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 01:51:28', '2025-06-19 01:51:28', 1),
(244, 203, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Meeting  with District Office \\/ Council- To identify relevant target group conducted\",\"status_description\":\"Unable to secure appointment date with relevant parties.\"},{\"target_text\":\"Brainstorming session conducted (park management)\",\"status_description\":\"on-going\"},{\"target_text\":\"Proposal on potential community \\/ participation program submitted for approval\",\"status_description\":\"\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Establishment of Rainforest\\/Nature Discovery Centre (SRDC NR and PNDC)\"}', '2025-06-19 01:52:08', '2025-06-19 01:52:08', 1),
(245, 201, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Concession agreement  (for Tg. Datu NP and Niah NP) between SFC-SSL signed\",\"status_description\":\"Not Achieved: \\r\\nConcession agreement will only be signed between SFC and SSL during the DUN tentatively in May 2025.\"},{\"target_text\":\"Sites handed over to SSL\",\"status_description\":\"In Progress\"},{\"target_text\":\"Selective tender for Bako concession process completed and successful operator identified\",\"status_description\":\"Not started\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Identify potential TPA to be managed by Managing Agent\"}', '2025-06-19 01:52:10', '2025-06-19 01:52:10', 1),
(246, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading\",\"status_description\":\"Wildlife assessment proposal approved by Q1, and reconnaissance survey had been conducted to Gunung Gading on 10 June 2025\"}],\"remarks\":\"\",\"brief_description\":\"To establishe a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 01:53:16', '2025-06-19 01:53:16', 1),
(247, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading\",\"status_description\":\"Wildlife assessment proposal approved by Q1, and reconnaissance survey had been conducted to Gunung Gading on 10 June 2025\"}],\"remarks\":\"\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 01:53:37', '2025-06-19 01:53:37', 1),
(248, 200, 2, 38, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"To develope 8 locations Tagang in Sarawak surounding TPAMs\",\"status_description\":\"2 locations System Tagang developed with communites at Sg Entangor, Sedilu and SG Meluang , Mulu\"}],\"remarks\":\"\",\"brief_description\":\"Implementation of community based economic programs (Facilitate implementation of Tagang System)\",\"program_name\":\"3. 5 . System Tagang\"}', '2025-06-19 03:16:49', '2025-06-19 03:16:49', 0),
(249, 202, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Create\\/ready evidences needed to fulfil 20 indicators for Bako NP & Santubong NP\",\"status_description\":\"\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"To certify 20 sites under IUCN Green List of Protected and Conserved Areas\"}', '2025-06-19 01:57:41', '2025-06-19 01:57:41', 1),
(250, 199, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"1. Submission of AHP Nomination Document to MUDeNR.\",\"status_description\":\"Completed\"},{\"target_text\":\"2. Preparation for site evaluation commenced: 2.1. Site Assessment - facilities and documentation 2.2 Community\\/stakeholder engagements\",\"status_description\":\"Completed\"}],\"remarks\":\"\",\"brief_description\":\"The title of AHP aims to have the sites to have its ecological process and life support systems maintained, genetic diversity preserved, sustainable utilization species and ecosystem ensured and wilderness with scenic, cultural, educational, research, recreational and tourism values maintained.  One of the benefits of attaining AHP is to promote cooperative efforts among ASEAN members, crucial for the conservation and management of parks. This collaboration aims to facilitate the development and implementation of regional conservation strategies and complementary mechanisms, enhancing national efforts to implement conservation measures. This contributes to the improvement of the management of protected areas and their biodiversity, aligning with both regional and international standards.\",\"program_name\":\"Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\"}', '2025-06-19 01:58:25', '2025-06-19 01:58:25', 1),
(251, 199, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"1. Submission of AHP Nomination Document to MUDeNR.\",\"status_description\":\"Completed\"},{\"target_text\":\"2. Preparation for site evaluation commenced: 2.1. Site Assessment - facilities and documentation 2.2 Community\\/stakeholder engagements\",\"status_description\":\"Completed\"}],\"remarks\":\"\",\"brief_description\":\"The title of AHP aims to have the sites to have its ecological process and life support systems maintained, genetic diversity preserved, sustainable utilization species and ecosystem ensured and wilderness with scenic, cultural, educational, research, recreational and tourism values maintained.  One of the benefits of attaining AHP is to promote cooperative efforts among ASEAN members, crucial for the conservation and management of parks. This collaboration aims to facilitate the development and implementation of regional conservation strategies and complementary mechanisms, enhancing national efforts to implement conservation measures. This contributes to the improvement of the management of protected areas and their biodiversity, aligning with both regional and international standards.\",\"program_name\":\"Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\"}', '2025-06-19 01:58:54', '2025-06-19 01:58:54', 1),
(252, 199, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"1. Submission of AHP Nomination Document to MUDeNR.\",\"status_description\":\"Completed\"},{\"target_text\":\"2. Preparation for site evaluation commenced: 2.1. Site Assessment - facilities and documentation 2.2 Community\\/stakeholder engagements\",\"status_description\":\"Completed\"},{\"target_text\":\"Site evaluation\",\"status_description\":\"Completed\"}],\"remarks\":\"\",\"brief_description\":\"The title of AHP aims to have the sites to have its ecological process and life support systems maintained, genetic diversity preserved, sustainable utilization species and ecosystem ensured and wilderness with scenic, cultural, educational, research, recreational and tourism values maintained.  One of the benefits of attaining AHP is to promote cooperative efforts among ASEAN members, crucial for the conservation and management of parks. This collaboration aims to facilitate the development and implementation of regional conservation strategies and complementary mechanisms, enhancing national efforts to implement conservation measures. This contributes to the improvement of the management of protected areas and their biodiversity, aligning with both regional and international standards.\",\"program_name\":\"Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\"}', '2025-06-19 02:00:38', '2025-06-19 02:00:38', 1),
(253, 202, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Create\\/ready evidences needed to fulfil 20 indicators for Bako NP & Santubong NP\",\"status_description\":\"In progress\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"To certify 20 sites under IUCN Green List of Protected and Conserved Areas\"}', '2025-06-19 02:04:07', '2025-06-19 02:04:07', 1),
(254, 206, 11, 38, '{\"target\":\"Meeting  with District Office \\/ Council to  identify relevant target group conducted; Resource person engaged to facilitate focus group discussion with  local community; Proposal on potential community \\/ participation program submitted for approval\",\"status_description\":\"Achieved: Stakeholders Engagement with Community (Buffer Zone) on 7 March 2025 : 14 villages involved in engagement over land matters for buffer zones - SFC to assist in CBET (homestay) for interested communities.; on going; -\",\"brief_description\":\"Identify community development \\/participation program at Niah National Park\"}', '2025-06-19 02:05:22', '2025-06-19 02:05:22', 1),
(255, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading\",\"status_description\":\"Wildlife assessment proposal approved by Q1, and reconnaissance survey had been conducted to Gunung Gading on 10 June 2025\"},{\"target_text\":\"Produce brochures, pamphlets, publications, or books.\",\"status_description\":\"Report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outside TPA)\"}],\"remarks\":\"Preparation of full report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outsite TPA) commenced by Q1\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 02:15:24', '2025-06-19 02:15:24', 1),
(256, 199, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"1. Submission of AHP Nomination Document to MUDeNR.\",\"status_description\":\"Completed\"},{\"target_text\":\"2. Preparation for site evaluation commenced: 2.1. Site Assessment - facilities and documentation 2.2 Community\\/stakeholder engagements\",\"status_description\":\"Completed\"},{\"target_text\":\"Site evaluation\",\"status_description\":\"Completed\"}],\"remarks\":\"\",\"brief_description\":\"The title of AHP aims to have the sites to have its ecological process and life support systems maintained, genetic diversity preserved, sustainable utilization species and ecosystem ensured and wilderness with scenic, cultural, educational, research, recreational and tourism values maintained.  One of the benefits of attaining AHP is to promote cooperative efforts among ASEAN members, crucial for the conservation and management of parks. This collaboration aims to facilitate the development and implementation of regional conservation strategies and complementary mechanisms, enhancing national efforts to implement conservation measures. This contributes to the improvement of the management of protected areas and their biodiversity, aligning with both regional and international standards.\",\"program_name\":\"Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\"}', '2025-06-19 02:18:13', '2025-06-19 02:18:13', 1),
(257, 207, 11, 38, '{\"target\":\"-\",\"status_description\":\"-\"}', '2025-06-19 02:23:54', '2025-06-19 02:23:54', 1),
(258, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading\",\"status_description\":\"Wildlife assessment proposal approved by Q1\\r\\nReconnaissance survey conducted to Gunung Gading on 10 June 2025 (Q2)\"},{\"target_text\":\"Produce brochures, pamphlets, publications, or books.\",\"status_description\":\"Prepare report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outside TPA)\"}],\"remarks\":\"Preparation of full report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outsite TPA) commenced by Q1\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 02:27:00', '2025-06-19 02:27:00', 1),
(259, 206, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Meeting  with District Office \\/ Council to  identify relevant target group conducted\",\"status_description\":\"Achieved: Stakeholders Engagement with Community (Buffer Zone) on 7 March 2025 : 14 villages involved in engagement over land matters for buffer zones - SFC to assist in CBET (homestay) for interested communities.\"},{\"target_text\":\"Resource person engaged to facilitate focus group discussion with  local community\",\"status_description\":\"on going\"},{\"target_text\":\"Proposal on potential community \\/ participation program submitted for approval\",\"status_description\":\"-\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"Achieve world class recognition for biodiversity conservation & protected areas management (Niah NP as Unesco World Heritage Site)\"}', '2025-06-19 02:37:54', '2025-06-19 02:37:54', 1),
(260, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading\",\"status_description\":\"Wildlife assessment proposal approved by Q1\\r\\nReconnaissance survey conducted to Gunung Gading on 10 June 2025 (Q2)\"},{\"target_text\":\"Produce brochures, pamphlets, publications, or books.\",\"status_description\":\"Prepare report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outside TPA)\"}],\"remarks\":\"Preparation of full report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outsite TPA) commenced by Q1\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 02:43:10', '2025-06-19 02:43:10', 1),
(261, 200, 2, 38, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"To implement 2 locations Tagang in Sarawak surounding TPAM.\",\"status_description\":\"\\\"1. BRO: Project proposal for 1 potential sites submitted and approved.\\r\\n\\r\\n2. SRO: Organized Workshop to  Develop System Tagang Project Proposal at Sg Betun  with DOA.\"}],\"remarks\":\"\",\"brief_description\":\"Implementation of community based economic programs : Facilitate implementation of Tagang System at 8 locations.\",\"program_name\":\"3. 5 . System Tagang\"}', '2025-06-19 03:16:49', '2025-06-19 03:16:49', 0),
(262, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading NP\",\"status_description\":\"Wildlife assessment proposal approved by Q1\\r\\nReconnaissance survey conducted to Gunung Gading on 10 June 2025 (Q2)\"},{\"target_text\":\"Produce brochures, pamphlets, publications, or books.\",\"status_description\":\"Prepare report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outside TPA)\"}],\"remarks\":\"Preparation of full report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outsite TPA) commenced by Q1\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 02:44:24', '2025-06-19 02:44:24', 1),
(263, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading National Park\",\"status_description\":\"Wildlife assessment proposal approved by Q1\\r\\nReconnaissance survey conducted to Gunung Gading on 10 June 2025 (Q2)\"},{\"target_text\":\"Produce brochures, pamphlets, publications, or books.\",\"status_description\":\"Prepare report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outside TPA)\"}],\"remarks\":\"Preparation of full report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outsite TPA) commenced by Q1\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 02:46:07', '2025-06-19 02:46:07', 1),
(264, 207, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Maludam NP\",\"status_description\":\"Call for tender\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"To develop and upgrade facilities at selected manned and unmanned TPAs.\"}', '2025-06-19 03:18:57', '2025-06-19 03:18:57', 0),
(265, 209, 11, 38, '{\"target\":\"-\",\"status_description\":\"Gunung Apeng NP and Sabal NP (JMA project) - Organize Tree Planting Activities and Field Report submitted on going\",\"brief_description\":\"Implementation of community engagement programs by engaging community participation in Biodiversity Conservation Projects -Landscape Rehabilitation Programs at 4 locations ( Gunung Apeng NP\\/Sedilu NP\\/Sabal NP\\/Piasau NP)\"}', '2025-06-19 02:48:35', '2025-06-19 02:48:35', 1),
(266, 202, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Create\\/ready evidences needed to fulfil 20 indicators for Bako NP & Santubong NP\",\"status_description\":\"5 indicators were evaluated and 2 sites has successfully completed Application Phase: \\r\\ni. Santubong NP on  02.01.25\\r\\nii. Bako NP on 05.03.25\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"To certify 20 sites under IUCN Green List of Protected and Conserved Areas\"}', '2025-06-19 02:49:41', '2025-06-19 02:49:41', 1),
(267, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading National Park\",\"status_description\":\"Wildlife assessment proposal approved by Q1\\r\\nReconnaissance survey conducted to Gunung Gading on 10 June 2025 (Q2)\"},{\"target_text\":\"Produce brochures, pamphlets, publications, or books.\",\"status_description\":\"Prepare report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outside TPA)\"}],\"remarks\":\"Preparation of full report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outsite TPA) commenced by Q1\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 02:50:15', '2025-06-19 02:50:15', 1),
(269, 207, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Maludam NP-Call up for tender\",\"status_description\":\"Call for tender.\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"To develop and upgrade facilities at selected manned and unmanned TPAs.\"}', '2025-06-19 03:18:57', '2025-06-19 03:18:57', 0),
(270, 198, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading National Park\",\"status_description\":\"Wildlife assessment proposal approved by Q1\\r\\nReconnaissance survey conducted to Gunung Gading on 10 June 2025 (Q2)\"},{\"target_text\":\"Produce brochures, pamphlets, publications, or books.\",\"status_description\":\"Prepare report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outside TPA) commenced by Q1\"}],\"remarks\":\"\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 02:52:06', '2025-06-19 02:52:06', 1),
(271, 211, 11, 38, '{\"target\":\"-\",\"status_description\":\"\\\"Turtle Conservation Project -Pugu MOA drated and vetted through SAG\",\"brief_description\":\"Implementation of community engagement programs by engaging community participation in Bodiversity Conservation Projects - Turtle Conservation Project at 3 locations ( Sematan\\/Lundu\\/Miri\"}', '2025-06-19 02:57:04', '2025-06-19 02:57:04', 1),
(272, 212, 11, 38, '{\"target\":\"FPIC and Socio economic project  proposal submitted and approved.; Commence FPIC and Socio economic survey Phase 1\\\"\",\"status_description\":\"Proposal submitted and Approved  date XXXXX; on going\",\"brief_description\":\"Implementation of community engagement programs by engaging community participation in Bodiversity Conservation Projects- Feed the Wildlife program at 2 locations ( Semenggoh NR\\/ Matang WC).\"}', '2025-06-19 02:57:37', '2025-06-19 03:01:10', 1),
(273, 212, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"FPIC and Socio economic project  proposal submitted and approved.\",\"status_description\":\"Proposal submitted and Approved  date XXXXX\"},{\"target_text\":\"Commence FPIC and Socio economic survey Phase 1\\\"\",\"status_description\":\"on going\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"3.5 Feed the Wildlife Program\"}', '2025-06-19 03:01:40', '2025-06-19 03:01:40', 1),
(274, 212, 2, 38, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"FPIC and Socio economic project  proposal submitted and approved.\",\"status_description\":\"Proposal submitted and Approved  date XXXXX\"},{\"target_text\":\"Commence FPIC and Socio economic survey Phase 1\\\"\",\"status_description\":\"on going\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"3.5 Feed the Wildlife Program\"}', '2025-06-19 03:02:14', '2025-06-19 03:02:14', 1),
(275, 209, 2, 38, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"-\",\"status_description\":\"Gunung Apeng NP and Sabal NP (JMA project) - Organize Tree Planting Activities and Field Report submitted on going\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"3.5 Landscape Rehabilitation Programs\"}', '2025-06-19 03:02:37', '2025-06-19 03:02:37', 1),
(276, 213, 11, 38, '{\"target\":\"Project proposal submitted and approved\",\"status_description\":\"Proposal submitted and approved date XXXXX\",\"brief_description\":\"Implementation of community engagement programs by engaging community participation in Bodiversity Conservation Projects\\r\\n\\r\\n\\r\\n\\r\\nImplementation of community engagement programs by engaging community participation in Bodiversity Conservation Projects- NTFP  at 2 locations ( Bintulu and Miri)\"}', '2025-06-19 03:03:24', '2025-06-19 03:06:12', 1),
(277, 199, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"1. Submission of AHP Nomination Document to MUDeNR.\",\"status_description\":\"Completed-Submitted hardcopies and softcopies of AHP Nomination Documents for Lambir Hills NP and Bako NP with supporting documents on January 2025.\"},{\"target_text\":\"2. Preparation for site evaluation commenced: 2.1. Site Assessment - facilities and documentation 2.2 Community\\/stakeholder engagements\",\"status_description\":\"Completed - AHP Familiarization workshop and excursion for stakeholders adjacent Lambir Hills NP and Bako NP. Talks on Introduction to AHP also being done with community nearby Lambir Hills NP and Bako NP at Rumah Panjang and Kampung Community Hall. Initiatives with stakeholders has been taken since 2024. Site Assessment being treated as Mock Assessment on February 2025.\"},{\"target_text\":\"Site evaluation\",\"status_description\":\"Completed-Lambir Hills NP and Bako NP were evaluated by Evaluator from ASEAN Centre for Biodiversity on March 2025.\"}],\"remarks\":\"\",\"brief_description\":\"The title of AHP aims to have the sites to have its ecological process and life support systems maintained, genetic diversity preserved, sustainable utilization species and ecosystem ensured and wilderness with scenic, cultural, educational, research, recreational and tourism values maintained.  One of the benefits of attaining AHP is to promote cooperative efforts among ASEAN members, crucial for the conservation and management of parks. This collaboration aims to facilitate the development and implementation of regional conservation strategies and complementary mechanisms, enhancing national efforts to implement conservation measures. This contributes to the improvement of the management of protected areas and their biodiversity, aligning with both regional and international standards.\",\"program_name\":\"Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks\"}', '2025-06-19 03:03:53', '2025-06-19 03:03:53', 1),
(278, 214, 11, 38, '{\"target\":\"1. Tanjung Datu - Project proposal submitted and approved; 2.  Miri Sibuti Coral Reef NP- Commence FPIC and Socio economic Survey Phase II\\\"\",\"status_description\":\"Project proposal submitted and approved date XXXX; Survey Completed date XXX\",\"brief_description\":\"Implementation of community engagement programs by engaging community participation in Bodiversity Conservation Projects- Logistic Services at 2 locations ( Tanjung Datu NP and Miri Sibuiti Coral Reefs NP\"}', '2025-06-19 03:11:50', '2025-06-19 03:11:50', 1),
(279, 214, 2, 38, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"1. Tanjung Datu - Project proposal submitted and approved\",\"status_description\":\"Project proposal submitted and approved date XXXX\"},{\"target_text\":\"2.  Miri Sibuti Coral Reef NP- Commence FPIC and Socio economic Survey Phase II\\\"\",\"status_description\":\"Survey Completed date XXX\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"3.5 Logistic services\"}', '2025-06-19 03:12:36', '2025-06-19 03:12:36', 1),
(280, 202, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Create\\/ready evidences needed to fulfil 20 indicators for Bako NP & Santubong NP\",\"status_description\":\"5 indicators were evaluated and 2 sites has successfully completed Application Phase: \\r\\ni. Santubong NP on  02.01.25\\r\\nii. Bako NP on 05.03.25\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"To certify 20 sites under IUCN Green List of Protected and Conserved Areas\"}', '2025-06-19 03:12:44', '2025-06-19 03:12:44', 1),
(281, 215, 11, 38, '{\"target\":\"TPA Services Conduct workshop with  Niah NP management and  SPC\",\"status_description\":\"not progress\",\"brief_description\":\"Implementation of community engagement programs by engaging community participation in Bodiversity Conservation Projects- Totally Protected Areas Services at 8 locations ( Bako NP\\/Niah NP\\/Similajau NP\\/Tun Ahmad Zaidi NP\\/ Lambir NP\\/ Kubah NP\\/Bukit Lima NP\\/ XXXX)\"}', '2025-06-19 03:16:43', '2025-06-19 03:16:43', 1),
(282, 215, 2, 38, '{\"rating\":\"severe-delay\",\"targets\":[{\"target_text\":\"TPA Services Conduct workshop with  Niah NP management and  SPC\",\"status_description\":\"not progress\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"3.5 Totally Protected Areas Services\"}', '2025-06-19 03:17:54', '2025-06-19 03:17:54', 1),
(284, 211, 2, 38, '{\"rating\":\"not-started\",\"targets\":[{\"target_text\":\"Finalize the Pugu MOA\",\"status_description\":\"\\\"Turtle Conservation Project -Pugu MOA drated and vetted through SAG\"}],\"remarks\":\"\",\"brief_description\":\"Implementation of community engagement programs by engaging community participation in Bodiversity Conservation Projects- Turtle Conservation Project at  3 locations ( Lundu\\/Sematan\\/Miri)\",\"program_name\":\"3.5 Turtle Conservation Project\"}', '2025-06-19 03:21:20', '2025-06-19 03:21:20', 1),
(285, 213, 2, 38, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Project proposal submitted and approved\",\"status_description\":\"Proposal submitted and approved date XXXXX\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"3.5 Non-Timber Forest Product (NTFP)\\/Beyond Timber Carnival Program\"}', '2025-06-19 03:21:49', '2025-06-19 03:21:49', 1),
(286, 198, 2, 38, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading National Park\",\"status_description\":\"Wildlife assessment proposal approved by Q1\\r\\nReconnaissance survey conducted to Gunung Gading on 10 June 2025 (Q2)\"},{\"target_text\":\"Produce brochures, pamphlets, publications, or books.\",\"status_description\":\"Prepare report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outside TPA) commenced by Q1\"}],\"remarks\":\"\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 03:24:25', '2025-06-19 03:24:25', 1),
(287, 198, 2, 38, '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Wildlife assessment at Gunung Gading National Park\",\"status_description\":\"Wildlife assessment proposal approved by Q1\\r\\nReconnaissance survey conducted to Gunung Gading on 10 June 2025 (Q2)\"},{\"target_text\":\"Produce brochures, pamphlets, publications, or books.\",\"status_description\":\"Prepare report for wildlife assessment for Gunong Mulu NP and Gunong Lesong (outside TPA) commenced by Q1\"}],\"remarks\":\"\",\"brief_description\":\"To establish a comprehensive database on flora and fauna inside TPAs in Sarawak for effective management\",\"program_name\":\"Integrated wildlife conservation and management in Sarawak\"}', '2025-06-19 03:41:49', '2025-06-19 03:41:49', 1),
(288, 212, 2, 38, '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"FPIC and Socio economic project  proposal submitted and approved.\",\"status_description\":\"Update status as May 2025 : Collection RM XXXXX . Proposal submitted and Approved  date XXXXX\"},{\"target_text\":\"Commence FPIC and Socio economic survey Phase 1\\\"\",\"status_description\":\"on going\"}],\"remarks\":\"\",\"brief_description\":\"\",\"program_name\":\"3.5 Feed the Wildlife Program\"}', '2025-06-19 03:42:38', '2025-06-19 03:42:38', 1);

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
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_standard_dates` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
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
(11, 2025, 5, '2025-01-01', '2025-06-30', 'open', '2025-06-18 01:58:27', 1, '2025-05-18 13:13:23'),
(12, 2025, 6, '2025-07-01', '2025-12-31', 'closed', '2025-05-18 13:13:23', 1, '2025-05-18 13:13:23');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `description` text,
  `pdf_path` varchar(255) NOT NULL,
  `pptx_path` varchar(255) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_public` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `period_id`, `report_name`, `description`, `pdf_path`, `pptx_path`, `generated_by`, `generated_at`, `is_public`) VALUES
(301, 2, 'Forestry Report - Q2 2025', '', '', 'pptx/Forestry_Q2-2025_20250521030906.pptx', 1, '2025-05-21 01:09:06', 0),
(312, 2, 'Forestry Report - Q2 2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250606082526.pptx', 1, '2025-06-06 08:25:26', 0),
(313, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618024920.pptx', 1, '2025-06-18 02:49:20', 0),
(314, 1, 'Forestry Report - Q1-2025', '', '', 'app/reports/pptx/Forestry_Q1-2025_20250618025043.pptx', 1, '2025-06-18 02:50:43', 0),
(315, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618025206.pptx', 1, '2025-06-18 02:52:06', 0),
(316, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618025425.pptx', 1, '2025-06-18 02:54:25', 0),
(317, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618025454.pptx', 1, '2025-06-18 02:54:54', 0),
(318, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618025638.pptx', 1, '2025-06-18 02:56:38', 0),
(319, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618025905.pptx', 1, '2025-06-18 02:59:05', 0),
(320, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618032122.pptx', 1, '2025-06-18 03:21:22', 0),
(321, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618032742.pptx', 1, '2025-06-18 03:27:42', 0),
(322, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618032914.pptx', 1, '2025-06-18 03:29:14', 0),
(323, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618033401.pptx', 1, '2025-06-18 03:34:01', 0),
(324, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618033606.pptx', 1, '2025-06-18 03:36:06', 0),
(325, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618033837.pptx', 1, '2025-06-18 03:38:37', 0),
(326, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618033903.pptx', 1, '2025-06-18 03:39:03', 0),
(327, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618033933.pptx', 1, '2025-06-18 03:39:33', 0),
(328, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618034028.pptx', 1, '2025-06-18 03:40:28', 0),
(329, 2, 'Forestry Report - Q2-2025', '', '', 'app/reports/pptx/Forestry_Q2-2025_20250618034231.pptx', 1, '2025-06-18 03:42:31', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors` (
  `sector_id` int(11) NOT NULL,
  `sector_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci
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
  `table_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_draft` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sector_outcomes_data`
--

INSERT INTO `sector_outcomes_data` (`id`, `metric_id`, `sector_id`, `period_id`, `table_name`, `data_json`, `is_draft`, `created_at`, `updated_at`, `submitted_by`) VALUES
(20, 7, 1, 2, 'TIMBER EXPORT VALUE (RM)', '{\"columns\":[\"2022\",\"2023\",\"2024\",\"2025\",\"2026\"],\"units\":{\"2022\":\"RM\",\"2023\":\"RM\",\"2024\":\"RM\",\"2025\":\"RM\"},\"data\":{\"January\":{\"2022\":408531176.77,\"2023\":263569916.63,\"2024\":276004972.69,\"2025\":null,\"2026\":0},\"February\":{\"2022\":239761718.38,\"2023\":226356164.3,\"2024\":191530929.47,\"2025\":null,\"2026\":0},\"March\":{\"2022\":394935606.46,\"2023\":261778295.29,\"2024\":214907671.7,\"2025\":null,\"2026\":0},\"April\":{\"2022\":400891037.27,\"2023\":215771835.07,\"2024\":232014272.14,\"2025\":null,\"2026\":0},\"May\":{\"2022\":345725679.36,\"2023\":324280067.64,\"2024\":324627750.87,\"2025\":null,\"2026\":0},\"June\":{\"2022\":268966198.26,\"2023\":235560482.89,\"2024\":212303812.34,\"2025\":null,\"2026\":0},\"July\":{\"2022\":359792973.34,\"2023\":244689028.37,\"2024\":274788036.68,\"2025\":null,\"2026\":0},\"August\":{\"2022\":310830376.16,\"2023\":344761866.36,\"2024\":210420404.31,\"2025\":null,\"2026\":0},\"September\":{\"2022\":318990291.52,\"2023\":210214202.2,\"2024\":191837139,\"2025\":null,\"2026\":0},\"October\":{\"2022\":304693148.3,\"2023\":266639022.25,\"2024\":null,\"2025\":null,\"2026\":0},\"November\":{\"2022\":303936172.09,\"2023\":296062485.55,\"2024\":null,\"2025\":null,\"2026\":0},\"December\":{\"2022\":289911760.38,\"2023\":251155864.77,\"2024\":null,\"2025\":null,\"2026\":0}}}', 0, '2025-04-27 03:45:15', '2025-06-17 08:06:47', 35),
(21, 8, 1, 2, 'TOTAL DEGRADED AREA', '{\"columns\":[\"2022\",\"2023\",\"2024\",\"2025\",\"2026\"],\"data\":{\"January\":{\"2022\":787.01,\"2023\":1856.37,\"2024\":3572.12,\"2025\":5.6,\"2026\":0},\"February\":{\"2022\":912.41,\"2023\":3449.94,\"2024\":6911.42,\"2025\":86.5,\"2026\":0},\"March\":{\"2022\":513.04,\"2023\":2284.69,\"2024\":3565.31,\"2025\":62.2,\"2026\":0},\"April\":{\"2022\":428.18,\"2023\":1807.69,\"2024\":2243.09,\"2025\":127.3,\"2026\":0},\"May\":{\"2022\":485.08,\"2023\":3255.8,\"2024\":3190.19,\"2025\":42,\"2026\":0},\"June\":{\"2022\":1277.9,\"2023\":3120.66,\"2024\":3618.48,\"2025\":0,\"2026\":0},\"July\":{\"2022\":745.15,\"2023\":2562.38,\"2024\":1378.09,\"2025\":0,\"2026\":0},\"August\":{\"2022\":762.69,\"2023\":2474.93,\"2024\":1536.83,\"2025\":0,\"2026\":0},\"September\":{\"2022\":579.09,\"2023\":3251.93,\"2024\":1141.79,\"2025\":0,\"2026\":0},\"October\":{\"2022\":676.27,\"2023\":3086.64,\"2024\":1311.2,\"2025\":0,\"2026\":0},\"November\":{\"2022\":2012.35,\"2023\":3081.63,\"2024\":942.5,\"2025\":0,\"2026\":0},\"December\":{\"2022\":1114.64,\"2023\":3240.14,\"2024\":969,\"2025\":0,\"2026\":0}}}', 0, '2025-05-13 23:25:38', '2025-06-18 03:18:09', 1),
(22, 9, 1, NULL, 'Repair and Maintenance of the Workshop', '{\"columns\":[],\"data\":{\"January\":[],\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 1, '2025-06-18 01:47:50', '2025-06-18 01:59:49', 1),
(23, 10, 1, NULL, 'Sarawak Delta Geopark (SDGp)', '{\"columns\":[\"Q1 2025 outcome\"],\"data\":{\"January\":{\"Q1 2025 outcome\":0},\"February\":{\"Q1 2025 outcome\":0},\"March\":{\"Q1 2025 outcome\":0},\"April\":{\"Q1 2025 outcome\":0},\"May\":{\"Q1 2025 outcome\":0},\"June\":{\"Q1 2025 outcome\":0},\"July\":{\"Q1 2025 outcome\":0},\"August\":{\"Q1 2025 outcome\":0},\"September\":{\"Q1 2025 outcome\":0},\"October\":{\"Q1 2025 outcome\":0},\"November\":{\"Q1 2025 outcome\":0},\"December\":{\"Q1 2025 outcome\":0}}}', 1, '2025-06-18 02:52:22', '2025-06-18 03:00:10', NULL),
(24, 11, 1, NULL, 'TPA Bako', '{\"columns\":[\"Local\",\"Foreign\"],\"data\":{\"January\":{\"Local\":0,\"Foreign\":0},\"February\":{\"Local\":0,\"Foreign\":0},\"March\":{\"Local\":0,\"Foreign\":0},\"April\":{\"Local\":0,\"Foreign\":0},\"May\":{\"Local\":0,\"Foreign\":0},\"June\":{\"Local\":0,\"Foreign\":0},\"July\":{\"Local\":0,\"Foreign\":0},\"August\":{\"Local\":0,\"Foreign\":0},\"September\":{\"Local\":0,\"Foreign\":0},\"October\":{\"Local\":0,\"Foreign\":0},\"November\":{\"Local\":0,\"Foreign\":0},\"December\":{\"Local\":0,\"Foreign\":0}}}', 0, '2025-06-18 02:58:42', '2025-06-18 02:58:42', NULL),
(25, 12, 1, NULL, 'Total Bako Visitor For 2025', '{\"columns\":[\"Local\",\"Foreigner\"],\"data\":{\"January\":{\"Local\":376,\"Foreigner\":1103},\"February\":{\"Local\":596,\"Foreigner\":1840},\"March\":{\"Local\":703,\"Foreigner\":2268},\"April\":{\"Local\":1098,\"Foreigner\":2934},\"May\":{\"Local\":1135,\"Foreigner\":2391},\"June\":{\"Local\":0,\"Foreigner\":0},\"July\":{\"Local\":0,\"Foreigner\":0},\"August\":{\"Local\":0,\"Foreigner\":0},\"September\":{\"Local\":0,\"Foreigner\":0},\"October\":{\"Local\":0,\"Foreigner\":0},\"November\":{\"Local\":0,\"Foreigner\":0},\"December\":{\"Local\":0,\"Foreigner\":0}}}', 0, '2025-06-18 03:03:18', '2025-06-18 03:12:54', NULL),
(26, 13, 1, NULL, 'Lambir Hills NP and Bako NP inscribed as ASEAN Heritage Parks', '{\"columns\":[],\"data\":{\"January\":[],\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 1, '2025-06-19 03:24:34', '2025-06-19 03:24:34', NULL),
(27, 14, 1, NULL, '20 TPAs certified under  IUCN Green List of Protected and Conserved Areas', '{\"columns\":[],\"data\":{\"January\":[],\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 1, '2025-06-19 03:31:59', '2025-06-19 03:33:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agency_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','agency') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sector_id` int(11) DEFAULT NULL,
  `agency_group_id` int(11) NOT NULL COMMENT '0-STIDC\r\n1-SFC\r\n2-FDS',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agency_group`
--
ALTER TABLE `agency_group`
  ADD PRIMARY KEY (`agency_group_id`),
  ADD KEY `sector_id` (`sector_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

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
-- Indexes for table `outcome_history`
--
ALTER TABLE `outcome_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `fk_outcome_history_user` (`changed_by`),
  ADD KEY `fk_outcome_history_record` (`outcome_record_id`);

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
-- Indexes for table `sector_outcomes_data`
--
ALTER TABLE `sector_outcomes_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `metric_sector_draft` (`metric_id`,`sector_id`,`is_draft`),
  ADD KEY `fk_period_id` (`period_id`),
  ADD KEY `fk_submitted_by` (`submitted_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `agency_name` (`agency_name`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `agency_id` (`agency_group_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agency_group`
--
ALTER TABLE `agency_group`
  MODIFY `agency_group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=547;

--
-- AUTO_INCREMENT for table `metrics_details`
--
ALTER TABLE `metrics_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outcomes_details`
--
ALTER TABLE `outcomes_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `outcome_history`
--
ALTER TABLE `outcome_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT for table `program_submissions`
--
ALTER TABLE `program_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=289;

--
-- AUTO_INCREMENT for table `reporting_periods`
--
ALTER TABLE `reporting_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `sector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sector_outcomes_data`
--
ALTER TABLE `sector_outcomes_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
-- Constraints for table `outcome_history`
--
ALTER TABLE `outcome_history`
  ADD CONSTRAINT `fk_outcome_history_record` FOREIGN KEY (`outcome_record_id`) REFERENCES `sector_outcomes_data` (`id`),
  ADD CONSTRAINT `fk_outcome_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`);

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
-- Constraints for table `sector_outcomes_data`
--
ALTER TABLE `sector_outcomes_data`
  ADD CONSTRAINT `fk_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`sector_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`agency_group_id`) REFERENCES `agency_group` (`agency_group_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

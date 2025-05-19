-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 10:20 AM
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
  `created_by` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`, `description`, `owner_agency_id`, `sector_id`, `start_date`, `end_date`, `created_at`, `updated_at`, `is_assigned`, `edit_permissions`, `created_by`) VALUES
(60, 'Implementation of Forest Landscape Restoration Throughout Sarawak', 'Description', 12, 1, '2025-05-15', '2026-06-19', '2025-05-14 17:54:04', '2025-05-14 17:54:04', 0, NULL, 12),
(61, 'Conservation & Protection of Wetlands & Watershed Whitin Heart of Borneo Sarawak (HoB)', 'Description 2', 12, 1, '2025-05-15', '2027-05-28', '2025-05-14 18:02:01', '2025-05-14 18:02:01', 0, NULL, 12),
(68, 'Quantifying Forest Carbon Stock in Sarawak', 'Description here', 12, 1, '2025-05-01', '2029-05-01', '2025-05-14 22:14:20', '2025-05-14 22:14:20', 0, NULL, 12),
(69, 'Bamkboo Industry Development', 'Desc', 12, 1, '2025-05-15', '2025-06-05', '2025-05-14 22:28:42', '2025-05-14 22:28:42', 0, NULL, 12),
(70, 'Furniture Park', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-14 22:29:44', '2025-05-14 22:29:44', 0, NULL, 12),
(71, 'Sarawak Delta Geopark (SDGp) UNESCO Global Geopark', '', 12, 1, '2025-05-15', '2025-05-15', '2025-05-14 22:32:46', '2025-05-14 22:32:46', 0, NULL, 12),
(72, 'Stengthenining protection for selected Totally Protected Areas(TPA)', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-14 22:36:12', '2025-05-14 22:36:12', 0, NULL, 12),
(73, 'Development and upgrading of integrated facilities of 20 TPA', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-14 22:38:36', '2025-05-14 22:38:36', 0, NULL, 12),
(74, 'Strengthening Forest Enforcement', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-14 22:40:13', '2025-05-14 22:40:13', 0, NULL, 12),
(75, 'Niah NP UNESCO World Heritage Site (WHS)', 'Desc', 12, 1, '2025-05-15', '2025-05-15', '2025-05-14 22:41:05', '2025-05-18 09:55:12', 0, NULL, 12),
(76, 'testing 123', 'description 1', 12, 1, '2025-05-01', '2025-05-18', '2025-05-18 10:12:28', '2025-05-18 12:58:56', 0, '{\"edit_permissions\":[]}', 12);

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
(51, 60, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Cumulative 7 million trees planted (20,00ha area) in 2024\",\"status_description\":\"2 Seed production Area Established Pueh Forest Reserve @ 51 mother trees and Sabal Forest Reserve @ 90 mother trees (geronggang)\"},{\"target_text\":\"5 planting program\\/collaboration\",\"status_description\":\"Asia-Pacific Regional Conference on Forest Landscape Resotration 2024 in collaboration with ITTO was held in Kuching, Sarawak on 27-28 August 2024.\"},{\"target_text\":\"Cumulative of 120 jobs created through community engagement in 2024\",\"status_description\":\"Achieved 35 million tree planting target on a 8 June 2024. Premier of Sarawak has planted tree number 35 million at Sabar FR in conjunction with the Satet-Level IDF 2024\"},{\"target_text\":\"100% planting data updated in Penghijauan Malaysia\",\"status_description\":\"21 planting program\\/collaboration organized in Q3\"}]}', '2025-05-14 17:54:04', '2025-05-14 17:54:04', 0),
(52, 61, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Workshop to prepare Proposal of Ramsar site\",\"status_description\":\"Appointment of consultant to conduct Multi-disciplinary assessment and feasibility study of Kuala Rajang in process of preparing document\\\\r\\\\n\\\\r\\\\nCommunity Development Program (study tours to Ramsar site - Kuching Wetland National Park) was conducted in 10-12 September 2024\"},{\"target_text\":\"Annual Workshop of Wetland and Watershed involving stakeholders and communities\",\"status_description\":\"Karnival Pembangunan Komunity Perhutanan Tahun 2024 bersama komuniti Tadahan Air, Baleh pada 14-22 September 2024\\\\r\\\\n\\\\r\\\\nProgram Communication, Education & Public Awareness in Waterland Area (Program Penghasilan Kraftandan Tempatan bersama Komunity Kawasan Tadahan Air, Baleh, Kapit pada 24-26 September 2024\"}]}', '2025-05-14 18:02:01', '2025-05-14 18:02:01', 0),
(59, 68, 2, 12, 'on-track-yearly', '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Organize seminar for knowledge sharing\",\"status_description\":\"Training and MOU signing ceremony successfully held at Fukuoka, Japan on 23-27 July 2024\"},{\"target_text\":\"Prelimary report on biomass findings.\",\"status_description\":\"Received CN analyser instrument on 4\\/9\\/2024. Currently installing the gas piping and setting up facilities\"},{\"target_text\":\"Forest biomass assessment at various forest in sarawak\",\"status_description\":\"Currently pending a letter to EPU on consulation work and also company profile from UPN serdang, Assesment of biomass was conducted in AUG at mangrove plot at Samunsam, Sebut harga for transport was completed and awarded in Sept 2024.\"}]}', '2025-05-14 22:14:20', '2025-05-14 22:14:20', 0),
(60, 69, 2, 12, 'severe-delay', '{\"rating\":\"severe-delay\",\"targets\":[{\"target_text\":\"To produce 650,000 seedlings\",\"status_description\":\"45,856 seedlings produced cumulative\"},{\"target_text\":\"To plant 1,500 ha of bamboo\",\"status_description\":\"262.11 ha area planted cumulative, 1 engagement with ANFA Renewables on Oct 8, 2024 and Dec 19, 2024\"}]}', '2025-05-14 22:28:42', '2025-05-14 22:28:42', 0),
(61, 70, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Completion of design, survey, soil investigation\",\"status_description\":\"Pending updates from land and survey on survey status of the lot\"}]}', '2025-05-14 22:29:44', '2025-05-14 22:29:44', 0),
(62, 71, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Submission of Application Dossier to Suruhanjaya Kebangsaan UNESCO Malaysia (SKUM) - FDS\",\"status_description\":\"Seminar on Sarawak Delta Geopark Expedition held on 17-18 July 2024\"},{\"target_text\":\"Inter-Agency Workshop - FDS, Readiness assessment by JK Pelaksana Geopark Kebangsaan (MUDeNR\\/JHS)\",\"status_description\":\"Final Draft Report (Application Dossier) submitted on 30 September 2024\"}]}', '2025-05-14 22:32:46', '2025-05-14 22:32:46', 0),
(63, 72, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"CADASTRAL SURVEY\",\"status_description\":\"SFS in principle approved the additional ceiling on 15.8.2924 during budget examination Pending receipt of official approval in order to proceed with consultant appointment\"},{\"target_text\":\"Completion of Loagan Bunut NP cadastral survey\",\"status_description\":\"Sabal NP : 95.5% completed, Consultant applied for EOT for field work for 8 weeks from 1st July until 30th August 2024. Consultant finalizing the data before submission to Land & Survey\"}]}', '2025-05-14 22:36:12', '2025-05-14 22:36:12', 0),
(64, 73, 2, 12, 'on-track-yearly', '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Call for Tender: Kubah NP\",\"status_description\":\"Matang Wildlife Centre (Staff accommodation & facilities): Management Tender Committee decided to award the tender to the successful tenderer during the meeting on 24 September 2024. Plan Tender stage and award in  Q4 2024.\"},{\"target_text\":\"Loagan Bunut NP, Gunung Gading NP & Piasau NR: -MP Final Draft -Approved by Controller -Published\",\"status_description\":\"Gunung Apeng NP (Ranger station): Pending reply on land acquisition enquiry to Land & Survey. Proposal to be refined to suit and consideration of optional site upon finalization of boundary demarcation exercise for scope of work submission under 13MP\\\\r\\\\nManagement Plan\\\\r\\\\nLoagan Bunut NP \\u2013 59% completion. Data collection and compilation stage.\\\\r\\\\nGunung Gading NP (46% completion) Data collection and compilation stage.\\\\r\\\\nPiasau NR: (46% completion) Data collection and compilation stage.\\\\r\\\\n\"}]}', '2025-05-14 22:38:36', '2025-05-14 22:38:36', 0),
(65, 74, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Implementation of forest enforcement activities \\u2013 2 Operasi Rengas Bersepadu (combating Illegal logging)\",\"status_description\":\"4 series of Operasi Rengas Bersepadu (combating Illegal logging) conducted in Kuching\\/Sri Aman, Kapit, Bintulu and Miri Region\"},{\"target_text\":\"Awareness Program (Forest Enforcement) \\u2013 2 programmes\",\"status_description\":\"2 Awareness Program on Forest Enforcement conducted at Long Busang Kapit, Long lama Miri\"},{\"target_text\":\"Technical training for forest enforcement officers \\u2013 1 programmes\",\"status_description\":\"Latihan Pengukuhan Penguatkuasaan Hutan (Technical training for forest enforcement officers) conducted at Central Region (Sibu\\/Kapit\\/Sarikei) and Northern Region (Bintulu\\/Miri\\/Limbang)\"}]}', '2025-05-14 22:40:13', '2025-05-14 22:40:13', 0),
(66, 75, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Draft of Niah for UNESCO book 1\",\"status_description\":\"Archaeological Heritage of Niah National Park\\u2019s Caves Complex has been inscribed as a UNESCO WHS on 28.07.2024.\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\nTechnical committee for the utilization of the federal fund was formed on 27 July 2024.\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\nRequest for the Steering Committee meeting had been sent out on 27 Sept 2024 and now pending for confirmation.\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\n\\\\\\\\r\\\\\\\\n\\\\\\\\r\\\\\\\\nlorenm ipsum\"}],\"remarks\":\"\"}', '2025-05-14 22:41:05', '2025-05-18 09:55:12', 1),
(67, 76, 2, 12, 'on-track-yearly', '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"30000 uds\",\"status_description\":\"15000 uds\"}],\"remarks\":\"\"}', '2025-05-18 10:12:28', '2025-05-18 12:36:08', 1),
(68, 76, 2, 12, 'on-track-yearly', '{\"program_name\":\"testing 12345\",\"description\":\"description 1\",\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"30000 uds\",\"status_description\":\"15000 uds\"}],\"remarks\":\"\"}', '2025-05-18 12:58:31', '2025-05-18 12:58:31', 1),
(69, 76, 2, 12, 'on-track-yearly', '{\"program_name\":\"testing 123\",\"description\":\"description 1\",\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"30000 uds\",\"status_description\":\"15000 uds\"}],\"remarks\":\"\"}', '2025-05-18 12:58:56', '2025-05-18 12:58:56', 1);

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
(293, 2, 'Forestry Report - Q2 2025', '', '', 'pptx/Forestry_Q2-2025_20250519094707.pptx', 1, '2025-05-19 07:47:07', 0);

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
(20, 7, 1, 2, 'TIMBER EXPORT VALUE (RM)', '{\"columns\":[\"2022\",\"2023\",\"2024\",\"2025\",\"2026\"],\"units\":{\"2022\":\"RM\",\"2023\":\"RM\",\"2024\":\"RM\",\"2025\":\"RM\"},\"data\":{\"January\":{\"2022\":408531176.77,\"2023\":263569916.63,\"2024\":276004972.69,\"2025\":null,\"2026\":0},\"February\":{\"2022\":239761718.38,\"2023\":226356164.3,\"2024\":191530929.47,\"2025\":null,\"2026\":0},\"March\":{\"2022\":394935606.46,\"2023\":261778295.29,\"2024\":214907671.7,\"2025\":null,\"2026\":0},\"April\":{\"2022\":400891037.27,\"2023\":215771835.07,\"2024\":232014272.14,\"2025\":null,\"2026\":0},\"May\":{\"2022\":345725679.36,\"2023\":324280067.64,\"2024\":324627750.87,\"2025\":null,\"2026\":0},\"June\":{\"2022\":268966198.26,\"2023\":235560482.89,\"2024\":212303812.34,\"2025\":null,\"2026\":0},\"July\":{\"2022\":359792973.34,\"2023\":244689028.37,\"2024\":274788036.68,\"2025\":null,\"2026\":0},\"August\":{\"2022\":310830376.16,\"2023\":344761866.36,\"2024\":210420404.31,\"2025\":null,\"2026\":0},\"September\":{\"2022\":318990291.52,\"2023\":210214202.2,\"2024\":191837139,\"2025\":null,\"2026\":0},\"October\":{\"2022\":304693148.3,\"2023\":266639022.25,\"2024\":null,\"2025\":null,\"2026\":0},\"November\":{\"2022\":303936172.09,\"2023\":296062485.55,\"2024\":null,\"2025\":null,\"2026\":0},\"December\":{\"2022\":289911760.38,\"2023\":251155864.77,\"2024\":null,\"2025\":null,\"2026\":0}}}', 0, '2025-04-27 11:45:15', '2025-05-05 06:42:42'),
(21, 8, 1, 2, 'TOTAL DEGRADED AREA', '{\r\n  \"columns\": [\"2022\", \"2023\", \"2024\", \"2025\", \"2026\"],\r\n  \"units\": {\r\n    \"2022\": \"Ha\",\r\n    \"2023\": \"Ha\",\r\n    \"2024\": \"Ha\",\r\n    \"2025\": \"Ha\"\r\n  },\r\n  \"data\": {\r\n    \"January\": {\r\n      \"2022\": 787.01,\r\n      \"2023\": 1856.37,\r\n      \"2024\": 3146.60,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"February\": {\r\n      \"2022\": 912.41,\r\n      \"2023\": 3449.94,\r\n      \"2024\": 6660.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"March\": {\r\n      \"2022\": 513.04,\r\n      \"2023\": 2284.69,\r\n      \"2024\": 3203.80,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"April\": {\r\n      \"2022\": 428.18,\r\n      \"2023\": 1807.69,\r\n      \"2024\": 1871.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"May\": {\r\n      \"2022\": 485.08,\r\n      \"2023\": 3255.80,\r\n      \"2024\": 2750.20,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"June\": {\r\n      \"2022\": 1277.90,\r\n      \"2023\": 3120.66,\r\n      \"2024\": 3396.30,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"July\": {\r\n      \"2022\": 745.15,\r\n      \"2023\": 2562.38,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"August\": {\r\n      \"2022\": 762.69,\r\n      \"2023\": 2474.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"September\": {\r\n      \"2022\": 579.09,\r\n      \"2023\": 3251.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"October\": {\r\n      \"2022\": 676.27,\r\n      \"2023\": 3086.64,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"November\": {\r\n      \"2022\": 2012.35,\r\n      \"2023\": 3081.63,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"December\": {\r\n      \"2022\": 1114.64,\r\n      \"2023\": 3240.14,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    }\r\n  }\r\n}', 0, '2025-05-14 07:25:38', '2025-05-14 07:26:04'),
(29, 9, 1, NULL, 'Table_9', '{\"columns\":[\"nea\"],\"data\":{\"January\":{\"nea\":123},\"February\":{\"nea\":0},\"March\":{\"nea\":0},\"April\":{\"nea\":0},\"May\":{\"nea\":0},\"June\":{\"nea\":0},\"July\":{\"nea\":0},\"August\":{\"nea\":0},\"September\":{\"nea\":0},\"October\":{\"nea\":0},\"November\":{\"nea\":0},\"December\":{\"nea\":0}},\"units\":{\"nea\":\"ha\"}}', 1, '2025-05-16 07:49:24', '2025-05-16 07:49:35'),
(30, 10, 1, NULL, '', '{\"columns\":[],\"units\":[],\"data\":{\"January\":[],\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 0, '2025-05-17 06:07:49', '2025-05-17 06:07:49'),
(31, 11, 1, NULL, '', '{\"columns\":[],\"units\":[],\"data\":{\"January\":[],\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 0, '2025-05-17 07:57:22', '2025-05-17 07:57:22'),
(32, 12, 1, NULL, '', '{\"columns\":[],\"units\":[],\"data\":{\"January\":[],\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 0, '2025-05-17 08:35:25', '2025-05-17 08:35:25'),
(33, 13, 1, NULL, '', '{\"columns\":[],\"units\":[],\"data\":{\"January\":[],\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 0, '2025-05-17 08:36:21', '2025-05-17 08:36:21'),
(34, 14, 1, NULL, '', '{\"columns\":[\"abc\"],\"units\":[],\"data\":{\"January\":{\"abc\":0},\"February\":{\"abc\":0},\"March\":{\"abc\":0},\"April\":{\"abc\":0},\"May\":{\"abc\":0},\"June\":{\"abc\":0},\"July\":{\"abc\":0},\"August\":{\"abc\":0},\"September\":{\"abc\":0},\"October\":{\"abc\":0},\"November\":{\"abc\":0},\"December\":{\"abc\":0}}}', 0, '2025-05-17 08:40:00', '2025-05-17 09:14:05');

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
(20, 7, 1, 2, 'TIMBER EXPORT VALUE (RM)', '{\"columns\":[\"2022\",\"2023\",\"2024\",\"2025\",\"2026\"],\"units\":{\"2022\":\"RM\",\"2023\":\"RM\",\"2024\":\"RM\",\"2025\":\"RM\"},\"data\":{\"January\":{\"2022\":408531176.77,\"2023\":263569916.63,\"2024\":276004972.69,\"2025\":null,\"2026\":0},\"February\":{\"2022\":239761718.38,\"2023\":226356164.3,\"2024\":191530929.47,\"2025\":null,\"2026\":0},\"March\":{\"2022\":394935606.46,\"2023\":261778295.29,\"2024\":214907671.7,\"2025\":null,\"2026\":0},\"April\":{\"2022\":400891037.27,\"2023\":215771835.07,\"2024\":232014272.14,\"2025\":null,\"2026\":0},\"May\":{\"2022\":345725679.36,\"2023\":324280067.64,\"2024\":324627750.87,\"2025\":null,\"2026\":0},\"June\":{\"2022\":268966198.26,\"2023\":235560482.89,\"2024\":212303812.34,\"2025\":null,\"2026\":0},\"July\":{\"2022\":359792973.34,\"2023\":244689028.37,\"2024\":274788036.68,\"2025\":null,\"2026\":0},\"August\":{\"2022\":310830376.16,\"2023\":344761866.36,\"2024\":210420404.31,\"2025\":null,\"2026\":0},\"September\":{\"2022\":318990291.52,\"2023\":210214202.2,\"2024\":191837139,\"2025\":null,\"2026\":0},\"October\":{\"2022\":304693148.3,\"2023\":266639022.25,\"2024\":null,\"2025\":null,\"2026\":0},\"November\":{\"2022\":303936172.09,\"2023\":296062485.55,\"2024\":null,\"2025\":null,\"2026\":0},\"December\":{\"2022\":289911760.38,\"2023\":251155864.77,\"2024\":null,\"2025\":null,\"2026\":0}}}', 0, '2025-04-27 11:45:15', '2025-05-05 06:42:42'),
(21, 8, 1, 2, 'TOTAL DEGRADED AREA', '{\r\n  \"columns\": [\"2022\", \"2023\", \"2024\", \"2025\", \"2026\"],\r\n  \"units\": {\r\n    \"2022\": \"Ha\",\r\n    \"2023\": \"Ha\",\r\n    \"2024\": \"Ha\",\r\n    \"2025\": \"Ha\"\r\n  },\r\n  \"data\": {\r\n    \"January\": {\r\n      \"2022\": 787.01,\r\n      \"2023\": 1856.37,\r\n      \"2024\": 3146.60,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"February\": {\r\n      \"2022\": 912.41,\r\n      \"2023\": 3449.94,\r\n      \"2024\": 6660.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"March\": {\r\n      \"2022\": 513.04,\r\n      \"2023\": 2284.69,\r\n      \"2024\": 3203.80,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"April\": {\r\n      \"2022\": 428.18,\r\n      \"2023\": 1807.69,\r\n      \"2024\": 1871.50,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"May\": {\r\n      \"2022\": 485.08,\r\n      \"2023\": 3255.80,\r\n      \"2024\": 2750.20,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"June\": {\r\n      \"2022\": 1277.90,\r\n      \"2023\": 3120.66,\r\n      \"2024\": 3396.30,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"July\": {\r\n      \"2022\": 745.15,\r\n      \"2023\": 2562.38,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"August\": {\r\n      \"2022\": 762.69,\r\n      \"2023\": 2474.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"September\": {\r\n      \"2022\": 579.09,\r\n      \"2023\": 3251.93,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"October\": {\r\n      \"2022\": 676.27,\r\n      \"2023\": 3086.64,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"November\": {\r\n      \"2022\": 2012.35,\r\n      \"2023\": 3081.63,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    },\r\n    \"December\": {\r\n      \"2022\": 1114.64,\r\n      \"2023\": 3240.14,\r\n      \"2024\": null,\r\n      \"2025\": null,\r\n      \"2026\": 0\r\n    }\r\n  }\r\n}', 0, '2025-05-14 07:25:38', '2025-05-14 07:26:04'),
(29, 9, 1, NULL, 'Table_9', '{\"columns\":[\"nea\"],\"data\":{\"January\":{\"nea\":123},\"February\":{\"nea\":0},\"March\":{\"nea\":0},\"April\":{\"nea\":0},\"May\":{\"nea\":0},\"June\":{\"nea\":0},\"July\":{\"nea\":0},\"August\":{\"nea\":0},\"September\":{\"nea\":0},\"October\":{\"nea\":0},\"November\":{\"nea\":0},\"December\":{\"nea\":0}},\"units\":{\"nea\":\"ha\"}}', 1, '2025-05-16 07:49:24', '2025-05-16 07:49:35'),
(30, 10, 1, NULL, '', '{\"columns\":[],\"units\":[],\"data\":{\"January\":[],\"February\":[],\"March\":[],\"April\":[],\"May\":[],\"June\":[],\"July\":[],\"August\":[],\"September\":[],\"October\":[],\"November\":[],\"December\":[]}}', 0, '2025-05-17 06:07:49', '2025-05-17 06:07:49');

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
(12, 'user', '$2y$10$/Z6xCsE7OknP.4HBT5CdBuWDZK5VNMf7MqwmGusJ0SM8xxaGQKdq2', 'testagency', 'agency', 1, '2025-03-25 07:42:27', '2025-05-05 06:41:55', 1),
(25, 'sfc', '$2y$10$wkBLipOw1EvgvpfrFTXaRO9/1OuFyCT3enAz3fr4nyOhKFBiG5M7C', 'Sarawak Forestry Corporation', 'agency', 1, '2025-05-05 06:40:10', '2025-05-05 06:40:10', 1),
(26, 'stidc', '$2y$10$ttWqO8C7DUAxBURRnvhKmu/swpsuLv.iTqsFrPnqRAECtqxsRbsA2', 'Sarawak Timber Industry Development Corporation', 'agency', 1, '2025-05-05 06:40:36', '2025-05-05 06:40:36', 1),
(27, 'forestdept', '$2y$10$304gq1GLTQvKOhmBqTp3b.oPyiwLCqlCP5lZkTfTJplVOH3QWXPt6', 'Forestry Department', 'agency', 1, '2025-05-05 06:41:16', '2025-05-05 06:41:16', 1);

--
-- Indexes for dumped tables
--

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
  ADD KEY `sector_id` (`sector_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `metrics_details`
--
ALTER TABLE `metrics_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

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
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `program_submissions`
--
ALTER TABLE `program_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `reporting_periods`
--
ALTER TABLE `reporting_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=294;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `sector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sector_metrics_data`
--
ALTER TABLE `sector_metrics_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `sector_outcomes_data`
--
ALTER TABLE `sector_outcomes_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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

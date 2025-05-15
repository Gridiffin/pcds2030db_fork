-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 08:41 AM
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
(51, 60, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Cumulative 7 million trees planted (20,00ha area) in 2024\",\"status_description\":\"2 Seed production Area Established Pueh Forest Reserve @ 51 mother trees and Sabal Forest Reserve @ 90 mother trees (geronggang)\"},{\"target_text\":\"5 planting program\\/collaboration\",\"status_description\":\"Asia-Pacific Regional Conference on Forest Landscape Resotration 2024 in collaboration with ITTO was held in Kuching, Sarawak on 27-28 August 2024.\"},{\"target_text\":\"Cumulative of 120 jobs created through community engagement in 2024\",\"status_description\":\"Achieved 35 million tree planting target on a 8 June 2024. Premier of Sarawak has planted tree number 35 million at Sabar FR in conjunction with the Satet-Level IDF 2024\"},{\"target_text\":\"100% planting data updated in Penghijauan Malaysia\",\"status_description\":\"21 planting program\\/collaboration organized in Q3\"}]}', '2025-05-15 01:54:04', '2025-05-15 01:54:04', 0),
(52, 61, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Workshop to prepare Proposal of Ramsar site\",\"status_description\":\"Appointment of consultant to conduct Multi-disciplinary assessment and feasibility study of Kuala Rajang in process of preparing document\\\\r\\\\n\\\\r\\\\nCommunity Development Program (study tours to Ramsar site - Kuching Wetland National Park) was conducted in 10-12 September 2024\"},{\"target_text\":\"Annual Workshop of Wetland and Watershed involving stakeholders and communities\",\"status_description\":\"Karnival Pembangunan Komunity Perhutanan Tahun 2024 bersama komuniti Tadahan Air, Baleh pada 14-22 September 2024\\\\r\\\\n\\\\r\\\\nProgram Communication, Education & Public Awareness in Waterland Area (Program Penghasilan Kraftandan Tempatan bersama Komunity Kawasan Tadahan Air, Baleh, Kapit pada 24-26 September 2024\"}]}', '2025-05-15 02:02:01', '2025-05-15 02:02:01', 0),
(59, 68, 2, 12, 'on-track-yearly', '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Organize seminar for knowledge sharing\",\"status_description\":\"Training and MOU signing ceremony successfully held at Fukuoka, Japan on 23-27 July 2024\"},{\"target_text\":\"Prelimary report on biomass findings.\",\"status_description\":\"Received CN analyser instrument on 4\\/9\\/2024. Currently installing the gas piping and setting up facilities\"},{\"target_text\":\"Forest biomass assessment at various forest in sarawak\",\"status_description\":\"Currently pending a letter to EPU on consulation work and also company profile from UPN serdang, Assesment of biomass was conducted in AUG at mangrove plot at Samunsam, Sebut harga for transport was completed and awarded in Sept 2024.\"}]}', '2025-05-15 06:14:20', '2025-05-15 06:14:20', 0),
(60, 69, 2, 12, 'severe-delay', '{\"rating\":\"severe-delay\",\"targets\":[{\"target_text\":\"To produce 650,000 seedlings\",\"status_description\":\"45,856 seedlings produced cumulative\"},{\"target_text\":\"To plant 1,500 ha of bamboo\",\"status_description\":\"262.11 ha area planted cumulative, 1 engagement with ANFA Renewables on Oct 8, 2024 and Dec 19, 2024\"}]}', '2025-05-15 06:28:42', '2025-05-15 06:28:42', 0),
(61, 70, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Completion of design, survey, soil investigation\",\"status_description\":\"Pending updates from land and survey on survey status of the lot\"}]}', '2025-05-15 06:29:44', '2025-05-15 06:29:44', 0),
(62, 71, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Submission of Application Dossier to Suruhanjaya Kebangsaan UNESCO Malaysia (SKUM) - FDS\",\"status_description\":\"Seminar on Sarawak Delta Geopark Expedition held on 17-18 July 2024\"},{\"target_text\":\"Inter-Agency Workshop - FDS, Readiness assessment by JK Pelaksana Geopark Kebangsaan (MUDeNR\\/JHS)\",\"status_description\":\"Final Draft Report (Application Dossier) submitted on 30 September 2024\"}]}', '2025-05-15 06:32:46', '2025-05-15 06:32:46', 0),
(63, 72, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"CADASTRAL SURVEY\",\"status_description\":\"SFS in principle approved the additional ceiling on 15.8.2924 during budget examination Pending receipt of official approval in order to proceed with consultant appointment\"},{\"target_text\":\"Completion of Loagan Bunut NP cadastral survey\",\"status_description\":\"Sabal NP : 95.5% completed, Consultant applied for EOT for field work for 8 weeks from 1st July until 30th August 2024. Consultant finalizing the data before submission to Land & Survey\"}]}', '2025-05-15 06:36:12', '2025-05-15 06:36:12', 0),
(64, 73, 2, 12, 'on-track-yearly', '{\"rating\":\"on-track-yearly\",\"targets\":[{\"target_text\":\"Call for Tender: Kubah NP\",\"status_description\":\"Matang Wildlife Centre (Staff accommodation & facilities): Management Tender Committee decided to award the tender to the successful tenderer during the meeting on 24 September 2024. Plan Tender stage and award in  Q4 2024.\"},{\"target_text\":\"Loagan Bunut NP, Gunung Gading NP & Piasau NR: -MP Final Draft -Approved by Controller -Published\",\"status_description\":\"Gunung Apeng NP (Ranger station): Pending reply on land acquisition enquiry to Land & Survey. Proposal to be refined to suit and consideration of optional site upon finalization of boundary demarcation exercise for scope of work submission under 13MP\\\\r\\\\nManagement Plan\\\\r\\\\nLoagan Bunut NP \\u2013 59% completion. Data collection and compilation stage.\\\\r\\\\nGunung Gading NP (46% completion) Data collection and compilation stage.\\\\r\\\\nPiasau NR: (46% completion) Data collection and compilation stage.\\\\r\\\\n\"}]}', '2025-05-15 06:38:36', '2025-05-15 06:38:36', 0),
(65, 74, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Implementation of forest enforcement activities \\u2013 2 Operasi Rengas Bersepadu (combating Illegal logging)\",\"status_description\":\"4 series of Operasi Rengas Bersepadu (combating Illegal logging) conducted in Kuching\\/Sri Aman, Kapit, Bintulu and Miri Region\"},{\"target_text\":\"Awareness Program (Forest Enforcement) \\u2013 2 programmes\",\"status_description\":\"2 Awareness Program on Forest Enforcement conducted at Long Busang Kapit, Long lama Miri\"},{\"target_text\":\"Technical training for forest enforcement officers \\u2013 1 programmes\",\"status_description\":\"Latihan Pengukuhan Penguatkuasaan Hutan (Technical training for forest enforcement officers) conducted at Central Region (Sibu\\/Kapit\\/Sarikei) and Northern Region (Bintulu\\/Miri\\/Limbang)\"}]}', '2025-05-15 06:40:13', '2025-05-15 06:40:13', 0),
(66, 75, 2, 12, 'target-achieved', '{\"rating\":\"target-achieved\",\"targets\":[{\"target_text\":\"Draft of Niah for UNESCO book\",\"status_description\":\"Archaeological Heritage of Niah National Park\\u2019s Caves Complex has been inscribed as a UNESCO WHS on 28.07.2024.\\\\r\\\\nTechnical committee for the utilization of the federal fund was formed on 27 July 2024.\\\\r\\\\nRequest for the Steering Committee meeting had been sent out on 27 Sept 2024 and now pending for confirmation.\\\\r\\\\n\"}]}', '2025-05-15 06:41:05', '2025-05-15 06:41:05', 0);

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `program_submissions`
--
ALTER TABLE `program_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `program_submissions`
--
ALTER TABLE `program_submissions`
  ADD CONSTRAINT `program_submissions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `program_submissions_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  ADD CONSTRAINT `program_submissions_ibfk_3` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

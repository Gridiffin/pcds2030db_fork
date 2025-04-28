-- Create reports table if it doesn't exist
CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `period_id` int(11) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `pptx_path` varchar(255) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`report_id`),
  KEY `period_id` (`period_id`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `reporting_periods` (`period_id`),
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
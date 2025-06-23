-- Migration: Create initiatives table
-- Date: 2025-06-23
-- Description: Create the initiatives table to group programs under strategic initiatives

CREATE TABLE IF NOT EXISTS `initiatives` (
    `initiative_id` INT AUTO_INCREMENT PRIMARY KEY,
    `initiative_name` VARCHAR(255) NOT NULL,
    `initiative_description` TEXT,
    `pillar_id` INT, -- Could be linked to sectors or a separate pillars table later
    `start_date` DATE,
    `end_date` DATE,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_pillar_id` (`pillar_id`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

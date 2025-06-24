-- Migration: Create program_outcome_links table
-- Date: 2025-06-23
-- Description: Create many-to-many relationship table between programs and outcomes

CREATE TABLE IF NOT EXISTS `program_outcome_links` (
    `link_id` INT AUTO_INCREMENT PRIMARY KEY,
    `program_id` INT NOT NULL,
    `outcome_id` INT NOT NULL,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Unique constraint to prevent duplicate links
    UNIQUE KEY `unique_program_outcome` (`program_id`, `outcome_id`),
    
    -- Foreign key constraints
    CONSTRAINT `fk_pol_program` 
        FOREIGN KEY (`program_id`) 
        REFERENCES `programs`(`program_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT `fk_pol_outcome` 
        FOREIGN KEY (`outcome_id`) 
        REFERENCES `outcomes_details`(`detail_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT `fk_pol_created_by` 
        FOREIGN KEY (`created_by`) 
        REFERENCES `users`(`user_id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Indexes for performance
    INDEX `idx_program_id` (`program_id`),
    INDEX `idx_outcome_id` (`outcome_id`),
    INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

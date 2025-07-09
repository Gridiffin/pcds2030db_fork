-- Program Logic Redesign Schema Migration
-- This file creates all tables and relationships for the new program logic

-- 1. Initiatives Table (if not already present)
CREATE TABLE IF NOT EXISTS initiatives (
  initiative_id INT AUTO_INCREMENT PRIMARY KEY,
  initiative_name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Programs Table
CREATE TABLE IF NOT EXISTS programs (
  program_id INT AUTO_INCREMENT PRIMARY KEY,
  initiative_id INT NOT NULL,
  program_name VARCHAR(255) NOT NULL,
  program_description TEXT,
  agency_id INT NOT NULL,
  is_deleted TINYINT(1) DEFAULT 0,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (initiative_id) REFERENCES initiatives(initiative_id),
  FOREIGN KEY (agency_id) REFERENCES agency(agency_id),
  FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Migration: Add start_date and end_date columns to programs table if not exist
ALTER TABLE programs
    ADD COLUMN IF NOT EXISTS start_date DATE NULL AFTER program_description,
    ADD COLUMN IF NOT EXISTS end_date DATE NULL AFTER start_date;

-- Migration: Add rating column to programs table
ALTER TABLE programs
    ADD COLUMN IF NOT EXISTS rating ENUM('monthly_target_achieved','on_track_for_year','severe_delay','not_started') DEFAULT 'not_started' AFTER program_number;

-- Migration: Remove rating column from program_submissions table
ALTER TABLE program_submissions
    DROP COLUMN IF EXISTS rating;

-- 3. Program User Assignments Table
CREATE TABLE IF NOT EXISTS program_user_assignments (
  assignment_id INT AUTO_INCREMENT PRIMARY KEY,
  program_id INT NOT NULL,
  user_id INT NOT NULL,
  role ENUM('editor','viewer') DEFAULT 'editor',
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 4. Program Submissions Table
CREATE TABLE IF NOT EXISTS program_submissions (
  submission_id INT AUTO_INCREMENT PRIMARY KEY,
  program_id INT NOT NULL,
  period_id INT NOT NULL,
  is_draft TINYINT(1) DEFAULT 1,
  is_submitted TINYINT(1) DEFAULT 0,
  status_indicator ENUM('not_started','in_progress','completed','delayed') DEFAULT 'not_started',
  description TEXT,
  start_date DATE,
  end_date DATE,
  submitted_by INT,
  submitted_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  is_deleted TINYINT(1) DEFAULT 0,
  FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE,
  FOREIGN KEY (period_id) REFERENCES reporting_periods(period_id) ON DELETE CASCADE,
  FOREIGN KEY (submitted_by) REFERENCES users(user_id)
);

-- 5. Program Targets Table
CREATE TABLE IF NOT EXISTS program_targets (
  target_id INT AUTO_INCREMENT PRIMARY KEY,
  submission_id INT NOT NULL,
  target_description TEXT,
  status_indicator ENUM('not_started','in_progress','completed','delayed') DEFAULT 'not_started',
  status_description TEXT,
  remarks TEXT,
  start_date DATE,
  end_date DATE,
  is_deleted TINYINT(1) DEFAULT 0,
  FOREIGN KEY (submission_id) REFERENCES program_submissions(submission_id) ON DELETE CASCADE
);

-- 6. Program Attachments Table
CREATE TABLE IF NOT EXISTS program_attachments (
  attachment_id INT AUTO_INCREMENT PRIMARY KEY,
  submission_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_size INT,
  file_type VARCHAR(100),
  uploaded_by INT NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_deleted TINYINT(1) DEFAULT 0,
  FOREIGN KEY (submission_id) REFERENCES program_submissions(submission_id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by) REFERENCES users(user_id)
); 
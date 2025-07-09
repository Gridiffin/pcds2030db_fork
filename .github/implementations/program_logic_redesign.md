# Program Logic Redesign (2024)

## Overview
This document details the requirements, logic, and step-by-step plan for the redesigned program management system, supporting period-specific submissions, draft/submitted workflows, robust access control, and auditability.

---

## Requirements Summary

1. **Initiative-Program-Target Structure**
   - One initiative has many programs
   - One program has many targets
   - One target has one status description
   - One program can have multiple file attachments
   - Each program has its own status indicator, rating, description, and optional timeline (dates)
   - Each target has its own optional timeline (dates), status indicator, and remarks (all text-based)
   - Each target is linked to one program

2. **Submission & Period Logic**
   - Submissions are period-specific (e.g., Q1 2025, Q2 2025, etc.)
   - Each (program, period) pair can have a draft and a submitted version
   - Editing/unsubmitting a submission only affects that specific period
   - Drafts are initialized with the last submitted data for that period
   - Only submitted versions are visible to admins/reports
   - Unsubmitting a period removes it from admin/report view, but does not affect other periods
   - Support for cross-period targets (targets can span multiple periods)

3. **Audit/History**
   - All changes (submissions, edits, unsubmit actions) are tracked in the audit log/history system
   - Previous submissions are archived for traceability

4. **Soft Deletes**
   - Support soft delete (archiving) for programs, targets, and submissions

5. **User Roles/Permissions & Access Control**
   - Only focal users can submit programs and set/change program rating and status indicator
   - Use a separate `program_user_assignments` table for access control (assign users to programs for edit/view permissions)

6. **Reporting Support**
   - Schema should support standard reporting (progress by initiative, period-based reporting, etc.)

7. **Attachments**
   - Use ON DELETE CASCADE for attachment cleanup (auto-delete attachments when a program/submission is deleted)
   - Count attachments on demand in the application (no need for triggers unless performance requires it)

8. **Submitted By**
   - Keep the `submitted_by` field for audit/history and accountability, even if only focal users can submit

---

## Entity Relationship Diagram (Textual)

Initiative
  └─ Program
      ├─ Program User Assignments (edit/view access)
      └─ Program Submission (per period)
            ├─ Targets (per submission)
            ├─ Attachments (per submission)
            ├─ Status, Rating, Description, Timeline (per submission)
            └─ Audit/History (per submission)

---

## Step-by-Step TODO Checklist

- [x] Document requirements and logic (this file)
- [x] Clarify user assignment and access control (separate table)
- [x] Clarify attachment cleanup and counting (ON DELETE CASCADE, count on demand)
- [x] Clarify submitted_by field (keep for audit/history)
- [x] Design database schema (tables, relationships, constraints)
- [ ] Ensure schema supports period-specific submissions and drafts
- [ ] Integrate soft delete (archiving) logic
- [ ] Align schema with audit/history tracking
- [ ] Add indexes/fields for standard reporting support
- [ ] Review and suggest improvements for maintainability, performance, and security
- [ ] Update this file as each step is completed

---

## Database Schema Design (SQL)

```sql
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
  rating ENUM('monthly_target_achieved','on_track_for_year','severe_delay','not_started') DEFAULT 'not_started',
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
```

---

*This plan will be updated as implementation progresses with checkmarks (✅) for completed tasks.*

# Program Logic Redesign: Fix Deprecated Column Errors (Phase 1)

## Problem
- Errors due to references to deprecated columns (`users_assigned`, `submission_date`, `owner_agency_id`, `content_json`, `rp.quarter`, `pa.program_id`, `is_assigned`) in backend files after database schema redesign.

## Solution
- Remove or update all references to deprecated columns in the affected files, aligning with the new schema.

## Tasks
- [x] Identify all references to deprecated columns in `app/lib/initiative_functions.php` and `app/views/agency/programs/view_programs.php`.
- [x] Remove `$config['columns']['programs']['users_assigned']` and any related logic from `initiative_functions.php`.
- [x] Update all SQL queries in `view_programs.php`:
  - [x] Replace `submission_date` with `submitted_at`.
  - [x] Remove `content_json`/`JSON_EXTRACT` for rating; use `rating` column directly.
  - [x] Remove all references to `owner_agency_id`; use `created_by` or `agency_id` as appropriate.
- [x] Refactor `get_program_details` in `agencies/programs.php` to remove `users_assigned` and use `program_user_assignments` for assigned users. (Fixed fatal error on update_program page)
- [x] Update all `reporting_periods` references in `agencies/programs.php` to use new schema (`period_type`, `period_number` instead of `quarter`, `submitted_at` instead of `submission_date`). (Fixed fatal error on update_program page)
- [x] Update `verify_program_access` and `get_attachment_for_download` in `agencies/program_attachments.php` to use `agency_id` instead of `users_assigned` for access control. (Fixed fatal error on update_program page)
- [x] Update all `program_attachments` references to use new schema (`submission_id` instead of `program_id`, `file_name` instead of `original_filename`, `uploaded_at` instead of `upload_date`, `is_deleted` instead of `is_active`). (Fixed fatal error on update_program page)
- [x] Create `is_program_assigned()` helper function and replace all `$program['is_assigned']` references in `update_program.php` to use the new assignment logic based on `program_user_assignments` table. (Fixed undefined array key warnings)
- [x] Add proper `isset()` checks for `start_date` and `end_date` fields in `update_program.php` to prevent undefined array key warnings. (Fixed undefined array key warnings)
- [x] Fix agency initiatives.php - remove deprecated columns (users_assigned, content_json) and update queries to use new schema (agency_id, rating). (Fixed fatal SQL syntax error and undefined array key warnings)
- [x] Update agency initiatives queries to use new schema structure with proper agency access control. (Fixed session variable references from user_id to agency_id)
- [x] Fix agency sectors view_all_sectors.php - remove deprecated columns (agency_name from users table, sector_id) and update to use new schema (agency table, agency_id). (Fixed fatal SQL syntax error and updated page to work with agency-based structure)
- [x] Create get_agency_outcomes() function in agencies/outcomes.php to work with new outcomes_details table schema. (Fixed undefined function error)
- [x] Test the affected pages to confirm errors are resolved.
- [x] Mark all tasks as complete in this file.

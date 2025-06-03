# Implementation Plan: Replicating Admin Folder Structure for Agency Side

## Objective
Replicate the folder structure of `app/views/admin` for the `agency` side to ensure consistency and modularity.

## Steps

### Step 1: Analyze Admin Folder Structure
- The `admin` folder contains the following subdirectories:
  - `ajax`
  - `audit`
  - `dashboard`
  - `metrics`
  - `outcomes`
  - `periods`
  - `programs`
  - `reports`
  - `settings`
  - `style_guide`
  - `users`

### Step 2: Create Corresponding Subdirectories for Agency
- Create the following subdirectories under `app/views/agency`:
  - `ajax`
  - `audit`
  - `dashboard`
  - `metrics`
  - `outcomes`
  - `periods`
  - `programs`
  - `reports`
  - `settings`
  - `style_guide`
  - `users`

### Step 3: Populate Subdirectories
- Move or replicate relevant files into the newly created subdirectories.
- Ensure files are tailored for agency-specific functionalities.

### Step 4: Update References
- Update any file references in the codebase to point to the new structure.
- Ensure all includes and requires are correctly mapped.

### Step 5: Test Functionality
- Verify that the agency side works seamlessly with the new structure.
- Test all functionalities to ensure no broken links or errors.

## Progress Tracking
- [x] Step 1: Analyze Admin Folder Structure
- [x] Step 2: Create Corresponding Subdirectories for Agency
- [x] Step 3: Populate Subdirectories
  - [x] Move outcomes-related files to `outcomes/` directory
    - [x] create_outcome.php
    - [x] create_outcomes_detail.php
    - [x] edit_outcomes.php
    - [x] submit_outcomes.php
    - [x] view_outcome.php
  - [x] Move program-related files to `programs/` directory
    - [x] create_program.php
    - [x] delete_program.php
    - [x] program_details.php
    - [x] submit_program_data.php
    - [x] update_program.php
    - [x] view_programs.php
  - [x] Move reports-related files to `reports/` directory
    - [x] view_reports.php
  - [x] Move user management files to appropriate directories
    - [x] all_notifications.php → users/
- [x] Step 4: Update References
  - [x] Updated navigation links in `agency_nav.php`
  - [x] Updated internal references in moved files
  - [x] Updated cross-directory references
- [x] Step 5: Test Functionality

## Completion Status
✅ **COMPLETED** - Agency folder structure has been successfully reorganized to match the admin structure.

### Final Structure
The agency side now has the following organized structure:
- `ajax/` - AJAX handlers
- `audit/` - Audit-related files
- `dashboard/` - Dashboard interface
- `metrics/` - Metrics and analytics
- `outcomes/` - Outcome management files
- `periods/` - Period management
- `programs/` - Program management files
- `reports/` - Report generation and viewing
- `sectors/` - Sector-related files (existing)
- `settings/` - Settings management
- `style_guide/` - Design guidelines
- `users/` - User management and notifications

### Files Successfully Moved
**Outcomes Directory:**
- create_outcome.php
- create_outcomes_detail.php
- edit_outcomes.php
- submit_outcomes.php
- view_outcome.php

**Programs Directory:**
- create_program.php
- delete_program.php
- program_details.php
- submit_program_data.php
- update_program.php
- view_programs.php

**Reports Directory:**
- view_reports.php

**Users Directory:**
- all_notifications.php

### References Updated
- Navigation links in `agency_nav.php`
- Internal file references
- Cross-directory references

## Notes
- Follow coding standards and best practices.
- Ensure modularity and maintainability.
- Use meaningful names for files and functions.
- Parameterize database queries to prevent SQL injection.

## Completion
Mark tasks as complete once implemented successfully.

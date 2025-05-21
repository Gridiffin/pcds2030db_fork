# PCDS2030 Dashboard - Cleanup Guide

## Overview

This document provides a structured approach to safely identifying and removing unused code from the PCDS2030 Dashboard project prior to the major restructuring effort. Following this process will help reduce technical debt and make the restructuring more manageable.

## Cleanup Process

The cleanup process consists of four phases:

1. **Identification** - Detect potentially unused files and code blocks
2. **Verification** - Confirm that identified items are truly unused
3. **Deprecation** - Safely move verified unused files to a deprecated folder
4. **Removal** - Permanently remove deprecated items after a verification period

## Phase 1: Identification (Week 1)

We've already identified several categories of potentially unused files:

### Test/Debug Files
- `check_db.php`
- `test_program_filter.html`

### AJAX/API Endpoints Not Referenced in JavaScript
- `ajax/add_quarter_column.php`
- `ajax/add_reporting_period_column.php`
- `api/check_metric.php`
- `api/check_outcome.php`
- `api/get_metric_data.php`
- `api/get_outcome_data.php`
- `api/get_periods.php`
- `api/get_recent_reports.php`
- `api/get_sectors.php`
- `api/save_metric_json.php`

### Helper Files Requiring Verification
- `includes/history_helpers.php`
- `includes/status_helpers.php`

## Phase 2: Verification (Week 1-3)

### 2.1 Adding Access Logging

We've created `add_file_logging.php` that will add logging code to potentially unused files.

**Steps:**

1. Review the list of files in `add_file_logging.php`
2. Run the script to add logging:
   ```bash
   cd d:\xampp\htdocs\pcds2030_dashboard
   php add_file_logging.php
   ```
3. Use the application normally for 1-2 weeks
4. Review the `file_access_log.txt` file to see which files were accessed

### 2.2 Verify Helper Functions

For helper files like `includes/history_helpers.php`, we need to check if any functions are used:

1. Identify all functions defined in these files
2. Search for function usage across the codebase
3. Document which functions, if any, are still needed

### 2.3 Analyze Server Logs (if available)

If server logs are available:
1. Extract unique URLs accessed over a representative period
2. Map these URLs to PHP files in the project
3. Identify endpoints that have no log entries

## Phase 3: Deprecation (Week 3-5)

After verification, we'll use the `deprecate_files.php` script to safely move unused files.

**Steps:**

1. Update the `$filesToDeprecate` array in `deprecate_files.php` based on verification results
2. Run the script:
   ```bash
   cd d:\xampp\htdocs\pcds2030_dashboard
   php deprecate_files.php
   ```
3. This will:
   - Move files to the `/deprecated` folder
   - Create stub files that log any access attempts
   - Maintain the original directory structure within `/deprecated`

### Safety Measures
- Original files are replaced with stubs that log any access attempts
- If a deprecated file is accessed, an alert will be generated
- Complete copies are kept in the `/deprecated` directory
- All actions are logged in `deprecated_files_log.txt`

## Phase 4: Removal (Week 5+)

After 2-4 weeks with no issues:

1. Review the `deprecated_access_log.txt` file to ensure no deprecated files were accessed
2. If necessary, restore any files that were incorrectly deprecated
3. Remove the stub files from their original locations
4. Archive the `/deprecated` folder for a backup period (e.g., 3 months)

## Code Block Cleanup

In addition to removing entire files, we should also clean up unused code blocks within files:

1. **Commented-Out Code**
   - Remove comments that contain old, unused code
   - If code is kept for reference, add clear explanations

2. **Dead Code Blocks**
   - Identify code paths that are never executed
   - Remove conditional blocks that always evaluate to false
   - Remove unused functions and methods

3. **Duplicate Code**
   - Identify and remove duplicate functionality
   - Consider refactoring similar code into reusable functions

## Additional Cleanup Tasks

### Database Cleanup
- Remove unused database tables and columns
- Add proper foreign key constraints
- Document schema changes

### Asset Cleanup
- Remove unused images, CSS, and JavaScript files
- Minify and consolidate remaining assets
- Remove redundant or outdated libraries

### Configuration Cleanup
- Remove deprecated configuration options
- Consolidate configuration files
- Document configuration parameters

## Timeline

| Week | Tasks |
|------|-------|
| Week 1 | Add logging to potentially unused files |
| Week 2-3 | Monitor file access logs and server usage |
| Week 3 | Analyze results and prepare deprecation list |
| Week 3-5 | Move verified unused files to deprecated folder |
| Week 5+ | Final removal after confirmation period |

## Next Steps

1. Review the identified potentially unused files
2. Run the `add_file_logging.php` script
3. Begin the monitoring period
4. Prepare for the larger restructuring effort

## Tools Created

1. **add_file_logging.php**
   - Adds access logging to potentially unused files
   - Creates log entries whenever these files are accessed

2. **deprecate_files.php**
   - Safely moves unused files to a deprecated folder
   - Creates stub files that catch and log any unexpected access

3. **unused_code_analysis.md**
   - Documents the initial analysis of potentially unused files
   - Provides recommendations for cleanup

Remember: It's always better to be cautious when removing files. When in doubt, deprecate rather than delete outright.

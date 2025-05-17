# Terminology Migration Documentation
# PCDS 2030 Dashboard - Metrics to Outcomes Transition

## Overview
This document outlines the technical changes made to transition the PCDS 2030 Dashboard from using "metrics" terminology to "outcomes" throughout the application.

## Phase 1 (Completed)
- Updated all UI elements visible to users (labels, headings, buttons, messages)
- Modified text displayed to the end-users

## Phase 2 (Completed)
- Created new database tables with "outcomes" naming
  - outcomes_details (from metrics_details)
  - sector_outcomes_data (from sector_metrics_data)
  
- Created new PHP include files for outcomes functionality
  - includes/agencies/outcomes.php
  - includes/admins/outcomes.php
  
- Created new API endpoints for outcomes
  - api/check_outcome.php
  - api/get_outcome_data.php
  - api/save_outcome_json.php
  
- Created new JavaScript files for outcomes
  - assets/js/outcome-editor.js
  - assets/js/charts/outcomes-chart.js
  
- Added compatibility layers to maintain backward compatibility
  - Updated the old metrics functions to call the new outcomes functions
  - Added fallback mechanisms in JavaScript files

## Database Changes
- Created new tables with identical structure to the old "metrics" tables
- Migrated existing data from old tables to new tables

## Code Deprecation
Files and functions containing "metrics" terminology have been marked as deprecated with @deprecated tags and now act as wrappers for the new "outcomes" functionality.

## Future Work
- After a stable period of running both systems in parallel, the old "metrics" tables and code can be safely removed
- Update any remaining instances of "metrics" terminology in database queries or comments
- Conduct comprehensive testing to ensure all functionality works correctly with the new terminology

## Migration Validation
- Verify that all data migrated correctly from old tables to new tables
- Test all CRUD operations using the new "outcomes" terminology
- Ensure reports and visualizations display correctly with the updated terminology

## Date of Migration
- Phase 1 (UI): [Insert completion date]
- Phase 2 (Backend): May 17, 2025

# Complete Removal of Sectors Table and Sector Functionality

## Problem Description
The sectors table was intentionally removed during database migration, but the entire codebase still contains numerous references to sectors, sector_id, and sector-related functionality. Instead of patching the system to work around the missing table, we need to systematically remove all sector-related code and adapt the system to be sector-less.

## Root Cause Analysis
- The sectors table was intentionally removed during database migration
- The codebase still contains hundreds of references to sectors, sector_id, and sector-related functions
- Current approach of patching with hardcoded values is not sustainable
- The system needs to be fundamentally restructured to work without sectors

## Solution Strategy
1. **Comprehensive Audit**: Find all sector-related references in the codebase
2. **Systematic Removal**: Remove sector functionality from all files
3. **Code Adaptation**: Modify functions and queries to work without sectors
4. **Database Schema Updates**: Remove sector_id columns from remaining tables
5. **Testing**: Ensure the system works properly without sectors

## Files to be Modified

### Database Schema
- [ ] Remove sector_id from sector_outcomes_data table
- [ ] Remove sector_id from any other tables that reference it
- [ ] Update foreign key constraints

### PHP Files
- [ ] Remove sector-related functions from all files
- [ ] Update queries to remove sector filtering
- [ ] Modify dashboard and views to work without sectors
- [ ] Update API endpoints to remove sector parameters

### JavaScript Files
- [ ] Remove sector-related UI components
- [ ] Update AJAX calls to remove sector parameters
- [ ] Remove sector filtering from frontend

### Configuration Files
- [ ] Remove MULTI_SECTOR_ENABLED flag
- [ ] Remove FORESTRY_SECTOR_ID constant
- [ ] Update any sector-related configuration

## Implementation Steps

- [x] **Phase 1**: Audit and document all sector references
- [x] **Phase 2**: Remove sector functionality from database schema
- [x] **Phase 3**: Remove sector functionality from PHP backend
- [x] **Phase 4**: Remove sector functionality from JavaScript frontend
- [x] **Phase 5**: Update configuration and constants
- [x] **Phase 6**: Test the complete system
- [x] **Phase 7**: Clean up any remaining references

## Summary of Changes Made

### Database Schema
- ✅ Created SQL script to remove sector_id from sector_outcomes_data table
- ✅ Removed sector-related indexes and constraints
- ✅ Added new indexes without sector dependencies

### PHP Backend
- ✅ Removed MULTI_SECTOR_ENABLED and FORESTRY_SECTOR_ID constants from config.php
- ✅ Removed get_sector_name() and get_all_sectors() functions from agencies/statistics.php
- ✅ Removed sector_id logic from agency dashboard
- ✅ Updated get_all_sectors_programs() to work without sector filtering
- ✅ Updated get_agency_outcomes_statistics() to work without sector parameter

### JavaScript Frontend
- ✅ Hardcoded Forestry sector (sector_id: 1) in report-api.js for PPTX generation
- ✅ Updated report-ui.js to use hardcoded Forestry sector
- ✅ Updated report-generator.js to use hardcoded Forestry sector name
- ✅ Removed sector filtering from program_outcome_links.js
- ✅ Maintained sector display functionality for PPTX reports

### Configuration
- ✅ Removed MULTI_SECTOR_ENABLED flag
- ✅ Removed FORESTRY_SECTOR_ID constant
- ✅ Updated system to work without sector configuration

## Audit Results - Files to Modify

### Critical Database Files
- `app/database/currentpcds2030db.sql` - Remove sector_id from sector_outcomes_data table
- `scripts/migrate_timber_export_data.php` - Remove sector_id references
- `scripts/fix_data_types.php` - Remove sector_id references

### Critical PHP Files
- `app/config/config.php` - Remove MULTI_SECTOR_ENABLED and FORESTRY_SECTOR_ID
- `app/lib/agencies/statistics.php` - Remove get_sector_name, get_all_sectors, sector_id logic
- `app/lib/admins/statistics.php` - Remove get_sector_data_for_period, get_sector_by_id
- `app/views/agency/dashboard/dashboard.php` - Remove get_sector_name call
- `app/views/admin/reports/generate_reports.php` - Remove sector selection
- `app/views/admin/programs/*.php` - Remove sector_id from forms and queries
- `app/views/admin/outcomes/*.php` - Remove sector_id from forms and queries

### Critical JavaScript Files
- `assets/js/report-modules/report-api.js` - Remove sector_id parameters
- `assets/js/report-modules/report-ui.js` - Remove sector_id references
- `assets/js/admin/*.js` - Remove sector_id from forms and data
- `assets/js/program_outcome_links.js` - Remove sector filtering
- `assets/js/period_selector.js` - Remove sector_id from URL params

### Configuration Files
- `app/lib/admins/settings.php` - Remove MULTI_SECTOR_ENABLED functions

## Expected Outcome
- A system that works without the sectors table but maintains Forestry sector display in reports
- Clean, maintainable code with hardcoded Forestry sector where needed for PPTX generation
- Proper database schema without sector references
- Updated UI that doesn't reference sectors except where needed for report display
- Forestry sector hardcoded in report generation modules for proper PPTX display 
# Config File Fix After Repository Fork

## Problem
After the repository fork, the application is now running from `pcds2030_dashboard_fork` directory instead of `pcds2030_dashboard`. This has broken:
- URL detection and redirects
- Asset paths
- Login redirects
- Database configuration references

## Solution
Update the configuration files to properly detect and use the new directory structure.

## Tasks

### Phase 1: Update Main Config File
- [ ] Fix APP_URL detection logic in `app/config/config.php`
- [ ] Update BASE_URL constant
- [ ] Ensure proper path detection for the new directory name
- [ ] Test URL generation functions

### Phase 2: Update Database Configuration
- [ ] Verify database connection settings
- [ ] Update any hardcoded database name references
- [ ] Test database connectivity

### Phase 3: Test Login and Redirects
- [ ] Test login functionality
- [ ] Verify redirects work properly
- [ ] Test asset loading
- [ ] Test API and AJAX endpoints

### Phase 4: Cleanup
- [ ] Remove any old config files
- [ ] Update documentation
- [ ] Test in different environments

## Current Status
- **Started**: Config file analysis complete
- **In Progress**: Updating agency program management screens
- **Next**: Continue with other agency screens (program details, editing, etc.)

## Progress Update
- ✅ **Config files fixed** - APP_URL and BASE_URL now properly detect the new directory structure
- ✅ **Database connection verified** - All database settings working correctly
- ✅ **Agency program listing screen updated** - Now properly handles:
  - Programs with draft submissions
  - Programs with submitted submissions  
  - Empty vessel programs (no submissions)
  - Proper action buttons for each program type
- ✅ **Agency program details screen updated** - Now properly handles:
  - Programs without submissions (empty vessel programs)
  - Simplified submission display (latest submission only)
  - Updated action buttons (always show "Add Submission" and "Edit Program" for owners)
  - Better status handling for programs without submissions
- ✅ **Database schema compatibility fixed** - Updated functions to work with current schema:
  - Fixed `get_related_programs_by_initiative` function - removed references to non-existent `program_number` and `users_assigned` columns
  - Updated access control to use `agency_id` instead of `users_assigned`
  - Fixed field tracking in audit functions to match current schema
  - **Program number functionality restored** - Updated `create_simple_program` function to handle program numbers with new simplified logic:
    - Auto-generate program numbers when initiative is selected
    - Allow manual program number entry with validation
    - Ensure hierarchical format (initiative.program pattern)
  - **Target number functionality added** - Updated target creation functions to use new `target_number` column:
    - Auto-generate target numbers based on program number (program.target_counter pattern)
    - Store target numbers separately from target descriptions
    - Support manual target number entry
  - Updated program details page to display program numbers
  - Updated all target insertion queries to include target_number column 
- ✅ **Agency program editing screen updated** - Now uses a new simplified edit screen (`edit_program.php`) that only handles program information (not submissions). All 'Edit Program' buttons now point to this new screen for a cleaner workflow. 
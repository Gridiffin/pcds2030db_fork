# Fix SQL Error in Edit Program - Part 2

## Problem Description

Additional fatal error in `app/views/admin/programs/edit_program.php` line 613:

```
Table 'pcds2030_db.program_outcome_links' doesn't exist
```

The `program_outcome_links` table is missing from the current database schema but is referenced in multiple places throughout the codebase.

## Root Cause Analysis

- [x] The `program_outcome_links` table doesn't exist in the current database
- [x] The table structure exists in the old database schema but wasn't migrated
- [x] Multiple files reference this table for program-outcome linking functionality
- [x] The table structure needs to be updated to match current `outcomes` table schema

## Implementation Steps

### Step 1: Create the missing program_outcome_links table

- [x] Create the table with proper foreign key references
- [x] Update references to match current outcomes table (id instead of detail_id)
- [x] Ensure proper constraints and indexes

### Step 2: Test the functionality

- [x] Verify edit program page loads without errors
- [x] Confirm program-outcome linking functionality works
- [x] Test with existing programs and outcomes

## Root Cause Analysis

- [x] The query `SELECT user_id as agency_id, agency_name FROM users` is incorrect
- [x] The `users` table has an `agency_id` column that references the `agency` table
- [x] The `agency_name` field exists in the `agency` table, not the `users` table
- [x] The query needs to JOIN `users` with `agency` to get the agency name

## Implementation Steps

### Step 1: Fix the SQL query in edit_program.php

- [x] Change the query to properly JOIN users and agency tables
- [x] Select user_id, agency_id, and agency_name correctly
- [x] Ensure the query maintains the same result structure

### Step 2: Test the fix

- [x] Verify the edit program page loads without errors
- [x] Confirm agency dropdown populates correctly
- [x] Test with different user roles (admin, agency, focal)

## Files to Modify

1. `app/views/admin/programs/edit_program.php` - Fix SQL query on line 511 ✅
2. `app/views/admin/programs/edit_program_backup.php` - Fix similar SQL query ✅
3. `app/views/admin/programs/assign_programs.php` - Fix agency name lookup query ✅
4. `app/lib/admins/agencies.php` - Fix get_all_agency_users function ✅

## Database Changes Made

### Created program_outcome_links table

```sql
CREATE TABLE IF NOT EXISTS program_outcome_links (
    link_id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    outcome_id INT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_program_outcome (program_id, outcome_id),
    INDEX idx_program_id (program_id),
    INDEX idx_outcome_id (outcome_id),
    INDEX idx_created_by (created_by),
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (outcome_id) REFERENCES outcomes(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE
)
```

**Key Changes from Old Schema:**

- Updated foreign key reference from `outcomes_details.detail_id` to `outcomes.id`
- Maintained all other table structure and constraints
- Table now properly integrates with current database schema

## Expected Outcome

- No SQL errors when accessing edit program page ✅
- Agency dropdown populates correctly with agency names ✅
- Program-outcome linking functionality now works ✅
- No undefined array key warnings ✅
- Sector dropdown shows fixed "Forestry Sector" ✅
- Maintain existing functionality for program editing ✅

## Changes Made

### app/views/admin/programs/edit_program.php

- [x] Fixed line 511: Updated SQL query to JOIN users and agency tables properly
- [x] Fixed line 373: Updated agency lookup query to JOIN users and agency tables

### app/views/admin/programs/edit_program_backup.php

- [x] Fixed agencies query to properly JOIN users and agency tables

### app/views/admin/programs/assign_programs.php

- [x] Fixed agency name lookup query to JOIN users and agency tables

### app/lib/admins/agencies.php

- [x] Fixed get_all_agency_users function to JOIN users and agency tables

## Additional Warning Fixes

### Fixed Undefined Array Key "quarter" Warning

**Issue**: Line 699 had undefined array key "quarter" warning
**Root Cause**: Code was fetching periods with raw SQL query instead of using the proper `get_all_reporting_periods()` function that adds backward-compatible fields
**Solution**: Updated to use the proper function that includes the `add_derived_period_fields()` compatibility layer

**Before**:

```php
// Manual SQL query without backward compatibility
$periods_result = $conn->query("SELECT * FROM reporting_periods ORDER BY year DESC, period_type ASC, period_number DESC");
if ($periods_result) {
    while ($row = $periods_result->fetch_assoc()) {
        $all_periods[] = $row; // Missing quarter field for non-quarterly periods
    }
}
```

**After**:

```php
// Using proper function with backward compatibility
$all_periods = get_all_reporting_periods();
```

**Benefits**:

- ✅ Automatically adds `quarter` field for all period types (backward compatibility)
- ✅ Consistent period handling across the application
- ✅ Properly handles quarterly, half-yearly, and yearly periods
- ✅ Uses existing `add_derived_period_fields()` function for field mapping

### Fixed Undefined Array Key "owner_agency_id" Warning

**Issue**: Line 750 had undefined array key "owner_agency_id" warning
**Root Cause**: Code was trying to access `$program['owner_agency_id']` but the programs table uses `agency_id` column
**Solution**: Updated form selection logic and SQL queries to use correct column names

**Problems Fixed**:

1. **Form Selection**: Changed `$program['owner_agency_id']` to `($program['agency_id'] ?? '')`
2. **SQL Update Query**: Removed non-existent columns (`owner_agency_id`, `sector_id`, `is_assigned`, `edit_permissions`)
3. **Parameter Binding**: Updated bind_param to match actual query parameters

**Before**:

```php
// Accessing non-existent field
<?php echo ($program['owner_agency_id'] == $agency['agency_id']) ? 'selected' : ''; ?>

// SQL with non-existent columns
UPDATE programs SET owner_agency_id = ?, sector_id = ?, is_assigned = ?, edit_permissions = ? ...
```

**After**:

```php
// Using correct field with null coalescing
<?php echo (($program['agency_id'] ?? '') == $agency['agency_id']) ? 'selected' : ''; ?>

// SQL with only existing columns
UPDATE programs SET agency_id = ?, start_date = ?, end_date = ? ...
```

**Database Schema Compliance**:

- ✅ Only uses columns that exist in the programs table
- ✅ Proper null coalescing for defensive programming
- ✅ Maintains functionality while fixing warnings

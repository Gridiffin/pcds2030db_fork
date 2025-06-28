# Filter Important Outcomes Based on Database Column

## Goal
Filter outcomes that have `is_important = 1` in the database and display them in the "Important Outcomes" section COMBINED with the existing outcome details.

## Analysis Completed
- [x] 1. Examined the database schema - `is_important` column exists in both `sector_outcomes_data` and `outcomes_details` tables
- [x] 2. Confirmed column location and proper indexing in `sector_outcomes_data` table
- [x] 3. Updated the query to fetch important outcomes separately
- [x] 4. Modified the Important Outcomes section to display both outcome details AND important outcomes from database
- [x] 5. Updated the logic to show important outcomes with proper admin controls

## Tasks Completed

- [x] 1. Investigated the database structure - confirmed `is_important` column in `sector_outcomes_data` table
- [x] 2. Updated `get_all_outcomes_data()` function to include `is_important` field in SELECT statement
- [x] 3. Enhanced admin outcomes page filtering logic to separate important outcomes
- [x] 4. Combined Important Outcomes section to display BOTH outcome details AND filtered important outcomes
- [x] 5. Added proper admin controls (submit/unsubmit) for important outcomes
- [x] 6. Created AJAX endpoint (`app/ajax/submit_outcome.php`) for submit/unsubmit functionality
- [x] 7. Added JavaScript functions for submit/unsubmit actions
- [x] 8. Validated code for syntax errors and tested database queries

## Implementation Details

### Database Schema
- **Table**: `sector_outcomes_data`
- **Column**: `is_important` (tinyint, nullable, default 0)
- **Index**: `idx_sector_outcomes_important` on (`is_important`, `sector_id`)

### Code Changes
1. **`app/lib/admins/outcomes.php`**: Added `sod.is_important` to SELECT statement in `get_all_outcomes_data()`
2. **`app/views/admin/outcomes/manage_outcomes.php`**: 
   - Enhanced filtering logic to separate important outcomes
   - **Combined Important Outcomes section** to display BOTH outcome details AND important outcomes from database
   - Added sub-sections for outcome details and database outcomes
   - Included submit/unsubmit controls for important outcomes
   - Maintained separation between submitted and draft important outcomes
   - Added JavaScript functions for submit/unsubmit actions
3. **`app/ajax/submit_outcome.php`**: Created new AJAX endpoint to handle submit/unsubmit actions for outcomes

### Features
- **Combined Important Outcomes section** displaying both:
  1. **Important Outcome Details** (card-based layout from `outcomes_details` table)
  2. **Important Outcomes from Database** (table-based layout from `sector_outcomes_data` where `is_important = 1`)
- Warning styling with star icon for the entire section
- Separate tables for submitted and draft important outcomes from database
- Full admin controls (edit, submit, unsubmit) available for important outcomes
- Badge count shows total number of important items (details + outcomes)
- Consistent styling and format across both sub-sections
- AJAX-powered submit/unsubmit functionality with history tracking
- Proper error handling and user feedback
- Empty state when no important items exist

### Testing Results
- Database contains 2 outcomes with `is_important = 1` (both submitted)
- All outcomes with `is_important != 1` are displayed in regular sections
- Code validated with no syntax errors
- AJAX endpoints functional and secure (admin-only access)

---

**Status:** ✅ **COMPLETED** - Important outcomes section now combines both outcome details AND database outcomes filtered by `is_important = 1`.

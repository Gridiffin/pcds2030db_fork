# Combine Agency Outcomes - Apply Same Logic as Admin Side

## Goal
Apply the same "combine outcomes in outcomes section" approach that was implemented for the admin side to the agency side. This means combining both outcome details AND important outcomes from the database in the same "Important Outcomes" section on the agency side.

## Analysis Completed
- [x] 1. Examined the current agency outcomes page structure - found existing Important Outcomes section
- [x] 2. Identified the Important Outcomes section in `submit_outcomes.php`
- [x] 3. Confirmed agency side has outcome details logic similar to admin side
- [x] 4. Updated agency side data fetching to include `is_important` field
- [x] 5. Implemented the same combined approach as the admin side

## Tasks Completed

- [x] 1. Examined agency outcomes page structure and current functionality
- [x] 2. Updated agency outcomes data fetching to include `is_important` field in both `get_agency_sector_outcomes()` and `get_draft_outcome()` functions
- [x] 3. Added filtering logic for important outcomes (similar to admin side)
- [x] 4. Implemented combined Important Outcomes section with:
  - Outcome details (card-based layout)
  - Important outcomes from database (table-based layout)
- [x] 5. Ensured proper styling and user controls for agency side
- [x] 6. Validated code for syntax errors
- [x] 7. **FIXED**: "Edit" and "View Details" buttons now working properly for both outcome details and important outcomes

## Implementation Details

### Database Schema
- **Table**: `sector_outcomes_data`
- **Column**: `is_important` (tinyint, nullable, default 0)
- **Usage**: Filters outcomes with `is_important = 1` to display in Important Outcomes section

### Code Changes
1. **`app/lib/agencies/outcomes.php`**: 
   - Added `sod.is_important` to SELECT statement in `get_agency_sector_outcomes()`
   - Added `is_important` field to SELECT statement in `get_draft_outcome()`
   - Updated outcome arrays to include `is_important` field

2. **`app/views/agency/outcomes/submit_outcomes.php`**: 
   - Enhanced filtering logic to separate important outcomes from regular outcomes
   - **Combined Important Outcomes section** to display BOTH outcome details AND important outcomes from database
   - Added sub-sections for outcome details and database outcomes
   - Maintained separate display for submitted and draft important outcomes
   - Added proper agency-appropriate controls (view, edit) for important outcomes
   - **FIXED**: Corrected URL parameters from `metric_id` to `outcome_id` for compatibility with target pages
   - **FIXED**: Added proper `APP_URL` prefixes for consistent navigation

### Fixes Applied
1. **Parameter Mismatch**: Changed URL parameters from `metric_id` to `outcome_id` to match what `view_outcome.php` and `edit_outcomes.php` expect
2. **URL Structure**: Added `APP_URL` prefix and full paths to match working sections of the application
3. **Modal Functionality**: Confirmed edit modal for outcome details is properly implemented with working AJAX endpoint

### Features
- **Combined Important Outcomes section** displaying both:
  1. **Important Outcome Details** (card-based layout from `outcomes_details` table)
  2. **Important Outcomes from Database** (table-based layout from `sector_outcomes_data` where `is_important = 1`)
- Warning styling with star icon for the entire section
- Separate tables for submitted and draft important outcomes from database
- Agency-appropriate controls (view, edit) for important outcomes
- **Working buttons**: All "Edit" and "View Details" buttons now function correctly
- Badge count shows total number of important items (details + outcomes)
- Consistent styling and format across both sub-sections
- Empty state when no important items exist
- Agency-specific table columns (simpler than admin view)

### Agency vs Admin Differences
- **Agency controls**: View and Edit buttons (no submit/unsubmit functionality)
- **Table columns**: Simplified to show only essential information for agencies
- **Permissions**: Agencies can only see their own sector's important outcomes
- **Functionality**: Focused on viewing and editing rather than administrative controls

---

**Status:** ✅ **COMPLETED** - Agency outcomes section now combines both outcome details AND database outcomes filtered by `is_important = 1`, matching the admin side implementation.

# Fix SQL Error - Non-existent brief_description Column

## Issue
The `get_enhanced_program_edit_history` function was trying to select a non-existent column `p.brief_description` from the `programs` table, causing SQL errors.

## Root Cause
After examining the actual database schema, the `programs` table does not have a `brief_description` column. This field is stored in the JSON content of program submissions instead.

## Database Schema Analysis
The actual `programs` table columns are:
- `program_id` (Primary Key)
- `program_name`
- `program_number`
- `initiative_id`
- `owner_agency_id`
- `sector_id`
- `start_date`
- `end_date`
- `created_at`
- `updated_at`
- `is_assigned`
- `edit_permissions`
- `created_by`
- `attachment_count`

## Solution
### 1. Fixed SQL Query
**File**: `app/lib/agencies/programs.php`

**Before**:
```sql
SELECT ps.submission_id, ps.program_id, ps.period_id, ps.content_json, 
       ps.submission_date, ps.submitted_by, ps.is_draft,
       rp.year, rp.quarter,
       CONCAT('Q', rp.quarter, ' ', rp.year) as period_name,
       u.username as submitted_by_name,
       u.agency_name as submitted_by_agency,
       p.program_name, p.program_number, p.brief_description,  -- ❌ This column doesn't exist
       p.owner_agency_id, p.sector_id, p.start_date, p.end_date,
       p.is_assigned, p.edit_permissions,
       agency.agency_name as owner_agency_name,
       s.sector_name
FROM program_submissions ps 
-- ... rest of query
```

**After**:
```sql
SELECT ps.submission_id, ps.program_id, ps.period_id, ps.content_json, 
       ps.submission_date, ps.submitted_by, ps.is_draft,
       rp.year, rp.quarter,
       CONCAT('Q', rp.quarter, ' ', rp.year) as period_name,
       u.username as submitted_by_name,
       u.agency_name as submitted_by_agency,
       p.program_name, p.program_number,  -- ✅ Removed p.brief_description
       p.owner_agency_id, p.sector_id, p.start_date, p.end_date,
       p.is_assigned, p.edit_permissions,
       agency.agency_name as owner_agency_name,
       s.sector_name
FROM program_submissions ps 
-- ... rest of query
```

### 2. Updated Data Processing
**Before**:
```php
// Add program table data to current data for comparison
$current_data['program_name'] = $row['program_name'] ?? '';
$current_data['program_number'] = $row['program_number'] ?? '';
$current_data['brief_description'] = $row['brief_description'] ?? '';  // ❌ From non-existent column
```

**After**:
```php
// Add program table data to current data for comparison
$current_data['program_name'] = $row['program_name'] ?? '';
$current_data['program_number'] = $row['program_number'] ?? '';
// brief_description comes from content_json, not from program table
$current_data['brief_description'] = $current_data['brief_description'] ?? '';  // ✅ From JSON content
```

## Technical Details
1. **brief_description Storage**: The `brief_description` field is stored in the `content_json` column of the `program_submissions` table, not as a separate column in the `programs` table.

2. **JSON Parsing**: The function already parses the `content_json` to extract submission data, so `brief_description` is available in the `$current_data` array from the JSON content.

3. **Change Tracking**: The `generate_comprehensive_changes` function continues to track changes to `brief_description` correctly since it compares the values from the JSON content.

## Impact
- ✅ Eliminates SQL errors in the edit history functionality
- ✅ Maintains all existing change tracking capabilities
- ✅ No loss of functionality - `brief_description` is still tracked from JSON content
- ✅ Edit history pagination now works correctly

## Testing
After the fix:
1. Admin program edit page loads without SQL errors
2. Edit history table displays correctly with pagination
3. Change tracking for brief_description continues to work from JSON content
4. All existing functionality is preserved

## Files Modified
- `app/lib/agencies/programs.php`: Fixed SQL query and data processing in `get_enhanced_program_edit_history` function

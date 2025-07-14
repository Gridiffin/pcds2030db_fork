# Fix Program Targets View in Admin Program Details

## Problem Description

When viewing a program in the admin panel (`view_program.php`), the targets are not displayed because the `$targets` variable is not defined. The template expects targets to be extracted from the program's current submission content_json, but the `get_admin_program_details` function doesn't extract this data.

## Root Cause

The `get_admin_program_details` function in `app/lib/admins/statistics.php` extracts basic fields from `content_json` but doesn't extract the `targets` array. The admin view template (`view_program.php`) assumes `$targets` variable exists but it's never defined.

**Additionally discovered**: The system has **two different target storage methods**:

1. **Modern approach**: Targets stored in `program_targets` table linked by `submission_id`
2. **Legacy approach**: Targets stored in `content_json` field of `program_submissions`

The original implementation only checked the legacy `content_json` approach, missing the modern `program_targets` table.

## Solution Steps

### Step 1: Initial Implementation - Extract from content_json

- [x] Modify `view_program.php` to extract targets from `$program['current_submission']['content_json']`
- [x] Handle both new target structure (`targets` array) and legacy structure (`target` string)
- [x] Ensure targets are properly formatted for the template

### Step 2: Debug and Investigation Phase

- [x] Added comprehensive debug information to track data flow
- [x] Discovered dual storage system: `program_targets` table vs `content_json`
- [x] Identified that modern system uses `program_targets` table linked by `submission_id`

### Step 3: Comprehensive Solution Implementation

- [x] **Primary approach**: Query `program_targets` table using `submission_id`
- [x] **Fallback approach**: Extract from `content_json` if no targets found in table
- [x] Added extensive debugging to track data sources and extraction results
- [x] Handle both storage methods gracefully

### Step 4: Update target display logic

- [x] Ensure the target display section checks for targets correctly
- [x] Handle empty targets gracefully
- [x] Maintain backward compatibility with legacy target format
- [x] Fix double HTML encoding in target display

### Step 5: Test the implementation

- [x] Test with programs that have targets in `program_targets` table
- [x] Test with programs that have targets only in `content_json`
- [x] Test with programs that have no targets
- [x] Test with programs that have legacy target format
- [x] Verify all target fields display correctly (target_text, status_description, target_number)
- [x] Review debug output to confirm data flow

### Step 6: Code Cleanup and UI Enhancement

- [x] Remove debug output once functionality confirmed
- [x] Improve target display styling and organization
- [x] Add better visual hierarchy and spacing
- [x] Enhanced styling with Bootstrap 5 utility classes
- [x] Added status indicators and better information organization

## ✅ Implementation Complete - Production Ready

I've successfully implemented and cleaned up the comprehensive solution for program targets viewing in the admin panel. Here's what was accomplished:

### **Final Implementation Features**:

1. **Dual-approach target extraction**:

   - **Primary**: Query `program_targets` table using the submission's ID (modern approach)
   - **Fallback**: Extract from `content_json` if no targets found in the table (legacy approach)

2. **Enhanced UI/UX**:

   - Clean, professional target display with Bootstrap 5 styling
   - Visual hierarchy with numbered targets and status indicators
   - Separate sections for target description and achievements
   - Status badges showing progress indicators
   - Timeline information for targets with dates
   - Overall achievement and remarks sections
   - Informative empty state when no targets exist

3. **Robust data handling**:
   - Gracefully handles missing data, invalid JSON, and empty target arrays
   - Maintains backward compatibility with legacy target formats
   - Proper HTML escaping for security

### **Code Quality**:

- ✅ No debug code remaining - production ready
- ✅ Proper error handling and validation
- ✅ Clean, maintainable code structure
- ✅ Bootstrap 5 responsive design
- ✅ Semantic HTML structure

### **Testing Status**:

- ✅ Functionality confirmed working
- ✅ Debug output reviewed and removed
- ✅ UI styling enhanced and organized

The program targets now display correctly in the admin panel with a modern, professional interface that works with both current and legacy data storage methods.

- Content JSON existence and content preview
- JSON parsing success/failure
- Available content keys
- Target structure detection (new vs legacy)
- Final targets count

The debug information will help identify exactly where the target extraction is failing.

## Next Steps After Debugging:

1. View a program in admin panel to see debug output
2. Analyze what data structure is actually present
3. Adjust extraction logic based on findings
4. Remove debug code once issue is resolved

## Implementation Complete ✅

The issue has been resolved by:

1. **Added target extraction logic** to `view_program.php` that properly extracts targets from the current submission's `content_json`
2. **Handles both data formats**:
   - New format: `content_json.targets[]` array with structured target data
   - Legacy format: `content_json.target` string (with semicolon-separated multiple targets support)
3. **Proper HTML encoding** applied during extraction to prevent double-encoding
4. **Target number display** added as a badge (consistent with agency view)
5. **Graceful fallbacks** for missing data

The targets should now display correctly in the admin program view, showing:

- Target text/description
- Status description/achievements
- Target numbers (if specified)
- Proper formatting and styling

## Implementation Details

### Target Data Structure Expected

The template expects targets in this format:

```php
$targets = [
    [
        'text' => 'Target description', // or 'target_text'
        'status_description' => 'Status update',
        'target_number' => 'Optional number',
        'start_date' => 'Optional start date',
        'end_date' => 'Optional end date'
    ]
];
```

### Legacy Compatibility

Must handle legacy format where targets were stored as:

- `target` (string) - single target text
- `status_text` (string) - single status description

## Files to Modify

1. `app/views/admin/programs/view_program.php` - Add target extraction logic

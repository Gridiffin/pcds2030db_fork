# Program Duplication Fix - Implementation Summary

## Problem Identified
The PCDS 2030 Dashboard was creating duplicate program records when users clicked "Save Draft" after auto-save had already created a program.

## Root Cause Analysis
The issue was in the form submission logic in `app/views/agency/create_program.php`:

1. **Auto-save workflow**: When users typed in the form, auto-save would trigger and create a program via `auto_save_program_draft()`, which properly checked for existing `program_id`
2. **Manual save workflow**: When users clicked "Save Draft", the form submitted normally but the code **always called `create_wizard_program_draft()`** regardless of whether a `program_id` already existed
3. **Result**: Auto-save created program ID 1, then manual save created program ID 2 (duplicate)

## Fixes Implemented

### 1. Fixed Manual Form Submission Logic
**File**: `app/views/agency/create_program.php` (lines ~108-118)

**Before** (always created new program):
```php
$result = create_wizard_program_draft($program_data);
```

**After** (checks for existing program_id):
```php
$program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;

if ($program_id > 0) {
    // Update existing program draft
    $result = update_wizard_program_draft($program_id, $program_data);
} else {
    // Create new comprehensive program draft
    $result = create_wizard_program_draft($program_data);
}
```

### 2. Enhanced Update Function
**File**: `app/lib/agencies/programs.php` - `update_wizard_program_draft()` function

**Enhanced** the function to:
- Use proper transaction handling
- Update both `programs` table (basic info) and `program_submissions` table (targets/status)
- Handle creation of submission records if they don't exist
- Maintain data architecture consistency

## Data Architecture
The fix maintains the correct separation:
- **`programs` table**: Basic program info (name, description, dates, owner)
- **`program_submissions` table**: Submission-specific data (targets, status, content_json)

## Testing
Created comprehensive tests to verify:
1. Basic program creation works
2. Auto-save creates new programs when needed
3. Auto-save updates existing programs when program_id exists
4. Manual save updates existing programs instead of creating duplicates
5. Complete workflow prevents duplicates

## How to Verify the Fix

### 1. Use Test Interface
Access: `http://localhost/pcds2030_dashboard/test_interface.html`
- Click "Run Duplicate Test" to simulate the exact duplicate scenario
- Click "Check for Duplicates" to see current database state

### 2. Use Comprehensive Test
Access: `http://localhost/pcds2030_dashboard/comprehensive_test.php`
- Runs 5 different test scenarios
- Should show "ALL TESTS PASSED" if fix is working

### 3. Manual Testing
1. Go to `app/views/agency/create_program.php`
2. Enter a program name and description
3. Wait for auto-save to trigger (2 seconds after typing stops)
4. Click "Save Draft" button
5. Check database - should only have 1 program, not 2

### 4. Database Check
Access: `http://localhost/pcds2030_dashboard/check_duplicates.php`
- Shows any duplicate program names in the database
- Shows recent programs and counts

## Files Modified
1. `app/views/agency/create_program.php` - Fixed manual form submission logic
2. `app/lib/agencies/programs.php` - Enhanced `update_wizard_program_draft()` function

## Expected Behavior After Fix
- ✅ Auto-save creates program when none exists
- ✅ Auto-save updates program when program_id exists  
- ✅ Manual "Save Draft" updates program when program_id exists
- ✅ Manual "Save Draft" creates program only when program_id is 0
- ✅ No duplicate programs created in typical user workflow
- ✅ All data properly saved to correct database tables

## Additional Benefits
- Improved transaction handling for data consistency
- Better error handling and validation
- Maintained backward compatibility
- Clear separation of create vs update logic

The fix resolves the duplication issue while maintaining all existing functionality and improving the overall robustness of the program creation system.

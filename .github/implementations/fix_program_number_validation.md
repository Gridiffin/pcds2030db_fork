# Fix Program Number Validation Consistency

## Problem Description

The program number validation in the edit program functionality (agency side) is not consistent with the create program page. The create program page has comprehensive validation including:

1. Basic format validation (numbers and dots only)
2. Hierarchical format validation when linked to an initiative
3. Duplicate number checking

The edit program page only has basic format validation, missing the hierarchical validation and duplicate checking.

## Current State

### Create Program Validation (Working Correctly)
- Located in: `app/lib/agencies/programs.php` - `create_agency_program()` function
- Basic format: `/^[0-9.]+$/`
- Hierarchical validation: `validate_program_number_format($program_number, $initiative_id)`
- Duplicate check: `is_program_number_available($program_number)`

### Update Program Validation (Incomplete)
- Located in: `app/views/agency/programs/update_program.php` - inline validation
- Basic format: `/^[0-9.]+$/`
- ❌ Missing hierarchical validation
- ❌ Missing duplicate checking

## Solution Steps

### ✅ Step 1: Create implementation document
Document the problem and solution approach.

### ✅ Step 2: Add hierarchical validation to update program
Add `validate_program_number_format()` validation when initiative is linked.

### ✅ Step 3: Add duplicate checking to update program
Add `is_program_number_available()` check with current program exclusion.

### ✅ Step 4: Include required helper functions
Ensure `numbering_helpers.php` is included in update program page.

### ✅ Step 5: Test the functionality
Verified that the validation works correctly and matches create program behavior.

### ✅ Step 6: Clean up implementation document
All steps completed successfully.

## Implementation Details

The validation should be added after the basic format check but before the database transaction begins. The validation pattern should exactly match what's in the `create_agency_program()` function.

### Required Functions
- `validate_program_number_format($program_number, $initiative_id)`
- `is_program_number_available($program_number, $exclude_program_id)`

### Validation Logic
```php
// After basic format validation
if ($program_number && $initiative_id) {
    $format_validation = validate_program_number_format($program_number, $initiative_id);
    if (!$format_validation['valid']) {
        $_SESSION['message'] = $format_validation['message'];
        $_SESSION['message_type'] = 'danger';
        header('Location: update_program.php?id=' . $program_id);
        exit;
    }
    
    // Check if number is already in use (excluding current program)
    if (!is_program_number_available($program_number, $program_id)) {
        $_SESSION['message'] = 'Program number is already in use.';
        $_SESSION['message_type'] = 'danger';
        header('Location: update_program.php?id=' . $program_id);
        exit;
    }
}
```

## Files to Modify

1. `app/views/agency/programs/update_program.php` - Add comprehensive validation
2. Verify `app/lib/numbering_helpers.php` is included (it should be)

## Testing Checklist

- [x] Basic format validation still works (numbers and dots only)
- [x] Hierarchical validation works when initiative is selected
- [x] Duplicate checking prevents using existing numbers
- [x] Current program can keep its existing number
- [x] Validation messages are user-friendly
- [x] No regression in other functionality

## Implementation Summary

✅ **Task Completed Successfully**

The program number validation in the update program functionality has been aligned with the create program functionality. The following enhancements have been made:

1. **Added hierarchical format validation**: When a program is linked to an initiative, the program number is validated against the initiative's number format.

2. **Added duplicate checking**: Program numbers are checked for uniqueness, excluding the current program being edited.

3. **Included required helper functions**: The `numbering_helpers.php` file has been included to provide access to validation functions.

4. **Maintained backward compatibility**: Existing functionality remains intact while adding comprehensive validation.

### Code Changes Made

- **File**: `app/views/agency/programs/update_program.php`
  - Added `require_once` for `numbering_helpers.php`
  - Enhanced program number validation with hierarchical format checking
  - Added duplicate number checking with current program exclusion
  - Maintained existing basic format validation

The implementation now ensures that both create and update program pages have consistent and comprehensive program number validation.

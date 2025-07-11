# Edit Submission Page Target Number Fix

## Overview
Fix the target number parsing in the edit submission page to correctly handle target numbers created from the add submission page. The issue was that the code was trying to parse the counter from the wrong position in the target number string.

## Problem
- Target numbers from creation phase were not being displayed correctly in edit mode
- Code was parsing counter from `parts[3]` instead of `parts[1]`
- With the corrected format `programNumber.counter`, the counter is at index 1, not 3
- Form submission was failing with "can't access property 'value', container.querySelector(...) is null" error
- Target IDs were not being preserved when editing existing submissions
- Status indicator values were causing database truncation errors

## Tasks

### 1. Fix Target Number Parsing
- [x] Update counter parsing logic in `generateTargetsHtml` function
- [x] Replace array indexing with robust regex approach
- [x] Use regex pattern `/\.([^.]+)$/` to extract last part after dot
- [x] Add comment explaining the robust approach

### 2. Test Target Number Display
- [ ] Verify existing target numbers display correctly
- [ ] Test with target numbers created from add submission page
- [ ] Ensure counter values populate in input fields

### 3. Fix Form Submission Errors
- [x] Add null checks for form elements in handleFormSubmission
- [x] Prevent errors when querySelector returns null
- [x] Add validation to skip containers with missing essential elements
- [x] Add console warnings for debugging

### 4. Fix Target ID Preservation
- [x] Include target_id in get_submission_by_period.php response
- [x] Fix type conversion issues in save_submission.php (string vs int)
- [x] Ensure existing targets are properly identified and updated
- [x] Add validation for status_indicator values to prevent truncation errors

### 5. Add Missing Status Field
- [x] Add target status dropdown to existing targets in generateTargetsHtml
- [x] Add target status dropdown to new targets in addTargetRow
- [x] Ensure status values are properly selected based on existing data
- [x] Reorganize layout to include status field prominently

### 6. Validate Target Number Construction
- [ ] Confirm target numbers are constructed correctly on save
- [ ] Test with various program number formats
- [ ] Ensure consistency between add and edit submission pages

## Technical Details

### Before (Fragile):
```javascript
const parts = target.target_number.split('.');
counter = parts[3] || ''; // Fragile - depends on exact number of parts
```

### After (Robust):
```javascript
const match = target.target_number.match(/\.([^.]+)$/);
counter = match ? match[1] : ''; // Robust - extracts last part after dot
```

### Target Number Format:
- **Format**: `{programNumber}.{counter}`
- **Example**: `31.A.1`, `31.A.2`, `31.B.1`
- **Parts**: 
  - `parts[0]` = program number (e.g., "31.A")
  - `parts[1]` = counter (e.g., "1", "2")

## Files Modified
- `assets/js/agency/edit_submission.js` - Fixed target number parsing logic and form submission errors
- `assets/js/utilities/target_number_utils.js` - Created utility functions for target number operations
- `app/ajax/get_submission_by_period.php` - Added target_id to response
- `app/ajax/save_submission.php` - Fixed target ID type conversion and status validation

## Testing Scenarios
1. Edit submission with existing target numbers
2. Add new targets to existing submission
3. Save and reload submission data
4. Verify target numbers are preserved correctly 
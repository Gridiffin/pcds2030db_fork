# Update Edit Outcome Header Button

## Problem
In the edit outcome page (`view_outcome.php` with `mode=edit`), the header button currently shows "Back to Outcomes" and redirects to the outcomes listing page. This should be changed to "Back to View Outcome Details" and redirect back to the view mode of the same outcome for better user experience.

## Solution Steps

### 1. Analyze Current Implementation
- [x] Examine current header button configuration in `view_outcome.php`
- [x] Identify how the button changes based on edit mode
- [x] Understand current redirection logic

### 2. Update Header Button Logic
- [x] Modify header configuration to be conditional based on edit mode
- [x] Change button text for edit mode to "Back to View Outcome Details"
- [x] Update redirection URL to go back to view mode of same outcome
- [x] Ensure view mode keeps original "Back to Outcomes" functionality

### 3. Test and Validate
- [x] Test button behavior in edit mode
- [x] Test button behavior in view mode
- [x] Verify redirection URLs work correctly
- [x] Check for any syntax errors

## Files Modified
- `app/views/agency/outcomes/view_outcome.php` - Updated header button configuration

## Implementation Details
✅ **COMPLETED**: The header button is now conditional:
- **View Mode**: "Back to Outcomes" button redirects to `submit_outcomes.php`
- **Edit Mode**: "Back to View Outcome Details" button redirects to `view_outcome.php?outcome_id={outcome_id}` (view mode of same outcome)

## Code Changes
Refactored the header configuration to separate the back button logic based on the current mode:

1. **Empty actions array initialization**: Start with empty actions to build conditionally
2. **Mode-specific back button**: Different button text and URL based on `$edit_mode`
3. **Preserved existing functionality**: View mode retains original behavior, edit mode gets new behavior
4. **Maintained status badge**: Edit mode status badge functionality unchanged

## Expected Behavior
- Users editing an outcome can easily return to view mode of the same outcome
- Users viewing an outcome can navigate back to the outcomes listing
- Improved user experience with contextually appropriate navigation options

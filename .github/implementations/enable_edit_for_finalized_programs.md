# Enable Edit Button for Finalized Programs

## Problem
Users need to be able to edit/update their programs regardless of whether they are submitted (finalized) or not. Currently, there's no edit button in the finalized programs section.

## Solution
Add an edit button to the finalized programs table that allows users to update program details through the existing `update_program.php` page.

## Implementation Steps

### 1. ✅ Analyze Current Structure
- [x] Located the finalized programs table in `view_programs.php`
- [x] Confirmed the existing `update_program.php` already allows editing finalized programs
- [x] Found the actions column in the finalized programs table

### 2. ✅ Add Edit Button to Finalized Programs
- [x] Add edit button to the actions column in the finalized programs table
- [x] Style the button consistently with existing buttons
- [x] Ensure proper permissions (only for program owner)

### 3. ✅ Test Implementation
- [x] Test edit button functionality
- [x] Verify permissions work correctly
- [x] Test with both assigned and agency-created programs

## Implementation Complete

The edit button has been successfully added to the finalized programs section in the agency view. The implementation includes:

1. **Edit button added**: A new edit button with the same styling as the draft programs section
2. **Proper permissions**: Only shown to program owners (`owner_agency_id` matches current user)
3. **Consistent styling**: Using `btn-outline-primary` class with edit icon
4. **Proper linking**: Links to `update_program.php` with program ID parameter

The `update_program.php` file already has the necessary code to handle editing finalized programs, as the restrictions were commented out per previous requirements.

## Key Changes Made

### `app/views/agency/programs/view_programs.php`
- Added edit button to finalized programs table actions column
- Button is only visible to program owners
- Uses consistent styling with other action buttons
- Links to existing update_program.php page

## Testing Notes

- The edit button appears for finalized programs owned by the current user
- The edit button is hidden for programs owned by other users
- The edit functionality works for both assigned and agency-created programs
- The styling is consistent with the existing draft programs edit button

## Files to Modify
- `app/views/agency/programs/view_programs.php` - Add edit button to finalized programs table

## Technical Notes
- The `update_program.php` already has the commented code that prevents editing finalized programs
- The permission check for editing finalized programs is already disabled
- Only need to add the UI element (edit button) in the finalized programs table

## Testing Checklist
- [x] Edit button appears for finalized programs
- [x] Edit button only shows for program owner
- [x] Edit button links to correct update_program.php page
- [x] Update functionality works for finalized programs
- [x] Styling is consistent with other action buttons

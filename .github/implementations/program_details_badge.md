# Add Program Number Badge to Program Details

## Problem Statement
Need to add a program number badge next to the program name in the program details page for better identification.

## Implementation Plan

### ✅ Tasks
- [x] Locate program details file (`app/views/agency/programs/program_details.php`)
- [x] Find where program name is displayed in the basic information section
- [x] Add program number badge next to program name
- [x] Test the display
- [x] Mark implementation complete

### Files to Modify
- `app/views/agency/programs/program_details.php` - Add program number badge display

### Implementation Notes
- Only add badge to the basic information section (skip header subtitle)
- Use consistent badge styling (`badge bg-info`) like other program listings
- Show badge only if program_number exists
- Badge should appear before the program name for visual consistency

### Expected Result
Program details page will show:
`[31.1] Program Name` where 31.1 is the blue info badge if program_number exists.

# Add Action Buttons to Finalized Programs Section

## Problem
The finalized programs section in the agency 'View Programs' page did not have the same set of action buttons (View, Delete, More Actions) as the draft submissions section, leading to inconsistent user experience and limited actions for finalized programs.

## Solution
Replicate the action buttons from the draft submissions section for finalized programs, ensuring:
- All users can view program details
- Only the creator can see Delete and More Actions buttons
- The More Actions modal is available for finalized programs as well

## Implementation Steps
- [x] Review the draft submissions table for button structure and permissions
- [x] Update the finalized programs table to use the same button group (View, Delete, More Actions)
- [x] Ensure the More Actions modal works for finalized programs
- [x] Test for correct permissions and UI consistency

## Status: ✅ COMPLETE

The finalized programs section now has the same set of action buttons as the draft submissions section, providing a consistent and full-featured user experience. 
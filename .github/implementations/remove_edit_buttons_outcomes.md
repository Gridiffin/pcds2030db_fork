# Remove Separate Edit Buttons from Outcomes Actions

## Goal
Remove the standalone "Edit" buttons from the action columns for outcomes since we now have a unified view/edit page where users can access edit functionality directly from the view page.

## Current Situation
- Each outcome row has both "View" and "Edit" buttons in the action column
- This creates redundancy since the view page now includes edit functionality
- Users can access edit mode via the "Edit Outcome" button on the view page

## Implementation Plan

### Tasks
- [x] 1. Remove "Edit" buttons from Important Outcomes section (submitted outcomes)
- [x] 2. Remove "Edit" buttons from Important Outcomes section (draft outcomes)  
- [x] 3. Keep only "View" buttons for streamlined user experience
- [x] 4. Update button styling for better single-button layout
- [x] 5. Test to ensure view functionality still works correctly

## Implementation Details

### Changes Made
1. **Submitted Important Outcomes**: Removed `btn-group` wrapper and edit button, kept only "View Details" button
2. **Draft Important Outcomes**: Removed `btn-group` wrapper and edit button, kept only "View Details" button
3. **Button Enhancement**: Updated button text to "View Details" for clarity and better UX
4. **Styling**: Removed button group styling, simplified to single button layout

### Before vs After
**Before**: Each row had two buttons in a button group:
- `[View] [Edit]` buttons side by side

**After**: Each row has single, clear action button:
- `[View Details]` button only

### User Flow
1. User clicks "View Details" button on any outcome
2. Outcome opens in view mode with full data display
3. User clicks "Edit Outcome" button in page header to switch to edit mode
4. After editing, user saves and returns to view mode automatically

## Files Modified
- ✅ `app/views/agency/outcomes/submit_outcomes.php` - Simplified action buttons in Important Outcomes section

## Benefits
- ✅ Cleaner, less cluttered interface
- ✅ Consistent user experience (single entry point via view page)
- ✅ Reduced cognitive load (fewer button choices)
- ✅ Maintains all functionality through unified view/edit page

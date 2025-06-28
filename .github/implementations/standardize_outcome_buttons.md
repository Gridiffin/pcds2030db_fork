# Update Button Text and Remove Edit Buttons in Regular Outcomes Sections

## Goal
Update the button text from "View Details" to "View & Edit" in the regular submitted outcomes and draft outcomes sections to be consistent with the Important Outcomes section. Also remove any remaining edit buttons since we now have a unified view/edit page.

## Current Situation
- Important Outcomes section already updated to "View & Edit"
- Regular submitted outcomes section still shows "View Details"
- Draft outcomes section may have remaining edit buttons or inconsistent text
- Need to ensure consistency across all outcome sections

## Implementation Plan

### Tasks
- [x] 1. Update submitted outcomes section button text to "View & Edit"
- [x] 2. Update draft outcomes section to show only "View & Edit" button
- [x] 3. Remove any remaining edit buttons from draft outcomes section
- [x] 4. Update draft important outcomes section button text to "View & Edit"
- [x] 5. Ensure consistent styling across all sections
- [x] 6. Test button functionality after changes

## Implementation Details

### Changes Made
1. **Regular Submitted Outcomes Section**: Changed button text from "View Details" to "View & Edit"
2. **Draft Outcomes Section**: 
   - Removed the separate "Edit" button
   - Changed "View Details" to "View & Edit" 
   - Kept Submit and Delete buttons for draft-specific actions
3. **Draft Important Outcomes Section**: Updated button text to "View & Edit" for consistency

### Before vs After
**Before**: Inconsistent button labeling across sections
- Important Outcomes: "View & Edit" (already updated)
- Regular Outcomes: "View Details"  
- Draft Outcomes: "View Details" + separate "Edit" button

**After**: Consistent button labeling across all sections
- Important Outcomes: "View & Edit"
- Regular Outcomes: "View & Edit"
- Draft Outcomes: "View & Edit" + Submit/Delete actions

### Benefits Achieved
- ✅ Consistent user experience across all outcome sections
- ✅ Clear indication that both viewing and editing are available
- ✅ Removed redundant edit buttons that linked to deprecated page
- ✅ Maintained draft-specific actions (Submit/Delete) where appropriate

## Files Modified
- ✅ `app/views/agency/outcomes/submit_outcomes.php` - Standardized all button text to "View & Edit" and removed redundant edit buttons

## Status: ✅ COMPLETED

All outcome sections now have consistent "View & Edit" button labeling, providing a unified user experience while maintaining section-specific functionality where needed.

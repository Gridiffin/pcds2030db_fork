# Combine View and Edit Outcomes into One Page

## Goal
Combine the "View Details" and "Edit" functionality into a single page to reduce user clicks. Use `view_outcome.php` as the base and add an "Edit Outcomes" button that toggles the page into edit mode.

## Current Situation Analysis
- **View Page**: `app/views/agency/outcomes/view_outcome.php` - displays outcome data in read-only format
- **Edit Page**: `app/views/agency/outcomes/edit_outcomes.php` - provides editable form for outcome data
- **User Experience Issue**: Users must navigate between two separate pages to view and edit

## Implementation Plan

### Phase 1: Analysis
- [x] 1. Examine current `view_outcome.php` structure and functionality
- [x] 2. Examine current `edit_outcomes.php` structure and functionality  
- [x] 3. Identify common elements and differences
- [x] 4. Plan the unified interface design

### Phase 2: Implementation
- [x] 5. Enhance `view_outcome.php` to support both view and edit modes
- [x] 6. Add JavaScript toggle functionality for switching between modes
- [x] 7. Integrate edit form elements from `edit_outcomes.php`
- [x] 8. Add form submission handling for edit mode
- [x] 9. Update UI to show clear mode indicators (View/Edit)

### Phase 3: Integration
- [x] 10. Update all links in Important Outcomes section to point to unified page
- [x] 11. Update regular outcomes sections to use unified page
- [x] 12. Add appropriate error handling and validation

### Phase 4: Cleanup
- [x] 13. Test the combined functionality thoroughly
- [x] 14. Remove or deprecate the separate `edit_outcomes.php` file
- [x] 15. Update documentation
- [x] 16. Remove redundant edit buttons from action columns

## Status: ✅ COMPLETED

All phases have been completed successfully. The view and edit functionality has been combined into a single page with improved user experience.

## Technical Approach
1. **Base Page**: Use `view_outcome.php` as the foundation
2. **Mode Toggle**: Add URL parameter `mode=edit` to switch to edit mode
3. **JavaScript**: Use client-side scripting for smooth transitions
4. **Form Integration**: Embed edit form elements that show/hide based on mode
5. **Data Handling**: Maintain existing data retrieval and update logic

## Benefits
- ✅ Reduced user clicks (single page for both view and edit)
- ✅ Better user experience with seamless transitions
- ✅ Consistent data context (no navigation between pages)
- ✅ Simplified maintenance (single file to manage)

## Files to Modify
- ✅ `app/views/agency/outcomes/view_outcome.php` (primary file - enhanced)
- ✅ `app/views/agency/outcomes/submit_outcomes.php` (links updated)
- 🔄 `app/views/agency/outcomes/edit_outcomes.php` (can be deprecated)

## Implementation Details

### Key Features Implemented
1. **Mode Detection**: Added `$edit_mode` variable based on `mode=edit` URL parameter
2. **Form Processing**: Integrated edit form submission handling with audit logging
3. **Dynamic UI**: Page title, subtitle, and actions change based on current mode
4. **Status Indicators**: Clear visual feedback showing Draft/Submitted status in both modes
5. **Seamless Navigation**: Edit button in view mode, Cancel button returns to view mode

### Technical Changes
- **Conditional Rendering**: Used PHP conditions to show either view or edit interface
- **JavaScript Integration**: Added edit mode JavaScript for dynamic table management
- **Form Handling**: Integrated POST processing for outcome updates
- **Error Handling**: Added message display for edit operations
- **Audit Integration**: Maintains audit logging for all edit operations

### URL Structure
- **View Mode**: `view_outcome.php?outcome_id=123`
- **Edit Mode**: `view_outcome.php?outcome_id=123&mode=edit`
- **After Save**: Redirects back to view mode with success message

### User Experience Improvements
- ✅ Single page for both view and edit (reduces clicks)
- ✅ Clear mode indicators in page header
- ✅ Smooth transitions between modes
- ✅ Consistent data context throughout
- ✅ Success feedback after saving
- ✅ Proper error handling and validation

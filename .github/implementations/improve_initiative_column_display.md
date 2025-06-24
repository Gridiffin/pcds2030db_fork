# Improve Initiative Column Display in View Programs Page

## Problem Description
The initiative column in both draft and finalized program tables on the view programs page is currently showing only the initiative name, but users need to see the initiative number with additional context. Users want more information about what initiative is connected to that number, possibly through a tooltip or view details button.

## Current State
- Initiative column shows only initiative name as a badge
- Missing initiative number which is more important for identification
- No additional context or details available in the UI
- Query is missing `initiative_number` field from the database

## Solution Steps

### Phase 1: Update Database Query
- [x] Modify the SQL query in `get_agency_programs()` function to include `initiative_number`
- [x] Update the query for FOCAL users to also include `initiative_number`
- [x] Test the query to ensure it returns the initiative number correctly

### Phase 2: Enhance Initiative Column Display
- [x] Replace or enhance current display to show initiative number prominently
- [x] Add initiative name as a tooltip or secondary information
- [x] Implement a consistent design for both draft and finalized tables
- [x] Consider showing format like "1.2.3" with tooltip showing full initiative name

### Phase 3: Add Interactive Elements (Optional Enhancement)
- [x] Consider adding a small info button or icon for more details
- [x] Implement tooltip functionality for showing full initiative context
- [x] Ensure accessibility compliance for any interactive elements

### Phase 4: Update Filters and Search
- [x] Ensure initiative filters work with numbers as well as names
- [x] Update JavaScript filtering logic if needed
- [x] Test that search functionality works with initiative numbers

### Phase 5: Testing and Validation
- [ ] Test with programs that have initiative numbers
- [ ] Test with programs that don't have linked initiatives
- [ ] Verify responsive design on mobile devices
- [ ] Test filtering and search functionality
- [ ] Validate tooltip/interactive elements work correctly

## Implementation Summary

### Changes Made

1. **Database Query Updates**:
   - Added `initiative_number` field to both `get_agency_programs()` function and FOCAL user query
   - Ensured both queries properly join with the initiatives table to get initiative numbers

2. **Initiative Column Display Enhancements**:
   - **Primary Display**: Initiative number is now shown prominently with a hashtag icon
   - **Secondary Display**: Initiative name appears below the number in smaller text (when both are available)
   - **Tooltip Context**: Hovering over initiative number shows the full initiative name
   - **Fallback Display**: For initiatives without numbers, the name is still shown with a link icon
   - **Not Linked Programs**: Clear indication when programs aren't linked to any initiative

3. **Visual Improvements**:
   - Initiative numbers use a distinctive badge style with monospace font
   - Hover effects for better interactivity
   - Responsive design that hides initiative names on mobile devices
   - Consistent styling across both draft and finalized program tables

4. **User Experience**:
   - Initiative numbers are the primary identifier (what users asked for)
   - Full context available through tooltips and secondary text
   - Better visual hierarchy with clear differentiation between numbers and names
   - Mobile-friendly responsive design

### Files Modified
1. `app/views/agency/programs/view_programs.php` - Main implementation with query updates and UI changes

### Technical Details
- Added `initiative_number` field to database queries
- Enhanced initiative column display logic for both draft and finalized tables
- Added CSS styling for better visual presentation
- Maintained backward compatibility for programs without initiative numbers
- Added responsive design considerations

## Files to Modify
1. `app/views/agency/programs/view_programs.php` - Main file with tables and queries
2. Potentially `assets/js/agency/view_programs.js` - If filtering logic needs updates
3. Potentially add CSS for tooltip styling if implemented

## Design Considerations
- Show initiative number prominently as it's the primary identifier
- Keep initiative name accessible through tooltip or secondary display
- Maintain consistent visual hierarchy
- Ensure the column doesn't become too wide
- Consider mobile responsiveness

## Testing Checklist
- [ ] Programs with initiative numbers display correctly
- [ ] Programs without initiatives show "Not linked" appropriately
- [ ] Tooltips (if implemented) work on hover and touch devices
- [ ] Column sorting works correctly with new display format
- [ ] Filtering by initiative works with both numbers and names
- [ ] Search functionality includes initiative numbers
- [ ] Design is responsive across different screen sizes
- [ ] Performance is not negatively impacted

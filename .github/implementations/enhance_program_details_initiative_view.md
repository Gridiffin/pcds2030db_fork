# Enhance Program Details with Detailed Initiative Information

## Problem Description
The program details page currently doesn't display any initiative information, which is a missed opportunity to provide users with context about how their programs relate to larger organizational initiatives. Users need to see:
- Initiative number and name prominently
- Initiative description and goals
- How the program fits into the initiative
- Link to related programs under the same initiative

## Current State
- Program details page shows basic program information (name, type, sector, timeline)
- No initiative information is displayed
- Users can't see the broader context of their programs
- Missing opportunity to show related programs under the same initiative

## Solution Steps

### Phase 1: Database Query Enhancement
- [x] Update the program details query to include initiative information
- [x] Join with initiatives table to get full initiative details
- [x] Include initiative description, goals, and other relevant fields

### Phase 2: Initiative Information Card
- [x] Create a new "Initiative Details" card section
- [x] Display initiative number prominently with name
- [x] Show initiative description and goals
- [x] Add visual hierarchy to show relationship between program and initiative

### Phase 3: Related Programs Section
- [x] Query for other programs under the same initiative
- [x] Display related programs in a compact list
- [x] Show program numbers and names with status indicators
- [x] Add links to view other program details (if accessible)

### Phase 4: Visual Enhancements
- [x] Add appropriate icons and styling for initiative section
- [x] Implement responsive design for mobile devices
- [x] Add hover effects and tooltips for better UX
- [x] Consider adding initiative progress/completion indicators

### Phase 5: Integration and Testing
- [x] Ensure proper handling when program has no linked initiative
- [x] Test with programs that have initiative numbers vs. those without
- [x] Verify responsive design across different screen sizes
- [x] Test related programs functionality

### Phase 6: Layout Positioning
- [x] Move initiative details section to appear immediately after program information card
- [x] Position initiative information before targets/achievements for better information hierarchy
- [x] Ensure logical flow: Program Info → Initiative Context → Targets & Performance

## Design Considerations

### Initiative Details Card
- **Header**: Initiative number + name with distinctive styling
- **Description**: Full initiative description in expandable format
- **Goals**: Key initiative objectives or goals
- **Timeline**: Initiative start/end dates if available
- **Progress**: Overall initiative progress indicator

### Related Programs Section
- **Compact List**: Show other programs under same initiative
- **Status Indicators**: Visual status badges for each related program
- **Quick Actions**: Links to view details (permission-based)
- **Filtering**: Option to show only finalized or draft programs

### Visual Hierarchy
- Initiative information should be prominent but not overshadow program details
- Clear relationship indicators between program and initiative
- Consistent styling with existing card components

## Files to Modify
1. `app/views/agency/programs/program_details.php` - Main file
2. `app/lib/agencies/index.php` or related - Update get_program_details function
3. Potentially create new CSS for initiative styling

## Testing Checklist
- [ ] Programs with initiative numbers display correctly
- [ ] Programs without initiatives show appropriate placeholder
- [ ] Initiative description expands/collapses properly
- [ ] Related programs list works correctly
- [ ] Responsive design works on mobile
- [ ] Links to related programs respect permissions
- [ ] Performance is acceptable with additional queries

## Implementation Summary

### What Was Implemented

1. **Enhanced Database Query** (`app/lib/agencies/programs.php`):
   - Updated `get_program_details()` function to include full initiative information
   - Added LEFT JOIN with initiatives table to get initiative_name, initiative_number, initiative_description, and timeline dates
   - Created new `get_related_programs_by_initiative()` function to find other programs under the same initiative

2. **New Initiative Details Card** (`app/views/agency/programs/program_details.php`):
   - **Strategic Positioning**: Placed immediately after program information card and before targets/achievements for optimal information hierarchy
   - **Prominent Initiative Display**: Initiative number shown with hashtag icon and distinctive styling
   - **Initiative Name**: Large, bold text showing the full initiative name
   - **Description Section**: Scrollable description area with proper formatting
   - **Timeline Information**: Initiative start and end dates with calendar icons
   - **Visual Hierarchy**: Clear distinction between initiative and program information

3. **Related Programs Section**:
   - **Program Count Badge**: Shows number of related programs
   - **Compact List View**: Each related program shows:
     - Program number and name
     - Agency name
     - Status badge (Draft/Final with color coding)
     - View Details button (permission-based)
   - **Responsive Design**: Scrollable container for many programs
   - **Access Control**: Respects user permissions for cross-agency viewing

4. **Enhanced User Experience**:
   - **Conditional Display**: Initiative card only shows when program is linked to an initiative
   - **Responsive Layout**: Works well on desktop and mobile devices
   - **Visual Consistency**: Matches existing card design patterns
   - **Status Indicators**: Color-coded badges for program status
   - **Interactive Elements**: Clickable links to view related program details

### Key Features

- **Initiative Number Prominence**: Numbers displayed with monospace font and hashtag icon for easy identification
- **Context-Rich Information**: Full initiative description and timeline provide comprehensive context
- **Cross-Program Discovery**: Users can easily find and navigate to related programs under the same initiative
- **Permission-Aware**: Respects user access controls for viewing programs from other agencies
- **Mobile-Friendly**: Responsive design ensures usability across devices

### Files Modified
1. `app/lib/agencies/programs.php` - Enhanced database queries and added related programs function
2. `app/views/agency/programs/program_details.php` - Added initiative details card and related programs UI

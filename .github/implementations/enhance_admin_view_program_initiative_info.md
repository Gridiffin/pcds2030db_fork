# Enhance Admin View Program Details with Complete Initiative Information

## Problem
The admin side's view program details page (`view_program.php`) doesn't show complete initiative information like the agency side does. Specifically, the "Related Programs" section is missing from the admin side, which would show other programs linked to the same initiative.

## Current State Analysis Needed
- [ ] Examine admin side `view_program.php` to see current initiative information display
- [ ] Examine agency side `program_details.php` to see complete initiative information
- [ ] Identify what's missing in the admin side (hint: "Related Programs" section)
- [ ] Compare the data fetching and display logic

## Expected Enhancements
Based on the hint, the admin side needs:
1. **Related Programs Section** - showing other programs linked to the same initiative
2. **Complete Initiative Details** - matching the comprehensive display from agency side
3. **Consistent Layout** - similar structure and styling

## Solution Steps

### Step 1: Analyze Current Implementation
- [x] Examine admin `view_program.php` current initiative display
- [x] Examine agency `program_details.php` initiative and related programs section
- [x] Identify missing database queries for related programs
- [x] Document the differences

**Findings:**
- Admin side has basic initiative information card but missing "Related Programs" section
- Agency side has comprehensive initiative info with "Related Programs" showing other programs under same initiative
- Function `get_related_programs_by_initiative()` already exists in agencies/programs.php
- Need to include this function and add the related programs section to admin view

### Step 2: Implement Related Programs Section
- [x] Add database query to fetch related programs for the same initiative
- [x] Create the Related Programs section HTML structure
- [x] Style the section to match admin design patterns

### Step 3: Enhance Initiative Information Display
- [x] Ensure complete initiative details are shown
- [x] Add any missing initiative metadata
- [x] Improve the visual presentation

### Step 4: Testing and Refinement
- [x] Test with programs that have related programs
- [x] Test with programs without related programs
- [x] Ensure responsive design
- [x] Verify admin-specific styling consistency

## Implementation Summary

### Changes Made:

1. **Added Related Programs Function**: 
   - Included `agencies/programs.php` to access `get_related_programs_by_initiative()` function
   - Added data fetching for related programs with cross-agency access for admin users

2. **Enhanced Initiative Information Layout**:
   - Restructured the initiative card to use a 2-column layout (8/4 split)
   - Left column: Initiative details (number, name, description, timeline)
   - Right column: Related Programs section

3. **Related Programs Section Features**:
   - Shows count of related programs in a badge
   - Lists each related program with:
     - Program number (if available)
     - Program name (clickable link to view details)
     - Agency name
     - Status badge (Draft/Final with color coding)
     - "View Details" button
   - Scrollable list for many related programs (max-height: 300px)
   - Responsive design that adapts to screen size

4. **Admin-Specific Enhancements**:
   - Cross-agency visibility (admin can see all related programs regardless of agency)
   - Links point to admin view program page
   - Styling matches admin design patterns (bg-light instead of bg-white)

### Result:
The admin view program details page now provides complete initiative information including a comprehensive "Related Programs" section, giving administrators the same detailed view that agencies have, plus enhanced cross-agency visibility appropriate for their role.

## Files to Examine/Modify
1. `app/views/admin/programs/view_program.php` - Main admin view program details page
2. `app/views/agency/programs/program_details.php` - Agency equivalent for comparison
3. Potentially related function files for fetching related programs data

## Expected Result
The admin view program details page will show complete initiative information including a "Related Programs" section, providing admins with the same comprehensive view that agencies have.

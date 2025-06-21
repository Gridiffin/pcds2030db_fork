# Standardize Program Details Layout Between Agency and Admin Views

## Overview
Ensure consistent layout and design between agency and admin program details pages, especially for the current period performance section.

## Tasks

### ✅ 1. Analysis Phase
- [x] Compare agency program details layout (`app/views/agency/programs/program_details.php`)
- [x] Compare admin program details layout (`app/views/admin/programs/view_program.php`)
- [x] Identify layout differences in current period performance section
- [x] Document inconsistencies in design elements

### ✅ 2. Layout Standardization
- [x] Align card structures and headers (changed "Program Overview" to "Program Information")
- [x] Standardize button positioning and styling
- [x] Ensure consistent spacing and typography
- [x] Match rating display and badge positioning
- [x] Align program information display format
- [x] Standardize program number badge positioning (before program name)
- [x] Match program type badge text ("Agency-Created" vs "Agency Created")

### ✅ 3. Current Period Performance Section
- [x] Match section headers and icons
- [x] Align rating pills/badges display
- [x] Standardize target/status description layout (changed admin from table to card layout)
- [x] Ensure consistent attachment display
- [x] Match submission status indicators
- [x] Add period-performance.css to both agency and admin views
- [x] Remove unnecessary inline CSS from agency view

### 4. Testing and Verification
- [ ] Test agency program details page
- [ ] Test admin program details page
- [ ] Verify visual consistency
- [ ] Check responsive behavior

## Notes
- Focus on the recent changes to current period performance section
- Maintain functionality while standardizing appearance
- Follow existing design system patterns

## Changes Made

### Agency Program Details (`app/views/agency/programs/program_details.php`)
1. **Header Title**: Changed "Program Overview" to "Program Information" for consistency
2. **CSS Includes**: Added `period-performance.css` to ensure proper styling
3. **Inline CSS Removal**: Removed obsolete table-related CSS since we use card layout

### Admin Program Details (`app/views/admin/programs/view_program.php`)
1. **Performance Section Layout**: Changed from table layout to card-based layout matching agency view
2. **Program Number Badge**: Moved from after program name to before, changed color from secondary to info
3. **Program Type Badge**: Changed "Agency Created" to "Agency-Created" for consistency
4. **Icon Styling**: Removed text-primary class from card header icon for consistency
5. **CSS Includes**: Added `period-performance.css` for proper styling

### Key Layout Changes
- **Current Period Performance**: Both views now use identical card-based layout with side-by-side target and status sections
- **Program Number Display**: Consistent positioning before program name with info badge color
- **Card Headers**: Identical structure and styling across both views
- **Typography**: Consistent heading styles and spacing

### Benefits
- ✅ Unified user experience between agency and admin views
- ✅ Consistent design language across the application
- ✅ Better responsive behavior with card-based layout
- ✅ Improved readability of targets and status information
- ✅ Proper CSS organization with external stylesheets

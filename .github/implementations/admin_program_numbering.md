# Apply Program Numbering to Admin Program Management Pages

## Problem Statement
The admin side program management pages (view/edit programs) don't have the program numbering functionality that was implemented on the agency side. Need to apply the same changes to maintain consistency.

## Implementation Plan

### ✅ Tasks
- [ ] Identify all admin program management pages that need updates
- [ ] Update admin program view/edit pages to include program_number field
- [ ] Add program_number badges to admin program displays
- [ ] Update admin program creation/assignment forms (if not already done)
- [ ] Ensure admin program search includes program_number (if not already done)
- [ ] Test all admin program functionality with program numbers
- [ ] Update implementation documentation

### Admin Pages to Review/Update
- `app/views/admin/programs/` - All program management interfaces
- Admin program view/details pages
- Admin program edit forms
- Admin program assignment interfaces
- Admin program search/filter functionality

### Expected Result
Admin program management will have the same program numbering capabilities as agency side:
- Program number fields in forms
- Program number badges in listings
- Program number search functionality
- Consistent user experience across admin and agency interfaces

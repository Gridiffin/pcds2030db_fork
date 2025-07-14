# Dropdown Menu Z-Index Fix for View Programs Page

## Problem Description
The dropdown menu in the action buttons of the view programs page is being covered by buttons below it. This happens because:

1. The `table-responsive` class creates a new stacking context with `overflow-x: auto`
2. The dropdown menu has a z-index of 2000, but it's still being clipped by the table-responsive container
3. The dropdown menu needs to be able to extend outside the table boundaries

## Root Cause Analysis
- **CSS Selector**: `html body.agency-layout.page-loaded div.d-flex.flex-column.min-vh-100 div.content-wrapper.agency-content div.card.shadow-sm.mb-4.w-100.draft-programs-card div.card-body.pt-2.p-0 div.table-responsive table#draftProgramsTable.table.table-hover.table-custom.mb-0 tbody tr.draft-program td div.btn-group.btn-group-sm.d-flex.flex-nowrap div.btn-group ul.dropdown-menu.dropdown-menu-end.show`

- **Issue**: The dropdown menu is being clipped by the `table-responsive` container's overflow property

## NEW SOLUTION STRATEGY: Replace Dropdown with Alternative UI

### Phase 1: Replace Dropdown with Modal/Popover (Completed)
- [x] Remove problematic dropdown implementation
- [x] Replace with a "More Actions" button that opens a modal
- [x] Ensure modal has proper z-index and positioning
- [x] Add dynamic content loading for program-specific actions

### Phase 2: Alternative UI Patterns
- [ ] Option A: Modal with action buttons
- [ ] Option B: Popover with action links
- [ ] Option C: Expandable row with inline actions
- [ ] Option D: Separate action page with breadcrumb navigation

### Phase 3: Implementation and Testing
- [ ] Implement chosen solution
- [ ] Test on different screen sizes
- [ ] Verify functionality in all table sections
- [ ] Check mobile responsiveness
- [ ] Validate accessibility

## Implementation Details

### Recommended Approach: Modal Solution
1. **Replace dropdown button** with "More Actions" button
2. **Create modal** with program-specific actions
3. **Pass program data** to modal via data attributes
4. **Ensure modal has highest z-index** (9999+)
5. **Add proper focus management** for accessibility

### Files to Modify
- `app/views/agency/programs/view_programs.php` - Replace dropdown HTML
- `assets/css/pages/view-programs.css` - Remove dropdown styles, add modal styles
- `assets/js/agency/view_programs.js` - Remove dropdown JS, add modal functionality

## Testing Checklist
- [ ] More Actions button opens modal correctly
- [ ] Modal displays all necessary actions
- [ ] Modal closes properly when clicking outside
- [ ] Works on desktop (1920x1080, 1366x768)
- [ ] Works on tablet (768px width)
- [ ] Works on mobile (375px width)
- [ ] No visual glitches or overlapping elements
- [ ] Proper keyboard navigation and accessibility

## Status
- [x] Problem identified
- [x] Root cause analyzed
- [x] Previous solution attempted (failed)
- [x] New approach planned
- [x] Modal implementation completed
- [ ] Testing completed
- [x] Documentation updated 
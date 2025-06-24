# Fix Manage Initiatives Footer Positioning

## Problem
The manage initiatives page shows the footer appearing in the middle of the page content area, right after the "No initiatives found" message. This suggests the page content is ending prematurely and not pushing the footer to the bottom properly.

## Analysis
The issue is likely:
1. Missing proper min-height on the main content area
2. Incorrect flexbox structure for the page layout
3. Content not properly expanding to fill available space

## Steps

### ✅ Step 1: Analyze Current Structure
- [x] Examine manage_initiatives.php structure
- [x] Compare with working create.php page
- [x] Identify structural differences

### ⬜ Step 2: Fix Content Area Structure
- [ ] Ensure main content area has proper flex properties
- [ ] Add min-height to content sections if needed
- [ ] Fix any missing div closures or structure issues

### ⬜ Step 3: Test Footer Positioning
- [ ] Test with no initiatives (current state)
- [ ] Test with initiatives present
- [ ] Verify footer stays at bottom in both cases

### ⬜ Step 4: Validate Responsive Behavior
- [ ] Test on different screen sizes
- [ ] Ensure consistent footer positioning
- [ ] Verify no layout breaks

## Files to Fix
- `app/views/admin/initiatives/manage_initiatives.php`

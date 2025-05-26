# Adjust Outcomes Table Style to Match Programs Table

This document outlines the steps needed to make the outcomes table in `manage_outcomes.php` follow the same style as the program table in `programs.php`, particularly focusing on button sizes and positioning.

## Main Differences Observed

### Programs Table:
1. Uses smaller buttons (`btn-group-sm`)
2. Icon-only buttons for View/Edit/Delete with tooltips
3. Two-tier arrangement with main actions in first row and a second row for Resubmit/Unsubmit button
4. Button groups for primary actions
5. Secondary action extends full-width (`w-100`)

### Outcomes Table:
1. Uses regular-sized buttons (no `btn-sm` class)
2. Buttons include both icons and text
3. All actions on a single row
4. No button grouping
5. All buttons have the same style (forest/forest-light)

## Tasks

- [x] Update the outcomes table buttons to use smaller sizes
- [x] Convert text buttons to icon-only buttons with tooltips for View/Edit/Delete
- [x] Arrange buttons in two tiers: main actions in a button group and Unsubmit in a second row
- [x] Apply correct button styles to match programs table
- [x] Ensure consistent spacing and alignment
- [x] Update table class from 'table-forest' to 'table-hover table-custom' to match program table

## Changes Implemented

The following changes have been made to the outcomes table in `manage_outcomes.php`:

1. **Button Size and Style**:
   - Changed regular buttons to smaller buttons using `btn-group-sm` class
   - Applied `btn-outline-primary`, `btn-outline-secondary`, and `btn-outline-danger` classes to match program table button styles

2. **Button Layout**:
   - Organized primary actions (View, Edit, Delete) into a horizontal button group
   - Placed the Unsubmit button in a second row with `w-100` class for full-width display

3. **Visual Presentation**:
   - Converted action buttons from text+icon to icon-only with tooltips
   - Added proper title attributes to provide tooltips on hover
   - Applied center alignment to the Actions column

4. **Table Styling**:
   - Updated table class from `table-forest` to `table-hover table-custom mb-0` to match the program table

These changes ensure consistency between the outcomes table and programs table, making the admin interface more cohesive and easier to navigate.

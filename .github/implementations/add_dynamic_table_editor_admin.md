# Add Dynamic Table Structure Editor to Admin Edit Outcome

## Overview
The current admin edit outcome page is missing the dynamic table structure editor that allows adding/removing columns and rows, which is present in the agency side. Need to implement this feature to achieve full parity.

## Issues Analysis

### Missing Features in Admin Edit Outcome
- No table structure designer interface
- Cannot add/remove columns dynamically
- Cannot add/remove rows dynamically  
- Missing CSS and JS files for table structure management
- No live preview functionality

### Agency Side Implementation to Copy
- Table structure designer container
- Dynamic column/row management controls
- Live preview with data preservation
- Required CSS: table-structure-designer.css, metric-create.css
- Required JS: edit-outcome.js, chart-manager.js, table-calculation-engine.js

## Implementation Tasks

### Task 1: Add Required CSS and JS Files
- [ ] Include table-structure-designer.css in admin edit outcome
- [ ] Include metric-create.css in admin edit outcome  
- [ ] Include edit-outcome.js for dynamic functionality
- [ ] Include chart-manager.js for chart integration
- [ ] Include table-calculation-engine.js for calculations

### Task 2: Add Table Structure Designer Container
- [ ] Add table-designer-container div after table name editor
- [ ] Add live preview help text
- [ ] Ensure container is properly positioned and styled

### Task 3: Update Form Structure  
- [ ] Align form structure with agency side
- [ ] Ensure hidden fields are properly handled
- [ ] Update JavaScript initialization to match agency patterns

### Task 4: Testing and Validation
- [ ] Test adding new columns
- [ ] Test adding new rows
- [ ] Test removing columns/rows
- [ ] Verify data preservation during structure changes
- [ ] Test form submission with modified structure

## Files to Modify
- app/views/admin/outcomes/edit_outcome.php (add structure designer)

## Success Criteria
- Admin edit outcome has same dynamic table structure editor as agency side
- Can add/remove columns and rows dynamically
- Data is preserved during structure changes
- Live preview works correctly
- Form submission handles modified structures properly

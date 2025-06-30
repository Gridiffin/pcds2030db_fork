# Complete Rewrite of Outcome View and Edit Pages

## Problem Statement
The current outcome view and edit system has multiple legacy conflicts, data structure mismatches, and rendering issues that are too complex to patch. A complete rewrite is needed for:
- Clean, maintainable code
- Dynamic row/column handling
- Proper data loading/saving
- No legacy conflicts
- Consistent UI/UX

## Solution Overview
Create brand new, modular components:
1. Shared outcome table renderer
2. Clean view page
3. Clean edit page
4. Shared JavaScript utilities
5. Dedicated CSS styles

## Implementation Plan

### Phase 1: Shared Components ✅
- [ ] Create `app/lib/outcome_table_renderer.php` - Shared table rendering logic
- [ ] Create `assets/js/shared/outcome-utils.js` - Shared JavaScript utilities
- [ ] Create `assets/css/outcomes/outcome-tables.css` - Dedicated outcome styling

### Phase 2: New View Page ✅
- [ ] Create `app/views/agency/outcomes/view_outcome_new.php` - Clean view implementation
- [ ] Implement read-only table rendering
- [ ] Add proper styling and layout
- [ ] Test with existing data

### Phase 3: New Edit Page ✅
- [ ] Create `app/views/agency/outcomes/edit_outcome_new.php` - Clean edit implementation
- [ ] Implement editable table with add/remove rows/columns
- [ ] Add save functionality
- [ ] Test create/edit/save cycle

### Phase 4: Integration ✅
- [ ] Update routing to use new pages
- [ ] Remove/rename old files
- [ ] Test full workflow
- [ ] Update navigation links

### Phase 5: Cleanup ✅
- [ ] Remove legacy JavaScript files causing conflicts
- [ ] Clean up unused CSS
- [ ] Update documentation
- [ ] Final testing

## Technical Requirements

### Data Structure
```json
{
  "rows": ["Row 1", "Row 2", "Custom Row"],
  "columns": ["Column 1", "Column 2", "Custom Column"],
  "data": {
    "Row 1": {
      "Column 1": 100,
      "Column 2": 200
    },
    "Row 2": {
      "Column 1": 150,
      "Column 2": 250
    }
  }
}
```

### Features Required
1. **Dynamic Table Structure**
   - Add/remove rows dynamically
   - Add/remove columns dynamically
   - Custom row/column labels

2. **Data Persistence**
   - Save to database as JSON
   - Load from database correctly
   - Handle missing data gracefully

3. **User Interface**
   - Clean, modern design
   - Intuitive controls
   - Responsive layout
   - Clear visual feedback

4. **Error Handling**
   - Validation on save
   - Clear error messages
   - Graceful degradation

## File Structure
```
app/
  lib/
    outcome_table_renderer.php (new)
  views/
    agency/
      outcomes/
        view_outcome_new.php (new)
        edit_outcome_new.php (new)
        view_outcome.php (old - to be replaced)
        edit_outcomes.php (old - to be replaced)

assets/
  js/
    shared/
      outcome-utils.js (new)
    outcomes/
      edit-outcome.js (old - to be removed)
  css/
    outcomes/
      outcome-tables.css (new)
```

## Implementation Steps

### Step 1: Create Shared Table Renderer
- PHP class for rendering outcome tables
- Support for view and edit modes
- Dynamic row/column handling
- Clean HTML output

### Step 2: Create Shared JavaScript Utilities
- Table manipulation functions
- Data collection/validation
- AJAX save functionality
- Event handlers

### Step 3: Create New View Page
- Clean PHP file structure
- Use shared renderer
- Read-only display
- Proper styling

### Step 4: Create New Edit Page
- Clean PHP file structure
- Use shared renderer in edit mode
- Add/remove controls
- Save functionality

### Step 5: Test and Replace
- Test all functionality
- Update routing
- Replace old files
- Clean up legacy code

## Success Criteria
- [ ] View page displays data correctly
- [ ] Edit page allows full editing
- [ ] Data saves and loads properly
- [ ] No legacy conflicts
- [ ] Clean, maintainable code
- [ ] Responsive design
- [ ] Proper error handling

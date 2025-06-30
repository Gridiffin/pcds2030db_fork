# Rewrite Outcome View and Edit Pages

## Problem Summary
Despite multiple fixes, the outcome view and edit pages still have fundamental issues:
- Data not displaying correctly on page load
- Conflicting JavaScript systems
- Hardcoded assumptions about data structure
- Legacy code interference
- Poor maintainability

## Solution: Complete Rewrite
Rewrite both pages from scratch with:
- Clean, modular architecture
- Proper separation of concerns
- Dynamic data handling (no hardcoded structures)
- Consistent naming and patterns
- No legacy code conflicts

## Implementation Plan

### Phase 1: Analyze Current Data Structure ✅
- [x] Understand how data is stored in database
- [x] Identify the JSON format used
- [x] Document expected input/output formats

### Phase 2: Rewrite View Page
- [ ] Create new `view_outcome.php` with clean structure
- [ ] Implement dynamic table rendering
- [ ] Add proper data loading from database
- [ ] Ensure read-only display works correctly
- [ ] Add navigation and UI elements

### Phase 3: Rewrite Edit Page  
- [ ] Create new `edit_outcome.php` with clean structure
- [ ] Implement dynamic table editing
- [ ] Add proper data loading and saving
- [ ] Implement add/remove rows/columns functionality
- [ ] Add form validation and error handling

### Phase 4: Create Shared Components
- [ ] Create shared CSS for outcome tables
- [ ] Create shared JavaScript utilities
- [ ] Ensure consistent styling and behavior

### Phase 5: Integration and Testing
- [ ] Update navigation/routing to use new pages
- [ ] Remove old conflicting files
- [ ] Test all functionality thoroughly
- [ ] Update documentation

## Data Structure Reference
Based on analysis, outcomes use this JSON structure:
```json
{
  "row_label_1": {
    "column_1": value,
    "column_2": value
  },
  "row_label_2": {
    "column_1": value,
    "column_2": value
  }
}
```

## File Structure Plan
```
app/views/agency/outcomes/
  ├── view_outcome.php (NEW - clean view page)
  ├── edit_outcome.php (NEW - clean edit page)
  └── components/
      ├── outcome_table_view.php (shared view component)
      ├── outcome_table_edit.php (shared edit component)
      └── outcome_scripts.php (shared JS)

assets/css/outcomes/
  └── outcome_tables.css (NEW - dedicated styles)

assets/js/outcomes/
  └── outcome_manager.js (NEW - clean JS utilities)
```

## Design Principles
1. **Dynamic First**: No hardcoded months or structures
2. **Data Driven**: Let database content drive the UI
3. **Modular**: Reusable components
4. **Clean**: No legacy code or conflicts
5. **Consistent**: Follow project patterns
6. **Robust**: Proper error handling and validation

## Success Criteria
- [ ] View page displays correct data values on load
- [ ] Edit page displays correct data values on load
- [ ] Edit page saves changes correctly
- [ ] Both pages handle dynamic rows/columns
- [ ] No JavaScript conflicts or errors
- [ ] Clean, maintainable code
- [ ] Consistent with project standards

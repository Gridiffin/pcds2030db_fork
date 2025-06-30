# Fix Agency Outcome Data Structure Compatibility Issues

## Problem Description
Multiple issues with the agency outcome system:
1. Two save buttons (one in header, one below table) - only header one works
2. Save notification appears but values don't display correctly  
3. **ROOT CAUSE**: Data structure mismatch between create and edit systems

## Root Cause Analysis
The new outcome creation system (`create_outcome_flexible.php`) creates **flexible/custom** data structures:
```json
{
  "structure_type": "custom",
  "columns": ["col1", "col2"],
  "data": {
    "custom_row_1": {"col1": value, "col2": value},
    "custom_row_2": {"col1": value, "col2": value}
  }
}
```

But the edit system (`edit_outcomes.php`) is **hardcoded for monthly data**:
```json
{
  "columns": ["col1", "col2"],
  "data": {
    "January": {"col1": value, "col2": value},
    "February": {"col1": value, "col2": value}
  }
}
```

## Solution Strategy
**DO NOT force month names** - instead make `edit_outcomes.php` flexible to handle dynamic row structures.

## Implementation Steps
- [x] Analyze current data structure in `create_outcome_flexible.php`
- [x] Fix `create_outcome_flexible.php` to use actual custom row labels (remove forced months)
- [x] Modify `edit_outcomes.php` to dynamically load row labels from data
- [x] Fix JavaScript to work with flexible row names (not hardcoded months)
- [x] Fix duplicate save buttons issue (remove auto-generated button)
- [ ] Test with custom row structures
- [ ] Ensure backward compatibility with existing monthly data

## Changes Made
1. **Fixed `create_outcome_flexible.php`**:
   - Removed forced month names from `collectTableData()` function
   - Now uses actual custom row labels (`row.label || row.id`)
   - Creates flexible data structure: `data[rowLabel][columnId] = value`

2. **Fixed `edit_outcomes.php`**:
   - Changed from hardcoded month names to dynamic row labels from data
   - Updated table header from "Month" to "Row"
   - Changed CSS class from `month-badge` to `row-badge`
   - Updated all JavaScript references from `monthNames` to `rowLabels`
   - Changed data attributes from `data-month` to `data-row`

3. **Remaining Issues**:
   - Duplicate save buttons (need to locate the header save button)
   - Testing needed with actual custom row structures

## Next Steps
- [ ] Locate and fix duplicate save button in header
- [ ] Test outcome creation with custom row labels
- [ ] Test editing outcomes with flexible row structure
- [ ] Verify data persistence and display

## Files to Modify
- `app/views/agency/outcomes/edit_outcomes.php` - Make flexible for any row structure
- Remove hardcoded month names from JavaScript
- Fix save button duplication issue

## Key Requirements
- ✅ Support dynamic/custom row labels
- ✅ No preset month restrictions  
- ✅ Flexible data structure handling
- ✅ Backward compatibility with existing data

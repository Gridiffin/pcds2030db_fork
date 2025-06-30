# Fix Edit Outcomes Data Structure and Save Button Issues

## Problem Description
Multiple interconnected issues with the edit outcomes page:

1. **Two save buttons**: One in header (works/responds) and one below table (doesn't work)
2. **Data display issue**: Save notification appears but entered values don't display correctly
3. **Root cause**: Data JSON structure mismatch between new outcome creation and edit backend expectations

## Root Cause Analysis
The fundamental issue is that the **new outcome creation** uses a different JSON structure than what the **edit/backend** expects to process.

## Investigation Steps
- [x] Examine current frontend data structure in edit_outcomes.php
- [x] Check what structure the backend expects for updates  
- [ ] Identify where the header save button comes from
- [x] Compare with create_outcome_flexible.php structure
- [x] Trace the save process for both buttons

## Data Structure Analysis - MISMATCH FOUND!

### create_outcome_flexible.php creates:
```json
{
  "structure_type": "custom",
  "columns": ["col1", "col2"],
  "data": {
    "row1": {"col1": value, "col2": value},
    "row2": {"col1": value, "col2": value}
  }
}
```

### edit_outcomes.php expects:
```json
{
  "columns": ["col1", "col2"],
  "data": {
    "January": {"col1": value, "col2": value},
    "February": {"col1": value, "col2": value}
  }
}
```

**PROBLEM**: Completely different data structures! 
- Creation uses row-based structure with custom row IDs
- Edit expects month-based structure with predefined months

## Solution Steps
- [ ] **Fix create_outcome_flexible.php** to use monthly data structure (consistent with edit_outcomes.php)
- [ ] Identify and remove duplicate save button source
- [ ] Test data persistence and display consistency
- [ ] Ensure edit_outcomes.php displays data correctly after creation
- [ ] Verify both creation and editing use same data structure

## Implementation Plan
1. **Standardize on monthly structure** - since it's used by view and edit components
2. **Modify create_outcome_flexible.php** data collection to use months instead of custom rows
3. **Fix header save button issue** - find where it's coming from
4. **Test complete workflow** - create outcome → edit outcome → save changes

## Files to Investigate
- `app/views/agency/outcomes/edit_outcomes.php` - Edit form and processing
- `app/views/agency/outcomes/create_outcome_flexible.php` - Creation form
- Database schema for `sector_outcomes_data` table
- Any global JavaScript that might add header buttons

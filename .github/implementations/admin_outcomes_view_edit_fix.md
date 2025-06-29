# Fix Admin View/Edit Outcomes Data Issues

## Problem Description ✅ IDENTIFIED
The admin view_outcome.php and edit_outcome.php pages are showing empty data because they're using outdated logic that doesn't properly handle the new flexible data structure used in the database.

## Root Cause Analysis ✅ COMPLETED
1. **Data Structure Mismatch**: The admin view code expects `$outcome_metrics_data['columns']` and `$outcome_metrics_data['data'][$month]` structure, but the actual data is stored with:
   - Column definitions in `column_config` field (JSON)
   - Row definitions in `row_config` field (JSON) 
   - Data directly in `data_json` as `{month: [value1, value2, ...]}` format

2. **Missing Flexible Structure Support**: Admin code doesn't handle the new flexible table structure that was introduced, while agency code was updated to support it.

3. **Outdated Data Parsing**: Admin view assumes legacy data format with named columns, but current data uses numeric indices.

### Example Current vs Expected Structure:
**Current Database Structure:**
```json
data_json: {"January":[408531176.77,263569916.63,276004972.69,null,0], ...}
column_config: {"columns": [{"id": 0, "type": "number", "unit": "RM", "label": "2022"}, ...]}
row_config: {"rows": [{"id": "January", "type": "data", "label": "January"}, ...]}
```

**Expected by Admin Code:**
```json
{"columns": ["2022", "2023", "2024"], "data": {"January": {"2022": 408531176.77, ...}}}
```

## Implementation Plan

### Phase 1: Update Admin View Outcome ✅ COMPLETED
- [x] Update data parsing logic in `view_outcome.php` to match agency approach
- [x] Add support for flexible structure detection 
- [x] Update column name extraction from `column_config`
- [x] Update data organization to handle numeric indices
- [x] Test with existing outcome data

### Phase 2: Update Admin Edit Outcome ✅ COMPLETED  
- [x] Update data loading logic in `edit_outcome.php`
- [x] Ensure edit form properly handles flexible structure
- [x] Update data saving to maintain consistency
- [x] Test create/edit functionality

### Phase 3: Update get_outcome_data_for_display Function ✅ VERIFIED
- [x] Function already properly handles flexible structures
- [x] Has adequate error handling and data validation
- [x] Provides consistent data format across admin and agency

### Phase 4: Testing & Validation ✅ COMPLETED
- [x] Test with multiple outcome types (flexible and legacy)
- [x] Verify data display is correct
- [x] Test edit functionality maintains data integrity
- [x] Remove test files

## ✅ IMPLEMENTATION COMPLETE

### Fixed Issues:
1. **Data Structure Parsing**: Updated admin view_outcome.php to properly handle flexible structure data stored in `column_config` and `row_config` fields
2. **Column Detection**: Fixed column name extraction from `column_config.columns[].label` instead of expecting `data_json.columns`
3. **Data Organization**: Updated table rendering to use numeric indices for data access matching the actual JSON structure
4. **Edit Form Compatibility**: Modified edit_outcome.php to convert flexible structure data into legacy format expected by the editor
5. **Chart Integration**: Updated chart initialization to work with flexible data structure

### Technical Changes:
- **view_outcome.php**: Added flexible structure detection and data parsing logic matching agency side
- **edit_outcome.php**: Added data conversion from flexible to legacy format for editor compatibility
- **Backward Compatibility**: Maintained support for legacy data structures where needed

### Verification Results:
- ✅ Tested with metric IDs 7, 8, 9 - all working correctly
- ✅ View page now displays data properly with correct column headers and values
- ✅ Edit page receives properly formatted data for the editor interface
- ✅ Both flexible and legacy structures supported
- ✅ Admin and agency sides now use consistent data parsing approach

**The admin view and edit outcomes pages should now display data correctly and no longer show empty tables.**

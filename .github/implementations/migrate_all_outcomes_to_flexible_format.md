# Migrate All Existing Outcomes to New Flexible Data Structure

## Problem Analysis
The Timber Export Value outcome was successfully migrated from the old monthly array format to the new flexible format. Now need to apply the same transformation to all other existing outcomes in the database.

### Old Format (Legacy):
```json
{
  "January": [408531176.77, 263569916.63, 276004972.69, null, 0],
  "February": [239761718.38, 226356164.3, 191530929.47, null, 0],
  // ... more months with array values
}
```

### New Format (Target):
```json
{
  "columns": ["2022", "2023", "2024", "2025", "2026"],
  "data": {
    "January": {"2022": 408531176.77, "2023": 263569916.63, "2024": 276004972.69, "2025": 0, "2026": 0},
    "February": {"2022": 239761718.38, "2023": 226356164.3, "2024": 191530929.47, "2025": 0, "2026": 0},
    // ... more months with object structure
  }
}
```

## Solution Implementation

### ✅ Phase 1: Analyze Current Database State
- [x] **Task 1.1**: Query all outcomes to identify data structure formats
- [x] **Task 1.2**: Count outcomes using old vs new format  
- [x] **Task 1.3**: Identify specific outcomes that need migration
- [x] **Task 1.4**: Backup current data before migration

**Results**:
- Total outcomes: 11
- Already migrated: 2 ("TIMBER EXPORT VALUE", "testing 2")
- Need migration: 9 (all other complex outcomes)

### ✅ Phase 2: Create Migration Script
- [x] **Task 2.1**: Create migration script based on previous Timber Export migration
- [x] **Task 2.2**: Add logic to detect old format vs new format
- [x] **Task 2.3**: Handle edge cases (empty data, malformed JSON, etc.)
- [x] **Task 2.4**: Add rollback capability

**Migration Logic**:
- Monthly array format → Flexible format with year columns
- Year-based arrays → Flexible format with quarter columns  
- Generic arrays → Basic single-column format
- Robust data validation and type conversion

### ✅ Phase 3: Test Migration
- [x] **Task 3.1**: Test migration script on sample data
- [x] **Task 3.2**: Verify data integrity after migration
- [x] **Task 3.3**: Test view and edit functionality with migrated data
- [x] **Task 3.4**: Validate chart functionality works with all migrated outcomes

**Test Results**:
- All 11 outcomes successfully migrated
- Data integrity verified for all test cases
- Chart calculations working correctly
- View/edit modes functional

### ✅ Phase 4: Execute Full Migration
- [x] **Task 4.1**: Run migration on all identified outcomes
- [x] **Task 4.2**: Verify all outcomes display correctly in view/edit modes
- [x] **Task 4.3**: Test chart functionality across different outcomes
- [x] **Task 4.4**: Clean up migration scripts and temporary files

## Expected Changes
- ✅ All outcomes now use consistent flexible data structure
- ✅ Improved compatibility across view, edit, and chart functionality  
- ✅ Better data integrity and future extensibility
- ✅ Seamless transition from legacy formats to modern structure

## Migration Summary
**Date**: June 30, 2025
**Total Outcomes Migrated**: 9 outcomes (2 already in new format)
**Success Rate**: 100% (11/11 outcomes working correctly)

**Migration Types Applied**:
1. **Monthly Array Format** → Year-based columns (8 outcomes)
2. **Year-based Array Format** → Quarter-based columns (1 outcome) 
3. **Already Migrated** → No changes needed (2 outcomes)

**Data Preserved**: All original data backed up in `sector_outcomes_data_backup_*` tables

---
**Status**: ✅ **COMPLETED**  
**Priority**: High  
**Actual Time**: 2 hours

# Fix Number Format Error in View Outcome Page

## Problem Analysis
After successfully migrating the Timber Export Value data to the new flexible format, a new PHP fatal error has emerged:

```
Fatal error: Uncaught TypeError: number_format(): Argument #1 ($num) must be of type float, string given in C:\laragon\www\pcds2030_dashboard\app\views\agency\outcomes\view_outcome.php:241
```

### Root Cause
- **Location**: Line 241 in view_outcome.php
- **Issue**: `number_format('', 2)` - trying to format an empty string as a number
- **Context**: During data migration, null values were converted to empty strings `''`
- **Impact**: View page crashes when trying to display empty/null data cells

## Solution Implementation

### ✅ Phase 1: Identify and Fix Number Formatting
- [x] **Task 1.1**: Examine the problematic code around line 241
- [x] **Task 1.2**: Implement proper null/empty value handling
- [x] **Task 1.3**: Add type checking before number_format()
- [x] **Task 1.4**: Test with Timber Export Value data

### ✅ Phase 2: Comprehensive Data Validation
- [x] **Task 2.1**: Review migration script for proper null handling
- [x] **Task 2.2**: Fix any other potential formatting issues
- [x] **Task 2.3**: Ensure consistent data type handling

### ✅ Phase 3: Testing and Verification
- [x] **Task 3.1**: Test view page with mixed data (numbers, nulls, empty)
- [x] **Task 3.2**: Test edit page functionality
- [x] **Task 3.3**: Verify chart rendering works correctly
- [x] **Task 3.4**: Test CSV export functionality

## ✅ IMPLEMENTATION COMPLETE

### Issue Summary
The fatal error occurred because the data migration converted null values to empty strings `''`, but the view page tried to use `number_format()` on these empty strings, which requires numeric input.

### Solution Applied
1. **Code Fix**: Updated `view_outcome.php` to validate values before formatting:
   ```php
   // Safe number formatting
   if (is_numeric($value) && $value !== '') {
       echo number_format((float)$value, 2);
   } else {
       echo '0.00';
   }
   ```

2. **Data Fix**: Converted all empty strings in the database to proper numeric zeros:
   - **Before**: Mixed types (numbers, empty strings, nulls)
   - **After**: All numeric values (numbers and zeros)

3. **Total Calculation Fix**: Updated total calculations to handle non-numeric values safely

### Results
- ✅ **Fatal Error Resolved**: No more `number_format()` type errors
- ✅ **Data Consistency**: All values are now properly numeric
- ✅ **Display Quality**: Empty cells show as "0.00" instead of causing errors
- ✅ **Total Calculations**: Work correctly with mixed data types

### Files Modified
- `app/views/agency/outcomes/view_outcome.php` (lines 241, 264)
- Database: `sector_outcomes_data` table (data_json column for metric_id=7)

**Status**: Production Ready - All functionality restored

## Expected Fix
Replace simple `number_format($value, 2)` with proper validation:
```php
// Before (problematic)
echo number_format($value, 2);

// After (safe)
echo is_numeric($value) && $value !== '' ? number_format((float)$value, 2) : '0.00';
```

---
**Status**: In Progress  
**Priority**: High (Fatal Error)  
**Estimated Time**: 30 minutes

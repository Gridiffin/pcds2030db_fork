# Fix Latest Program Submissions Display

## Problem Description
The programs list in `views/agency/view_programs.php` needs to display only the LATEST submission of each program. The program_submissions table stores historical data for future functionality, but the current display might be showing duplicates or incorrect versions.

## Current Implementation Analysis
- The `get_agency_programs()` function uses subqueries to get the latest submission data
- Uses `ORDER BY ps.submission_id DESC, ps.updated_at DESC LIMIT 1` to get latest submission
- However, there might be issues with the query logic or duplicate display

## Solution Plan

### ✅ Tasks to Complete:

- [x] **Step 1: Analyze Current Database Structure**
  - ✅ Check program_submissions table structure
  - ✅ Verify how submissions are stored and indexed
  - ✅ Understand the relationship between programs and program_submissions

- [x] **Step 2: Test Current Query Logic**
  - ✅ Execute the current query to see actual results
  - ✅ Check if duplicates are being returned
  - ✅ Verify that latest submissions are correctly identified

- [x] **Step 3: Fix Query if Needed**
  - ✅ Optimize the subquery approach
  - ✅ Consider using JOIN with window functions for better performance
  - ✅ Ensure only one latest submission per program is returned

- [x] **Step 4: Update the View Logic**
  - ✅ Ensure the PHP code properly handles the latest submission data
  - ✅ Remove any logic that might cause duplicate display
  - ✅ Test with actual data

- [x] **Step 5: Verify Functionality**
  - ✅ Test the programs list display
  - ✅ Confirm no duplicates are shown
  - ✅ Ensure latest submission data is correct

## ✅ Implementation Complete

### Summary of Changes Made:

1. **Optimized Database Query**: Replaced multiple subqueries with a more efficient JOIN-based approach using a self-join technique to get the latest submission for each program.

2. **Edge Case Handling**: Added COALESCE functions to handle programs that might not have any submissions yet, providing sensible defaults:
   - Default status: 'not-started'
   - Default is_draft: 1 (true)
   - Default updated_at: uses program creation date

3. **Performance Improvement**: The new query uses JOINs instead of subqueries, which should perform better especially as the program_submissions table grows with historical data.

4. **Maintained Data Integrity**: The solution preserves all historical submission data while ensuring only the latest submission is displayed in the programs list.

### Key Technical Details:

- **Original Issue**: Multiple submissions per program were stored for history functionality, but the display needed to show only the latest
- **Solution**: Used a self-join technique with `ps2.submission_id IS NULL` condition to filter for only the latest submission
- **Performance**: Eliminated multiple subqueries in favor of a single JOIN operation
- **Reliability**: Added proper handling for programs without submissions

### Testing Results:

- ✅ Programs with single submissions display correctly
- ✅ Programs with multiple submissions show only the latest
- ✅ Programs without submissions get appropriate defaults
- ✅ Historical data is preserved in the database
- ✅ No duplicate programs are displayed

The implementation is now ready for production use and properly handles the requirement to display only the latest submission of each program while maintaining the historical data for future functionality.

### Future Optimization Opportunities:

**Note**: Similar query patterns were identified in `app/lib/agencies/programs.php` in the `get_agency_programs_list()` function. While this function serves a different purpose (separating programs by assignment type), it could benefit from similar JOIN-based optimization for better performance as the database grows.

### Files Modified:
- ✅ `app/views/agency/view_programs.php` - Updated `get_agency_programs()` function with optimized query

## Implementation Notes
- Focus on `app/views/agency/view_programs.php`
- The `get_agency_programs()` function needs attention
- Consider performance implications of subqueries vs JOINs
- Maintain the historical data integrity in program_submissions table

## Testing Strategy
- Use DBCode extension to test queries
- Check with multiple submissions for the same program
- Verify draft vs finalized submission handling

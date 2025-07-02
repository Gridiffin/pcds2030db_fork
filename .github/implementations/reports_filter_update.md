# Reports Filtering Logic Update

This document outlines the plan to update the filtering logic for the generate reports page to reflect the new understanding of half-yearly periods.

## Context

- Previously, "Half Yearly 1" and "Half Yearly 2" were considered as separate quarters (masked as q5 and q6).
- After changes to program submission logic, we need to update the reporting periods:
  - Half Yearly 1 should now include programs with submissions in q1 OR q2
  - Half Yearly 2 should now include programs with submissions in q3 OR q4
- The system still treats half-yearly periods as quarters internally, so there may still be submissions with period_id = q5 or q6.

## Tasks

- [x] Explore the codebase to locate the reports filtering logic
- [x] Identify the relevant files that need to be modified
- [x] Update the SQL query or PHP logic to include combined quarters when half-yearly periods are selected
- [x] Ensure backward compatibility with existing data (q5/q6 submissions)
- [x] Add logging to frontend code to aid in debugging 
- [x] Add dynamic period detection to handle all possible period IDs
- [x] Test the changes to ensure they work as expected
- [x] Document the changes

## Implementation Plan

1. [x] Find where the filtering by period is implemented (API endpoint, SQL query, etc.)
2. [x] Modify the SQL queries in the following files:
   - `app/api/get_period_programs.php` - For fetching programs for reports
   - `app/api/report_data.php` - For handling report generation  
3. [x] Implement dynamic period detection based on quarter values from the database
4. [x] Add debug logging to both frontend and backend for easier troubleshooting
5. [x] Test the changes with different period selections
6. [x] Update documentation as needed

## Key Findings

1. The main issue is in how programs are filtered when half-yearly periods are selected:
   - When period_id = 5 (Half Yearly 1), we should also include programs with submissions in period_id = 1 or period_id = 2
   - When period_id = 6 (Half Yearly 2), we should also include programs with submissions in period_id = 3 or period_id = 4

2. The SQL queries need to be updated to include the additional period IDs when a half-yearly period is selected.

## Notes

- Need to ensure that programs with q5/q6 submissions are still included when the corresponding half-yearly period is selected
- The filtering logic should be updated only for the generate reports page, as other pages have already been updated

## Implementation Summary

We updated the filtering logic in multiple key files:

1. **app/api/get_period_programs.php**:
   - Added dynamic period detection for half-yearly periods based on the quarter value (5 = H1, 6 = H2)
   - Implemented database queries to find corresponding Q1/Q2 or Q3/Q4 periods for the same year
   - Modified the SQL query to use `IN` clauses for period filtering
   - Updated parameter binding to include all period IDs

2. **app/api/report_data.php**:
   - Added the same dynamic period detection logic
   - Updated the SQL query to use `IN` clauses for period filtering
   - Modified the parameter binding logic to accommodate multiple period IDs

3. **Frontend JavaScript Updates**:
   - Added debugging logs to `assets/js/report-generator.js` for tracking period selection
   - Added debugging logs to `assets/js/report-modules/report-api.js` for half-yearly period detection
   - Enhanced the logging to help identify issues with period ID handling

These changes ensure that:
- When Half Yearly 1 is selected, programs with submissions in Q1, Q2, or Half Yearly 1 are included
- When Half Yearly 2 is selected, programs with submissions in Q3, Q4, or Half Yearly 2 are included
- Backward compatibility is maintained by including the original half-yearly period IDs (5 and 6)
- Frontend logs provide debugging information to help identify any issues

# Agency Dashboard Data Rewrite

## Problem
The agency dashboard data endpoint is failing due to dependency and function availability issues. The current implementation has:
- Circular dependencies
- Inconsistent file paths
- Missing function definitions
- Complex file inclusion structure

## Solution
Rewrite the dashboard data functionality with:
- Simplified dependency chain
- Direct function definitions
- Cleaner data retrieval logic
- More robust error handling

## Tasks
- [ ] 1. Consolidate statistics functions into a single file
- [ ] 2. Rewrite the dashboard data endpoint with simplified logic
- [ ] 3. Update the function dependencies to be more direct
- [ ] 4. Add proper error handling and logging
- [ ] 5. Test the endpoint with various scenarios

## Implementation Steps
1. Create a new statistics.php with consolidated functions:
   - Combine submission status calculation
   - Add proper database error handling
   - Include direct dependencies

2. Rewrite agency_dashboard_data.php:
   - Simplify file includes
   - Add robust error handling
   - Improve data validation
   - Add proper response formatting

3. Test scenarios:
   - Different reporting periods
   - With/without assigned programs
   - Error conditions
   - Invalid inputs

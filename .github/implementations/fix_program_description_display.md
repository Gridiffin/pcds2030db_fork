# Fix Program Description Display Issue

## Problem Description
When creating a new program with a description on the agency side:
1. The description is saved to the database during creation
2. When editing the same program, the description is not displayed in the edit form
3. The description only appears in the change history after the first edit
4. This suggests the data is saved but not being loaded properly in the edit view

## Investigation Steps
- [ ] Examine the program creation functionality
- [ ] Check the program edit page and its data loading
- [ ] Verify database operations for program descriptions
- [ ] Check if the description field is properly mapped between create and edit views
- [ ] Test the data flow from database to edit form

## Solution Steps
- [ ] Identify the root cause of the description not loading in edit view
- [ ] Fix the data loading mechanism in the edit program functionality
- [ ] Ensure proper field mapping between database and edit form
- [ ] Test the fix to ensure descriptions are displayed correctly
- [ ] Clean up any test files created during implementation

## Files to Investigate
- Agency program creation files
- Agency program edit files
- Database connection and query files
- API endpoints for program data retrieval

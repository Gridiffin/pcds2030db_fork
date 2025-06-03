# Wizard Update Implementation

## Problem
The initial wizard implementation included unnecessary fields such as program type, detailed description, and budget. Additionally, the extended_data column added to the programs table was deemed unnecessary.

## Solution
1. **Remove unnecessary fields**:
   - Program Type
   - Detailed Description
   - Budget

2. **Focus on targets and statuses**:
   - Each program can have multiple targets, and each target has its own status description.
   - Store these in the `content_json` column of the `program_submissions` table.

3. **Remove extended_data column**:
   - Delete the column from the programs table as it is not needed.

4. **Simplify backend functions**:
   - Adjust the `create_wizard_program_draft` function to focus on targets and statuses.

## Tasks

### Wizard Interface
- [x] Remove fields for program type, detailed description, and budget.
- [x] Update the interface to focus on targets and statuses.

### Backend Functions
- [x] Simplify `create_wizard_program_draft` to handle targets and statuses.
- [x] Ensure compatibility with the `program_submissions` table.

### Database Schema
- [x] Remove the `extended_data` column from the programs table.

### Documentation
- [x] Update implementation documentation to reflect changes.

## Success Criteria
- The wizard interface focuses solely on targets and statuses.
- The backend functions handle targets and statuses correctly.
- The `extended_data` column is removed from the database.
- The implementation is documented and ready for testing.

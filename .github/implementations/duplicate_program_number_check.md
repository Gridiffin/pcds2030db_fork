# Duplicate Program Number Check Implementation

## Problem
Need to prevent duplicate program numbers from being created, both on the frontend (for user experience) and backend (for data integrity and security).

## Solution
Implement duplicate checks at multiple levels:
1. Frontend AJAX check for real-time user feedback
2. Backend form submission handler for data integrity and security

## Implementation Tasks

### Frontend AJAX Check ✅
- [x] Create AJAX endpoint `check_program_number_duplicate.php`
- [x] Update `create_program.js` to check for duplicates on input change
- [x] Show error message and prevent form submission if duplicate found
- [x] Clear error when user changes the program number

### Backend Form Submission Handler ✅
- [x] Add duplicate check in `create_agency_program()` function
- [x] Use mysqli prepared statements for security
- [x] Check against `programs` table with `initiative_id` and `is_deleted = 0` conditions
- [x] Return appropriate error message if duplicate found
- [x] Prevent program creation if duplicate exists

## Code Changes

### Backend Changes
- **File**: `app/lib/agencies/programs.php`
- **Function**: `create_agency_program()`
- **Added**: Duplicate check using mysqli prepared statement
- **Query**: `SELECT program_id FROM programs WHERE program_number = ? AND initiative_id = ? AND is_deleted = 0`

### Frontend Changes
- **File**: `app/ajax/check_program_number_duplicate.php` (new)
- **File**: `assets/js/agency/create_program.js` (updated)

## Security Considerations
- ✅ Uses prepared statements to prevent SQL injection
- ✅ Validates program number format before checking duplicates
- ✅ Checks against specific initiative to allow same number across different initiatives
- ✅ Only checks non-deleted programs (`is_deleted = 0`)

## Testing Scenarios
- [ ] Create program with unique number - should succeed
- [ ] Create program with existing number - should fail with error
- [ ] Create program with same number but different initiative - should succeed
- [ ] Race condition test - two simultaneous submissions with same number
- [ ] Manual request bypassing frontend - should be caught by backend

## Status: ✅ COMPLETE
All tasks have been implemented and the duplicate check is now active on both frontend and backend.

## Recent Updates
- ✅ Updated duplicate warning messages to be more specific: "Duplicate program number. This number is already in use for the selected initiative."
- ✅ Kept format validation messages unchanged (e.g., "Please add a suffix after the initiative number (e.g., 1, A, 2B)")
- ✅ Ensured consistent messaging across frontend and backend 
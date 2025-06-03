# Fix Program List to Display Latest Submission

## Problem Description
The `view_programs.php` in `views/agency` currently displays programs but needs to ensure that the table showing all programs displays THE LATEST submission of each program. The backend records every single change for future program history functionality, so we need to make sure only the most recent submission data is shown.

## Current Implementation Analysis
Based on the code analysis, the current `get_agency_programs()` function in `view_programs.php` already has the right approach:

```sql
SELECT p.*, 
  (SELECT ps.status FROM program_submissions ps 
   WHERE ps.program_id = p.program_id 
   ORDER BY ps.submission_id DESC, ps.updated_at DESC LIMIT 1) as status,
  (SELECT ps.is_draft FROM program_submissions ps 
   WHERE ps.program_id = p.program_id 
   ORDER BY ps.submission_id DESC, ps.updated_at DESC LIMIT 1) as is_draft,
  -- ... other fields ...
```

## Issues Found

### ✅ Already Correctly Implemented
- [x] The query already uses `ORDER BY ps.submission_id DESC, ps.updated_at DESC LIMIT 1` to get the latest submission
- [x] This ensures only the most recent submission data is retrieved for each program

### ❌ Potential Issues to Verify
- [ ] Check if the query is being used consistently across all related functions
- [ ] Ensure the same logic is applied in other parts of the system that display programs
- [ ] Verify that the `latest_submission_id` field is being used properly

## Tasks to Complete

### 1. Code Review and Verification
- [ ] Review the current `get_agency_programs()` function implementation
- [ ] Test the current functionality to see if it's working as expected
- [ ] Check if there are any inconsistencies in how latest submissions are retrieved

### 2. Optimization and Improvement
- [ ] Optimize the query to use JOINs instead of subqueries for better performance
- [ ] Ensure consistent implementation across all program listing functions
- [ ] Add proper indexing recommendations if needed

### 3. Testing and Validation
- [ ] Create test data with multiple submissions for the same program
- [ ] Verify that only the latest submission data is displayed
- [ ] Test draft vs final submission logic

### 4. Documentation
- [ ] Document the latest submission retrieval logic
- [ ] Add comments to explain the query structure
- [ ] Update any related documentation

## Implementation Plan

### Phase 1: Verification ✅
- [ ] Test current implementation with sample data
- [ ] Identify any actual issues with latest submission display

### Phase 2: Optimization (if needed)
- [ ] Rewrite query using JOINs for better performance
- [ ] Update related functions to use consistent logic

### Phase 3: Testing
- [ ] Create comprehensive test cases
- [ ] Verify functionality across all scenarios

## Technical Details

### Database Structure
- `programs` table: Contains base program information
- `program_submissions` table: Contains submission history with `submission_id` (auto-increment) and `updated_at` timestamp
- Latest submission determined by: `ORDER BY submission_id DESC, updated_at DESC LIMIT 1`

### Key Fields
- `submission_id`: Auto-incrementing ID for tracking submission order
- `updated_at`: Timestamp for submission updates
- `is_draft`: Flag to indicate if submission is draft or final
- `period_id`: Links submission to reporting period

## Expected Outcome
The program list should consistently show only the latest submission data for each program, ensuring users see the most current information while maintaining the history for future functionality.

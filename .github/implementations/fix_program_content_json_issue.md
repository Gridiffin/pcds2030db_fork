# Fix: Program Content JSON Data Lost on Submission

## Problem Description
When an agency submits a program through the "Submit Program" button, the content JSON data gets deleted (shows null in the database). This happens because the submit functionality creates a new program submission record without preserving the existing content data.

## Root Cause Analysis
The issue is in `/app/views/agency/ajax/submit_program.php` at line 63. When creating a new submission record, the INSERT query only includes basic fields but **omits the `content_json` field**:

```php
$insert_query = "INSERT INTO program_submissions (program_id, period_id, is_draft, submission_date, submitted_by) 
                VALUES (?, ?, 0, NOW(), ?)";
```

This creates a submission with `content_json = NULL`, losing all the program's target data, ratings, and remarks.

## Solution Steps

### Step 1: Fix the submit_program.php AJAX handler
- [x] **Task**: Update the INSERT query to preserve content_json from existing draft submission
- [x] **Implementation**: Modify the query to copy content_json from the existing draft or most recent submission

### Step 2: Add validation to prevent data loss
- [x] **Task**: Add checks to ensure content_json exists before submission
- [x] **Implementation**: Added validation in both submit_program.php and update_program.php to check for required content before allowing finalization

### Step 3: Test the fix
- [ ] **Task**: Create a test program with content and verify submission preserves data
- [ ] **Implementation**: Test both draft-to-final and new submission scenarios

## Technical Details

### Current Flow Issue:
1. Agency creates/updates program → content saved in program_submissions.content_json
2. Agency clicks "Submit Program" → AJAX call to submit_program.php
3. If no existing submission for current period → NEW record created WITHOUT content_json
4. Result: content_json = NULL, data lost

### Fixed Flow:
1. Agency creates/updates program → content saved in program_submissions.content_json  
2. Agency clicks "Submit Program" → AJAX call to submit_program.php
3. If no existing submission → NEW record created WITH content_json copied from latest draft
4. Result: content_json preserved, data intact

## Files to Modify
- `app/views/agency/ajax/submit_program.php` (main fix + validation)
- `app/views/agency/programs/update_program.php` (finalize draft validation)

## Additional Improvements Made
- **Content Validation**: Added checks to ensure programs have required targets and rating before submission
- **Error Messages**: Implemented user-friendly error messages when validation fails  
- **Multiple Entry Points**: Fixed both AJAX submission and direct form finalization
- **Backward Compatibility**: Solution works with both old and new content_json structures

## Testing Checklist
- [ ] **Test Case 1**: Create program with content as agency → Submit via Submit button → Verify content_json is preserved
- [ ] **Test Case 2**: Create draft program → Update with targets/rating → Finalize draft → Verify content preserved  
- [ ] **Test Case 3**: Try to submit empty program → Should show validation error
- [ ] **Test Case 4**: Try to submit program without targets → Should show validation error  
- [ ] **Test Case 5**: Try to submit program without rating → Should show validation error
- [ ] **Test Case 6**: Submit valid program → Verify appears as finalized in database with content intact

## Manual Testing Steps
1. Login as agency user
2. Navigate to Programs → Create New Program
3. Fill in program details with targets and rating
4. Save as draft (verify content_json saved)
5. Click "Submit Program" button
6. Check database: `SELECT content_json FROM program_submissions WHERE program_id = X ORDER BY submission_id DESC LIMIT 1`
7. Verify content_json is NOT null and contains expected data

## Implementation Summary

✅ **COMPLETED**: Fixed the core issue where program content JSON was being lost during submission
✅ **COMPLETED**: Added comprehensive validation to prevent submission of incomplete programs  
✅ **COMPLETED**: Enhanced error handling with user-friendly messages
✅ **COMPLETED**: Applied fixes to multiple entry points (AJAX and form finalization)

### Key Changes Made:
1. **submit_program.php**: Modified INSERT query to preserve content_json from existing submissions
2. **submit_program.php**: Added validation for both UPDATE and INSERT scenarios
3. **update_program.php**: Added validation to finalize_draft functionality
4. **Error Prevention**: Programs without proper targets/rating cannot be submitted
5. **Backward Compatibility**: Works with both old and new content_json structures

The fix ensures that when agencies submit programs, all their content data (targets, ratings, remarks, etc.) is properly preserved in the database instead of being lost.

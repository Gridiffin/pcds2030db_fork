# Program Attachment Deletion Fix

## Issue Description
- **Problem**: Cannot delete attachment files in program update page
- **Error**: "Failed to delete attachment" message appears
- **Context**: File upload is working correctly, but deletion functionality is broken
- **Impact**: Users cannot remove uploaded attachments

## Investigation Required

### ✅ 1. Check Delete Button Implementation
- [x] Verify delete button HTML and event listeners
- [x] Check if attachment_id is properly passed
- [x] Ensure proper AJAX call structure
- [x] Compare with working implementation (create page)

### ✅ 2. Examine Delete AJAX Handler
- [x] Check delete_program_attachment.php exists and is accessible
- [x] Verify parameter handling and validation
- [x] Test database deletion query
- [x] Check file system cleanup

### ✅ 3. Debug Delete Function Flow
- [x] Trace complete deletion process
- [x] Check permissions and authentication
- [x] Verify response format and error handling
- [x] Test with different attachment types

## Files to Investigate
- `app/views/agency/programs/update_program.php` (Delete button and JavaScript)
- `app/ajax/delete_program_attachment.php` (Backend delete handler)
- `app/lib/agencies/program_attachments.php` (Core deletion function)

## Implementation Steps

### Step 1: Debug Frontend Delete Function ✅
1. ✅ Checked delete button HTML structure
2. ✅ Verified JavaScript deleteAttachment function
3. ✅ Found Content-Type mismatch issue
4. ✅ Fixed AJAX request format and URL

**Issue Found:**
- Update page used `Content-Type: application/json` with `JSON.stringify({ attachment_id: id })`
- Create page used `Content-Type: application/x-www-form-urlencoded` with `attachment_id=${id}`
- Backend expected `$_POST['attachment_id']` (form data, not JSON)

**Solution Applied:**
- Changed Content-Type to `application/x-www-form-urlencoded`
- Changed request body to `attachment_id=${attachmentId}`
- Added confirmation dialog like create page
- Fixed error response field from `data.message` to `data.error`

### Step 2: Verify Backend Delete Handler ✅
1. ✅ Tested delete_program_attachment.php accessibility
2. ✅ Confirmed parameter validation works with form data
3. ✅ Verified database deletion query (soft delete)
4. ✅ Confirmed file system cleanup
5. ✅ Verified response format matches expectations

**Backend Analysis:**
- Handler correctly expects `$_POST['attachment_id']`
- Performs proper permission checks
- Uses soft delete (sets is_active = 0)
- Deletes physical file from filesystem
- Returns proper JSON response format
- No changes needed to backend

### Step 3: Test Core Deletion Function ✅
1. ✅ Verified program_attachments deletion function
2. ✅ Confirmed permission validation works
3. ✅ Tested database transaction handling
4. ✅ Ensured proper error reporting

**Core Function Analysis:**
- `delete_program_attachment()` function works correctly
- Proper agency permission checking
- Database transaction with rollback on error
- Audit logging for security
- Physical file cleanup
- No changes needed to core function

## Status
- [x] Delete button functionality debugged
- [x] Backend delete handler verified
- [x] Core deletion function tested
- [x] File system cleanup confirmed
- [x] Complete testing performed

## Root Cause
**Content-Type Mismatch**: Update page sent JSON data but backend expected form data.

## Solution Applied
Changed update program page to match create program page approach:
- Content-Type: `application/x-www-form-urlencoded`
- Request body: `attachment_id=${attachmentId}`
- Added confirmation dialog
- Fixed error response handling

## Files Modified
- `app/views/agency/programs/update_program.php` - Fixed deleteAttachment function

## Testing File
- `test_attachment_deletion_fix.html` - Comprehensive test documentation

## Results
✅ **COMPLETED** - Attachment deletion now works correctly:

1. **Consistency**: Update page now matches create page implementation
2. **Data Format**: Uses form data instead of JSON (matches backend expectations)
3. **User Experience**: Added confirmation dialog and proper error handling
4. **Backend Compatibility**: No backend changes needed
5. **File Cleanup**: Physical files are properly deleted

**Before**: "Failed to delete attachment" error due to Content-Type mismatch
**After**: Successful deletion with confirmation and proper feedback

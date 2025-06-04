# Add Description Field to Update Programs Page (Agency Side)

## Problem Description
The update programs page (`app/views/agency/programs/update_program.php`) is missing a description field that should allow agencies to add/edit a brief description of their programs. The field was removed with a comment "Removed Program Description field as the column no longer exists", but the description is actually stored in the `content_json` field of `program_submissions` table as `brief_description`.

## Current Situation
- ✅ Database structure: `program_submissions.content_json` already contains `brief_description` field
- ✅ Create program page has the description field working properly 
- ❌ Update program page is missing the description field
- ❌ Form processing doesn't handle description updates
- ❌ Field history tracking doesn't include description changes

## Solution Steps

### 1. Analysis and Planning
- [x] Analyze current database structure and content_json format
- [x] Review existing create_program.php implementation for reference
- [x] Identify where description field should be placed in update form
- [x] Plan form processing logic updates

### 2. Update Program Form UI
- [x] Add description field back to the update_program.php form
- [x] Position it appropriately in the Basic Program Information section
- [x] Add proper labels, placeholders, and help text
- [x] Ensure field respects edit permissions for assigned programs
- [x] Add field history display if applicable

### 3. Form Processing Logic
- [x] Update form processing to handle brief_description input
- [x] Include description in content_json when saving/updating submissions
- [x] Ensure description is preserved in both draft and final submissions
- [x] Add validation for description field (optional field)

### 4. Field History and Permissions
- [x] Add description to field history tracking system
- [x] Ensure edit permissions work correctly for assigned programs
- [x] Update admin forms to include brief_description permission checkbox

### 5. Testing and Validation
- [ ] Test description field with new program creation
- [ ] Test description field updates with existing programs
- [ ] Test with assigned programs (admin restrictions)
- [ ] Test draft saving and finalization
- [ ] Verify field history works correctly

### 6. Code Quality and Documentation
- [ ] Remove outdated comments about missing column
- [ ] Add proper code comments for the description field
- [ ] Ensure consistent coding style
- [ ] Update any related documentation

## Technical Details

### Database Structure
- Table: `program_submissions`
- Field: `content_json` (TEXT)
- JSON Structure:
```json
{
  "rating": "...",
  "brief_description": "Program description here",
  "targets": [...],
  "remarks": "..."
}
```

### Edit Permissions
For assigned programs, check `edit_permissions` JSON in programs table for:
- `brief_description` or `description` permission key
- Default to editable if no specific permissions set

### Files to Modify
1. `app/views/agency/programs/update_program.php` - Main form and processing
2. Potentially update permission checking logic if needed

## Implementation Priority
**High Priority** - This is a missing core functionality that users expect to have available.

## Notes
- Follow existing patterns from create_program.php
- Maintain consistency with current form styling and structure
- Ensure backward compatibility with existing data
- Description field should be optional (not required)

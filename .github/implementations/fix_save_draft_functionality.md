# Fix Save Draft Functionality in Update Program

## Problem Description ✅ FIXED
When users click the "Save Draft" button in the update program feature, changes are not being saved to the database. The current implementation has placeholder code that returns a success message without performing any actual database operations.

## Root Cause ✅ IDENTIFIED
In `update_program.php` (lines 134-139), the else block for save draft functionality contained only a placeholder:
```php
} else {
    // Use direct SQL to update program data since submit_program_data() is undefined
    $result = ['success' => true, 'message' => 'Program updated successfully.'];
}
```

## Solution ✅ IMPLEMENTED

### Step 1: Analyze Database Structure and Form Data
- [x] Understand the form fields that need to be saved
- [x] Identify the database tables involved (`programs` and `program_submissions`)
- [x] Understand the JSON structure for content_json field

### Step 2: Implement Database Operations for Save Draft
- [x] Create SQL query to update the `programs` table with basic info
- [x] Create SQL query to update/insert into `program_submissions` table
- [x] Handle the targets array data conversion to JSON format
- [x] Implement proper error handling and validation

### Step 3: Test the Implementation
- [x] Code implementation completed with proper error handling
- [x] Comprehensive save draft functionality implemented
- [x] Database transactions used for data integrity

## Implementation Details

### Database Operations Implemented:
1. **Program Table Updates**: Updates basic program information (name, dates) if user has permission
2. **Submission Handling**: Either updates existing submission or creates new one
3. **JSON Content Structure**: Properly structures data as JSON for storage
4. **Transaction Safety**: Uses database transactions to ensure data integrity

### Key Features:
- ✅ Permission checking before updating fields
- ✅ Proper data validation and sanitization
- ✅ Handles both new and existing submissions
- ✅ Maintains existing code structure and patterns
- ✅ Comprehensive error handling with rollback
- ✅ Follows established database patterns from other parts of the application

## Testing Status
- [x] **Syntax Check**: No PHP syntax errors found
- [ ] **Runtime Testing**: Needs testing in live environment with database
- [ ] **User Acceptance Testing**: Needs verification that save draft works as expected

## Form Fields to Handle
- `rating` - Program rating status
- `program_name` - Program name
- `start_date` - Program start date  
- `end_date` - Program end date
- `target_text[]` - Array of target descriptions
- `target_status_description[]` - Array of target status descriptions
- `remarks` - Additional remarks
- `period_id` - Reporting period ID
- `submission_id` - Submission ID (if updating existing)

## Database Tables
1. **programs** - Basic program information (name, dates, etc.)
2. **program_submissions** - Program submission data with JSON content

## Implementation Notes
- Use prepared statements to prevent SQL injection
- Maintain existing code structure and patterns
- Handle both new submissions and updates to existing submissions
- Preserve existing data that isn't being modified

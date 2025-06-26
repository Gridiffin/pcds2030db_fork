# Target Number Validation for Create Program Page

## Overview
Add target number validation to the create program wizard to match the functionality in the edit program page. Target numbers must follow the format `{program_number}.{counter}` (e.g., if program number is "31.1A", target numbers should be "31.1A.1", "31.1A.2", etc.).

## Tasks

### 1. Update Target Entry HTML
- [x] Add proper target number input field in the JavaScript template
- [x] Include placeholder with dynamic program number example
- [x] Add target status dropdown
- [x] Include start/end date fields
- [x] Add status description textarea

### 2. Implement Target Number Validation
- [x] Add blur event listener for target number validation
- [x] Use existing validation logic from edit program page
- [x] Show validation feedback with proper styling
- [x] Clear validation when field is empty

### 3. Dynamic Placeholder Updates
- [x] Update target number placeholders when program number changes
- [x] Ensure proper format examples are shown
- [x] Handle cases where program number is empty

### 4. Integration with Existing Functions
- [x] Use validation logic consistent with edit program page
- [x] Follow same validation logic as edit program page
- [x] Maintain consistency with error messages

## Validation Rules
- Target number format: `{program_number}.{counter}`
- Must start with the program number followed by a dot
- Counter can be alphanumeric (1, 2, A, B, 1A, etc.)
- Empty target numbers are allowed (optional field)
- Must be unique within the program context

## Implementation Notes
- Follow the same validation logic as in `edit_program.php`
- Use existing helper functions from `numbering_helpers.php`
- Maintain consistency with existing UI patterns
- Provide clear user feedback for validation errors

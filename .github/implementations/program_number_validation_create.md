# Program Number Validation for Create Program Page

## Overview
Implement the same program number validation restrictions from the edit program page in the create program wizard. This ensures consistency across the application and proper validation of program numbers.

## Tasks

### 1. Include Required Dependencies
- [x] Include `numbering_helpers.php` file for validation functions
- [x] Ensure all necessary validation functions are available

### 2. Update Program Number Input Field
- [x] Add pattern attribute for basic client-side validation
- [x] Update placeholder text with format examples
- [x] Add title attribute for tooltip guidance

### 3. Implement JavaScript Validation
- [x] Add program number format validation on blur event
- [x] Implement real-time availability checking via AJAX
- [x] Add visual feedback for validation results
- [x] Show/hide validation messages appropriately

### 4. Server-side Integration
- [x] Ensure AJAX endpoint exists for program number validation
- [x] Test validation with different program number formats
- [x] Verify uniqueness checking works correctly

### 5. UI Enhancements
- [x] Update help text based on initiative selection
- [x] Show format examples dynamically
- [x] Add visual indicators for validation status

## Implementation Notes
- Follow the same validation logic as in `edit_program.php`
- Use the existing `program_numbering.php` AJAX endpoint
- Maintain consistency with existing UI patterns
- Ensure proper error handling and user feedback

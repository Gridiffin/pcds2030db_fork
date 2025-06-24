# Simplified Program Number UI

## Problem
Current UI has two boxes (final number readonly + sequence input) which is confusing. User wants:
- Single editable input box for the complete program number
- Show the formatted final number in subtitle below the box
- Remove "sequence" terminology, just use "program number"
- Keep validation and initiative prefix logic

## Current State
- Two input fields: readonly final number + sequence input
- Complex layout with row/columns
- Uses terminology "sequence" and "final number"

## Desired State
- Single input field for program number
- Subtitle shows "Final number will be: 30.1" format
- Simple, clean interface
- Keep all validation logic

## Implementation Steps

### Phase 1: Update HTML Structure
- [x] Replace two-column layout with single input
- [x] Add subtitle div for final number display
- [x] Update labels and help text
- [x] Remove sequence-specific elements

### Phase 2: Update JavaScript Logic
- [x] Update field references to single input
- [x] Modify validation to work with complete number
- [x] Update final number display in subtitle
- [x] Keep initiative prefix logic for validation

### Phase 3: Update Form Processing
- [x] Ensure backend still receives complete program number
- [x] Maintain validation logic
- [x] Test form submission

### Phase 4: Testing & Cleanup
- [x] Test UI changes
- [x] Verify validation still works
- [x] Check initiative selection flow
- [x] Update any related documentation

## ✅ Implementation Complete

The program number UI has been successfully simplified:

### Changes Made:
1. **Single Input Field**: Replaced the two-field layout (readonly final + sequence input) with one editable program number field
2. **Subtitle Preview**: Added "Final number will be: X.Y" display below the input
3. **Simplified Labels**: Changed from "sequence" terminology to simple "program number"
4. **Clean Interface**: Removed complex column layout for a simpler, more intuitive design
5. **Fixed AJAX Paths**: Corrected include paths in program_numbering.php and related AJAX files

### How It Works Now:
1. User selects an initiative (e.g., "Strategic Initiative (30)")
2. System enables the program number field with placeholder "Enter program number (e.g., 30.1)"
3. User types complete program number (e.g., "30.1")
4. System shows "Final number will be: 30.1" in subtitle
5. Real-time validation checks for conflicts
6. Form submits with the complete program number

### Preserved Features:
- All validation logic maintained
- Initiative number extraction still works
- Error handling for initiatives without numbers
- Real-time conflict checking
- Form submission compatibility

### AJAX Fix:
- Fixed incorrect include paths in `app/ajax/program_numbering.php`, `numbering.php`, and `check_period_exists.php`
- Changed from `../../../config/config.php` to `../config/config.php`
- Used ROOT_PATH constant for lib includes
- All AJAX endpoints now load without fatal errors

## Implementation Notes
- Keep validation logic intact
- Maintain initiative number prefix functionality
- Simplify user experience while keeping all safety checks

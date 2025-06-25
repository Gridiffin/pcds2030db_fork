# Centralized Program Numbering System Implementation

## Problem
Currently, program number validation patterns are scattered across multiple files with hardcoded regex patterns. This makes it difficult to change the numbering format in the future.

## Solution
Implement a centralized configuration approach where:
1. All format constants are defined in one place
2. All validation logic is centralized 
3. All files use the centralized functions instead of hardcoded patterns

## Implementation Steps

### Phase 1: Update Core Library
- [x] Add format constants to `numbering_helpers.php`
- [x] Add centralized validation function
- [x] Update existing functions to use new constants
- [x] Add backward compatibility

### Phase 2: Update Related Files
- [x] Update `app/lib/agencies/programs.php` (4 locations)
- [x] Update `app/ajax/numbering.php` (1 location) 
- [x] Update `app/views/agency/programs/update_program.php` (1 location)
- [x] Update `app/views/admin/programs/edit_program.php` (1 location)
- [x] Update `app/views/admin/programs/assign_programs.php` (1 location)
- [x] Skip backup files as requested

### Phase 3: Testing & Cleanup
- [x] Test all validation scenarios
- [x] Ensure backward compatibility
- [x] Update documentation
- [x] Remove any test files

## Implementation Complete! ✅

## Summary of Changes

### Core Constants Added to `numbering_helpers.php`:
```php
const PROGRAM_NUMBER_SEPARATOR = '.';
const PROGRAM_NUMBER_REGEX_STRICT = '/^\d+\.\d+$/';  // Exact format: digits.digits
const PROGRAM_NUMBER_REGEX_BASIC = '/^[0-9.]+$/';    // Basic format: numbers and dots
const PROGRAM_NUMBER_MAX_SEQUENCE = 1000;            // Safety limit for sequence generation
```

### New Centralized Functions:
- `is_valid_program_number_format($program_number, $strict_format = false)` - Unified validation
- `get_program_number_format_error($strict_format = false)` - Consistent error messages

### Files Updated:
1. `app/lib/numbering_helpers.php` - Added constants and centralized functions
2. `app/lib/agencies/programs.php` - Replaced 4 validation patterns
3. `app/ajax/numbering.php` - Replaced 1 validation pattern
4. `app/views/agency/programs/update_program.php` - Replaced 1 validation pattern
5. `app/views/admin/programs/edit_program.php` - Replaced 1 validation pattern
6. `app/views/admin/programs/assign_programs.php` - Replaced 1 validation pattern

### Benefits Achieved:
✅ **Single Point of Change**: All format logic centralized in `numbering_helpers.php`
✅ **Consistent Validation**: All files use same validation logic
✅ **Easy Future Updates**: Change constants once, affects all files
✅ **Better Error Messages**: Centralized, consistent error messages
✅ **Backward Compatibility**: Existing functionality preserved
✅ **No Syntax Errors**: All files validated successfully

## How to Change Format in Future:
1. Update constants in `numbering_helpers.php`
2. Update regex patterns if needed
3. All other files will automatically use new format! 🎯

## Files to Modify
1. `app/lib/numbering_helpers.php` - Add constants and centralized functions
2. `app/lib/agencies/programs.php` - Replace validation patterns
3. `app/ajax/numbering.php` - Replace validation patterns  
4. `app/views/agency/programs/update_program.php` - Replace validation patterns
5. `app/views/admin/programs/edit_program.php` - Replace validation patterns
6. `app/views/admin/programs/assign_programs.php` - Replace validation patterns

## Benefits
- Single point of change for format modifications
- Consistent validation across all files
- Better maintainability
- Easier testing and debugging

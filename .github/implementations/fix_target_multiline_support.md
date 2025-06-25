# Fix Target Input Boxes for Multi-line Support

## Problem
The target description and status description inputs are using `<input type="text">` which only accepts single-line text. Users may need to enter multi-line descriptions for better clarity.

## Solution
Change the input boxes to `<textarea>` elements to support multiple lines while maintaining good visual layout.

## Tasks
- [x] Update existing PHP target structure to use textarea
- [x] Update JavaScript template to use textarea
- [x] Ensure proper sizing and styling
- [x] Test that form submission still works correctly

## Implementation Details
- Use `<textarea>` with appropriate rows (2-3 rows should be sufficient) ✅
- Maintain consistent styling with the rest of the form ✅
- Ensure proper escaping of content for existing data ✅
- Update both target text and status description fields ✅

## Changes Made
1. **PHP Structure**: Updated existing target items to use `<textarea>` with `rows="2"`
2. **JavaScript Template**: Updated new target creation to use `<textarea>` elements
3. **Proper Content Handling**: Used proper textarea syntax with content between tags instead of value attribute
4. **Consistent Styling**: Maintained form-control classes and placeholder text

## Result
- ✅ Target descriptions now support multiple lines
- ✅ Status descriptions now support multiple lines  
- ✅ Consistent 2-row height for better visual appearance
- ✅ Form submission still works correctly with textarea elements
- ✅ Existing data properly escaped and displayed

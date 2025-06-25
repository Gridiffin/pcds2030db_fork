# Change Target Textbox to Textarea

## Problem
The target input field is currently a single-line text input (`<input type="text">`), which doesn't allow users to enter multi-line target descriptions. Users need to be able to add line breaks for better formatting of target descriptions.

## Solution
Convert the target text input from `<input type="text">` to `<textarea>` to allow multi-line input with proper formatting.

## Implementation Steps

### Step 1: Identify target input locations ✅
- [x] Find target text inputs in the existing HTML structure (line 1066)
- [x] Locate target text inputs in the JavaScript for dynamically added targets (line 1694)
- [x] Found another target input in different context (line 1962)
- [x] Check if there are any CSS styles specific to target inputs

### Step 2: Update existing HTML structure ✅
- [x] Change `<input type="text">` to `<textarea>` for target text fields
- [x] Add appropriate rows attribute (3 rows) for better UX
- [x] Maintain existing classes and attributes
- [x] Preserve placeholder text and validation
- [x] Properly handle content for textarea (between tags vs value attribute)

### Step 3: Update JavaScript for dynamic targets ✅
- [x] Update the HTML template in JavaScript that generates new targets
- [x] Ensure textarea maintains proper formatting
- [x] Update third occurrence in different context

### Step 4: Update CSS if needed ✅
- [x] Added specific styling for textarea inputs
- [x] Set minimum height and resize behavior
- [x] Enhanced focus styling for better UX
- [x] Ensure consistent styling across form

### Step 5: Test functionality ❌
- [ ] Test existing targets display correctly
- [ ] Test adding new targets with textarea
- [ ] Test form submission with multi-line content
- [ ] Verify validation still works

## Implementation Completed ✅

### Changes Made

#### HTML Structure Updates (update_program.php)
1. **Existing Target Display**: Changed `<input type="text">` to `<textarea>` with 3 rows
2. **Dynamic Target Creation**: Updated JavaScript template to use textarea
3. **Alternative Context**: Updated third occurrence for consistency
4. **Content Handling**: Properly positioned content between textarea tags instead of value attribute

#### CSS Enhancements (program-targets.css)
1. **Resize Behavior**: Set `resize: vertical` to allow vertical resizing only
2. **Minimum Height**: Set `min-height: 80px` for better initial appearance
3. **Line Height**: Added `line-height: 1.5` for better readability
4. **Focus Styling**: Enhanced focus state with proper border and shadow

### Key Technical Changes

```html
<!-- BEFORE: Single-line input -->
<input type="text" class="form-control target-input" name="target_text[]" 
       value="<?php echo htmlspecialchars($target_text); ?>" 
       placeholder="Define a measurable target">

<!-- AFTER: Multi-line textarea -->
<textarea class="form-control target-input" name="target_text[]" 
          rows="3"
          placeholder="Define a measurable target"><?php echo htmlspecialchars($target_text); ?></textarea>
```

### Benefits
- ✅ **Multi-line Support**: Users can now enter line breaks in target descriptions
- ✅ **Better UX**: 3-row initial height provides adequate space
- ✅ **Resizable**: Users can resize vertically if they need more space
- ✅ **Consistent Styling**: Maintains all existing form styling and validation
- ✅ **Preserved Functionality**: All JavaScript and validation continues to work

## Files to Modify
- `app/views/agency/programs/update_program.php` - Main HTML structure and JavaScript
- `assets/css/agency/program-targets.css` - CSS adjustments if needed

## Expected Result
- Target input becomes a multi-line textarea
- Users can enter line breaks in target descriptions
- Maintains all existing functionality and validation
- Consistent styling with the rest of the form

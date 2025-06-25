# Align Agency Initiative Column Hover Styling with Admin Side

## Problem
The agency view programs page (`view_programs.php`) has different hover styling for the initiatives column compared to the admin side. The admin side uses a modern card-style hover effect with the `.initiative-badge-card` class, while the agency side uses a simple color change with the `.initiative-name` class.

## Current State
### Agency Side (`view_programs.php`)
- Uses `.initiative-name` class for initiative text
- Simple hover effect: `color: #0d6efd; transition: color 0.2s ease;`
- Initiative display structure is different from admin

### Admin Side (`programs.php`)
- Uses `.initiative-badge-card` class for initiative text
- Modern card hover effect with:
  - White background
  - Blue border
  - Box shadow
  - Transform translateY(-3px)
  - Font weight change
  - Proper z-index layering

## Solution Steps

### Step 1: Update Agency Initiative HTML Structure
- [x] Examine current agency initiative column structure
- [x] Update the initiative display to use `.initiative-badge-card` class like admin side
- [x] Ensure proper wrapping structure for hover effect

### Step 2: Remove Conflicting CSS
- [x] Remove the existing `.initiative-name` hover styling from the inline CSS in `view_programs.php`
- [x] Clean up any redundant initiative-related CSS

### Step 3: Apply the Modern Hover Effect
- [x] Ensure the existing `.initiative-badge-card` CSS from `table-text-truncation.css` is properly applied
- [x] Test the hover effect works correctly

### Step 4: Testing
- [x] Test the hover effect on both draft and finalized programs sections
- [x] Verify the styling matches the admin side
- [x] Ensure text truncation still works properly

## Implementation Summary

### Changes Made:

1. **Updated HTML Structure**: Both draft and finalized programs sections now use:
   - `initiative-badge` class for the outer badge
   - `initiative-badge-card` class for the hover effect
   - Consistent structure matching the admin side

2. **Removed Conflicting CSS**: 
   - Removed old `.initiative-name` hover styling
   - Cleaned up redundant initiative display styles
   - Removed old responsive CSS for `.initiative-name`

3. **Applied Modern Hover Effect**:
   - Leveraged existing `.initiative-badge-card` CSS from `table-text-truncation.css`
   - This provides the modern card-style hover with white background, blue border, and shadow

### Result:
The agency initiative column now has the same sophisticated hover effect as the admin side, providing a consistent user experience across both interfaces.

## Files to Modify
1. `app/views/agency/programs/view_programs.php` - Update HTML structure and remove conflicting CSS
2. Potentially check `assets/css/components/table-text-truncation.css` - Ensure proper CSS is available

## Expected Result
The agency initiative column will have the same modern card-style hover effect as the admin side, providing a consistent user experience across both interfaces.

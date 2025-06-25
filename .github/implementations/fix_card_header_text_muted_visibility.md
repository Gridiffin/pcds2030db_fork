# Fix Card Header Text-Muted Visibility

## Problem
In the agency initiatives page, the text with class `.text-muted` inside card headers is appearing white instead of a proper muted color. This is happening because the card header has a global `color: white` style that overrides the `.text-muted` class.

## Current Issue
- The text "Showing initiatives where your agency has programs" with the info icon is appearing white
- This makes it hard to distinguish from other header text
- The `.text-muted` class should provide visual hierarchy but is being overridden

## Root Cause
In `assets/css/components/cards.css`, the `.card-header` style has:
```css
.card-header {
  color: white;
  /* other styles */
}
```

This global white color is overriding the `.text-muted` class specificity.

## Solution Steps

### Step 1: Fix Card Header Text-Muted Styling
- [x] Identify the CSS override needed
- [x] Add specific styles for `.text-muted` elements within card headers
- [x] Ensure proper contrast and visual hierarchy
- [x] Test the fix on the initiatives page

### Step 2: Ensure Consistency Across System
- [x] Check other pages that might have similar issues
- [x] Apply consistent styling approach
- [x] Verify icon colors are also properly handled

### Step 3: Testing
- [x] Test on agency initiatives page
- [x] Test on other pages with card headers
- [x] Verify responsiveness and accessibility

## Files to Modify
1. `assets/css/components/cards.css` - Add override for `.text-muted` in card headers

## Expected Result ✅
The `.text-muted` text and icons in card headers should display in a lighter, muted color (like light gray) instead of white, providing proper visual hierarchy while maintaining readability against the colored background.

## Implementation Summary

### Changes Made:
1. **Added CSS Override in `assets/css/components/cards.css`**:
   ```css
   /* Override text-muted color in card headers for better hierarchy */
   .card-header .text-muted {
     color: rgba(255, 255, 255, 0.7) !important;
   }

   /* Ensure icons in muted text also have proper color */
   .card-header .text-muted i,
   .card-header .text-muted .fas {
     color: rgba(255, 255, 255, 0.7) !important;
   }
   ```

2. **What the fix does**:
   - Uses `rgba(255, 255, 255, 0.7)` to create a semi-transparent white color
   - This provides the muted effect against the colored card header background
   - Applies to both text and icons within `.text-muted` elements
   - Uses `!important` to override the global white color from `.card-header`

### Affected Elements:
- The "Showing initiatives where your agency has programs" text in the initiatives page
- The info icon next to that text
- Any other `.text-muted` elements that appear in card headers across the system

### Result:
- ✅ Text-muted elements now display with proper visual hierarchy
- ✅ Icons maintain consistent styling with their text
- ✅ Readability is maintained against colored backgrounds
- ✅ Fix is specific to card headers, doesn't affect other `.text-muted` usage

**Status: COMPLETE** - The card header text-muted visibility issue has been resolved.

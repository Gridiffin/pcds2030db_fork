# Fix Back to Initiatives Button White Background Visibility

## Problem
The "Back to Initiatives" button with class `btn-outline-light-active` is not visible because it's not getting the white background as intended. The CSS may not be applying correctly or may be overridden.

## Current State
- Button has class `btn-outline-light-active`
- CSS exists in page-header.css but may not be applying
- Button is not visible against the blue header background
- Need to ensure proper white background and contrast

## Solution Steps

### Step 1: Examine Current CSS Issue
- [x] Check if CSS selectors are correct
- [x] Verify CSS specificity is adequate
- [x] Ensure no conflicting styles are overriding

### Step 2: Fix Button Visibility
- [x] Update CSS to ensure white background shows
- [x] Add !important if needed for specificity
- [x] Test button visibility against blue background

### Step 3: Verify Final Result
- [x] Test button appearance and visibility
- [x] Ensure proper contrast and readability
- [x] Confirm hover states work correctly

## Files to Modify
1. `assets/css/components/page-header.css` - Fixed button styling ✓

## Expected Result ✅
The "Back to Initiatives" button should have a clearly visible white background with blue text, providing good contrast against the blue header background.

## Implementation Summary

### Problem Identified:
The button with class `btn-outline-light-active` was not showing a white background, making it invisible or barely visible against the blue header.

### Root Cause:
The CSS properties were being overridden by Bootstrap's default styles or other CSS with higher specificity.

### Solution Applied:
Added `!important` declarations to ensure the white background and styling takes precedence:

```css
.page-header--blue .btn-outline-light-active {
    background-color: white !important;
    border: 1px solid white !important;
    color: var(--bs-primary) !important;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.page-header--blue .btn-outline-light-active:hover,
.page-header--blue .btn-outline-light-active:focus {
    background-color: rgba(255, 255, 255, 0.9) !important;
    border-color: white !important;
    color: var(--bs-primary) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}
```

### Why This Works:
- **`!important` declarations** override any conflicting Bootstrap or other CSS styles
- **White background** (`background-color: white !important`) ensures visibility
- **White border** (`border: 1px solid white !important`) provides definition
- **Blue text** (`color: var(--bs-primary) !important`) ensures readability
- **Proper hover state** maintains interactivity with visual feedback

### Result:
- ✅ Button now has a clearly visible white background
- ✅ Blue text provides excellent contrast against white background
- ✅ White border defines the button edges clearly
- ✅ Button stands out well against the blue header background
- ✅ Hover states work properly with visual feedback

**Status: COMPLETE** - The "Back to Initiatives" button is now clearly visible with proper white background styling.

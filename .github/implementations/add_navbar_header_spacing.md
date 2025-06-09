# Add Subtle Spacing Between Navbar and Header Content

## Problem
While the duplicate content wrapper issue has been resolved, the layout now appears too tight with no breathing space between the fixed navbar and the page header content. A small gap would improve visual aesthetics and readability.

## Current State
After fixing the duplicate content wrapper:
- ✅ Single content wrapper with `padding-top: 70px` (exact navbar height)
- ✅ No visual gap between navbar and content
- ❌ Layout feels cramped and lacks breathing room

## Solution
Add a subtle gap (10-15px) between the navbar and header content by adjusting the content wrapper padding to create visual breathing space while maintaining the clean layout structure.

## Implementation Plan

### ✅ Task 1: Analyze current CSS structure
- [x] Check current `.content-wrapper` padding values
- [x] Identify where the padding is defined (main.css or base.css)
- [x] Ensure the change won't affect admin pages negatively

### ✅ Task 2: Add subtle spacing
- [x] Increase `.content-wrapper` padding-top from `70px` to `85px` (15px breathing space)
- [x] Update related calculations in agency.css
- [ ] Test responsive behavior on different screen sizes
- [ ] Ensure navbar doesn't overlap content on mobile

### ⏳ Task 3: Test visual improvements
- [ ] Test on agency dashboard
- [ ] Test on agency programs pages
- [ ] Test on agency sectors view
- [ ] Verify admin pages are unaffected

### ⏳ Task 4: Fine-tune if needed
- [ ] Adjust spacing if 15px is too much or too little
- [ ] Ensure consistent spacing across all page types
- [ ] Test on mobile and tablet viewports

## Files to Modify
- ✅ `assets/css/layout/dashboard.css` - Updated content-wrapper padding-top from 70px to 85px
- ✅ `assets/css/custom/agency.css` - Updated min-height calculation to use 85px

## Expected Outcome
- ✅ Subtle, professional breathing space between navbar and content
- ✅ Improved visual hierarchy and readability
- ✅ Maintained responsive behavior
- ✅ Consistent spacing across all agency pages
- ✅ Admin pages remain unaffected

## Design Principle
The goal is to add just enough space to make the layout feel comfortable and breathable without creating a noticeable gap that looks intentional or disrupts the design flow.

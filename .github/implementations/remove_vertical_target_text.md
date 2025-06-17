# Remove Vertical Target Text in Mobile View

## Problem
In the mobile view of the program details tables, there's an unwanted vertical "Target:" text appearing in the content area of the table, below the "Program Target" header. This text only appears on mobile screens and needs to be removed.

## Analysis Required
- [x] Identify the CSS rule that's creating the vertical text
- [x] Determine which CSS file contains this rule
- [x] Create a fix that removes the vertical text while preserving the mobile layout

## Implementation Steps

### Step 1: Locate the CSS Rule
- [x] Search for mobile-specific CSS rules in the responsive-performance-table.css and admin-performance-table.css files
- [x] Find CSS rules targeting target-cell that might be adding the vertical text in mobile view

### Step 2: Modify CSS
- [x] Found the CSS rule in table-word-wrap.css that's adding the "Target:" text
- [x] Modified the CSS selector to exclude both .performance-table and .program-details-table
- [x] Ensured the fix works for both admin and agency views

### Step 3: Test
- [x] Test the fix on mobile view to ensure the vertical "Target:" text is gone
- [x] Verify that the rest of the mobile layout remains intact
- [x] Verify that desktop view is unaffected

## IMPLEMENTATION COMPLETED ✅

**Root Cause:**
The vertical "Target:" text was coming from a CSS rule in `table-word-wrap.css` that added this text before elements with the `.target-cell` class. The rule specifically excluded cells within `.performance-table` but didn't exclude cells within our new unified `.program-details-table`.

**Fix Applied:**
- Updated the CSS selector to exclude both `.performance-table` and `.program-details-table` classes:
```css
.target-cell:not(.performance-table .target-cell):not(.program-details-table .target-cell):before
```

**Benefits:**
- Removes the duplicate "Target:" text in the mobile view
- Maintains consistency across admin and agency views
- Preserves the existing mobile layout and styling
- No impact on desktop view

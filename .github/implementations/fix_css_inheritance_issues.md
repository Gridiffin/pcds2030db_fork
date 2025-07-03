# Fix CSS Inheritance Issues After Style Refactor

## Problem
After moving inline styles to CSS files, the styles are now affecting other tables across the project:
1. Header text is now black everywhere, but should be white for other tables and only black for program tables
2. Weird "file icons" (text-center:before elements) appearing in random rows before icons and badges

## Root Cause
- CSS styles were made too generic and are affecting all tables globally
- Pseudo-elements are being applied unintentionally
- Need to scope styles specifically to program tables only

## Tasks
- [x] Analyze current CSS files to identify problematic selectors
- [x] Make header text styles specific to program tables only
- [x] Fix pseudo-element issue causing file icons
- [x] Test changes across different pages to ensure no regression
- [x] Update documentation

## Files to Check/Modify
- ✅ `assets/css/pages/view-programs.css` - Scoped table header styles to program cards only
- ✅ `assets/css/components/tables.css` - Removed problematic global pseudo-element
- `assets/css/main.css` - No changes needed
- Other table pages - Should now work correctly

## Implementation Details

### Issue 1: Header Text Color
**Problem**: `.table thead th` selector in `view-programs.css` was too generic and affected all tables globally

**Solution**: Changed from:
```css
.table thead th {
    color: #495057; /* Black text */
}
```

To scoped selectors:
```css
.draft-programs-card .table thead th,
.finalized-programs-card .table thead th {
    color: #495057; /* Black text only for program tables */
}
```

### Issue 2: File Icons in Table Cells
**Problem**: Global `::before` pseudo-elements were adding FontAwesome file icons to all `.text-center` table cells

**Solution**: Removed problematic pseudo-elements from both files:
- Removed from `view-programs.css`: `.table tbody tr td.text-center::before`
- Removed from `tables.css`: `.table tbody tr td.text-center::before`

### Issue 3: Card Title Color
**Problem**: `.card-header .card-title` selector was too generic and affecting all card titles globally

**Solution**: Changed from:
```css
.card-header .card-title {
    color: #2c3e50; /* Dark text affecting all cards */
}
```

To scoped selectors:
```css
.draft-programs-card .card-header .card-title,
.finalized-programs-card .card-header .card-title {
    color: #ffffff; /* White text only for program cards */
}
```

### Files Modified
1. **`view-programs.css`**:
   - Scoped table header styles to `.draft-programs-card` and `.finalized-programs-card`
   - Scoped card title styles to program cards only with white color
   - Scoped badge styles to program cards only
   - Removed global `::before` pseudo-element
   - Added scoped empty state styling

2. **`tables.css`**:
   - Removed global `::before` pseudo-element that was causing file icons

## Status: ✅ COMPLETE

All CSS inheritance issues have been resolved:

1. **Header Text Color**: Table headers are now scoped to program tables only
   - Program tables: Black headers (`.draft-programs-card .table thead th`)
   - Other tables: Default white headers (Bootstrap default)

2. **File Icons**: Removed problematic `::before` pseudo-elements
   - No more unwanted file icons in table cells
   - Clean table display across all pages

3. **Card Title Color**: Card titles are now scoped to program cards only
   - Program cards: White card titles (`.draft-programs-card .card-header .card-title`)
   - Other cards: Default Bootstrap styling

The fixes are targeted and should not affect other parts of the application.

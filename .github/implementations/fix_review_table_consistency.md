# Fix: Review Table Consistency - Status Description Column

## Problem
The review step (overview) in the create program wizard had inconsistent table structures for displaying targets:

1. **Manual form updates** (`updateReviewTargets()` function): Table was missing "Status Description" column
2. **Loading saved data** (`populateReviewFromData()` function): Table included "Status Description" column

This caused the review table to show different columns depending on how the data was populated.

## Solution
Updated the `updateReviewTargets()` function to match the table structure used in `populateReviewFromData()`:

### Changes Made

**1. Updated `updateReviewTargets()` function** (around line 790):
- Added "Status Description" column to table header
- Added status description field collection: `entry.querySelector('.target-status-description')?.value || '-'`
- Added status description to table row output with same styling as the other function
- Added HTML escaping using `escapeHtml()` for security consistency

**2. Updated static HTML table header** (around line 461):
- Changed from 2 columns (Target, Status) to 6 columns (Target #, Number, Description, Status, Status Description, Timeline)
- Now matches the dynamic table structure created by JavaScript

### Technical Details

**Before (updateReviewTargets function):**
```javascript
let targetsHtml = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Target #</th><th>Number</th><th>Description</th><th>Status</th><th>Timeline</th></tr></thead><tbody>';
```

**After (updateReviewTargets function):**
```javascript
let targetsHtml = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Target #</th><th>Number</th><th>Description</th><th>Status</th><th>Status Description</th><th>Timeline</th></tr></thead><tbody>';
```

**Before (static HTML):**
```html
<tr>
    <th>Target</th>
    <th>Status</th>
</tr>
```

**After (static HTML):**
```html
<tr>
    <th>Target #</th>
    <th>Number</th>
    <th>Description</th>
    <th>Status</th>
    <th>Status Description</th>
    <th>Timeline</th>
</tr>
```

## Result
- Both manual form updates and saved data loading now show the same table structure
- All target information including status descriptions is consistently displayed in the review step
- Review table matches the edit page functionality and data display
- Security maintained with HTML escaping

## Files Modified
- `c:\laragon\www\pcds2030_dashboard\app\views\agency\programs\create_program.php`

## Status
✅ **COMPLETED** - Review table now consistently shows all target information including status descriptions regardless of how the data is populated.

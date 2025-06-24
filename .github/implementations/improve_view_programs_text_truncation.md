# Improve View Programs Page Text Truncation

## Problem Description
The View Programs page currently doesn't handle long program names properly, causing table layout issues and poor user experience. The View All Sectors page has a much better approach with text truncation and tooltips that should be applied to the View Programs page.

## Current Issues
- ❌ No width constraints for program name column
- ❌ Long program names break table layout
- ❌ No tooltips for full program names
- ❌ Inconsistent styling compared to All Sectors page

## Solution Overview
Apply the clean text truncation approach from View All Sectors page while maintaining the rich metadata (program numbers, badges, type indicators) that the View Programs page provides.

## Implementation Steps

### Phase 1: Apply Text Truncation to Program Name Column
- [x] Add `text-truncate` class and `max-width` to program name cells
- [x] Wrap program names in spans with `title` attributes for tooltips
- [x] Maintain existing badges and indicators within the truncated structure
- [x] Apply to both draft and finalized program tables

### Phase 2: CSS Enhancements
- [x] Add supporting CSS classes for consistent styling
- [x] Ensure proper responsive behavior
- [x] Test truncation with various program name lengths

### Phase 3: Testing and Validation
- [x] Test with long program names
- [x] Verify tooltips work correctly
- [x] Ensure responsive design is maintained
- [x] Check that all existing functionality is preserved

## Technical Approach

### Target Structure (Based on All Sectors approach):
```html
<td class="text-truncate" style="max-width: 250px;">
    <div class="fw-medium">
        <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
            <?php if (!empty($program['program_number'])): ?>
                <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
            <?php endif; ?>
            <?php echo htmlspecialchars($program['program_name']); ?>
        </span>
        <?php if ($is_draft): ?>
            <span class="draft-indicator" title="Draft"></span>
        <?php endif; ?>
    </div>
    <div class="small text-muted program-type-indicator">
        <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
        <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
    </div>
</td>
```

### Key Changes:
1. **Add `text-truncate` class** to table cell
2. **Set `max-width: 250px`** (slightly larger than All Sectors to accommodate badges)
3. **Wrap program name in span** with `title` attribute for tooltip
4. **Maintain all existing badges and indicators**
5. **Preserve program type indicators**

## Files to Modify
- `app/views/agency/programs/view_programs.php` - Main implementation
- Update CSS if needed for enhanced styling

## Expected Benefits
- ✅ Consistent table layout regardless of program name length
- ✅ Better user experience with tooltips for full names
- ✅ Maintains all existing functionality and metadata
- ✅ Consistent styling with All Sectors page
- ✅ Improved responsive design

## Implementation Summary

### Changes Made

1. **Modified Program Name Cells (Both Draft & Finalized Tables)**:
   - Added `text-truncate` class and `max-width: 250px` to table cells
   - Wrapped program names in `<span class="program-name" title="...">` for tooltips
   - Maintained all existing badges (program numbers) and indicators (draft status)
   - Preserved program type indicators (Assigned vs Agency-Created)

2. **Enhanced CSS Styling**:
   - Added `.program-name` class with hover effects and truncation support
   - Improved tooltip behavior with `cursor: help`
   - Ensured responsive behavior with proper overflow handling
   - Added transition effects for better user experience

### Technical Implementation Details

**Before:**
```html
<td>
    <div class="fw-medium">
        <span class="badge bg-info me-2">1.1</span>
        Very Long Program Name That Could Break Layout
    </div>
</td>
```

**After:**
```html
<td class="text-truncate" style="max-width: 250px;">
    <div class="fw-medium">
        <span class="program-name" title="Very Long Program Name That Could Break Layout">
            <span class="badge bg-info me-2">1.1</span>
            Very Long Program Name That Could Break Layout
        </span>
    </div>
</td>
```

### Files Modified
- ✅ `app/views/agency/programs/view_programs.php` - Applied text truncation to both draft and finalized program tables
- ✅ Added enhanced CSS styling for improved user experience

### Testing Results
- ✅ Long program names now display with clean ellipsis truncation
- ✅ Tooltips show full program names on hover
- ✅ Table layout remains consistent regardless of name length
- ✅ All existing functionality (badges, indicators, actions) preserved
- ✅ Responsive design maintained across screen sizes

## Status: ✅ COMPLETED

The View Programs page now uses the same clean text truncation approach as the View All Sectors page while maintaining all its rich metadata and functionality. The implementation provides a consistent user experience across both pages.

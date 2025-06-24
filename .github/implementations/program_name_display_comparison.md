# Program Name Display Comparison: View Programs vs View All Sectors

## Problem Analysis
The user noticed differences in how long program names are handled between the "View Programs" page and the "View All Sectors" page. This analysis compares the two approaches and identifies the key differences.

## Current Implementation Comparison

### View All Sectors Page (`app/views/agency/sectors/view_all_sectors.php`)

**Table Structure:**
```html
<td class="text-truncate" style="max-width: 200px;">
    <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
        <?php echo htmlspecialchars($program['program_name']); ?>
    </span>
</td>
```

**Key Features:**
- ✅ Uses Bootstrap's `text-truncate` class
- ✅ Inline `max-width: 200px` constraint
- ✅ Full program name shown in tooltip via `title` attribute
- ✅ Simple, clean approach with ellipsis for overflow
- ✅ No additional program metadata (numbers, badges, etc.)

### View Programs Page (`app/views/agency/programs/view_programs.php`)

**Table Structure:**
```html
<td>
    <div class="fw-medium">
        <?php if (!empty($program['program_number'])): ?>
            <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
        <?php endif; ?>
        <?php echo htmlspecialchars($program['program_name']); ?>
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

**Key Features:**
- ❌ No explicit width constraint or text truncation
- ✅ Rich program metadata (program numbers as badges)
- ✅ Program type indicators (Assigned vs Agency-Created)
- ✅ Draft status indicators
- ❌ Long program names can break table layout
- ❌ More complex structure that takes up more vertical space

## Key Differences Identified

### 1. **Text Overflow Handling**
- **All Sectors**: Uses `text-truncate` + `max-width` for clean ellipsis
- **View Programs**: No overflow protection, text can expand indefinitely

### 2. **Content Complexity**
- **All Sectors**: Simple program name only
- **View Programs**: Program name + badges + type indicators + draft status

### 3. **Vertical Space Usage**
- **All Sectors**: Single line per program
- **View Programs**: Multi-line with metadata below program name

### 4. **User Experience**
- **All Sectors**: Hover tooltip shows full name for truncated text
- **View Programs**: No tooltip, but shows more context visually

## Recommended Solutions

### Option 1: Apply All Sectors Approach to View Programs (Simple)
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

### Option 2: Hybrid Approach (Advanced)
```css
.program-name-cell {
    max-width: 300px;
}

.program-name-wrapper {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.program-name-wrapper:hover {
    white-space: normal;
    overflow: visible;
    position: relative;
    z-index: 10;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 4px;
    border-radius: 4px;
}
```

### Option 3: Consistent Styling (Recommended)
Apply unified approach across both pages:

1. **Standardize column widths**
2. **Use consistent overflow handling**
3. **Implement uniform tooltip system**
4. **Maintain visual hierarchy**

## Implementation Priority

### High Priority
- [x] Analyze differences between pages
- [x] Apply text truncation to View Programs page
- [x] Add tooltips for full program names
- [x] Test responsive behavior

### Medium Priority
- [x] Standardize table column widths across pages ✅ **COMPLETE**
- [x] Implement hover effects for better UX ✅ **COMPLETE** 
- [x] Add CSS classes for reusable components ✅ **COMPLETE**
- [x] Apply unified display method for both columns ✅ **COMPLETE**

### Low Priority
- [ ] Consider expanding tooltip content with additional metadata
- [ ] Implement click-to-expand functionality for very long names

## Technical Notes

1. **Bootstrap Classes Used:**
   - `text-truncate`: Handles text overflow with ellipsis
   - `fw-medium`: Medium font weight for emphasis
   - `small text-muted`: Secondary information styling

2. **CSS Properties:**
   - `max-width`: Constrains element width
   - `overflow: hidden`: Prevents text overflow
   - `text-overflow: ellipsis`: Shows "..." for truncated text
   - `white-space: nowrap`: Prevents text wrapping

3. **Accessibility Considerations:**
   - `title` attribute provides full text for screen readers
   - Proper semantic structure maintained
   - Color contrast preserved for badges and indicators

## Conclusion

The **View All Sectors** page uses a more robust approach for handling long program names with proper truncation and tooltips. The **View Programs** page needs similar overflow protection while maintaining its rich metadata display. The recommended approach is to apply the truncation technique from All Sectors while preserving the additional context elements from View Programs.

---

## ✅ IMPLEMENTATION COMPLETE - FINAL VERSION

### Final Solution Applied
The **View Programs** page now has **proper text truncation and white box hover expansion** for both program names and initiative names, achieving perfect consistency with the **View All Sectors** page behavior.

#### Current Implementation:
```php
// Program Name Column (300px max-width)
<td class="text-truncate" style="max-width: 300px;">
    <span class="program-name" title="Full Program Name">
        [Badge] Program Name Text
    </span>
</td>

// Initiative Column (250px max-width) 
<td class="text-truncate" style="max-width: 250px;">
    <div class="d-flex align-items-center" title="Full Initiative Name">
        <span class="badge">[INI001]</span>
        <span class="initiative-name">Initiative Name Text</span>
    </div>
</td>
```

#### CSS Components Created:
**File**: `assets/css/components/table-text-truncation.css`
- `.program-name` - Handles program name truncation and white box hover expansion
- `.initiative-name` - Handles initiative name truncation and white box hover expansion  
- Both classes provide identical hover behavior with white overlay, shadow, and full text display

#### Modular CSS Organization:
- ✅ **Separated from base.css**: Moved table-specific styles to dedicated component file
- ✅ **Proper Import**: Added to `main.css` imports for centralized CSS management
- ✅ **Reusable Components**: Can be used across any table in the application

#### Column Specifications:
- **Program Name Column**: `max-width: 300px` with truncation and hover expansion
- **Initiative Column**: `max-width: 250px` with truncation and hover expansion

#### Final Achievements:
1. ✅ **Perfect Text Truncation**: Both columns truncate long text with ellipsis
2. ✅ **White Box Hover Expansion**: Both columns show full text in white overlay on hover
3. ✅ **Visual Consistency**: Initiative badges preserved while adding truncation behavior
4. ✅ **Balanced Layout**: Proper space distribution (300px + 250px)
5. ✅ **Modular CSS**: Organized components properly separated from base styles
6. ✅ **Reusable Components**: Table text truncation can be used throughout the app

#### Benefits:
- **For Users**: Consistent hover expansion behavior across all text columns
- **For Developers**: Modular CSS components, easy to maintain and extend
- **For Performance**: Clean CSS organization and optimized hover effects

🎉 **Task Complete**: Program names and initiative names now have identical text truncation and white box hover expansion behavior, with properly organized modular CSS components!

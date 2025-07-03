# Create Specific Card Title Class for View Programs

## Problem
Need a specific CSS class for the view programs card titles to control their color independently from other card titles across the project.

## Tasks
- [x] Create a specific CSS class for view programs card titles
- [x] Set the color to black
- [x] Ensure it only affects the program view cards
- [x] Update the view programs page to use the new class

## Implementation Details

### New CSS Class
Created `.view-programs-card-title` class that:
- Sets color to black (#2c3e50) with !important for specificity
- Maintains proper font weight (600) and styling
- Includes flexbox alignment for icons and text
- Zero margin for clean layout
- Only affects elements with this specific class

### CSS Implementation
```css
.view-programs-card-title {
    font-weight: 600;
    color: #2c3e50 !important;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
```

### HTML Update
Updated both card titles in `view_programs.php`:

**Draft Programs Card:**
```html
<h5 class="card-title view-programs-card-title m-0 d-flex align-items-center">
    <i class="fas fa-edit text-warning me-2"></i>
    Draft Programs 
    <!-- badges and content -->
</h5>
```

**Finalized Programs Card:**
```html
<h5 class="card-title view-programs-card-title m-0 d-flex align-items-center">
    <i class="fas fa-check-circle text-success me-2"></i>
    Finalized Programs 
    <!-- badges and content -->
</h5>
```

### Files Modified
- ✅ `assets/css/pages/view-programs.css` - Added the new class
- ✅ `app/views/agency/programs/view_programs.php` - Updated both card titles

## Status: ✅ COMPLETE

The `.view-programs-card-title` class has been successfully implemented and applied:
- **Black color**: #2c3e50 for both card titles
- **Applied to both cards**: Draft Programs and Finalized Programs cards
- **Clean implementation**: Uses the new class alongside existing Bootstrap classes
- **No conflicts**: The !important declaration ensures the black color takes precedence

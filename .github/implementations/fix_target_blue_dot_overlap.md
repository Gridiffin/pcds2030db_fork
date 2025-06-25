# Fix Target Blue Dot Overlap Issue

## Problem
The blue dot element in the target items is overlapping with the target counter, making it hard to read. The CSS path shows that there's a `.text-primary` class on an `h6.target-number` element that's causing visual issues.

## Root Cause
Based on the CSS path provided:
```
html body.admin-layout.page-loaded div.d-flex.flex-column.min-vh-100 div.content-wrapper.admin-content div.container-fluid div.row div.col-12 div.card div.card-body form#edit-program-form div.mb-4 div#targets-container div.target-item.border.rounded.p-3.mb-3 div.d-flex.justify-content-between.align-items-center.mb-2 h6.target-number.mb-0.text-primary
```

The issue is that there's a target numbering system with `h6.target-number.mb-0.text-primary` that has a blue color (`.text-primary`) and possibly some CSS pseudo-elements or styling that's creating overlap.

## Solution Options
1. **Remove the blue dot styling** - Clean up any pseudo-elements or visual elements causing overlap
2. **Improve the layout** - Better spacing and positioning to prevent overlap  
3. **Make the counter more prominent** - Increase contrast and visibility as user suggested

## Tasks
- [x] Identify where the target numbering HTML structure comes from
- [x] Check if there are CSS pseudo-elements causing the blue dot
- [x] Fix the JavaScript template for adding new targets
- [x] Improve the target item layout to prevent overlap
- [x] Test the visual appearance after changes

## Implementation Details
1. **Root Cause Found**: The `.target-number` class in `assets/css/components/period-performance.css` creates a circular blue badge (24px x 24px) with white text, causing overlap when used as a heading class.

2. **Fixed HTML Structure**: Updated both PHP and JavaScript to include proper target numbering headers:
   ```php
   <div class="d-flex justify-content-between align-items-center mb-2">
       <h6 class="mb-0 fw-bold target-number">Target <?php echo ($index + 1); ?></h6>
   </div>
   ```

3. **Added CSS Override**: Custom styles in the edit_program.php file to override the circular badge styling:
   ```css
   #edit-program-form .target-number {
       display: block !important;
       width: auto !important;
       height: auto !important;
       background: none !important;
       color: var(--bs-dark) !important;
       border-radius: 0 !important;
       font-size: 1rem !important;
       font-weight: 600 !important;
   }
   ```

4. **Improved JavaScript**: Added automatic target renumbering when targets are added or removed.

## Result
- ✅ No more blue dot overlap
- ✅ Clear, readable target numbering
- ✅ Consistent styling between existing and new targets
- ✅ Automatic renumbering when targets are added/removed

# Design Document: Header Visibility Fix

## Overview

This document outlines the design for fixing the header visibility issues in the PCDS 2030 Dashboard. The current header has two main problems: (1) the breadcrumbs are not visible due to the positioning of the header box, and (2) elements are being covered by the navbar which is currently fixed at the top of the page. The solution will modify the CSS for both the navbar and page header components to address these issues while maintaining compatibility with the existing design system. The key change will be converting the navbar from fixed to static positioning.

## Architecture

The fix will be implemented by modifying both the navbar and page header CSS files. No changes to the PHP component structure are required, as this is purely a styling issue.

### Component Structure

The existing component structure will be maintained:

```
app/views/layouts/page_header.php   # Main component file (no changes needed)
app/views/layouts/admin_nav.php     # Admin navbar file (no changes needed)
assets/css/layout/page_header.css   # Page header CSS file to be modified
assets/css/layout/navigation.css    # Navigation CSS file to be modified
```

## Components and Interfaces

### CSS Modifications

The following CSS modifications will be made to the page header component:

1. **Adjust Vertical Positioning**:
   - Increase the top padding of the `.page-header` class to create more space between the navigation bar and the header content
   - This will ensure the breadcrumbs are fully visible

2. **Text Color Changes**:
   - Change the text color of all header elements (title, subtitle, breadcrumbs) to white
   - Ensure proper contrast against the background colors
   - Update hover states for breadcrumb links to maintain visibility

3. **Breadcrumb Visibility Improvements**:
   - Adjust the margin and padding of the breadcrumb container to ensure proper positioning
   - Ensure breadcrumb separators have appropriate color and contrast

### HTML Structure

No changes to the HTML structure are required. The existing structure is as follows:

```html
<header class="page-header [additional classes]">
    <div class="container">
        <div class="row">
            <!-- Breadcrumb (Left-aligned) -->
            <div class="col-12">
                <nav aria-label="breadcrumb" class="page-header__breadcrumb">
                    <ol class="breadcrumb">
                        <!-- Breadcrumb items -->
                    </ol>
                </nav>
            </div>
            
            <!-- Title and Subtitle (Centered) -->
            <div class="col-12 text-center">
                <h1 class="page-header__title">[Title]</h1>
                <p class="page-header__subtitle">[Subtitle]</p>
            </div>
        </div>
    </div>
</header>
```

## Data Models

No changes to data models are required for this fix.

## Error Handling

No specific error handling is required for this CSS-only fix.

## Testing Strategy

The header visibility fix will be tested in the following ways:

1. **Visual Testing**:
   - Verify the breadcrumbs are fully visible on all pages
   - Verify all text in the header is white and readable
   - Verify proper contrast between text and background

2. **Responsive Testing**:
   - Test on mobile, tablet, and desktop viewports
   - Ensure the fix works across different screen sizes

3. **Cross-Browser Testing**:
   - Test in Chrome, Firefox, Safari, and Edge
   - Ensure consistent appearance across browsers

## Design Decisions and Rationale

### Increased Top Padding

The decision to increase the top padding of the header box is based on the need to create more space between the navigation bar and the header content. This will ensure the breadcrumbs are fully visible without overlapping with other elements.

```css
.page-header {
    padding: 2.5rem 0 1.5rem; /* Increased top padding */
    /* other properties remain the same */
}
```

### White Text Color

Changing the text color to white will ensure better visibility against the colored background of the header. This applies to all text elements in the header, including the title, subtitle, and breadcrumbs.

```css
.page-header__title,
.page-header__subtitle,
.page-header .breadcrumb-item,
.page-header .breadcrumb-item.active,
.page-header .breadcrumb-item + .breadcrumb-item::before {
    color: #ffffff;
}

.page-header .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.9);
}

.page-header .breadcrumb-item a:hover {
    color: #ffffff;
}
```

### Maintaining Theme Variants

The existing theme variants (light, dark, primary, secondary) will be maintained, but with adjustments to ensure text is always white for better visibility. This approach ensures compatibility with the existing design system while addressing the visibility issues.

### Responsive Considerations

The fix will maintain the existing responsive behavior of the header component, ensuring that the breadcrumbs remain visible and properly positioned across different screen sizes. The existing media queries will be preserved with appropriate adjustments to maintain consistency.
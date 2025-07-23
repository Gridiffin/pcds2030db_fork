# Design Document: Page Header Redesign

## Overview

This document outlines the design for the new page header component of the PCDS 2030 Dashboard. The redesigned header will feature a clean, simple layout with centered title and subtitle, and a left-aligned breadcrumb. The design will ensure visual separation from other page elements while maintaining a cohesive flow throughout the application.

## Architecture

The page header will be implemented as a reusable PHP component that can be included in the base layout. It will maintain the existing configuration pattern but with simplified structure and styling.

### Component Structure

```
app/views/layouts/page_header.php   # Main component file
assets/css/layouts/page_header.css   # Dedicated CSS file
```

The CSS will be imported into the main application stylesheet to ensure it's available across all pages.

## Components and Interfaces

### PHP Component

The page header component will accept the following configuration parameters:

```php
$header_config = [
    'title' => 'Page Title',              // Required: Main page title
    'subtitle' => 'Optional subtitle',     // Optional: Secondary text below title
    'breadcrumb' => [                     // Optional: Breadcrumb navigation items
        [
            'text' => 'Home',
            'url' => '/index.php'
        ],
        [
            'text' => 'Current Page',
            'url' => null                 // No URL for current page
        ]
    ],
    'classes' => ''                       // Optional: Additional CSS classes
];
```

### HTML Structure

The page header will use the following HTML structure:

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

### CSS Design

The page header will use the following CSS approach:

```css
/* Page Header Component */
.page-header {
    padding: 1.5rem 0;
    margin-bottom: 1.5rem;
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.page-header__title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
}

.page-header__subtitle {
    font-size: 1rem;
    color: #6c757d;
    margin-bottom: 0;
}

.page-header__breadcrumb {
    margin-bottom: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .page-header {
        padding: 1rem 0;
    }
    
    .page-header__title {
        font-size: 1.5rem;
    }
    
    .page-header__subtitle {
        font-size: 0.875rem;
    }
}
```

## Data Models

The page header component doesn't require specific data models beyond the configuration array passed to it. It will use the following data structures:

1. `$header_config` - Main configuration array
2. `$breadcrumb` - Array of breadcrumb items, each with text and URL

## Error Handling

The component will implement the following error handling:

1. Default values for all configuration parameters to prevent errors if they're missing
2. Validation of breadcrumb items to ensure they have the required properties
3. Graceful degradation if certain elements are not provided (e.g., hiding subtitle if not provided)

## Testing Strategy

The page header component will be tested in the following ways:

1. **Visual Testing**: Verify the header appears correctly across different pages and screen sizes
2. **Configuration Testing**: Test with various configuration options to ensure flexibility
3. **Integration Testing**: Verify the header works correctly within the base layout
4. **Responsive Testing**: Ensure proper display on mobile, tablet, and desktop viewports

## Design Decisions and Rationale

### Centered Title and Subtitle

The title and subtitle will be centered to create a clear visual hierarchy and focal point at the top of each page. This aligns with modern web design practices and creates a clean, professional appearance.

### Left-Aligned Breadcrumb

The breadcrumb will be left-aligned to maintain conventional navigation patterns. Users typically expect breadcrumbs to start from the left, following the natural reading direction in English.

### Visual Separation

The header will use subtle background color and border to create visual separation from the navbar and main content. This ensures users can clearly distinguish between different sections of the page while maintaining a cohesive design.

### Bootstrap Integration

The design leverages Bootstrap's grid system and utility classes to ensure consistency with the rest of the application and to simplify responsive behavior.

### Simplified Structure

The new design removes unnecessary complexity from the previous header implementation, focusing only on the essential elements (title, subtitle, breadcrumb) as requested.
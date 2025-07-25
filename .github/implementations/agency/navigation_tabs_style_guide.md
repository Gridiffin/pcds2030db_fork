# Navigation Tabs Style Guide

## Overview
This style guide provides standardized patterns for implementing navigation tabs across the application, based on the successful implementation in the view programs page.

## Basic Tab Structure

### HTML Structure
```html
<div class="card shadow-sm mb-4">
    <div class="card-header p-0">
        <nav class="nav nav-tabs card-header-tabs" id="tabNavigation" role="tablist">
            <button class="nav-link active" 
                    id="tab1-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#tab1-content" 
                    type="button" 
                    role="tab" 
                    aria-controls="tab1-content" 
                    aria-selected="true">
                <i class="fas fa-icon text-primary me-2"></i>
                Tab Label
                <span class="badge bg-primary ms-2">Count</span>
            </button>
            <!-- Additional tabs... -->
        </nav>
    </div>
    
    <div class="tab-content" id="tabNavigationContent">
        <div class="tab-pane fade show active" 
             id="tab1-content" 
             role="tabpanel" 
             aria-labelledby="tab1-tab">
            <!-- Tab content -->
        </div>
        <!-- Additional tab panes... -->
    </div>
</div>
```

## CSS Styling Standards

### Tab Navigation Styling
```css
/* Enhanced Card Headers */
.card-header {
    padding: 1.5rem 2rem;
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

/* Tab Links */
.nav-tabs .nav-link {
    font-size: 1.1rem;
    font-weight: 600;
    padding: 1rem 1.5rem;
    color: #495057;
    border: none;
    border-radius: 0;
    transition: all 0.3s ease;
}

/* Active Tab */
.nav-tabs .nav-link.active {
    font-weight: 700;
    color: #007bff;
    background-color: #fff;
    border: none;
    border-bottom: 3px solid #007bff;
    position: relative;
}

/* Tab Hover Effects */
.nav-tabs .nav-link:hover:not(.active) {
    color: #0056b3;
    background-color: rgba(0, 123, 255, 0.05);
    border-color: transparent;
}

/* Tab Badges */
.nav-tabs .nav-link .badge {
    font-size: 0.85rem;
    padding: 0.4rem 0.6rem;
    border-radius: 0.375rem;
    margin-left: 0.5rem;
}

/* Tab Icons */
.nav-tabs .nav-link i {
    margin-right: 0.5rem;
    font-size: 1rem;
}
```

## Tab Content Standards

### Tab Pane Structure
```html
<div class="tab-pane fade" id="tab-content" role="tabpanel" aria-labelledby="tab-link">
    <div class="card-body p-0">
        <!-- Header Section (Optional) -->
        <div class="p-4 border-bottom">
            <h5 class="card-title m-0 d-flex align-items-center">
                <i class="fas fa-icon text-primary me-2"></i>
                Section Title
                <span class="badge bg-secondary ms-2">Count</span>
            </h5>
        </div>
        
        <!-- Main Content -->
        <div class="p-4">
            <!-- Tab content goes here -->
        </div>
    </div>
</div>
```

## Color Scheme Guidelines

### Status-Based Colors
- **Draft/In Progress**: `text-warning` (#ffc107), `bg-warning`
- **Completed/Success**: `text-success` (#28a745), `bg-success`
- **Information**: `text-info` (#17a2b8), `bg-info`
- **Templates/Neutral**: `text-secondary` (#6c757d), `bg-secondary`
- **Priority/Important**: `text-danger` (#dc3545), `bg-danger`
- **Primary Action**: `text-primary` (#007bff), `bg-primary`

### Badge Color Mapping
```css
/* Success States */
.badge.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

/* Warning States */
.badge.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
    color: #212529 !important;
}

/* Info States */
.badge.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%) !important;
}
```

## Responsive Design

### Mobile Optimization
```css
@media (max-width: 768px) {
    .nav-tabs .nav-link {
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
    }
    
    .nav-tabs .nav-link .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .nav-tabs .nav-link i {
        margin-right: 0.25rem;
    }
}

/* Stack tabs vertically on very small screens */
@media (max-width: 576px) {
    .nav-tabs {
        flex-direction: column;
    }
    
    .nav-tabs .nav-link {
        border-radius: 0.25rem;
        margin-bottom: 0.25rem;
        text-align: center;
    }
}
```

## Accessibility Standards

### Required Attributes
1. **Role Attributes**: Use `role="tablist"` on nav container, `role="tab"` on buttons, `role="tabpanel"` on content
2. **ARIA Labels**: Include `aria-controls`, `aria-selected`, and `aria-labelledby`
3. **Keyboard Navigation**: Ensure tab key navigation works properly
4. **Screen Reader Support**: Use descriptive text and proper heading structure

### Example with Full Accessibility
```html
<nav class="nav nav-tabs" role="tablist" aria-label="Content sections">
    <button class="nav-link active" 
            id="drafts-tab"
            data-bs-toggle="tab" 
            data-bs-target="#drafts"
            type="button"
            role="tab"
            aria-controls="drafts"
            aria-selected="true"
            aria-describedby="drafts-description">
        <i class="fas fa-edit text-warning me-2" aria-hidden="true"></i>
        Draft Items
        <span class="badge bg-warning text-dark ms-2" aria-label="3 draft items">3</span>
    </button>
</nav>
```

## JavaScript Integration

### Tab Switching Logic
```javascript
// Initialize Bootstrap tabs
const tabTriggerList = document.querySelectorAll('#tabNavigation button[data-bs-toggle="tab"]');
const tabList = [...tabTriggerList].map(tabTriggerEl => new bootstrap.Tab(tabTriggerEl));

// Handle tab change events
document.addEventListener('shown.bs.tab', function (event) {
    const targetId = event.target.getAttribute('data-bs-target');
    const targetPane = document.querySelector(targetId);
    
    // Update URL hash (optional)
    window.location.hash = targetId.substring(1);
    
    // Trigger any content-specific logic
    loadTabContent(targetId);
});

// Restore active tab from URL hash
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash) {
        const tabTrigger = document.querySelector(`button[data-bs-target="${hash}"]`);
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }
});
```

## Common Tab Patterns

### 1. Status-Based Tabs (Most Common)
- Draft/In Progress
- Completed/Finalized  
- Templates/Available
- Archived/Inactive

### 2. Category-Based Tabs
- By Department
- By Priority Level
- By Date Range
- By Type/Classification

### 3. Action-Based Tabs
- Create New
- Edit Existing
- Review/Approve
- Reports/Analytics

## Implementation Checklist

- [ ] Use semantic HTML structure with proper roles
- [ ] Include all required accessibility attributes
- [ ] Apply consistent styling classes
- [ ] Add appropriate icons and badges
- [ ] Implement responsive design
- [ ] Test keyboard navigation
- [ ] Test screen reader compatibility
- [ ] Add JavaScript for enhanced functionality
- [ ] Include loading states for dynamic content
- [ ] Test on mobile devices

## Best Practices

1. **Limit Tab Count**: Keep to 3-5 tabs maximum for optimal UX
2. **Clear Labels**: Use descriptive, concise tab labels
3. **Visual Hierarchy**: Use icons and badges to improve scannability
4. **Progressive Disclosure**: Load heavy content only when tab is activated
5. **State Persistence**: Remember user's last selected tab
6. **Error States**: Handle and display errors within appropriate tabs
7. **Loading States**: Show loading indicators for dynamic content
8. **Empty States**: Provide helpful messages when tabs have no content

## File Organization

When implementing tabs, organize files as follows:
```
assets/css/components/
├── navigation-tabs.css          # Main tab styling
├── navigation-tabs-responsive.css   # Mobile optimizations
└── navigation-tabs-themes.css      # Color variations

assets/js/components/
├── navigation-tabs.js           # Core functionality
└── navigation-tabs-utils.js     # Helper functions
```

This style guide ensures consistent, accessible, and maintainable tab implementations across the application.
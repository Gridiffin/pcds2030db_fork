# Initiative View Page Redesign

## Overview
Redesign the view_initiative.php page to follow the structure and design from initiative_progress_dashboard_mock.html while maintaining existing functionality and header format.

## Requirements
1. **Page Header**: Use generic header content from mock, follow existing header format (don't display initiative name as title)
2. **Initiative Structure**: Follow the exact structure from mock file (initiative overview section)
3. **Existing Elements**: Keep existing initiative elements for gradual adjustment

## Implementation Tasks

### Phase 1: Header Redesign
- [x] Update page header to use generic title from mock
- [x] Copy header content structure from mock
- [x] Maintain existing header format and breadcrumbs
- [x] Update subtitle to match mock style

### Phase 2: Initiative Overview Section
- [x] Create initiative header section following mock structure
- [x] Add initiative title with leaf icon
- [x] Add meta information row (initiative number, dates, elapsed time, status)
- [x] Style according to mock design

### Phase 3: Core Metrics Section
- [x] Add metric cards section structure
- [x] Implement timeline progress card
- [x] Implement health score card  
- [x] Implement status card
- [x] Keep existing content but restructure layout

### Phase 4: CSS Styling
- [x] Add CSS variables from mock
- [x] Implement metric card styles
- [x] Add hover effects and transitions
- [x] Ensure responsive design

### Phase 5: Integration and Testing
- [ ] Test with existing data
- [ ] Verify all existing functionality works
- [ ] Test responsive behavior
- [ ] Clean up any redundant code

## Design Elements to Copy from Mock

### CSS Variables
```css
--forest-deep: #4A6A52;
--forest-medium: #67885F;
--success-light: #d4edda;
--warning-light: #fff3cd;
--danger-light: #f8d7da;
--high-contrast-dark: #343a40;
--high-contrast-light: #f8f9fa;
--border-color: #ced4da;
```

### Structure Elements
1. Initiative header with title and meta information
2. Metric cards layout (3 columns)
3. Forest/nature theme styling
4. Enhanced shadows and borders

## Files to Modify
- `app/views/agency/initiatives/view_initiative.php`
- `assets/css/main.css` (for new styles)

## Notes
- Maintain all existing PHP functionality
- Keep existing database queries and data processing
- Preserve existing navigation and breadcrumbs
- Gradually adjust existing elements as mentioned by user

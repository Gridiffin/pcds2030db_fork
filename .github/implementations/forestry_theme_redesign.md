# Forestry Theme Redesign Implementation Plan

## Color Palette
```scss
--forest-deep: #537D5D    // Deep forest green for primary actions/emphasis
--forest-medium: #73946B  // Medium forest green for secondary elements
--forest-light: #9EBC8A   // Light forest green for backgrounds/accents
--forest-pale: #D2D0A0    // Pale sage for subtle highlights/backgrounds
```

## Phase 1: CSS Consolidation ✅
- [x] Create admin-common.css with forest theme components
  - [x] Tables
  - [x] Filter controls
  - [x] Status indicators
  - [x] User status styles
  - [x] Change indicators
  - [x] Program row states
  - [x] Custom alerts
  - [x] Period controls
  - [x] Badge styling
  - [x] Form controls
  - [x] Toast notifications
  - [x] Year groups/Accordion

- [x] Update core admin interface files
  - [x] admin/reporting_periods.php - Removed inline styles
  - [x] admin/programs.php - Removed inline styles
  - [x] admin/manage_users.php - Removed inline styles and applied forest theme
  - [x] admin/manage_metrics.php - Removed inline styles and applied forest theme
  - [x] admin/system_settings.php - Updated with forest theme components and enhanced UI
  - [x] admin/manage_programs.php - Applied forest theme styling

- [x] Create component-specific CSS files for agency views
  - [x] agency/view_programs.php - Moved to components/rating-indicators.css
  - [x] agency/view_all_sectors.php - Moved to components/sectors-view.css
  - [x] agency/create_metric_detail.php - Moved to components/metric-details.css
  - [x] agency/create_metric.php - Moved styles to dedicated components
  - [x] agency/all_notifications.php - Applied forest theme styling

- [ ] Update main.css imports to include new component files
- [ ] Remove all inline styles from PHP files
- [ ] Verify no styling breaks after consolidation

## Phase 2: Typography Update ✅
- [x] Add Poppins font configuration
  - [x] Update font preloading in header.php
  - [x] Update variables.css with new font definitions
  - [x] Add font weights: 300 (light), 400 (regular), 500 (medium), 600 (semibold)
- [x] Remove Nunito font references

## Phase 3: Color Scheme Implementation ✅
- [x] Update variables.css with new forest theme colors
- [x] Update component colors:
  - [x] Buttons
  - [x] Cards
  - [x] Tables
  - [x] Forms
  - [x] Alerts/Notifications
  - [x] Status badges
  - [x] Navigation
  - [x] Progress indicators  - [x] Modals
  - [x] Toast notifications
  - [x] Status indicators

## Phase 4: Minimal UI Enhancements ✅
- [x] Update spacing and padding for cleaner look (in completed components)
- [x] Implement subtle transitions for interactive elements
- [x] Ensure consistent border-radius across components
- [x] Review and adjust component shadows for depth
- [x] Verify accessibility (contrast ratios) with new colors
- [x] Fine-tune hover and active states
- [x] Add subtle animations for interactive feedback

## Phase 5: Style Guide & Documentation 🟨
- [x] Update style-guide.php to showcase:
  - [x] Color palette
  - [x] Typography
  - [x] Layout components
  - [x] Core UI components
    - [x] Buttons
    - [x] Cards
    - [x] Tables
    - [x] Forms
    - [x] Navigation
    - [x] Progress bars
    - [x] Alerts
    - [x] Modals
  - [x] Dashboard components
  - [x] Program management components
  - [x] Interface elements
  - [x] Admin & agency specific components
- [x] Add component documentation and usage examples
- [x] Include accessibility guidelines

## Phase 6: Testing & Refinement ✅
- [x] Test in multiple browsers
  - [x] Chrome
  - [x] Firefox
  - [ ] Safari (limited testing)
  - [x] Edge
- [x] Verify responsive design
  - [x] Mobile (320px+)
  - [x] Tablet (768px+)
  - [x] Desktop (1024px+)
  - [x] Large Desktop (1440px+)
- [ ] Check dark mode compatibility (deferred to future enhancement)
- [x] Performance testing
  - [x] CSS file size optimization (script created)
  - [x] Reduce unused styles
  - [x] Check render performance
- [x] Testing tools created
  - [x] Cross-browser testing scripts for Windows and Linux/Mac
  - [x] Responsive design testing tools
  - [x] Basic accessibility checker
  - [x] CSS optimization script
- [ ] Get stakeholder feedback (in progress)
  - [ ] Admin interface review
  - [ ] Agency interface review
  - [x] Accessibility review

## Completed Implementation
1. ✅ Complete navigation styling with forest theme
2. ✅ Implement progress indicators with forest theme colors
3. ✅ Complete accessibility verification of existing components
4. ✅ Update documentation in style-guide.php
5. ✅ Complete cross-browser testing for all components
6. ✅ Create testing tools for ongoing maintenance
7. ✅ Create performance optimization scripts
8. ✅ Document the implementation and testing process

## Next Steps for Deployment
1. 📋 Collect stakeholder feedback and make final adjustments
2. 📋 Perform final CSS optimizations using the provided script
3. 📋 Conduct a live testing session with agency users
4. 📋 Plan for gradual rollout across all dashboard instances

## Notes
- Keep interface professional and clean for government usage
- Maintain high contrast for readability
- Ensure all interactive elements are obviously interactive
- Maintain consistency across admin and agency interfaces
- Document all color variables and utility classes
- Add comments for complex CSS calculations or effects

## Notes
- Keep interface professional and clean for government usage
- Maintain high contrast for readability
- Ensure all interactive elements are obviously interactive
- Maintain consistency across admin and agency interfaces

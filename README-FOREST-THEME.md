# PCDS2030 Dashboard - Forest Theme Implementation

## Overview
The Forest Theme is a complete redesign of the PCDS2030 Dashboard interface, transitioning from the previous blue-oriented theme to a green color palette that better aligns with forestry and environmental programs. This implementation includes comprehensive styling, component updates, and accessibility considerations.

## Color Palette
The Forest Theme uses a carefully selected color palette:

- **Forest Deep (#537D5D)**: Deep forest green for primary actions and emphasis
- **Forest Medium (#73946B)**: Medium forest green for secondary elements
- **Forest Light (#9EBC8A)**: Light forest green for backgrounds and accents
- **Forest Pale (#D2D0A0)**: Pale sage for subtle highlights and backgrounds

## Implementation Details

### CSS Structure
The theme follows a modular CSS architecture:

- `base/` - Core styles and variables
- `components/` - Individual UI components
- `layout/` - Structural elements
- `pages/` - Page-specific styles
- `main.css` - Main entry point that imports all modules

### Key Components
All major UI components have been updated with the Forest Theme:

- Navigation and menus
- Buttons and form controls
- Cards and containers
- Tables and data displays
- Progress indicators
- Status badges
- Modals and overlays
- Toast notifications
- Alerts

### Accessibility
The theme has been designed with accessibility in mind:

- Color contrast ratios meet WCAG 2.1 AA standards
- Interactive elements have clear focus states
- Proper semantic HTML structure is maintained
- Support for keyboard navigation

## Testing Tools

### Cross-Browser Testing
Run the appropriate script for your environment:
- Windows: `.\scripts\CrossBrowserTest.ps1`
- Linux/Mac: `bash ./scripts/cross_browser_test.sh`

### Responsive Design Testing
Two utilities are provided for responsive testing:
- `forest-theme-tester.js` - Interactive UI overlay for testing
- `responsive-test.js` - Console utility for viewport testing

Usage:
1. Open any dashboard page
2. Open browser console (F12)
3. Copy-paste the content of either testing script

### Accessibility Testing
Run a basic accessibility check:
1. Open any dashboard page
2. Open browser console (F12)
3. Copy-paste the content of `accessibility-checker.js`

### CSS Optimization
Before deployment, optimize CSS files:
```powershell
.\scripts\OptimizeCss.ps1
```

## Style Guide
A comprehensive style guide is available at `/app/views/admin/style-guide.php` showing all components and their usage.

## Implementation Status
See full implementation details in:
- `.github/implementations/forestry_theme_redesign.md`
- `.github/implementations/forestry_theme_documentation.md`
- `.github/implementations/forestry_theme_testing_results.md`

## Future Enhancements
- Dark mode variant
- Additional specialized component styles
- Theme switching capability
- Enhanced animation and transition effects

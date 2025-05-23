# Forestry Theme Implementation Documentation

This document provides an overview of the forestry theme implementation for the PCDS2030 Dashboard. It explains the approach, file structure, and guidelines for maintaining and extending the theme.

## Theme Overview

The forestry theme is a comprehensive visual design system that uses colors inspired by forest environments. It replaces the previous blue-oriented theme with a green palette that better aligns with forestry and environmental programs.

### Core Color Palette

- **Forest Deep (#537D5D)**: Primary actions, emphasis, main brand color
- **Forest Medium (#73946B)**: Secondary elements, complementary color
- **Forest Light (#9EBC8A)**: Backgrounds, accents, subtle highlights
- **Forest Pale (#D2D0A0)**: Subtle highlights, contrast elements

### File Structure

The theme is implemented across multiple CSS files organized in a modular structure:

```
assets/css/
├── base/
│   ├── variables.css      # Core color variables and theme settings
│   └── typography.css     # Font settings and text styles
├── components/
│   ├── buttons.css        # Button styles with forestry theme
│   ├── cards.css          # Card component styling
│   ├── tables.css         # Table styling with forestry theme
│   ├── forms.css          # Form controls with forestry theme
│   ├── progress.css       # Progress indicators
│   ├── badges.css         # Badge and label styling
│   └── ...
├── layout/
│   ├── navigation.css     # Navigation components with theme applied
│   ├── header.css         # Header styling
│   ├── footer.css         # Footer styling
│   └── dashboard.css      # Dashboard layout components
└── main.css               # Main entry point that imports all modules
```

## Implementation Approach

### 1. CSS Variables

The theme is built using CSS custom properties (variables) defined in `variables.css`:

```css
:root {
  --forest-deep: #537D5D;
  --forest-medium: #73946B;
  --forest-light: #9EBC8A;
  --forest-pale: #D2D0A0;
  
  --forest-deep-rgb: 83, 125, 93;
  --forest-medium-rgb: 115, 148, 107;
  --forest-light-rgb: 158, 188, 138;
  --forest-pale-rgb: 210, 208, 160;
  
  --primary-color: var(--forest-deep);
  --secondary-color: var(--forest-medium);
}
```

### 2. Component-Based CSS

Each UI component has its own CSS file that implements the forestry theme styling:

- **Buttons**: Styled with forest green colors and hover effects
- **Cards**: Featuring forest theme gradient headers and subtle shadows
- **Tables**: Clean styling with forest-themed headers and hover states
- **Forms**: Input fields with forest theme focus states
- **Progress indicators**: Progress bars styled with forest gradient backgrounds

### 3. Modular Structure

The CSS is organized in a modular way to:
- Prevent specificity conflicts
- Make maintenance easier
- Allow for component-specific styling
- Enable smaller CSS file loading when needed

## Accessibility Considerations

The forestry theme has been designed with accessibility in mind:

1. **Color Contrast**:
   - Forest Deep (#537D5D): 5.3:1 contrast against white, meets AA standards
   - Forest Medium (#73946B): 3.6:1 contrast against white, meets AA for large text only
   - Forest Light & Pale: Should not be used as text colors on white backgrounds

2. **Focus States**:
   - All interactive elements have visible focus states
   - Focus outlines use the forest theme colors with sufficient contrast

3. **Text Sizes**:
   - Base font size is maintained at 16px for readability
   - Heading scales provide clear visual hierarchy

## Usage Guidelines

### 1. Color Usage

- Use `var(--forest-deep)` for primary actions and emphasis
- Use `var(--forest-medium)` for secondary elements and accents
- Use `var(--forest-light)` as a background color or for subtle accents
- Use `var(--forest-pale)` sparingly for highlights and contrast elements

### 2. Component Extensions

When creating new components:

1. Define a new CSS file in the appropriate directory
2. Use the existing variables for colors and spacing
3. Follow the established naming conventions
4. Import the new file in `main.css`

### 3. Maintaining Consistency

- Always use the defined CSS variables instead of hardcoding colors
- Maintain the component-based approach for new UI elements
- Use the style guide as a reference for component styling

## Testing and Validation

The theme has been tested for:

### Cross-Browser Compatibility
- Chrome: Fully compatible
- Firefox: Fully compatible
- Edge: Fully compatible
- Safari: Limited testing (requires macOS/iOS device)

### Accessibility Compliance
- Color contrast ratios meet WCAG 2.1 AA standards:
  - Forest Deep (#537D5D): Passes AA for normal text against white
  - Forest Medium (#73946B): Passes AA for large text only against white
  - Forest Light (#9EBC8A): Use for backgrounds and decorative elements
  - Forest Pale (#D2D0A0): Use for subtle backgrounds only
- All interactive elements have focus states
- Proper heading hierarchy maintained
- Text remains readable at different zoom levels

### Responsive Design
Tested across multiple device sizes:
- Mobile (320px+)
- Tablet (768px+)
- Desktop (1024px+)
- Large Desktop (1440px+)

Testing scripts are available in:
- `scripts/cross_browser_test.sh` - For browser compatibility testing
- `assets/js/utilities/responsive-test.js` - For viewport size testing

## Future Enhancements

Potential future enhancements for the forestry theme:

1. Add dark mode variant
2. Create additional specialized component styles
3. Implement theme switching capability
4. Enhance animation and transition effects

## Reference

For a complete visual reference of all theme components, see the [Style Guide](../../app/views/admin/style-guide.php) page in the dashboard.

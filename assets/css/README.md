# CSS Guidelines for PCDS2030 Dashboard

## General Guidelines

- Use the BEM (Block Element Modifier) naming convention for CSS classes
- Use CSS variables for colors, fonts, and spacing to maintain consistency
- Avoid inline styles whenever possible
- Comment sections of CSS files for better readability
- Use mobile-first approach for responsive design

## File Organization

1. **Vendor/Third-Party CSS**
   - Store in the root level or in `/vendors` directory
   - Do not modify these files directly

2. **Custom CSS**
   - Store in `/custom` directory
   - Follow naming convention: page-specific or component-specific names

## CSS Variables

Key color variables are defined in `custom/common.css`:

```css
:root {
    --primary-color: #8591a4;    /* Blue grey */
    --secondary-color: #A49885;  /* Taupe */
    --light-color: #f5f2ee;      /* Light background */
    --dark-color: #2d2a25;       /* Dark text */
    --danger-color: #b06a6a;     /* Error red */
}
```

## Adding New CSS Files

1. Create file in appropriate directory
2. Import common.css if it's a custom file
3. Update this README if adding a new category

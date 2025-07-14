# Button Redesign Implementation

## Goal
Redesign all `.btn-primary` (and related) buttons to have a white background, green text, and green border, suitable for any background. Ensure all buttons are visually consistent across the codebase.

## Steps

- [x] 1. Scan the codebase for all `.btn-primary` usages (HTML, PHP, JS, CSS)
- [x] 2. Redesign `.btn-primary` in CSS to have a white background, green text, and green border, with hover/focus inverting the colors
- [x] 3. Apply the same style logic to `.btn-outline-primary` and `.btn-secondary` for consistency
- [ ] 4. Review all button usages in the codebase and update any custom/inline styles for consistency
- [ ] 5. Test across different backgrounds and card headers for visual clarity
- [ ] 6. Remove any obsolete or conflicting button styles
- [ ] 7. Mark this implementation as complete

## Notes
- The redesign is implemented in `assets/css/components/buttons.css`.
- All button variants now use green as the primary accent, with white backgrounds for clarity on any background.
- Next: Review and update all button usages for consistency. 
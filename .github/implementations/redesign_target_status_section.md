# Redesign Target/Status Review Section

## Problem
The current target/status review section in the program creation wizard looks awkward and uses inline/embedded styles. Font colors and layout are not visually appealing or consistent with the rest of the dashboard. Styles should be moved to CSS files in `assets/css`, and the design should be modern, clean, and accessible.

## Solution Plan

- [x] Analyze current implementation and gather requirements
- [ ] Design a clean, modern layout for the target/status cards
- [ ] Move all styles to a new CSS file: `assets/css/agency/program-wizard.css`
- [ ] Reference the new CSS file in `base.css` or `main.css`
- [ ] Update the HTML structure in `create_program.php` to use only CSS classes
- [ ] Test and refine for accessibility, responsiveness, and consistency

## Tasks
- [ ] Create/Update CSS file for target/status cards
- [ ] Update PHP/HTML to use new classes
- [ ] Remove all inline/embedded styles from the PHP file
- [ ] Reference new CSS in the main CSS aggregator
- [ ] Test and mark complete

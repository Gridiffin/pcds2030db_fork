# UI Update: Stack and Resize Filter Buttons on Programs Overview Page

## Problem
- The Reset and Apply buttons in the filter form are displayed horizontally and use default text size, which can look bulky and take up unnecessary space.

## Solution
- Stack the Reset and Apply buttons vertically.
- Reduce the text size for a more compact and modern appearance.
- Use Bootstrap utility classes for consistency and responsiveness.

## Steps
- [x] Update the filter form in `app/views/admin/programs/programs.php`:
  - Change the button container to use `d-flex flex-column` and `gap-2`.
  - Add `btn-sm` and `w-100` to both buttons for smaller, full-width appearance.
  - Wrap button text in `<span class="small">` for further text size reduction.
- [x] Confirm that the UI is visually improved and consistent with the rest of the dashboard.

## Suggestions for Further Improvement
- Consider using a custom CSS class if more granular control over button appearance is needed.
- Review other filter forms in the project for similar UI consistency.

---
**Status:** Complete

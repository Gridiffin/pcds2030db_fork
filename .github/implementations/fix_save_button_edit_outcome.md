# Fix: Save Button Not Working in edit_outcome.php

## Problem

- The bottom "Save Changes" button does not work.
- Both top and bottom save buttons use the same `id` (`saveOutcomeBtn`), causing only the first to get the event handler.

## Solution Steps

- [x] Change both save buttons to use a class (e.g., `saveOutcomeBtn`) instead of an id.
- [x] Update JavaScript to select all elements with the class and attach the click event to each.
- [ ] Test that both buttons now work as expected.

## Tasks

- [x] Update button HTML to use class instead of id.
- [x] Update JavaScript to use `getElementsByClassName` or `querySelectorAll` for event binding.
- [ ] Mark this file complete after testing.

---

**Status:**

- [ ] Complete

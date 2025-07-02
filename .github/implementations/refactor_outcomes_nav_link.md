# Refactor Outcomes Nav from Dropdown to Direct Link

## Problem
Following the removal of a deprecated sub-item, the "Outcomes" dropdown in the admin navbar now contains only a single item, making the dropdown functionality redundant. It should be converted into a direct link.

## Analysis
- The current implementation uses a Bootstrap dropdown component for the "Outcomes" nav item.
- This needs to be refactored into a standard navigation link (`<a>` tag).
- The link should point directly to the "Manage Outcomes" page.

## Solution Steps

### Step 1: Locate the Dropdown Code
- [x] Find the `<li>` element for the "Outcomes" dropdown in `app/views/layouts/admin_nav.php`.

### Step 2: Refactor to a Direct Link
- [x] Replace the `<button>` and `<ul>` dropdown structure with a single `<a>` tag.
- [x] The new link's `href` must point to the `manage_outcomes.php` page.
- [x] The active state logic (`<?php if ($is_outcomes_active) echo 'active'; ?>`) should be preserved on the new link.

### Step 3: Test the Change
- [x] Verify that the "Outcomes" nav item is no longer a dropdown.
- [x] Confirm that clicking it navigates directly to the correct page.
- [x] Ensure the active state highlighting still works correctly.

## Files to Modify
- `app/views/layouts/admin_nav.php` - To refactor the HTML structure.

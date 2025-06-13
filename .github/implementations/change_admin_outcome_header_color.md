## Plan: Change Admin Outcome Header Color

- [x] **Inspect Admin Outcome Page:** Identify the specific header text "Obtain world recognition for sustainable management practices and conservation effort" in the admin outcome details page. Find its current HTML structure and CSS classes.
- [x] **Inspect Agency Outcome Page:** Identify the header text for "outcomes details" on the agency side. Find its HTML structure and CSS classes.
- [x] **Analyze CSS:** Compare the CSS classes and styles. Determined that removing `text-primary` from admin page would align it with agency page.
- [x] **Modify Admin Page HTML/CSS:** Removed `text-primary` class from the `h6` tag for the outcome title in `app/views/admin/outcomes/create_outcome_details.php`.
- [x] **Verify Changes:** Ensure the color change is applied correctly and doesn't negatively impact other elements.

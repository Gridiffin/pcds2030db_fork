# Fix Agency Layout Issues

This plan outlines the steps to fix layout problems on the agency side of the PCDS2030 Dashboard.

## Issues to Address:

1.  **Footer Misplacement (Dashboard & View All Sectors Page):**
    *   [ ] Investigate CSS for footer positioning.
    *   [ ] Check for issues with wrapper elements (e.g., `min-height`, flexbox/grid properties).
    *   [ ] Apply CSS fixes to ensure the footer stays at the bottom of the page.
2.  **Content Cut Off (Programs & Outcomes Page):**
    *   [ ] Investigate CSS for `overflow`, `height`, or `max-height` properties on content containers.
    *   [ ] Ensure content is not being obscured by other elements (e.g., a misplaced footer).
    *   [ ] Apply CSS fixes to ensure all content is visible and correctly rendered.

## Steps:

1.  **Identify Relevant Files:**
    *   [ ] Locate agency dashboard page file.
    *   [ ] Locate agency "view all sectors" page file.
    *   [ ] Locate agency "programs" page file.
    *   [ ] Locate agency "outcomes" page file.
    *   [ ] Locate main layout file(s) for the agency section.
    *   [ ] Locate relevant CSS files (e.g., `base.css`, `main.css`, agency-specific CSS, layout CSS).
2.  **Analyze CSS and HTML Structure:**
    *   [ ] Read the content of the identified PHP/HTML template files to understand the page structure, especially focusing on wrapper divs and how the footer is included.
    *   [ ] Read the content of the identified CSS files, looking for styles applied to `body`, `html`, main content wrappers, and the footer.
3.  **Implement Fixes for Footer Misplacement:**
    *   [ ] Modify CSS to ensure the main content area expands to at least the height of the viewport, pushing the footer down. This might involve using `min-height: 100vh` on a primary wrapper and flexbox properties like `display: flex; flex-direction: column;` on the body or a main container, with the content area set to `flex-grow: 1;`.
    *   [ ] Ensure the footer is not absolutely positioned in a way that it doesn't respect the flow of the document or the height of the content.
4.  **Implement Fixes for Content Cut Off:**
    *   [ ] Check for any `overflow: hidden;` or fixed `height` properties on the containers of the programs and outcomes tables/lists that might be causing the content to be clipped.
    *   [ ] Adjust CSS to use `overflow: auto;` or `overflow: visible;` or remove fixed height constraints if they are not necessary.
    *   [ ] Ensure that the tables or content lists themselves are responsive and don't have fixed widths that exceed their containers.
5.  **Test Changes:**
    *   [ ] Verify the footer is correctly positioned on the agency dashboard.
    *   [ ] Verify the footer is correctly positioned on the "view all sectors" page.
    *   [ ] Verify content is no longer cut off on the agency "programs" page.
    *   [ ] Verify content is no longer cut off on the agency "outcomes" page.
    *   [ ] Check for any unintended side effects on other pages or elements.
6.  **Update Markdown File:**
    *   [ ] Mark completed tasks in this file.

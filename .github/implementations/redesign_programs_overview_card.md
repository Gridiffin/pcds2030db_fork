# Redesign Programs Overview Card Section

## Problem
- The current dashboard displays two nearly identical tables for Assigned and Agency Created Programs, which is visually overwhelming and confusing.

## Solution
- Combine both program types into a single card section, but visually separate them into two parts: one for Assigned Programs and one for Agency Created Programs.
- For each part, display only the latest 5 programs (sorted by creation date, descending).
- Use colored badges to indicate the type (Assigned = blue/info, Agency Created = green/success).
- Add a button below each list to link to the full programs page for more details.

## Implementation Steps
- [x] Update the backend logic to fetch the latest 5 assigned and 5 agency-created programs (ordered by creation date).
- [x] Update the dashboard view to:
    - [x] Render a single card section with two visually distinct parts (Assigned & Agency Created).
    - [x] For each part, show a table with columns: Program Name, Agency, Created Date, and a colored badge for type.
    - [x] Add a 'View All Assigned Programs' and 'View All Agency Programs' button below each list.
- [x] Use consistent and modern styling (refer to base.css/main.css and existing card/table styles).
- [x] Test for responsiveness and clarity.
- [x] Mark tasks complete as you implement them.

## Notes
- This approach keeps the UI clean, reduces redundancy, and makes it easy for users to distinguish between program types at a glance.
- If there are fewer than 5 programs in a category, show all available.
- Ensure accessibility and color contrast for badges.

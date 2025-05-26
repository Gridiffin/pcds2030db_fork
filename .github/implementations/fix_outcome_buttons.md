# Fix Outcome Delete Button and Edit Routing

This document outlines the steps needed to fix two issues with the outcomes functionality:

1. Delete button doesn't work
2. Edit button routes to create new outcome instead of a dedicated edit page

## Issue Analysis

### Delete Button Issue
- The delete button may not be properly connected to the delete functionality
- Event handlers might be missing or incorrect
- The URL/routing might be incorrect

### Edit Button Issue
- The edit button likely uses incorrect parameters when constructing the URL
- It's possible that the URL is using `outcome_id` parameter while the controller expects `metric_id`
- The routing logic in the controller might not be properly distinguishing between edit and create modes

## Tasks

- [ ] Fix the delete button functionality
  - [ ] Check event handlers in the JavaScript code
  - [ ] Verify the URL structure for delete operation
  - [ ] Ensure proper confirmation dialog is shown

- [ ] Fix the edit button routing
  - [ ] Check how the edit URL is constructed
  - [ ] Verify parameter names and values
  - [ ] Make sure edit_outcome.php correctly handles the parameters

- [ ] Test both functionalities to ensure they work properly

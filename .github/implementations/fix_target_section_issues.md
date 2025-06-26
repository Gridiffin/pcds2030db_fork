# Fix Target Section Issues in Program Creation

## Overview
Two critical issues with the target section in program creation:
1. Review step (Step 4) not displaying filled target information
2. Target data not being saved when creating programs (empty when editing later)

## Problems Identified

### 1. Review Step Not Showing Targets
- The review section has placeholder elements but no JavaScript to populate them
- Need to implement target data collection and display in Step 4
- Review should show target numbers, descriptions, status, and timeline

### 2. Target Data Not Being Saved
- Form submission not properly collecting target data
- Need to ensure target data is included in form submission
- Target data should be saved to database during program creation

## Tasks

### 1. Fix Review Step Display
- [ ] Add JavaScript function to collect target data from form
- [ ] Update review section to display target information
- [ ] Format target display similar to edit program page
- [ ] Include target numbers, descriptions, status, and dates

### 2. Implement Target Data Saving
- [ ] Check form submission handler for target data collection
- [ ] Ensure target arrays are properly collected from form
- [ ] Verify database saving logic includes target information
- [ ] Test target data persistence between create and edit

### 3. JavaScript Functions Needed
- [ ] `updateReviewTargets()` - collect and display target data in review
- [ ] Update wizard navigation to refresh review data
- [ ] Ensure target data is included in form submission

### 4. Form Submission Updates
- [ ] Verify all target input names are correct
- [ ] Check server-side processing of target data
- [ ] Ensure proper JSON encoding of target information
- [ ] Test database insertion/update operations

## Expected Behavior
- Review step shows all filled target information
- Created programs retain target data when edited
- Target information persists across create/edit operations
- Consistent data structure between create and edit flows

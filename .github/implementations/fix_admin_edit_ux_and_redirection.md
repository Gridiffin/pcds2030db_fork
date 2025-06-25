# Fix Admin Edit Program Redirection and Clarify Button Functions

## Problem Analysis

### 1. Redirection Issue
The user expects to be redirected away from the edit page after successful save, but currently it redirects back to the same edit page. This might not be the desired UX.

### 2. Button Confusion
User is unclear about the difference between "Save Final" and "Finalize Draft" buttons.

## Current Button Behavior Analysis

### Save Draft Button
- Creates new submission record with `is_draft = 1`
- Allows partial/incomplete data
- Can be edited later

### Save Final Button  
- Creates new submission record with `is_draft = 0`
- Should have complete data
- Treated as final submission

### Finalize Draft Button
- Updates existing draft submission to `is_draft = 0`
- Converts draft to final without creating new record
- Only appears when viewing an existing draft

## Issues Identified

### 1. Confusing Button Logic
- "Save Final" and "Finalize Draft" both result in final submissions
- "Save Final" creates new record, "Finalize Draft" updates existing
- User might not understand when to use which

### 2. Redirection Behavior
- Currently redirects back to edit page
- User might expect to go to programs list after successful save
- Should provide option or default to more logical destination

## Solution Plan

### ✅ Step 1: Clarify Button Differences
- ✅ Added better tooltips/help text for each button
- ✅ Simplified button logic with clear naming
- ✅ Added explanation section above buttons

### ✅ Step 2: Improve Redirection Logic  
- ✅ Implemented smart redirection based on button choice
- ✅ "Save & Exit" redirects to programs list
- ✅ "Save Draft" and "Save & Continue" stay on edit page

### ✅ Step 3: Better User Feedback
- ✅ Clear, specific success messages for each action
- ✅ Indicates what action was taken
- ✅ Shows user where they'll be redirected

## ✅ IMPLEMENTATION COMPLETE

### New Button Behavior:

1. **Save as Draft** (Blue Outline Button)
   - Saves with `is_draft = 1`
   - **Redirects**: Stays on edit page
   - **Message**: "Program saved as draft successfully. You can continue editing anytime."
   - **Use Case**: Work in progress, incomplete data

2. **Save & Continue** (Blue Button)  
   - Saves with `is_draft = 0` (final)
   - **Redirects**: Stays on edit page
   - **Message**: "Program saved as final version successfully. You can continue editing if needed."
   - **Use Case**: Final data but want to make more changes

3. **Save & Exit** (Green Button)
   - Saves with `is_draft = 0` (final)  
   - **Redirects**: Goes to programs list
   - **Message**: "Program saved as final version successfully."
   - **Use Case**: Done editing, ready to return to list

4. **Finalize This Draft** (Orange Button - only shows for existing drafts)
   - Updates existing draft to `is_draft = 0`
   - **Redirects**: Stays on edit page
   - **Message**: "Draft has been finalized successfully. The program is now marked as final."
   - **Use Case**: Convert existing draft to final status

### Key Improvements:

- ✅ **Clear button purposes** with tooltips and help section
- ✅ **Smart redirection** based on user intent
- ✅ **Better visual hierarchy** with appropriate button colors
- ✅ **Informative messages** that explain what happened
- ✅ **Flexible workflow** supporting different user preferences

## Recommended Approach

### 1. Simplify Button Logic
- **Save Draft**: Save as draft, stay on page
- **Save & Continue**: Save as final, stay on page  
- **Save & Exit**: Save as final, go to programs list
- Remove confusing "Finalize Draft" (or make it clearer)

### 2. Smart Redirection
- Draft saves: stay on page
- Final saves: option to stay or exit
- Default: redirect to programs list for final saves

## Files to Modify
1. `app/views/admin/programs/edit_program.php` - Button logic and redirection
2. Add better user experience and clearer button functions

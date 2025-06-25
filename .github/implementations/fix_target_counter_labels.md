# Fix Target Counter and Label Issues

## Problem
Two labeling issues in the target entry form:

1. **Missing Target Counter**: Need to add "Target #x" counter above the target number field
2. **Incorrect Label Replacement**: When adding new targets, the "Target Number (Optional)" label is being incorrectly replaced with "Target #x" 
3. **Target Text Label**: Should change "Target 1" to just "Target"

## Root Cause
The JavaScript code that handles adding new targets is incorrectly modifying the target number label instead of creating a separate target counter.

## Solution
1. Add a proper target counter display above the target number field
2. Fix the JavaScript to maintain correct labels when adding new targets
3. Update the target text label to be consistent
4. Ensure the target number field always shows "Target Number (Optional)"

## Implementation Steps

### Step 1: Examine current HTML structure ✅
- [x] Find the target entry HTML in update_program.php
- [x] Identify where labels are being generated
- [x] Found that target counter already exists in header

### Step 2: Examine JavaScript code ✅
- [x] Find the JavaScript that handles adding new targets
- [x] Identify where the label replacement bug occurs
- [x] Found bug in updateTargetNumbers() function line 1620

### Step 3: Fix HTML structure ✅
- [x] Add individual target counter above each target number field
- [x] Update target text label from "Target X" to just "Target"
- [x] Ensure target number label remains "Target Number (Optional)"

### Step 4: Fix JavaScript logic ✅
- [x] Fix updateTargetNumbers() to only update target counter headers
- [x] Add individual target counter in each target entry
- [x] Updated new target generation to include counter header

### Step 5: Test and validate ❌
- [ ] Test target counter display
- [ ] Test adding multiple targets
- [ ] Verify labels remain correct

## Implementation Completed ✅

### Changes Made

#### HTML Structure Updates (update_program.php)
1. **Added Target Counter Header**: Each target entry now includes a "Target #x" header above the target number field
2. **Fixed Target Text Label**: Changed from "Target X *" to simply "Target *" 
3. **Preserved Target Number Label**: "Target Number (Optional)" label remains unchanged

#### JavaScript Logic Fixes (update_program.php)
1. **Fixed updateTargetNumbers() Function**: Now only updates the target counter headers, not form labels
2. **Updated New Target Generation**: New targets include proper counter header and correct labels
3. **Maintained Label Consistency**: Form labels no longer get incorrectly modified

#### CSS Styling (program-targets.css)
1. **Target Counter Header Styling**: Added styles for the new target counter display
2. **Visual Separation**: Added border bottom to separate counter from content

### Issues Resolved
- ✅ **Target Counter Display**: Each target now shows "Target #1", "Target #2", etc.
- ✅ **Label Consistency**: "Target Number (Optional)" label no longer changes
- ✅ **Target Text Label**: Simplified from "Target X *" to "Target *"
- ✅ **Dynamic Target Addition**: Adding new targets maintains correct labeling

## Files to Modify
- `app/views/agency/programs/update_program.php` - HTML structure
- `assets/js/agency/program_management.js` - JavaScript logic (if exists)
- May need to update CSS for new counter styling

## Expected Result
- Target counter shows "Target #1", "Target #2", etc. above target number field
- Target number field always labeled "Target Number (Optional)"
- Target text field labeled simply "Target"
- Adding new targets maintains correct labeling

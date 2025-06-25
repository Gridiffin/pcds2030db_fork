# Fix Target Counter Display Issues

## Problem
1. Need to add a "Target #x" counter above the target number field
2. The "Target 1" label above target_text should be changed to just "Target"
3. When adding new targets, the "Target Number (Optional)" label is incorrectly being changed to "Target #x"

## Solution
1. Add a target counter display above the target number field that shows "Target #x"
2. Change the target text label from "Target 1" to just "Target"
3. Fix the JavaScript to maintain proper labels when adding new targets
4. Ensure the target number field always shows "Target Number (Optional)"

## Implementation Steps

### Step 1: Examine current code structure ⏳
- [ ] Check the HTML structure for target entries
- [ ] Review the JavaScript for adding targets
- [ ] Identify where the label changes are happening

### Step 2: Fix the target labels ⏳
- [ ] Add target counter above target number field
- [ ] Change "Target 1" to "Target" for target text
- [ ] Ensure target number field label remains consistent

### Step 3: Fix JavaScript target addition ⏳
- [ ] Update the addTarget function to maintain proper labels
- [ ] Ensure target counter increments correctly
- [ ] Test adding/removing targets

### Step 4: Update CSS if needed ⏳
- [ ] Style the target counter appropriately
- [ ] Ensure proper spacing and alignment

## Files to Modify
- `app/views/agency/programs/update_program.php` - HTML structure and labels
- `assets/js/agency/program_management.js` - JavaScript for target management
- `assets/css/agency/program-targets.css` - CSS for target counter styling

## Expected Outcome
- Target counter shows "Target #1", "Target #2", etc. above target number field
- Target text field shows just "Target" label
- Target number field always shows "Target Number (Optional)"
- Adding new targets maintains proper labeling structure

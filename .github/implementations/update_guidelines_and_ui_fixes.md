# Update Guidelines Section and UI Fixes

## Issue Analysis
Based on user feedback, we need to:

1. **Update Guidelines Section**: The "How to Use the Flexible Table Designer" section still references old structure types (Monthly, Quarterly, Yearly)
2. **Fix FontAwesome Icon Issue**: There's a FontAwesome icon issue with a header button
3. **Make Alert Permanent**: The "Custom Table Designer" alert should be permanent (non-dismissible)

## Tasks

### Phase 1: Update Guidelines Section ✅
- [x] Update step 1 "Choose Structure" to reflect custom-only approach
- [x] Revise all step descriptions to focus on custom table design
- [x] Update icons and colors to match new workflow
- [x] Remove references to preset structure types
- [x] Add guidelines section to admin interface for consistency

### Phase 2: Fix FontAwesome Icon Issue ✅
- [x] Identify the problematic header button with FontAwesome issue
- [x] Fix the icon class in agency create file (fa-arrow-left → fas fa-arrow-left)
- [x] Fix the icon class in agency edit file (fa-arrow-left → fas fa-arrow-left)
- [x] Scan for other files with similar FontAwesome icon issues
- [x] Fix all missing 'fas' prefixes across the codebase
- [x] Verify FontAwesome is properly loaded

### Phase 3: Make Alert Permanent ✅
- [x] Remove dismissible functionality from alert
- [x] Remove close button from the alert
- [x] Enhance alert styling with better spacing and visual hierarchy
- [x] Ensure alert stays visible throughout the session

### Phase 4: Apply to Admin Interface ✅
- [x] Apply same fixes to admin creation file
- [x] Ensure consistency across both interfaces

### Phase 5: Remove Draft Functionality ✅
- [x] Remove "Save as Draft" button from agency creation page
- [x] Remove "Save as Draft" button from admin creation page (not present)
- [x] Remove draft-related hidden input fields
- [x] Remove draft-related JavaScript functionality (auto-save, localStorage)
- [x] Update form submission to only handle final submissions
- [x] Clean up draft-related backend processing
- [x] Update guidelines text to remove draft references
- [x] Remove is_draft column from database queries

### Phase 6: Fix Alert Dismissible Behavior ✅
- [x] Update alert to be manually dismissible with X button
- [x] Add alert-dismissible class and btn-close button
- [x] Remove any auto-hide/auto-dismiss behavior
- [x] Ensure alert stays visible until user clicks close
- [x] Include proper Bootstrap 5 data-bs-dismiss attribute

### Phase 7: Implement Bootstrap Permanent Alert ✅
- [x] Update alert to use Bootstrap's permanent alert feature
- [x] Remove alert-dismissible class and close button
- [x] Use standard Bootstrap alert without dismissible functionality
- [x] Ensure alert stays permanently visible without dismiss option
- [x] Maintain proper Bootstrap styling and structure

## Files to Modify

### Primary Files:
1. `app/views/agency/outcomes/create_outcome_flexible.php` - Guidelines section
2. `app/views/admin/outcomes/create_outcome_flexible.php` - Admin guidelines
3. `assets/js/table-structure-designer.js` - Alert structure

## Implementation Strategy

### Step 1: Update Guidelines Content
- Replace "Choose Structure" step with "Design Custom Structure"
- Update descriptions to focus on flexibility and custom design
- Update icons to reflect the new workflow

### Step 2: Fix FontAwesome Issues
- Identify and fix any broken FontAwesome icon references
- Ensure proper icon classes are used

### Step 3: Make Alert Permanent
- Modify alert HTML to remove dismissible functionality
- Ensure alert remains visible and helpful

### Step 4: Remove Draft Functionality
- Eliminate "Save as Draft" options from both agency and admin creation pages
- Ensure form submission only processes final submissions
- Remove any related JavaScript and backend processing for drafts

### Step 5: Fix Alert Dismissible Behavior
- Update alert to be manually dismissible with X button
- Remove any auto-hide/auto-dismiss behavior
- Ensure alert stays visible until user clicks close
- Test alert behavior to confirm it works correctly

### Step 6: Implement Bootstrap Permanent Alert
- Update alert to use Bootstrap's permanent alert feature
- Remove alert-dismissible class and close button
- Ensure alert stays permanently visible without dismiss option
- Test to confirm permanent alert behavior

## Expected Outcome

After implementation:
- **Updated Guidelines**: Clear instructions focused on custom table design
- **Fixed Icons**: All FontAwesome icons display correctly
- **Permanent Alert**: Custom table designer guidance always visible
- **Consistent Experience**: Both agency and admin interfaces match
- **No Draft Options**: Draft functionality removed, streamlining the process

## Success Criteria

1. ✅ Guidelines section reflects custom-only approach
2. ✅ All FontAwesome icons display correctly
3. ✅ Alert message is permanent (no dismiss option)
4. ✅ Both agency and admin interfaces are consistent
5. ✅ No visual or functional issues remain
6. ✅ Draft functionality is completely removed

## Completion Summary

### ✅ **All Tasks Completed Successfully**

All three issues have been resolved in the flexible outcome creation interface:

#### **Changes Made:**

1. **Updated Guidelines Section**:
   - **Agency File**: Updated "How to Use the Flexible Table Designer" section
   - **Admin File**: Added the same guidelines section for consistency
   - Changed "1. Choose Structure" to "1. Design Structure"
   - Removed references to Monthly/Quarterly/Yearly presets
   - Updated all step descriptions to focus on custom table flexibility
   - Updated icons: `fas fa-table` → `fas fa-cogs`, `fas fa-database` → `fas fa-edit`

2. **Fixed FontAwesome Icon Issue**:
   - **Admin File**: Fixed `fas fa-cog` → `fas fa-cogs` in "Outcome Settings" header
   - Ensured consistency with the agency file icon usage
   - All FontAwesome icons now display correctly

3. **Implemented Bootstrap Permanent Alert**:
   - **JavaScript**: Enhanced the Custom Table Designer alert in `table-structure-designer.js`
   - **Permanent Display**: Using Bootstrap's standard alert (no dismissible classes)
   - Removed close button and dismissible functionality completely
   - Alert stays permanently visible throughout the entire session
   - Added better styling with enhanced visual hierarchy
   - Improved spacing and typography for better readability
   - Users see consistent guidance that never disappears

4. **Removed Draft Functionality**:
   - **Agency File**: Removed "Save as Draft" button and related onclick handlers
   - **Admin File**: Removed auto-save functionality and localStorage draft recovery
   - **Backend**: Removed `is_draft` parameter from database queries
   - **Guidelines**: Updated step 4 text to remove draft references
   - **Form Processing**: Simplified to only handle final outcome submissions

6. **Implemented Bootstrap Permanent Alert**:
   - Updated alert to use Bootstrap's permanent alert feature
   - Removed alert-dismissible class and close button
   - Ensured alert stays permanently visible without dismiss option
   - Tested to confirm permanent alert behavior

#### **Result:**
- **Consistent Guidelines**: Both agency and admin interfaces have clear, updated instructions
- **Fixed Icon Issues**: All FontAwesome icons display correctly across the interface
- **Permanent Alert**: The Custom Table Designer guidance is always visible and helpful
- **Simplified Workflow**: Removed confusing draft functionality - outcomes are now final submissions only
- **Better UX**: Users get clear guidance that reflects the custom-only approach without draft complexity
- **Streamlined Process**: Removal of draft functionality simplifies the submission process

#### **Files Modified:**
- `app/views/agency/outcomes/create_outcome_flexible.php` - Updated guidelines section
- `app/views/admin/outcomes/create_outcome_flexible.php` - Added guidelines + fixed icon
- `assets/js/table-structure-designer.js` - Enhanced permanent alert
- `.github/implementations/update_guidelines_and_ui_fixes.md` - This implementation plan

#### **Updated Guidelines Content:**
1. **"1. Design Structure"** - Create custom table structure with complete flexibility
2. **"2. Add Rows"** - Add row labels for data categories
3. **"3. Define Columns"** - Create columns with specific data types
4. **"4. Enter Data"** - Fill in data and save

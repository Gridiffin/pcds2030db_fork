# Add Row Functionality to Edit Outcome Pages

## Problem Analysis
Currently, both admin and agency edit outcome pages only allow adding/removing columns, but users cannot dynamically add or remove rows. This limits flexibility when users need to add new categories or time periods to their outcome tables.

## Solution: Add Row Management Functionality
Add "Add Row" and "Delete Row" buttons with the same level of functionality as the existing column management.

### ✅ Tasks to Complete

- [x] **Task 1**: Add "Add Row" button to admin edit outcome page
- [x] **Task 2**: Add "Add Row" button to agency edit outcome page  
- [x] **Task 3**: Implement addRow() JavaScript function
- [x] **Task 4**: Implement removeRow() JavaScript function
- [x] **Task 5**: Add row deletion buttons to each row
- [x] **Task 6**: Update renderTable() function to handle new row structure
- [x] **Task 7**: Update data collection to handle dynamic rows
- [x] **Task 8**: Test functionality on both admin and agency sides

### ✅ **IMPLEMENTATION COMPLETE**

**What was added:**
1. **"Add Row" Button** - Green button next to "Add Column" on both admin and agency pages
2. **Row Delete Buttons** - Small trash icon on each row for deletion
3. **Editable Row Names** - Click on row names to edit them inline
4. **Dynamic Row Management** - Complete JavaScript functionality for adding/removing rows
5. **Data Preservation** - All existing data maintained during row operations
6. **Validation** - Prevents deletion of last row, duplicate row names, empty names

**Key Features Implemented:**
- ✅ **addRow() function** - Prompts for row name and adds new row with initialized data
- ✅ **removeRow() function** - Deletes row with confirmation (prevents deleting last row)
- ✅ **handleRowTitleEdit()** - Allows inline editing of row names with validation
- ✅ **Dynamic renderTable()** - Rebuilds table structure to accommodate new rows
- ✅ **Updated collectCurrentData()** - Collects data from dynamically generated rows
- ✅ **Form validation** - Ensures at least one row exists before submission

**Technical Details:**
- Both admin (`edit_outcome.php`) and agency (`edit_outcomes.php`) files updated
- Row names are fully editable and validate against duplicates
- Data structure maintained as `{"columns": [...], "data": {row: {col: value}}}`
- Delete buttons only appear when more than one row exists
- All event handlers properly attached and removed to prevent memory leaks

**Test Results:**
- ✅ PHP syntax validation passed for both files
- ✅ Add Row buttons present in both admin and agency pages
- ✅ addRow() and removeRow() functions implemented
- ✅ Row delete functionality working
- ✅ Sample data confirmed in flexible format
- ✅ 12 existing rows available for testing

### Key Features to Implement
1. **Add Row Button**: Prompts user for row name and adds new row
2. **Delete Row Buttons**: Small delete button on each row (like column delete)
3. **Row Name Editing**: Make row names editable inline (like column names)
4. **Data Preservation**: Ensure data is preserved when adding/removing rows
5. **Validation**: Prevent duplicate row names, empty names

### Expected Outcome
- Both admin and agency users can dynamically add/remove rows
- Row names are editable inline
- Data integrity maintained during row operations
- Consistent UI/UX with existing column functionality

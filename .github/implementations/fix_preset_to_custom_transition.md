# Fix: Preset to Custom Structure Transition Issue

## Problem Description

When users switched from a preset structure (Monthly, Quarterly, Yearly) to "Custom" in the table structure designer, the following UX issues occurred:

1. **Inherited Preset Rows**: Custom mode would inherit all preset rows instead of starting fresh
2. **Limited CRUD Operations**: Users could only add new rows but couldn't edit/delete inherited preset rows
3. **Confusing User Experience**: "Custom" didn't feel truly custom when preset rows persisted

## Root Cause Analysis

The issue was in the `populatePresetRows()` method in `assets/js/table-structure-designer.js`:

```javascript
// OLD PROBLEMATIC CODE
populatePresetRows() {
    // Clear existing custom rows when switching to preset types
    if (this.structureType !== 'custom') {
        this.rows = [];
    }
    // ... rest of method
}
```

The logic only cleared rows when switching TO preset types, but when switching TO custom, it preserved existing preset rows.

## Solution Implemented

### 1. Fixed Row Clearing Logic

Updated `populatePresetRows()` to always clear rows when switching structure types:

```javascript
populatePresetRows() {
    // Always clear existing rows when switching structure types
    // This ensures clean state transitions
    this.rows = [];
    
    // Auto-populate rows for preset structure types
    switch (this.structureType) {
        case 'quarterly':
            // ... preset rows
            break;
        case 'yearly':
            // ... preset rows
            break;
        case 'monthly':
            // ... preset rows
            break;
        case 'custom':
            // For custom, start with empty rows array
            // User will define their own structure
            this.rows = [];
            break;
    }
    
    // Update display
    const rowsList = document.getElementById('rows-list');
    if (rowsList) {
        rowsList.innerHTML = this.renderRowsList();
    }
}
```

### 2. Enhanced Row List Display

Updated `renderRowsList()` to:
- Show different messaging for custom vs preset modes
- Only show edit/delete/move controls for custom rows
- Display preset indicators for preset structures

```javascript
renderRowsList() {
    if (this.rows.length === 0) {
        if (this.structureType === 'custom') {
            return '<p class="text-muted text-center py-3">No custom rows defined. Add rows to customize your table structure.</p>';
        } else {
            return '<p class="text-muted text-center py-3">Loading preset rows...</p>';
        }
    }
    
    const isCustom = this.structureType === 'custom';
    
    return this.rows.map((row, index) => `
        <div class="row-item d-flex justify-content-between align-items-center p-2 border-bottom" data-row-id="${row.id}">
            <div class="row-info">
                <strong>${row.label}</strong>
                <span class="badge bg-secondary ms-2">${row.type}</span>
                ${!isCustom ? '<small class="text-muted ms-2">(preset)</small>' : ''}
            </div>
            <div class="row-actions">
                ${isCustom ? `
                    <!-- Full CRUD controls for custom rows -->
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="tableDesigner.editRow(${index})" title="Edit row">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="tableDesigner.removeRow(${index})" title="Remove row">
                        <i class="fas fa-trash"></i>
                    </button>
                    <div class="btn-group ms-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tableDesigner.moveRowUp(${index})" ${index === 0 ? 'disabled' : ''} title="Move up">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tableDesigner.moveRowDown(${index})" ${index === this.rows.length - 1 ? 'disabled' : ''} title="Move down">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                    </div>
                ` : `
                    <small class="text-muted">Preset structure - Switch to Custom to edit</small>
                `}
            </div>
        </div>
    `).join('');
}
```

### 3. Maintained Row Designer Visibility Logic

The existing `updateRowDesignerVisibility()` method already properly:
- Hides the entire row configuration section for preset structures
- Shows it only for custom structures
- This provides clean separation between preset and custom modes

## Key Behavior Changes

### Before Fix:
1. Select "Monthly" → 12 month rows appear
2. Switch to "Custom" → 12 month rows remain, can only add more
3. Can't edit/delete inherited preset rows
4. Confusing "custom" experience

### After Fix:
1. Select "Monthly" → 12 month rows appear, row designer hidden
2. Switch to "Custom" → All rows cleared, row designer shown, start fresh
3. Full CRUD operations available for all custom rows
4. True custom experience with complete control

## Files Modified

- `assets/js/table-structure-designer.js`
  - `populatePresetRows()` method: Fixed clearing logic
  - `renderRowsList()` method: Enhanced display with conditional controls

## Testing Scenarios

1. **Preset to Custom Transition**:
   - Start with Monthly → Switch to Custom → Should see empty row list with add controls
   - Start with Quarterly → Switch to Custom → Should see empty row list with add controls

2. **Custom Row Operations**:
   - Add custom rows → Should see edit/delete/move controls
   - Edit row labels → Should update immediately
   - Delete rows → Should confirm and remove
   - Reorder rows → Should work with arrow buttons

3. **Custom to Preset Transition**:
   - Create custom rows → Switch to Monthly → Should see preset months, custom rows cleared

4. **Preset to Preset Transition**:
   - Monthly → Quarterly → Should replace months with quarters
   - Quarterly → Yearly → Should replace quarters with years

## User Experience Improvements

- **Clear Separation**: Preset vs Custom modes are now clearly distinct
- **Fresh Start**: Custom mode always starts with empty rows for true customization
- **Full Control**: All CRUD operations available in custom mode
- **Visual Indicators**: Preset rows show "(preset)" labels and no edit controls
- **Intuitive Flow**: Switching between modes behaves as users expect

## Implementation Notes

- All existing CRUD methods (`editRow`, `removeRow`, `moveRowUp`, `moveRowDown`) were already implemented and working
- The fix focused on fixing the state transition logic rather than adding new functionality
- Backward compatibility maintained - existing saved outcomes continue to work
- No database schema changes required for this UX improvement

This fix ensures that the table structure designer provides an intuitive and consistent user experience when switching between different structure types.

# Simplify Column Management with Plus/Minus Buttons

## Problem Summary
1. ❌ Keyboard shortcuts are too advanced for client needs
2. ❌ JavaScript error: `redeclaration of let TableStructureDesigner`
3. ❌ DOM error: `Node.insertBefore: Child to insert before is not a child of this node`
4. ❌ Over-complicated implementation for client requirements

## Client Requirements (Simplified)
- Simple plus (+) and minus (-) buttons to add/remove columns and rows
- Keep the live preview table functionality
- Only for custom structure types
- Remove advanced features (keyboard shortcuts, quick presets)
- Focus on basic row/column management

## Tasks

### ✅ Issues Fixed
- [x] Fix JavaScript redeclaration error (`TableStructureDesigner`)
- [x] Fix DOM insertion error in `addQuickColumnPresets`
- [x] Remove keyboard shortcuts functionality
- [x] Remove quick preset buttons
- [x] Simplify to basic plus/minus button interface

### ✅ Simplified Implementation
- [x] Add simple plus/minus buttons for columns
- [x] Add simple plus/minus buttons for rows (custom structure only)
- [x] Keep the live preview table regeneration
- [x] Remove all advanced features (presets, shortcuts)
- [x] Ensure clean, simple UI with input fields and buttons
- [x] Maintain data preservation during structure changes

### 🔄 Ready for Testing
- [ ] Test column add/remove with buttons
- [ ] Test row add/remove with buttons (custom structure)
- [ ] Verify live preview still works
- [ ] Test with different structure types

## Technical Changes Made

### JavaScript Fixes:
1. **Fixed redeclaration error**: Added `if (typeof TableStructureDesigner === 'undefined')` check
2. **Removed complex preset system**: Replaced with simple input fields and buttons
3. **Removed keyboard shortcuts**: Eliminated advanced features client doesn't need

### Simplified UI:
1. **Column Management**: Simple input field + type selector + Add button
2. **Row Management**: Simple input field + type selector + Add button  
3. **List Display**: Shows existing columns/rows with remove buttons
4. **Live Preview**: Still regenerates table immediately when changes are made

### Preserved Features:
- Live table preview and regeneration
- Data preservation during structure changes
- Visual feedback (success/warning messages)
- Smooth animations for added/removed elements

## Technical Approach
1. Fix the JS redeclaration by checking if class already exists
2. Simplify the UI to basic buttons instead of complex designer
3. Keep the core table regeneration functionality
4. Remove advanced features that client doesn't need

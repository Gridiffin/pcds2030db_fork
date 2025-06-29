# Fix TableStructureDesigner Loading and Simplify Interface

## Problem Summary
1. ❌ `TableStructureDesigner not available` - Script not loading properly
2. ❌ Simple column controls not being initialized when TableStructureDesigner fails
3. ❌ Need to ensure basic functionality works without complex designer
4. ❌ Missing fallback for when advanced features are unavailable

## Root Cause Analysis
- TableStructureDesigner script may not be loading due to path issues or script errors
- The fallback `addBasicColumnRowControls()` is not being called properly
- Need to ensure basic add/remove functionality works independently

## Tasks

### ✅ Issues to Fix
- [ ] Check TableStructureDesigner script loading
- [ ] Fix the fallback initialization when designer is not available
- [ ] Ensure simple controls work without complex designer
- [ ] Add basic table manipulation functions that don't rely on TableStructureDesigner

### ✅ Implementation Plan
- [ ] Create standalone simple column/row management
- [ ] Add direct table manipulation functions
- [ ] Ensure live preview works without complex designer
- [ ] Test with basic add/remove functionality

## Technical Approach
1. Check if TableStructureDesigner is actually needed for basic functionality
2. Create simplified functions that work independently
3. Ensure live table updates work with basic DOM manipulation
4. Provide clear fallback when advanced features aren't available

# Redesign Target Layout - Move Counter for Better Separation

## Problem
The target counter "Target 1", "Target 2" is still too close to the input boxes despite margin adjustments. Need a better layout solution with clear visual separation.

## Solution Options
1. **Move counter to the left side** - Make it a sidebar-style label
2. **Move counter above the entire target container** - Outside the bordered box
3. **Move counter to the top-left corner** - As a badge or label
4. **Create a separate header bar** - With background color for clear separation

## Chosen Solution
Move the target counter outside the bordered container and create a clear visual hierarchy:
- Counter outside the target-item border
- Target inputs in a separate contained section
- Better visual separation and breathing room

## Tasks
- [x] Restructure PHP layout to move counter outside target-item
- [x] Update JavaScript template to match new structure
- [x] Add visual styling for better separation
- [x] Test the new layout for better UX

## Implementation Details
**New Structure Created:**
```
Target 1                    ← Outside the border (clear separation)
┌─────────────────────────────────────┐
│ [Target Description] [Status Desc]  │  ← Inside bordered container
│                              [🗑️]   │
└─────────────────────────────────────┘
```

## Changes Made
1. **PHP Structure**: 
   - Moved `<h6 class="target-number">` outside the `.target-item` border
   - Wrapped in `.mb-4` container for proper spacing
   - Added `text-primary` class for visual distinction

2. **JavaScript Template**: 
   - Updated to match new container structure
   - Counter outside bordered section
   - Proper container hierarchy

3. **JavaScript Functions**:
   - Updated `updateTargetNumbers()` to work with new container structure
   - Fixed remove functionality to remove entire container including counter

4. **Visual Improvements**:
   - Clear separation between counter and input boxes
   - Counter now has primary color styling for visibility
   - Better spacing with dedicated container structure

## Result
- ✅ **Clear Visual Separation**: Counter is now outside the bordered input area
- ✅ **Better Hierarchy**: Obvious distinction between target number and form fields
- ✅ **Improved Spacing**: Natural gap created by the separate containers
- ✅ **Enhanced UX**: Cleaner, more organized appearance
- ✅ **Consistent Functionality**: All target operations work correctly with new structure

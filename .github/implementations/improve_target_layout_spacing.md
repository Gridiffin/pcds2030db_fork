# Update Target Layout - Status Label and Gap Improvements

## Problem
1. Status counter shows "Status 1", "Status 2" etc., but should show "Status Description" for better clarity
2. Need more breathing room between the target counter header and the input boxes

## Solution
1. Change status labels from "Status X" to "Status Description" 
2. Increase the margin-bottom on the target header section for better spacing

## Tasks
- [x] Update PHP structure to use "Status Description" instead of numbered status
- [x] Update JavaScript template to use "Status Description" 
- [x] Update JavaScript updateTargetNumbers function to not change status labels
- [x] Increase gap between target header and input boxes

## Implementation Details
- Remove dynamic status numbering and use static "Status Description" label ✅
- Increase mb-3 to mb-4 for more spacing between header and form fields ✅
- Ensure consistent labeling across existing and new targets ✅

## Changes Made
1. **PHP Structure**: Changed `Status <?php echo ($index + 1); ?>` to `Status Description`
2. **JavaScript Template**: Changed `Status ${targetCount}` to `Status Description`
3. **Spacing**: Updated `mb-3` to `mb-4` in the target header div
4. **JavaScript Function**: Simplified `updateTargetNumbers()` to only update target numbers, not status labels

## Result
- ✅ All status labels now show "Status Description" instead of numbered counters
- ✅ Increased breathing room between target headers and input boxes
- ✅ Consistent labeling across existing and dynamically added targets
- ✅ Cleaner, more user-friendly interface

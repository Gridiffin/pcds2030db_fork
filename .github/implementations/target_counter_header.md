# Target Counter Header Enhancement

## Overview
Add a dynamic target counter header to the targets container that shows the current number of targets and updates when targets are added or removed.

## Requirements
- Show current target count in the targets card header
- Update dynamically when targets are added/removed
- Provide clear visual indication of target quantity
- Maintain consistency with existing UI design

## Implementation Tasks

### Phase 1: Header Enhancement
- [x] Identify targets card header location
- [x] Add target counter to header
- [x] Style the counter appropriately
- [x] Ensure proper initial count

### Phase 2: Dynamic Updates
- [x] Update counter when targets are added
- [x] Update counter when targets are removed
- [x] Handle edge cases (no targets, single target)

### Phase 3: Styling & Polish
- [x] Match existing design patterns
- [x] Add appropriate icons/badges
- [x] Test visual consistency

## Implementation Details

### Target Counter Display
- Format: "Program Targets (X)" where X is the count
- Alternative: "Program Targets • X targets"
- Should be visually distinct but not overwhelming

### JavaScript Updates
- Update `updateTargetNumbers()` function to also update counter
- Update add/remove target event handlers
- Initial count calculation on page load

## Files to Modify
- `app/views/agency/programs/update_program.php` - Add counter to header and JavaScript

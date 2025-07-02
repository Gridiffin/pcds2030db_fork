# Clean dhtmlxGantt Layout Solution for Program Separators

## Problem
We need program rows to appear as separators without timeline areas, while target rows show timeline visualization.

## Solution: Use dhtmlxGantt Layout Configuration
Based on the official documentation, we can use dhtmlxGantt's layout system to create a cleaner solution.

## Implementation Steps

### Phase 1: Create Layout-Based Solution
- [x] Use dhtmlxGantt's layout configuration to create custom views
- [x] Implement dhtmlxGantt's native project type for programs (no timeline bars)
- [x] Use dhtmlxGantt's native task type for targets (with timeline bars)
- [x] Remove all CSS-based hacks and JavaScript DOM manipulation

### Phase 2: Data Structure Changes
- [x] Modified data transformation to use 'project' type for programs
- [x] Modified data transformation to use 'task' type for targets
- [x] Use dhtmlxGantt's native parent-child relationships
- [x] Leverage dhtmlxGantt's built-in project behavior

### Phase 3: Clean Implementation - Simple Types Only
- [x] Use dhtmlxGantt's native 'project' type for programs
- [x] Use dhtmlxGantt's native 'task' type for targets  
- [x] Enable dhtmlxGantt types configuration
- [x] Update task templates to use correct types
- [x] Update data transformation to use 'project' and 'task' types
- [x] Simple approach - let dhtmlxGantt handle project display natively
- [x] Added grid panel scrolling functionality
- [x] Test and verify basic solution works

### Phase 4: Grid Enhancements
- [x] Enable grid scrolling with `scrollable: true`
- [x] Enable elastic columns with `grid_elastic_columns: true`
- [x] Add CSS for proper grid overflow handling
- [x] Ensure grid panel is scrollable when content exceeds width

### Phase 5: Native CSS Classes Implementation
- [x] Use dhtmlxGantt's native CSS classes instead of custom styling
- [x] Apply styling to `.gantt_row.gantt_row_project` (dhtmlxGantt's built-in class)
- [x] Apply styling to `.gantt_row.gantt_row_task` (dhtmlxGantt's built-in class)
- [x] Remove custom `grid_row_class` template (let dhtmlxGantt handle automatically)
- [x] Clean implementation using only dhtmlxGantt's native features

### Phase 6: Cleanup
- [ ] Remove backup files and test files if solution works
- [ ] Remove any remaining comprehensive CSS approaches
- [ ] Ensure maintainable solution

## Current Implementation
Simple approach using dhtmlxGantt's native behavior:
- Programs: `type: 'project'` (dhtmlxGantt will handle display)
- Targets: `type: 'task'` (normal timeline bars with status colors)
- No complex CSS or layout modifications
- Let dhtmlxGantt's native project type behavior handle the separation

## Expected Result
- Programs appear as clean separator rows (no timeline area at all)
- Targets show normal timeline visualization
- Solution uses only dhtmlxGantt's native features
- No custom CSS hacks or JavaScript DOM manipulation

## Key dhtmlxGantt Features to Use
- `gantt.config.layout` for custom layout configuration
- `hide_empty: true` for hiding empty layout sections
- Multiple grid/timeline views with different data bindings
- Native dhtmlxGantt view system instead of CSS/JS hacks

# Multi-Agency Report Filter Improvement

## Problem Analysis
The current agency filter on the generate reports page has the following issues:
1. When switching between agencies, the program selection resets completely
2. Previously selected programs from other agencies are lost
3. The order numbering resets to zero when switching agencies
4. The current implementation replaces the entire program list instead of filtering/adding to it

## Current Behavior
- Agency filter works as a replacement filter (shows only selected agency's programs)
- When switching agencies, all previous selections are lost
- No multi-agency selection capability in one report

## Desired Behavior
- Agency filter should work as an additive filter for program display
- Previously selected programs from other agencies should remain selected
- Order numbering should be maintained across agency switches
- Support multi-agency program inclusion in reports
- Visual separation between agencies while maintaining cross-agency selections

## Implementation Plan

### Phase 1: Analysis and Planning
- [x] Analyze current implementation in `report-generator.js`
- [x] Review API endpoint `get_period_programs.php`
- [x] Understand current program selection logic
- [x] Document the issue and solution approach

### Phase 2: Backend API Enhancement
- [x] Modify `get_period_programs.php` to support multi-agency mode
- [x] Add agency information to API response for proper grouping
- [x] Ensure API returns agency information for proper grouping
- [x] Test API changes

### Phase 3: Frontend Logic Improvement
- [x] Modify `report-generator.js` to maintain program selections across agency changes
- [x] Implement additive program selection logic
- [x] Create persistent selection state management
- [x] Update order numbering to maintain sequential order across agencies
- [x] Add visual indicators for cross-agency selections

### Phase 4: UI/UX Enhancements
- [x] Update agency filter UI to clarify multi-selection behavior
- [x] Add visual separation between agencies in program list
- [x] Show selected programs count per agency
- [x] Add "Clear All Selections" and "Select All from Agency" buttons
- [x] Improve program ordering visual feedback
- [x] Add CSS styling for enhanced user experience

### Phase 5: Critical Bug Fix - Report Generation Integration
- [x] **CRITICAL**: Fix form submission to collect ALL selected programs from global state
- [x] Update `report-ui.js` to use globalProgramSelections instead of DOM-based collection
- [x] Ensure program order information is preserved from global state
- [x] Add validation to ensure programs are selected before generation
- [x] Add debugging functions for troubleshooting
- [ ] Test report generation with multi-agency program selection
- [ ] Validate that all selected programs appear in generated reports

### Phase 6: Testing and Validation
- [ ] Test multi-agency selection functionality
- [ ] Verify program ordering works across agencies
- [ ] Test report generation with mixed agency programs
- [ ] Validate UI/UX improvements
- [ ] Clean up any test files

## Technical Implementation Details

### 1. Backend Changes
- Enhance API to include `agency_name` and `owner_agency_id` in response
- Support mode parameter: 'filter' vs 'additive'
- Return all programs when no agency filter is applied

### 2. Frontend State Management
- Maintain global program selection state
- Track selected programs across agency filter changes
- Preserve order numbering state
- Implement efficient state updates

### 3. UI Improvements
- Change agency filter from single-select to multi-select OR
- Add toggle for "Filter View" vs "Add to Selection" mode
- Visual grouping by agency with clear selection indicators
- Enhanced program ordering controls

### 4. Alternative Solutions Considered
1. **Multi-select Agency Dropdown**: Allow selecting multiple agencies at once
2. **Toggle Mode**: Switch between "Filter" and "Add" modes
3. **Tabbed Interface**: Separate tabs for each agency with global selection state
4. **Persistent Selection Panel**: Show selected programs in a separate panel

## Recommended Approach
Implement a **hybrid solution** that combines:
- Multi-select agency filter capability
- Persistent selection state across filter changes
- Clear visual separation and indicators
- Intuitive "Add to Report" workflow

This will provide the most flexible and user-friendly experience for creating multi-agency reports.

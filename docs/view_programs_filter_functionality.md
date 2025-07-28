# View Programs Filter Functionality

## Overview

The View Programs page (`app/views/agency/programs/view_programs.php`) features a sophisticated filtering system that allows users to search and filter programs across all tabs (Draft Submissions, Finalized Submissions, and Program Templates). The filtering works in real-time and provides visual feedback through filter badges.

## Filter Components

### 1. **Search Bar**
- **Location**: Top-left of the filters section
- **Functionality**: Searches across program names, program numbers, and initiative names
- **Real-time**: Updates as you type (with 300ms debounce)
- **Case-insensitive**: Searches are not case-sensitive

### 2. **Status Filter**
- **Location**: Middle of the filters section
- **Options**:
  - All Status (default)
  - Monthly Target Achieved
  - On Track for Year
  - Severe Delays
  - Not Started
- **Functionality**: Filters programs based on their rating/status

### 3. **Initiative Filter**
- **Location**: Right side of the filters section
- **Options**:
  - All Initiatives (default)
  - Not Linked to Initiative
  - Dynamic list of all active initiatives
- **Functionality**: Filters programs based on their associated initiative

### 4. **Reset Button**
- **Location**: Far right of the filters section
- **Functionality**: Clears all active filters and resets to default state

## Technical Implementation

### File Structure
```
assets/js/agency/view-programs/
├── view-programs.js      # Main entry point
├── filters.js           # Filter logic and functionality
├── logic.js             # Business logic
└── dom.js               # DOM manipulation
```

### HTML Structure
```html
<!-- Unified Filters Section -->
<div class="content-card shadow-sm mb-3">
    <div class="card-body pb-0">
        <div class="row g-3">
            <!-- Search Filter -->
            <div class="col-md-4 col-sm-12">
                <label for="globalProgramSearch" class="form-label">Search Programs</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="globalProgramSearch" 
                           placeholder="Search by program name or number">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2 col-sm-6">
                <label for="globalStatusFilter" class="form-label">Status</label>
                <select class="form-select" id="globalStatusFilter">
                    <option value="">All Status</option>
                    <option value="monthly_target_achieved">Monthly Target Achieved</option>
                    <option value="on_track_for_year">On Track for Year</option>
                    <option value="severe_delay">Severe Delays</option>
                    <option value="not_started">Not Started</option>
                </select>
            </div>

            <!-- Initiative Filter -->
            <div class="col-md-3 col-sm-6">
                <label for="globalInitiativeFilter" class="form-label">Initiative</label>
                <select class="form-select" id="globalInitiativeFilter">
                    <option value="">All Initiatives</option>
                    <option value="no-initiative">Not Linked to Initiative</option>
                    <?php foreach ($active_initiatives as $initiative): ?>
                        <option value="<?php echo $initiative['initiative_id']; ?>">
                            <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Reset Button -->
            <div class="col-md-1 col-sm-12 d-flex align-items-end">
                <button id="resetGlobalFilters" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
        
        <!-- Filter Badges -->
        <div id="globalFilterBadges" class="filter-badges mt-2"></div>
    </div>
</div>
```

### JavaScript Implementation

#### Filter Class Structure
```javascript
export class ViewProgramsFilters {
    constructor() {
        this.logic = new ViewProgramsLogic();
        this.activeFilters = {};
        this.currentActiveTab = 'draft';
    }
    
    init() {
        this.initGlobalFilters();
        this.initTabSwitching();
    }
}
```

#### Filter Application Logic
```javascript
applyTableFilter(containerId, filters) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const programBoxes = container.querySelectorAll('.program-box');
    
    programBoxes.forEach(programBox => {
        let visible = true;
        
        // Search filter
        if (filters.search && filters.search.trim() !== '') {
            const searchTerm = filters.search.toLowerCase().trim();
            const programName = programBox.querySelector('.program-name')?.textContent.toLowerCase() || '';
            const programNumber = programBox.querySelector('.program-number')?.textContent.toLowerCase() || '';
            const initiativeName = programBox.querySelector('.initiative-badge, .initiative-icon')?.textContent.toLowerCase() || '';
            
            visible = visible && (
                programName.includes(searchTerm) ||
                programNumber.includes(searchTerm) ||
                initiativeName.includes(searchTerm)
            );
        }
        
        // Status filter
        if (filters.status && filters.status !== '') {
            const rating = programBox.getAttribute('data-status') || '';
            visible = visible && (rating === filters.status);
        }
        
        // Initiative filter
        if (filters.initiative && filters.initiative !== '') {
            const initiativeId = programBox.getAttribute('data-initiative-id') || '0';
            
            if (filters.initiative === 'no-initiative') {
                visible = visible && (initiativeId === '0' || initiativeId === '');
            } else {
                visible = visible && (initiativeId === filters.initiative);
            }
        }
        
        // Show/hide program box
        programBox.style.display = visible ? '' : 'none';
    });
}
```

### Data Attributes for Filtering

Each program box has specific data attributes that enable filtering:

```html
<div class="program-box" 
     data-status="<?php echo $current_rating; ?>" 
     data-status-order="<?php echo $rating_order[$current_rating] ?? 999; ?>"
     data-initiative="<?php echo !empty($program['initiative_name']) ? htmlspecialchars($program['initiative_name']) : 'zzz_no_initiative'; ?>"
     data-initiative-id="<?php echo $program['initiative_id'] ?? '0'; ?>">
```

- `data-status`: Contains the program's rating (monthly_target_achieved, on_track_for_year, etc.)
- `data-status-order`: Numeric order for sorting
- `data-initiative`: Initiative name for display
- `data-initiative-id`: Initiative ID for filtering

## Filter Features

### 1. **Real-time Filtering**
- Filters are applied immediately as you type or select options
- Search has a 300ms debounce to prevent excessive processing
- All tabs are filtered simultaneously

### 2. **Filter Badges**
- Active filters are displayed as removable badges below the filter controls
- Each badge shows the filter type and value
- Click the X button to remove individual filters
- Badges update automatically when filters change

### 3. **Counter Updates**
- Tab counters update in real-time to show filtered results
- Counts reflect only visible programs after filtering
- Updates happen automatically when filters change

### 4. **Cross-tab Functionality**
- Filters work across all three tabs (Draft, Finalized, Templates)
- Switching tabs maintains active filters
- Each tab shows only programs that match the current filters

### 5. **Reset Functionality**
- Reset button clears all filters at once
- Returns to default "show all" state
- Updates all tabs and counters

## Usage Examples

### Search by Program Name
1. Type "forestry" in the search box
2. Results show only programs with "forestry" in the name, number, or initiative
3. All tabs update to show matching programs

### Filter by Status
1. Select "Monthly Target Achieved" from status dropdown
2. Results show only programs with that specific status
3. Counter badges update to reflect filtered counts

### Filter by Initiative
1. Select a specific initiative from the dropdown
2. Results show only programs linked to that initiative
3. "Not Linked to Initiative" option shows programs without initiatives

### Combined Filtering
1. Search for "forestry" AND select "Monthly Target Achieved" status
2. Results show only forestry programs that have achieved monthly targets
3. Filter badges show both active filters

### Remove Individual Filters
1. Click the X button on any filter badge
2. That specific filter is removed
3. Results update to reflect remaining filters

## Status Mapping

The system maps database rating values to display labels:

```php
$rating_map = [
    'not_started' => [
        'label' => 'Not Started', 
        'class' => 'secondary',
        'icon' => 'fas fa-hourglass-start'
    ],
    'on_track_for_year' => [
        'label' => 'On Track for Year', 
        'class' => 'warning',
        'icon' => 'fas fa-calendar-check'
    ],
    'monthly_target_achieved' => [
        'label' => 'Monthly Target Achieved', 
        'class' => 'success',
        'icon' => 'fas fa-check-circle'
    ],
    'severe_delay' => [
        'label' => 'Severe Delays', 
        'class' => 'danger',
        'icon' => 'fas fa-exclamation-triangle'
    ]
];
```

## Performance Considerations

### 1. **Debouncing**
- Search input uses 300ms debounce to prevent excessive filtering
- Reduces CPU usage during rapid typing

### 2. **Efficient DOM Queries**
- Uses `querySelectorAll` to get all program boxes at once
- Filters are applied by showing/hiding elements rather than recreating them

### 3. **Counter Updates**
- Counters are updated with a small delay to ensure DOM changes are complete
- Prevents race conditions between filtering and counting

## Accessibility Features

### 1. **Keyboard Navigation**
- All filter controls are keyboard accessible
- Tab navigation works properly through all filter elements

### 2. **Screen Reader Support**
- Proper labels and ARIA attributes on all filter controls
- Filter badges include remove buttons with appropriate labels

### 3. **Visual Feedback**
- Clear visual indicators for active filters
- Consistent styling across all filter components

## Browser Compatibility

The filtering system works across all modern browsers:
- Chrome/Edge (Chromium-based)
- Firefox
- Safari
- Mobile browsers

## Future Enhancements

### Potential Improvements
1. **Advanced Search**: Add support for complex search queries
2. **Filter Persistence**: Save filter state in localStorage
3. **Export Filtered Results**: Allow export of filtered program lists
4. **Custom Filter Presets**: Save and reuse common filter combinations
5. **Date Range Filtering**: Filter by program creation or submission dates

### Performance Optimizations
1. **Virtual Scrolling**: For large program lists
2. **Lazy Loading**: Load program data as needed
3. **Caching**: Cache filtered results for better performance 
# dhtmlxGantt Integration Guide

## Overview
The PCDS2030 Dashboard now uses dhtmlxGantt library to display initiative timelines with programs and targets. This provides a professional, feature-rich Gantt chart with proper timeline visualization and status-based coloring.

## Features
- Two-tier timeline header (Years/Quarters)
- Hierarchical structure (Programs as parents, Targets as children)
- Status-based color coding for targets
- Interactive tooltips and navigation
- Responsive design
- Professional styling

## Integration

### Files Structure
```
assets/
├── css/components/dhtmlxgantt.css     # Custom styling for dhtmlxGantt
└── js/components/dhtmlxgantt.js       # PCDSGanttChart class configuration

app/
├── api/simple_gantt_data.php          # API endpoint for Gantt data
└── views/agency/initiatives/view_initiative.php  # Main integration page
```

### Usage

#### Basic Implementation
```javascript
// Initialize dhtmlxGantt
const ganttChart = new PCDSGanttChart('gantt_container_id', 'api_url');
```

#### HTML Structure
```html
<div id="gantt_here" class="gantt_container">
    <!-- dhtmlxGantt will render here -->
</div>

<!-- Include dhtmlxGantt from CDN -->
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">

<!-- Include custom configuration -->
<script src="assets/js/components/dhtmlxgantt.js"></script>
```

## Data Structure

### API Response Format
The API should return data in this format:
```json
{
    "success": true,
    "data": {
        "initiative": {
            "id": 1,
            "name": "Initiative Name",
            "start_date": "2024-01-01",
            "end_date": "2025-12-31"
        },
        "programs": [
            {
                "program_id": 1,
                "name": "Program Name",
                "program_number": "P1",
                "targets": [
                    {
                        "id": 1,
                        "target_id": 1,
                        "name": "Target Name",
                        "target_number": "T1.1",
                        "status": "on-target",
                        "start_date": "2024-01-01",
                        "end_date": "2024-12-31"
                    }
                ]
            }
        ]
    }
}
```

### dhtmlxGantt Task Format
The data is automatically transformed to dhtmlxGantt format:
```javascript
[
    {
        id: 1,
        text: "Program Name",
        number: "P1",
        type: "program",
        start_date: "2024-01-01",
        end_date: "2025-12-31",
        open: true,
        readonly: true
    },
    {
        id: 2,
        text: "Target Name",
        number: "T1.1",
        type: "target",
        target_id: 1,
        status: "on-target",
        start_date: "2024-01-01",
        end_date: "2024-12-31",
        parent: 1,
        readonly: true
    }
]
```

## Status Colors

### Target Status Mapping
- **Completed**: `#17a2b8` (Blue)
- **On Target**: `#28a745` (Green)
- **At Risk**: `#ffc107` (Yellow/Orange)
- **Off Target**: `#dc3545` (Red)
- **Not Started**: `#6c757d` (Gray)

### Program Color
- **Programs**: `#6c757d` (Gray) - Used for parent program rows

## Configuration

### Timeline Scales
```javascript
gantt.config.scales = [
    {
        unit: "year",
        step: 1,
        format: "%Y"
    },
    {
        unit: "quarter", 
        step: 1,
        format: function(date) {
            return "Q" + Math.floor((date.getMonth()) / 3 + 1);
        }
    }
];
```

### Grid Columns
```javascript
gantt.config.columns = [
    {
        name: "text",
        label: "Item",
        width: 200,
        tree: true
    },
    {
        name: "number",
        label: "Number", 
        width: 80,
        align: "center"
    }
];
```

## Testing

### Test Pages
- `test_dhtmlxgantt.php` - Visual test of the Gantt chart
- `test_api_gantt.php` - API endpoint validation test

### Browser Console
The implementation includes detailed console logging for debugging:
```javascript
console.log('Loading data from:', this.apiUrl);
console.log('API response data:', data);
console.log('Transformed gantt data:', ganttData);
```

## Responsive Design
The chart automatically adjusts for different screen sizes:
- Mobile: Grid width 250px, height 400px
- Desktop: Grid width 300px, height 600px

## Error Handling
- API connection errors
- JSON parsing errors
- Missing data validation
- User-friendly error messages

## Browser Support
- Chrome (recommended)
- Firefox
- Safari
- Edge
- IE11+ (with polyfills)

## Dependencies
- dhtmlxGantt (via CDN)
- Bootstrap 5 (for styling)
- Font Awesome (for icons)
- Modern JavaScript (ES6+)

# Initiative View Improvements

## Issues to Address

### 1. Initiative Number Badge
- [x] Move initiative counter from meta section to beside initiative name
- [x] Style as a visible badge next to the title

### 2. Progress Bar Text Visibility
- [x] Fix white text on white background in progress bar
- [x] Ensure proper contrast for readability

### 3. Health Score Logic
- [x] Research how initiative health is determined
- [x] Document the calculation method
- [x] Implement proper health calculation based on program performance

### 4. Remove Duplicate Initiative Name
- [x] Remove initiative name from "Initiative Information" section
- [x] Keep only the overview section title

### 5. Code Organization
- [x] Move inline styles to separate CSS file
- [x] Move any inline scripts to separate JS files
- [x] Import new CSS file into main.css
- [x] Scan existing assets for reusable classes/functions
- [x] Remove duplicated code

## Files to Modify
- `app/views/agency/initiatives/view_initiative.php`
- `assets/css/pages/initiative-view.css`
- `assets/css/main.css` (imports)
- Potentially create `assets/js/initiative-view.js`

## Health Score Calculation Documentation

The initiative health score is calculated based on the performance ratings of all programs under the initiative:

### Rating Score Mapping
- **Target Achieved/Completed**: 100 points
- **On Track/On Track Yearly**: 75 points  
- **Delayed**: 50 points
- **Severe Delay**: 25 points
- **Not Started**: 10 points

### Health Categories
- **80-100**: Excellent - Programs performing well (Green)
- **60-79**: Good - Based on program performance (Green)
- **40-59**: Fair - Some programs need attention (Yellow)
- **0-39**: Poor - Programs need improvement (Red)

### Visual Representation
- Circular progress indicator with dynamic color based on score
- Percentage display in center
- Descriptive text below with appropriate color coding

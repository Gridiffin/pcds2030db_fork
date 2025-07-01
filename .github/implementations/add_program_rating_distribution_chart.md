# Add Program Rating Distribution Graph - Implementation Plan

## Overview
Add a program rating distribution chart to the initiative view page, showing the distribution of program ratings for programs under the current initiative.

## Requirements
- Display program rating distribution chart below the "Initiative Information" section
- Use similar design and functionality as the agency dashboard chart
- Show ratings for programs belonging to the current initiative only
- Responsive design that works on different screen sizes

## Implementation Tasks

### 1. Research Existing Implementation
- ✅ Find the rating distribution chart code in agency dashboard
- ✅ Analyze the chart structure, data processing, and styling
- ✅ Identify reusable components and functions

### 2. Data Processing
- ✅ Create function to get rating distribution for initiative programs
- ✅ Process program ratings using existing rating conversion functions
- ✅ Calculate counts and percentages for each rating category

### 3. Chart Implementation
- ✅ Add new chart section below Initiative Information card
- ✅ Implement Chart.js donut/pie chart for rating distribution
- ✅ Add proper styling and responsive behavior
- ✅ Include legend and data labels

### 4. Styling and Integration
- ✅ Ensure chart matches existing design patterns
- ✅ Add proper spacing and layout
- ✅ Test responsiveness on different screen sizes
- ✅ Integrate with existing CSS structure

### 5. Code Organization (Complete)
- ✅ Move inline JavaScript to separate file in assets/js
- ✅ Follow project conventions for script organization
- ✅ Ensure proper script loading and dependencies
- ✅ Created `assets/js/agency/initiative-view.js` for chart functionality
- ✅ Updated PHP to include external JavaScript file
- ✅ Pass rating data through JSON element instead of inline PHP

### 6. Testing and Optimization
- [ ] Test with different initiative data sets
- [ ] Verify chart displays correctly with various rating distributions
- [ ] Ensure performance is acceptable
- [ ] Validate data accuracy

## Technical Approach
1. Leverage existing rating conversion functions
2. Use Chart.js library (already used in dashboard)
3. Follow existing card layout patterns
4. Reuse color schemes from dashboard charts

## Expected Outcome
A visually appealing and informative rating distribution chart that helps users understand the performance distribution of programs within the initiative.

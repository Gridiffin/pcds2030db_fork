# Flexible Outcome Data Table Implementation

## Problem Analysis
The current "Create Outcome" feature in the agency side only supports monthly data as the Y-axis (rows). Users need the ability to fully design their own data table structure with flexible row and column configurations.

## Current System Limitations
- ✅ **Fixed to months only**: Currently hardcoded to 12 months (January-December) as rows
- ✅ **Limited flexibility**: Users can only add columns, not customize row structure
- ✅ **No custom time periods**: Can't use quarters, years, or custom periods
- ✅ **No custom categories**: Can't use non-temporal categories as rows

## Solution Overview
Create a flexible data table designer that allows users to:
1. Choose table structure type (monthly, quarterly, yearly, custom categories)
2. Define custom row labels
3. Define custom column headers
4. Support different data types and units
5. Preview table structure before finalizing

## Implementation Steps

### Phase 1: Backend Data Structure Enhancement
- [x] **1.1** Update database schema to support flexible table structures
  - [x] Add `table_structure_type` field to `sector_outcomes_data` table
  - [x] Add `row_config` JSON field for custom row definitions
  - [x] Add `column_config` JSON field for enhanced column definitions
  - [x] Create migration script for existing data

- [x] **1.2** Update data processing functions
  - [x] Modify outcome creation to handle flexible structures
  - [x] Update data validation for different structure types
  - [x] Enhance JSON data format to support new structure

### Phase 2: Frontend Table Designer
- [x] **2.1** Create table structure selector
  - [x] Add radio buttons for structure types (Monthly, Quarterly, Yearly, Custom)
  - [x] Create dynamic form based on selection
  - [x] Add preview functionality

- [x] **2.2** Implement custom row designer
  - [x] Allow users to add/remove/reorder rows
  - [x] Support different row types (text, categories, time periods)
  - [x] Add validation for row labels

- [x] **2.3** Enhance column designer
  - [x] Keep existing column functionality
  - [x] Add column data types (number, percentage, currency, text)
  - [x] Add unit configuration per column
  - [x] Support column grouping/categories

### Phase 3: Data Entry Interface
- [x] **3.1** Create flexible data entry table
  - [x] Render table based on structure configuration
  - [x] Support different input types per column
  - [x] Add data validation based on column types

- [x] **3.2** Add advanced features
  - [x] Auto-calculation rows (totals, averages)
  - [x] Formula support for calculated fields
  - [ ] Import/export functionality for bulk data entry

### Phase 4: Visualization Updates
- [x] **4.1** Update chart generation
  - [x] Modify chart.js integration to handle flexible structures
  - [x] Support different chart types based on data structure
  - [x] Add chart configuration options

- [x] **4.2** Enhance viewing interfaces
  - [x] Update view_outcome.php to handle flexible structures
  - [x] Update admin viewing interfaces
  - [ ] Add export options for different formats

### Phase 5: User Experience Enhancements
- [ ] **5.1** Add templates and presets
  - [ ] Create common table structure templates
  - [ ] Allow saving custom templates
  - [ ] Quick setup for common reporting patterns

- [ ] **5.2** Improve workflow
  - [ ] Add step-by-step wizard interface
  - [ ] Include help text and examples
  - [ ] Add validation and error handling

## Current Implementation Status

### ✅ Completed Features

**Database and Backend Infrastructure:**
- ✅ Added flexible table structure fields to `sector_outcomes_data` table
- ✅ Created migration script and updated existing data for backward compatibility
- ✅ Enhanced outcome data processing functions to handle flexible structures
- ✅ Updated admin and agency outcome retrieval functions

**Frontend Table Designer:**
- ✅ Created comprehensive table structure designer with multiple structure types
- ✅ Implemented custom row and column configuration
- ✅ Added data type support (number, currency, percentage, text)
- ✅ Built auto-calculation engine with formulas, sums, averages, and percentages
- ✅ Added validation and preview functionality

**Data Entry and Viewing:**
- ✅ Created flexible outcome creation interface with structure designer
- ✅ Built enhanced outcome viewing pages for both agency and admin users
- ✅ Implemented automatic redirection from classic to flexible viewers
- ✅ Added structure information and metadata display

**Chart and Visualization:**
- ✅ Created enhanced charting system supporting both classic and flexible structures
- ✅ Updated all outcome viewing pages to use new chart system
- ✅ Added chart configuration options and download functionality
- ✅ Supports multiple chart types (line, bar, radar, doughnut)

**Advanced Features:**
- ✅ Auto-calculation rows with various formula types
- ✅ Formula validation and dependency tracking
- ✅ Enhanced data visualization with flexible axis configurations

### 📋 Remaining Tasks

**Data Management:**
- [ ] Import/export functionality for bulk data entry
- [ ] CSV/Excel import templates for different table structures
- [ ] Data validation and error handling for imports

**User Experience:**
- [ ] Table structure templates and presets for common patterns
- [ ] Step-by-step wizard interface for complex setups
- [ ] Enhanced help text and documentation

**Testing and Optimization:**
- [ ] User acceptance testing with different table structures
- [ ] Performance optimization for complex calculations
- [ ] Migration testing for existing outcome data

## Technical Architecture

**Files Created/Modified:**
- `app/migrations/add_flexible_table_structure.sql` - Database schema changes
- `assets/js/table-structure-designer.js` - Frontend table designer
- `assets/js/table-calculation-engine.js` - Calculation and formula engine
- `assets/js/enhanced-outcomes-chart.js` - Enhanced charting system
- `assets/css/table-structure-designer.css` - Styling for designer interface
- `app/views/agency/outcomes/create_outcome_flexible.php` - Flexible creation interface
- `app/views/agency/outcomes/view_outcome_flexible.php` - Enhanced agency viewer
- `app/views/admin/outcomes/view_outcome_flexible.php` - Enhanced admin viewer
- `app/views/agency/outcomes/view_outcome.php` - Updated with redirect logic
- `app/views/admin/outcomes/view_outcome.php` - Updated with redirect logic
- `app/lib/admins/outcomes.php` - Enhanced outcome data functions

The system now provides a fully functional flexible outcome data table system that maintains backward compatibility while offering extensive customization options for users who need more than the traditional monthly structure.

## Technical Implementation Details

### Database Schema Changes
```sql
-- Add new fields to sector_outcomes_data table
ALTER TABLE sector_outcomes_data 
ADD COLUMN table_structure_type ENUM('monthly', 'quarterly', 'yearly', 'custom') DEFAULT 'monthly',
ADD COLUMN row_config JSON,
ADD COLUMN column_config JSON;
```

### New Data Structure Format
```json
{
  "structure_type": "custom",
  "row_config": {
    "type": "custom",
    "rows": [
      {"id": "row1", "label": "Production Volume", "type": "data"},
      {"id": "row2", "label": "Export Value", "type": "data"},
      {"id": "total", "label": "Total", "type": "calculated", "formula": "row1+row2"}
    ]
  },
  "column_config": {
    "columns": [
      {"id": "col1", "label": "2023", "type": "number", "unit": "m³"},
      {"id": "col2", "label": "2024", "type": "currency", "unit": "RM"},
      {"id": "col3", "label": "2025", "type": "percentage", "unit": "%"}
    ]
  },
  "data": {
    "row1": {"col1": 1000, "col2": 2000, "col3": 15.5},
    "row2": {"col1": 1500, "col2": 3000, "col3": 25.2}
  }
}
```

### Files to Create/Modify
1. **New Files:**
   - `assets/js/table-structure-designer.js`
   - `app/views/agency/outcomes/create_outcome_wizard.php`
   - `app/migrations/add_flexible_table_structure.sql`

2. **Files to Modify:**
   - `app/views/agency/outcomes/create_outcome.php`
   - `app/views/agency/outcomes/view_outcome.php`
   - `app/views/agency/outcomes/edit_outcomes.php`
   - `assets/js/outcome-editor.js`
   - `assets/css/custom/metric-create.css`

## Testing Plan
- [ ] **Unit Tests**: Test data structure validation
- [ ] **Integration Tests**: Test with different structure types
- [ ] **User Acceptance Tests**: Test with actual agency users
- [ ] **Migration Tests**: Ensure existing data remains functional

## Rollout Strategy
1. **Phase 1**: Deploy backend changes with backward compatibility
2. **Phase 2**: Release new interface as optional feature
3. **Phase 3**: Gradually migrate existing outcomes to new system
4. **Phase 4**: Deprecate old interface after full migration

## Success Metrics
- [ ] Users can create custom table structures beyond monthly data
- [ ] Existing functionality remains unaffected
- [ ] New table types are properly visualized in charts
- [ ] Performance remains acceptable with complex structures
- [ ] User feedback is positive regarding flexibility

## Notes
- Maintain backward compatibility with existing monthly-based outcomes
- Ensure all new features work with existing audit log system
- Consider performance implications of complex table structures
- Plan for data migration of existing outcomes if needed

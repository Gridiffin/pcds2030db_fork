# Outcomes System Analysis

## Overview

I've conducted a comprehensive study of the outcomes and outcomes details functionality in the PCDS2030 Dashboard system. This analysis covers the database structure, relationships, data flow, and potential concerns.

## Database Structure

### Key Tables

#### 1. `outcomes_details` Table
- **Purpose**: Master definition table for outcome types/templates
- **Primary Key**: `detail_id` (auto-increment)
- **Key Fields**:
  - `detail_name`: Human-readable name of the outcome type
  - `detail_json`: JSON structure defining layout and display format
  - `is_cumulative`: Flag indicating if this outcome accumulates over time
  - `is_draft`: Draft status flag
  - `is_important`: Flag for important outcomes used in slide reports
  - `created_at`, `updated_at`: Timestamps

#### 2. `sector_outcomes_data` Table  
- **Purpose**: Actual outcome data submitted by agencies for specific sectors/periods
- **Primary Key**: `id` (auto-increment)
- **Key Fields**:
  - `metric_id`: Links to outcomes via logic (NOT a foreign key to outcomes_details)
  - `sector_id`: References sectors table
  - `period_id`: References reporting_periods table
  - `table_name`: Display name for this outcome instance
  - `data_json`: JSON containing the actual outcome data (monthly values, columns, etc.)
  - `is_draft`: Draft status flag
  - `submitted_by`: User who submitted this outcome
  - `is_important`: Flag for important outcomes used in slide reports

#### 3. `program_outcome_links` Table
- **Purpose**: Links programs to outcome definitions
- **Key Fields**:
  - `program_id`: References programs table
  - `outcome_id`: References `outcomes_details.detail_id`
  - `created_by`: User who created the link

## Data Flow and Relationships

### 1. Outcome Definition Flow
```
outcomes_details (templates) → sector_outcomes_data (actual data)
```

### 2. Program Integration Flow
```
programs → program_outcome_links → outcomes_details
```

### 3. Automation Flow
```
program completion → outcome_automation.php → sector_outcomes_data
```

## Key Concerns Identified

### 1. **Inconsistent Identifier Usage**
- `outcomes_details` uses `detail_id` as primary key
- `sector_outcomes_data` uses `metric_id` field but this is NOT a foreign key
- The relationship between these tables is managed through application logic, not database constraints
- This creates potential for orphaned records and data integrity issues

### 2. **Dual JSON Storage Pattern**
- `outcomes_details.detail_json`: Stores layout/template information
- `sector_outcomes_data.data_json`: Stores actual monthly data
- Different JSON schemas serve different purposes but naming could be confusing

### 3. **Complex Automation Logic**
- Program completion triggers automatic outcome updates
- Automation is currently DISABLED in the code (see `outcome_automation.php`)
- When enabled, completed programs automatically increment outcome values
- This creates dependencies between programs and outcomes that may not be obvious

### 4. **Draft vs Submitted State Management**
- Both tables have `is_draft` fields
- `outcomes_details.is_draft`: Controls if the outcome template is available
- `sector_outcomes_data.is_draft`: Controls if the data submission is final
- State transitions are managed through admin and agency interfaces

### 5. **Multiple Access Patterns**
- Admin users: Can manage outcome templates and view all sector data
- Agency users: Can only submit data for their assigned sector
- Data is filtered by `sector_id` from user session

## Data Examples

### Outcomes Details (Templates)
```json
{
  "layout_type": "simple",
  "items": [
    {
      "value": "32",
      "description": "On-going programs and initiatives by SFC (as of Sept 2024)"
    }
  ]
}
```

### Sector Outcomes Data (Actual Data)
```json
{
  "columns": ["2022", "2023", "2024", "2025", "2026"],
  "units": {"2022": "RM", "2023": "RM", "2024": "RM", "2025": "RM"},
  "data": {
    "January": {"2022": 408531176.77, "2023": 263569916.63},
    "February": {"2022": 239761718.38, "2023": 226356164.3}
  }
}
```

## File Organization

### Admin Views
- `manage_outcomes.php`: List and filter all outcomes
- `edit_outcome.php`: Create/edit outcome templates and data
- `view_outcome.php`: Display outcome details
- `unsubmit_outcome.php`: Change submission status

### Agency Views  
- `submit_outcomes.php`: Main interface for agencies
- `create_outcome.php`: Create new outcome data
- `edit_outcomes.php`: Edit draft outcome data
- `view_outcome.php`: View submitted outcome details

### Core Libraries
- `app/lib/admins/outcomes.php`: Admin outcome functions
- `app/lib/agencies/outcomes.php`: Agency outcome functions
- `app/lib/outcome_automation.php`: Program-outcome automation logic

## Potential Issues

### 1. **Data Integrity Risks**
- No foreign key constraint between `sector_outcomes_data.metric_id` and `outcomes_details.detail_id`
- Relies on application-level validation
- Risk of orphaned outcome data

### 2. **Confusing Naming Convention**
- `metric_id` in sector_outcomes_data doesn't reference a metrics table
- Actually references `outcomes_details.detail_id`
- Could lead to developer confusion

### 3. **Complex State Management**
- Multiple draft states across different tables
- Admin can unsubmit agency submissions
- History tracking exists but may not capture all state changes

### 4. **Automation Dependencies**
- Program completion affects outcome values
- Currently disabled but creates hidden dependencies
- May cause unexpected behavior when re-enabled

## Recommendations

### 1. **Database Schema Improvements**
- Add proper foreign key constraint between tables
- Consider renaming `metric_id` to `outcome_template_id` for clarity
- Add database-level constraints for data integrity

### 2. **Documentation**
- Document the relationship between outcomes_details and sector_outcomes_data
- Clarify the purpose of each JSON field
- Document the automation behavior

### 3. **Code Refactoring**
- Standardize function naming conventions
- Centralize outcome validation logic
- Improve error handling for missing relationships

### 4. **User Interface**
- Make the relationship between templates and data clearer in the UI
- Add warnings when deleting outcome templates that have associated data
- Improve feedback for automation-related changes

This analysis should help you better understand the outcomes system and identify areas that may need attention or clarification.

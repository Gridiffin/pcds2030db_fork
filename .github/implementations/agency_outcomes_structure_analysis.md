# Agency Side Outcomes Structure Analysis

## Overview
This document provides a comprehensive analysis of how outcomes work on the agency side of the PCDS2030 Dashboard system, including database structure, file organization, and data flow.

## Database Tables Responsible for Outcomes

### Primary Table: `sector_outcomes_data`
**Location**: Main database table storing all outcome data
**Purpose**: Stores outcome definitions, data, and metadata

**Schema**:
```sql
CREATE TABLE sector_outcomes_data (
    id INT PRIMARY KEY AUTO_INCREMENT,           -- Internal record ID
    metric_id INT NOT NULL,                      -- Business logic ID (user-facing)
    sector_id INT NOT NULL,                      -- Links to sectors table
    period_id INT NULL,                          -- Links to reporting_periods table
    table_name VARCHAR(255) NOT NULL,            -- Display name for the outcome
    data_json LONGTEXT NOT NULL,                 -- JSON structure containing columns and monthly data
    is_draft TINYINT NOT NULL DEFAULT 1,         -- 0 = submitted, 1 = draft
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    submitted_by INT NULL                        -- Links to users table
);
```

**Key Indexes**:
- `metric_sector_draft` (metric_id, sector_id, is_draft)
- `fk_period_id` (period_id)
- `fk_submitted_by` (submitted_by)

### Supporting Tables

#### `outcome_history`
**Purpose**: Tracks changes and history of outcomes
```sql
CREATE TABLE outcome_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    outcome_record_id INT NOT NULL,              -- Links to sector_outcomes_data.id
    metric_id INT NOT NULL,                      -- Business logic ID
    data_json LONGTEXT NOT NULL,                 -- Snapshot of data at time of change
    action_type VARCHAR(50) NOT NULL,            -- 'create', 'edit', 'submit', 'unsubmit'
    status VARCHAR(50) NOT NULL,                 -- 'draft', 'submitted'
    changed_by INT NOT NULL,                     -- Links to users table
    change_description TEXT NULL,                -- Optional description
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `outcomes_details`
**Purpose**: Stores reusable outcome detail templates/structures
```sql
CREATE TABLE outcomes_details (
    detail_id INT PRIMARY KEY AUTO_INCREMENT,
    detail_name VARCHAR(255) NOT NULL,
    detail_json LONGTEXT NOT NULL,               -- JSON structure for outcome templates
    is_draft INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Data Structure and Flow

### JSON Data Format
The `data_json` field in `sector_outcomes_data` stores outcome data in this format:

```json
{
    "columns": ["Column1", "Column2", "Column3"],
    "units": ["unit1", "unit2", "unit3"],
    "data": {
        "January": {
            "Column1": 100.50,
            "Column2": 200.75,
            "Column3": 300.25
        },
        "February": {
            "Column1": 110.50,
            "Column2": 220.75,
            "Column3": 330.25
        },
        // ... for all 12 months
    }
}
```

### Outcome Lifecycle States
1. **Draft** (`is_draft = 1`): Editable by agency users
2. **Submitted** (`is_draft = 0`): Read-only, submitted for review

## File Structure - Agency Side

### Core Agency Files
```
app/views/agency/outcomes/
├── create_outcome.php              # Create new outcomes
├── create_outcomes_detail.php      # Create outcome templates
├── edit_outcomes.php               # Edit draft outcomes
├── submit_outcomes.php             # Main outcomes management page
├── submit_draft_outcome.php        # Submit draft to finalize
└── view_outcome.php                # View submitted outcome details
```

### Library Functions
```
app/lib/agencies/
├── outcomes.php                    # Core agency outcome functions
├── core.php                       # Core agency functionality
└── index.php                      # Agency authentication functions
```

### Key Functions in `app/lib/agencies/outcomes.php`

#### `get_agency_sector_outcomes($sector_id)`
- **Purpose**: Retrieves submitted outcomes for a specific sector
- **Returns**: Array of outcome records with basic metadata
- **Usage**: Display submitted outcomes list

#### `get_draft_outcome($sector_id)`  
- **Purpose**: Retrieves draft outcomes for a specific sector
- **Returns**: Array of draft outcome records
- **Usage**: Show editable drafts to agency users

#### `get_agency_outcomes_statistics($sector_id, $period_id = null)`
- **Purpose**: Generate statistics for dashboard display
- **Returns**: 
  ```php
  [
      'total_outcomes' => int,
      'submitted_outcomes' => int, 
      'draft_outcomes' => int,
      'recent_outcomes' => array
  ]
  ```

## Agency User Workflow

### 1. Creating Outcomes
**File**: `create_outcome.php`
**Process**:
1. User enters table name and defines column structure
2. User enters monthly data for each column
3. Data is saved as JSON in `sector_outcomes_data` table
4. Initially saved as draft (`is_draft = 1`)

### 2. Managing Drafts
**File**: `submit_outcomes.php`
**Features**:
- Lists all submitted outcomes (read-only)
- Lists all draft outcomes (editable)
- Provides actions: Edit, Submit, Delete for drafts
- Shows current reporting period information

### 3. Editing Outcomes
**File**: `edit_outcomes.php`
**Process**:
1. Loads existing draft data from database
2. Populates editable form with current values
3. Allows modification of table name and data
4. Saves changes back to same record

### 4. Submitting Outcomes
**File**: `submit_draft_outcome.php`
**Process**:
1. Changes `is_draft` from 1 to 0
2. Records submission in audit log
3. Outcome becomes read-only
4. Creates history record

### 5. Viewing Submitted Outcomes
**File**: `view_outcome.php`
**Features**:
- Displays outcome data in table format
- Shows chart visualization of data
- Provides export options (CSV, chart image)
- Read-only view with creation/edit dates

## Key Business Rules

### Access Control
- Agency users can only access outcomes for their assigned sector (`$_SESSION['sector_id']`)
- Submitted outcomes are read-only
- Only draft outcomes can be edited or deleted

### Data Validation
- Table names must be unique within sector/period
- Monthly data must be numeric
- All 12 months are displayed (missing data shows as 0 or "—")

### Identification System
- `metric_id`: Business logic identifier (user-facing)
- `id`: Internal database record ID
- Agency side primarily uses `metric_id` for operations

## Integration Points

### With Admin Side
- Admin can view all outcomes across sectors
- Admin can unsubmit outcomes (change back to draft)
- Admin has additional management capabilities

### With Reporting System
- Submitted outcomes feed into report generation
- Data is aggregated across sectors for comprehensive reports
- Chart data comes from the JSON structure

### With Audit System
- All outcome operations are logged via `audit_logs` table
- History tracking via `outcome_history` table
- User actions are tracked for accountability

## Technical Notes

### Performance Considerations
- Indexes on `metric_id`, `sector_id`, and `is_draft` for efficient queries
- JSON data is stored as LONGTEXT for flexible structure
- Prepared statements used throughout for security

### Data Integrity
- Foreign key constraints to `users`, `sectors`, and `reporting_periods`
- Soft delete pattern used where applicable
- Transaction support for multi-step operations

### Future Enhancement Areas
- Consider JSON column type for better MySQL 8.0+ support
- Potential for outcome templates/categories
- Bulk import/export functionality
- Real-time collaboration features

## Status: ✅ COMPLETE
This analysis documents the current structure and functionality of outcomes on the agency side of the PCDS2030 Dashboard system.

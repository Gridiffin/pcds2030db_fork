# PCDS 2030 Dashboard - Program Flow Analysis

## System Overview

The PCDS 2030 Dashboard is a quarterly reporting system for Sarawak's forestry sector agencies. Here's how the program flow works and how it manages reporting periods and targets.

## Core Program Flow

### 1. Reporting Period Management

#### Period Types Supported
- **Quarterly Periods**: Q1 (Jan-Mar), Q2 (Apr-Jun), Q3 (Jul-Sep), Q4 (Oct-Dec)
- **Half-Yearly Periods**: H1 (Jan-Jun), H2 (Jul-Dec)

#### How the System Knows Which Period It's In

The system uses an **automated period management system** that:

1. **Auto-creates periods** based on the current calendar year
2. **Auto-determines the current active period** based on the current date
3. **Automatically opens/closes periods** based on calendar timing
4. **Respects admin manual overrides** when administrators manually open specific periods

**Key Function: `get_current_reporting_period()`**
```php
// Priority order for determining current period:
1. Open quarterly period matching current calendar quarter
2. Open half-yearly period matching current half of year  
3. Any other manually opened period by admin
4. Next upcoming period if none are open
5. Most recent period as fallback
```

**Period Status Logic:**
- Only **ONE period can be "open"** at a time for submissions
- Opening a new period automatically closes all others
- The system auto-opens the period that matches the current calendar quarter/half-year
- Admins can manually override by opening any period

### 2. Program and Target Structure

#### Programs
- **Programs** are the main entities that agencies track
- Each program belongs to one **agency** and one **sector** (currently only Forestry)
- Programs have **start_date** and **end_date** for their overall timeline
- Programs are **ongoing initiatives** that get reported on quarterly

#### Program Submissions (The Key to Understanding Targets)
- For each **open reporting period**, agencies can submit progress updates for their programs
- These submissions are stored in `program_submissions` table
- Each submission contains **JSON data** with flexible content including:
  - **Targets for that specific period**
  - **Achievements for that period** 
  - **Status ratings** (on-track, delayed, completed, not-started, target-achieved)
  - **Brief descriptions and remarks**

**Critical Point**: Targets are **period-specific**, not program-wide. Each quarter, agencies set new targets and report achievements for that specific period.

### 3. Target Management Flow

#### How Targets Work
```json
// Example program submission content_json structure:
{
  "rating": "on-track",
  "brief_description": "Forest Conservation Initiative progress",
  "targets": [
    {
      "target_text": "Plant 1,000 trees in designated areas",
      "status_description": "650 trees planted, on track for completion"
    },
    {
      "target_text": "Complete environmental impact assessment", 
      "status_description": "Assessment 80% complete, expected completion next month"
    }
  ]
}
```

#### Target Lifecycle
1. **Period Opens**: Admin opens a reporting period (e.g., Q2 2025)
2. **Agencies Set Targets**: For each of their programs, agencies create submissions with:
   - Specific targets for this quarter
   - Current achievements toward those targets
   - Overall program status rating
3. **Period Stays Open**: Agencies can update their submissions (draft/final)
4. **Period Closes**: No more submissions allowed, data is locked for reporting
5. **Historical Data**: All previous period data remains accessible for trend analysis

### 4. Data Flow Architecture

```
Reporting Period (Q2 2025) - STATUS: OPEN
├── Program A (Forest Conservation)
│   └── Submission for Q2 2025
│       ├── Target 1: Plant 1000 trees
│       ├── Target 2: Complete assessment
│       └── Status: on-track
├── Program B (Timber Management)
│   └── Submission for Q2 2025  
│       ├── Target 1: Process 500 permits
│       └── Status: delayed
└── Program C (Wildlife Protection)
    └── Submission for Q2 2025
        ├── Target 1: Install 20 cameras
        └── Status: target-achieved
```

### 5. Key System Behaviors

#### Period Selection Throughout the System
1. **Dashboard Views**: Show data for the current open period by default
2. **Agency Interfaces**: Allow filtering by different periods to view historical data
3. **Admin Reports**: Can generate reports for any period
4. **Program Lists**: Filter programs by which periods they have submissions for

#### Draft vs Final Submissions
- Agencies can save **draft submissions** and continue editing
- **Final submissions** are locked and used for official reporting
- The `is_draft` flag controls this behavior

#### Cross-Period Program Tracking
- Programs are **continuous entities** that span multiple periods
- Each period captures a "snapshot" of progress through submissions
- Historical tracking shows how programs evolve over time
- Programs can be created in one period and continue reporting in subsequent periods

### 6. Database Relationships

```
reporting_periods (period_id, year, quarter, status, start_date, end_date)
    ↓
program_submissions (submission_id, program_id, period_id, content_json)
    ↓
programs (program_id, program_name, owner_agency_id, start_date, end_date)
```

**Key Relationship**: The `program_submissions.period_id` foreign key links each set of targets/achievements to a specific reporting period.

### 7. Admin vs Agency Perspectives

#### Admin Users Can:
- Create and manage reporting periods
- Open/close periods for submissions
- View all agency data across all periods
- Generate cross-agency reports
- Manage users and system settings

#### Agency Users Can:
- Submit program updates during open periods
- View their own programs across all periods  
- See other agencies' programs (read-only)
- Edit their draft submissions until finalized

### 8. Current Implementation Status

**Active Scope**: Currently focused only on **Forestry sector** with three agencies:
- Forestry Department
- Sarawak Forestry Corporation (SFC)  
- Sarawak Timber Industry Development Corporation (STIDC)

**Multi-Sector Ready**: The system architecture supports multiple sectors but this is currently disabled via configuration flags.

## Summary

The system operates on a **period-centric model** where:
- **Time is organized by reporting periods** (quarters/half-years)
- **Programs are ongoing initiatives** that get reported on each period
- **Targets are period-specific** - each quarter agencies set new targets for their programs
- **The current period is automatically determined** by calendar date but can be overridden by admins
- **Historical data is preserved** allowing for trend analysis and progress tracking over time

This design allows for flexible quarterly reporting while maintaining historical continuity of program progress.

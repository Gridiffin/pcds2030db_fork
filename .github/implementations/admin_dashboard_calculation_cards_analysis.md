# Admin Dashboard Calculation Cards Analysis

## Overview
This document analyzes all the calculation cards found in the admin dashboard that involve calculations related to program target achievements and various statistics.

## Calculation Cards Found

### 1. Agencies Reporting Card
**Location**: `app/views/admin/dashboard/dashboard.php` (lines 151-167)
**Calculation**: 
- `agencies_reported / total_agencies` ratio
- Displays as: `X/Y` format (e.g., "5/10")
- Subtitle shows count of agencies reported

### 2. Programs On Track Card
**Location**: `app/views/admin/dashboard/dashboard.php` (lines 169-187)
**Calculation**:
- Shows count of programs on track
- Calculates percentage: `(on_track_programs / total_programs) * 100`
- Displays percentage in subtitle

### 3. Programs Delayed Card
**Location**: `app/views/admin/dashboard/dashboard.php` (lines 189-207)
**Calculation**:
- Shows count of delayed programs
- Calculates percentage: `(delayed_programs / total_programs) * 100`
- Displays percentage in subtitle

### 4. Overall Completion Card
**Location**: `app/views/admin/dashboard/dashboard.php` (lines 209-227)
**Calculation**:
- Shows completion percentage
- Formula: `(agencies_reported / total_agencies) * 100`
- Includes progress bar visualization

### 5. Sector Overview Table
**Location**: `app/views/admin/dashboard/dashboard.php` (lines 492-510)
**Calculation**:
- Submission percentage per sector: `submission_pct`
- Progress bar shows completion percentage
- Status badges based on percentage ranges:
  - 100%: "Complete"
  - 75-99%: "Almost Complete" 
  - 25-74%: "In Progress"
  - 0-24%: "Early Stage"

### 6. Outcomes Statistics Cards
**Location**: `app/views/admin/dashboard/dashboard.php` (lines 371-400)
**Calculations**:
- Total Outcomes count
- Submitted Outcomes count
- Draft Outcomes count
- Sectors with Outcomes count

### 7. Forestry Sector Specific Cards (if enabled)
**Location**: `app/views/admin/dashboard/dashboard.php` (lines 547-575)
**Calculations**:
- Agency count in forestry sector
- Program count in forestry sector
- Submission progress percentage with progress bar
- Status badges based on completion percentage

## Backend Calculation Functions

### 1. get_period_submission_stats()
**Location**: `app/lib/admins/statistics.php` (lines 132-186)
**Calculations**:
- `agencies_reported` - Count of unique agencies that submitted
- `total_agencies` - Count of active agencies
- `on_track_programs` - Count of finalized submissions
- `delayed_programs` - Count of draft submissions
- `total_programs` - Total program count
- `completion_percentage` - `(agencies_reported / total_agencies) * 100`

### 2. get_sector_data_for_period()
**Calculations**:
- Per-sector submission percentages
- Agency counts per sector
- Program counts per sector

### 3. get_outcomes_statistics()
**Calculations**:
- Total outcomes count
- Submitted vs draft outcomes
- Sectors with outcomes

## AJAX Dynamic Updates

### admin_dashboard_data.php
**Location**: `app/ajax/admin_dashboard_data.php`
**Dynamic Calculations**:
- Real-time percentage calculations for stat cards
- Progress bar width calculations
- Status badge determinations based on percentages

## Total Count of Calculation Cards

Based on the analysis, I found **7 main calculation card types** in the admin dashboard:

1. **Agencies Reporting** - Ratio calculation (X/Y format)
2. **Programs On Track** - Count + percentage calculation
3. **Programs Delayed** - Count + percentage calculation  
4. **Overall Completion** - Percentage calculation with progress bar
5. **Sector Overview** - Multiple percentage calculations per sector
6. **Outcomes Statistics** - Multiple count calculations (4 sub-cards)
7. **Forestry Sector Cards** - Sector-specific calculations (if enabled)

## Key Calculation Patterns

### Percentage Calculations
- Program achievement percentages
- Completion percentages
- Sector-wise submission percentages

### Ratio Calculations
- Agencies reporting ratios
- Program status distributions

### Progress Indicators
- Progress bars for visual percentage representation
- Status badges based on percentage thresholds

### Real-time Updates
- AJAX-based dynamic updates for period changes
- Responsive calculations based on selected reporting period

## Notes
- All calculations are period-specific (filtered by reporting period)
- Calculations exclude draft programs from statistics
- Percentage calculations are rounded to whole numbers
- Progress bars provide visual representation of completion rates
- Status badges use predefined percentage thresholds for categorization

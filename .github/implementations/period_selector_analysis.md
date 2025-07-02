# Period Selector Analysis

## Overview
The period selector is a reusable component used across multiple pages in the PCDS2030 Dashboard to allow users to filter data by different reporting periods.

## Current Implementation

### 1. Period Selector Components (PHP)
There are three main period selector PHP files in `/app/lib/`:

#### a) `period_selector_dashboard.php`
- **Used by**: Admin Dashboard, Agency Dashboard, Programs pages
- **Features**: Groups periods by half-year (H1: Q1+Q2, H2: Q3+Q4)
- **Display**: Shows combined periods like "2024 - Half Year 1 (Jan 1 - Jun 30, 2024)"
- **Value**: Comma-separated period IDs for multi-period selection

#### b) `period_selector.php`
- **Used by**: General reporting/viewing pages
- **Features**: Simple quarter-by-quarter selection
- **Display**: Shows individual quarters like "2024 Q1"
- **Value**: Single period ID

#### c) `period_selector_edit.php`
- **Used by**: Program editing pages
- **Features**: Similar to basic selector but includes program data fetching
- **Special**: Fetches program data via AJAX when period changes
- **ID**: Uses `periodSelectorEdit` instead of `periodSelector`

### 2. JavaScript Handler
**File**: `/assets/js/period_selector.js`

#### Key Functions:
- `initPeriodSelector()` - Initializes the dropdown event handlers
- `updatePageContent(periodId)` - Makes AJAX calls to update page content
- Handles browser history (back/forward navigation)
- Shows loading indicators during updates

#### AJAX Endpoints Used:
- `/app/ajax/admin_dashboard_data.php` - Admin dashboard data
- `/app/ajax/agency_dashboard_data.php` - Agency dashboard data  
- `/app/ajax/admin_programs_data.php` - Admin programs data
- `/app/ajax/agency_programs_data.php` - Agency programs data
- `/app/ajax/admin_reports_data.php` - Admin reports data
- `/app/ajax/agency_reports_data.php` - Agency reports data

### 3. Page Integration

#### Pages Using Period Selectors:
- **Admin Dashboard** (`/admin/dashboard/dashboard.php`) - Uses `period_selector_dashboard.php`
- **Agency Dashboard** (`/agency/dashboard/dashboard.php`) - Uses `period_selector_dashboard.php`
- **Admin Programs** (`/admin/programs/programs.php`) - Uses `period_selector_dashboard.php`
- **Agency Sectors** (`/agency/sectors/view_all_sectors.php`) - Uses `period_selector_dashboard.php`
- **Program Editing** (`/agency/programs/update_program.php`) - Uses `period_selector_edit.php`

### 4. Common Features
- All period selectors show period status (Open/Closed)
- Period indicator icons change based on status
- Responsive design with Bootstrap styling
- Loading spinners during AJAX operations

## Key Files for Modification

### To Change Period Selector Logic:
1. **Core Logic**: `/app/lib/period_selector_dashboard.php` (most commonly used)
2. **JavaScript Handler**: `/assets/js/period_selector.js`
3. **AJAX Data Providers**: `/app/ajax/*_data.php` files

### To Change Period Selector UI:
1. **HTML Structure**: The three period selector PHP files in `/app/lib/`
2. **Styling**: Would be in main CSS files (referenced through headers)

## Centralized Logic
✅ **YES** - The period selector uses centralized logic:
- Same JavaScript file (`period_selector.js`) handles all period selectors
- Common PHP structure across all selector variants
- Shared AJAX endpoints for data fetching
- Consistent styling and behavior

## Modification Strategy
If you need to change period selector behavior:
1. **Frontend changes**: Modify `/assets/js/period_selector.js`
2. **Backend logic**: Modify the appropriate `/app/lib/period_selector_*.php` file
3. **Data fetching**: Modify the relevant `/app/ajax/*_data.php` files
4. **Styling**: Update CSS files referenced in `/layouts/headers.php`

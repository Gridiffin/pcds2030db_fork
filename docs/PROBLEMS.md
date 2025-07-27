 ⎿ Admin CSS Modularization Plan

    Problem Analysis

    Currently, all admin modules are importing main.css (352.82 kB bundle), causing performance issues compared to the agency side which has
     dedicated bundles per page. The admin side needs complete CSS architecture restructuring.

    Solution: Three-Tier Modular Architecture

    1. Shared Base Layer (assets/css/admin/shared/base.css)

    Create a dedicated admin-only base CSS that includes:
    - Design Tokens: CSS variables, colors, typography
    - Reset/Base Styles: Normalize, base element styling  
    - Layout Components: Grid, navigation, headers, footers
    - Core Components: Buttons, forms, tables, cards, modals
    - Admin-specific Components: admin-common.css, shared-modals.css

    Estimated size: ~50-80kB (vs 352kB main.css)

    2. Page-Specific CSS Files

    Create dedicated CSS entry points for each admin module:
    - assets/css/admin/dashboard/dashboard.css
    - assets/css/admin/programs/programs.css 
    - assets/css/admin/users/users.css
    - assets/css/admin/reports/reports.css
    - assets/css/admin/settings/settings.css
    - assets/css/admin/outcomes/outcomes.css
    - assets/css/admin/periods/periods.css

    Each file imports base + page-specific components only.

    3. Component-Specific CSS (Granular Level)

    Move specialized components to dedicated locations:
    - assets/css/admin/programs/tables.css (program-specific table styles)
    - assets/css/admin/users/forms.css (user management forms)
    - assets/css/admin/reports/pagination.css (report-specific pagination)
    - assets/css/admin/dashboard/cards.css (dashboard-specific cards)

    Implementation Plan

    Phase 1: Base Layer Creation

    1. Create assets/css/admin/shared/base.css
      - Extract shared components from main.css
      - Include: variables, reset, layout, core components
      - Add admin-specific: admin-common.css, shared-modals.css
    2. Identify Shared vs Specific Components
      - Audit all admin JS files to see what CSS they import
      - Categorize components as "shared" vs "page-specific"

    Phase 2: Page-Specific CSS Creation

    Create dedicated CSS files for each admin module:

    1. Dashboard (assets/css/admin/dashboard/dashboard.css)
      - Base + dashboard cards + quick actions + stats overview
    2. Programs (assets/css/admin/programs/programs.css)  
      - Base + program tables + bulk assignment + program-specific modals
    3. Users (assets/css/admin/users/users.css)
      - Base + user tables + user forms + role management
    4. Reports (assets/css/admin/reports/reports.css)
      - Base + report generation + pagination + report tables
    5. Settings (assets/css/admin/settings/settings.css)
      - Base + system settings + audit logs + period management
    6. Outcomes (assets/css/admin/outcomes/outcomes.css)
      - Base + outcome tables + KPI management
    7. Periods (assets/css/admin/periods/periods.css)
      - Base + period management + reporting periods

    Phase 3: Component Granularization

    Move page-specific components to deeper structure:
    - Program tables → assets/css/admin/programs/components/tables.css
    - User forms → assets/css/admin/users/components/forms.css
    - Report pagination → assets/css/admin/reports/components/pagination.css

    Phase 4: JavaScript Updates

    Update all admin JS files to import their specific CSS instead of main.css:

    Before:
    import '../../css/main.css';
    import '../../css/components/admin-common.css';
    import '../../css/pages/admin.css';

    After:
    import '../../css/admin/programs/programs.css'; // Contains base + page-specific

    Phase 5: Build Configuration

    Update vite.config.js to add missing admin modules:
    - admin-dashboard
    - admin-users 
    - admin-settings
    - admin-outcomes
    - admin-periods

    Expected Results

    Performance Improvements

    - Dashboard: ~60-80kB (vs 352kB) - 75% reduction
    - Programs: ~80-120kB (vs 352kB) - 65% reduction  
    - Users: ~60-90kB (vs 352kB) - 70% reduction
    - Reports: ~70-100kB (vs 352kB) - 70% reduction

    Bundle Structure (After)

    dist/css/
    ├── admin-dashboard.bundle.css    (~70kB)
    ├── admin-programs.bundle.css     (~100kB)  
    ├── admin-users.bundle.css        (~80kB)
    ├── admin-reports.bundle.css      (~90kB)
    ├── admin-settings.bundle.css     (~60kB)
    ├── admin-outcomes.bundle.css     (~70kB)
    └── admin-periods.bundle.css      (~60kB)

    File Structure (Final)

    assets/css/admin/
    ├── shared/
    │   └── base.css                 (Core admin foundation)
    ├── dashboard/
    │   ├── dashboard.css           (Entry point)
    │   └── components/
    │       ├── cards.css
    │       └── stats.css
    ├── programs/
    │   ├── programs.css            (Entry point) 
    │   └── components/
    │       ├── tables.css
    │       └── bulk-assignment.css
    ├── users/
    │   ├── users.css               (Entry point)
    │   └── components/
    │       ├── forms.css
    │       └── tables.css
    └── [other modules...]

    Implementation Order

    1. Create shared base CSS
    2. Create page-specific CSS entry points  
    3. Update JavaScript imports
    4. Update build configuration
    5. Test and validate bundle sizes
    6. Refactor to component granularization
    7. Final optimization and cleanup

    This architecture will match the agency-side approach while providing significant performance improvements and better maintainability.
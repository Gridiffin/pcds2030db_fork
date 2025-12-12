# PCDS 2030 Dashboard - Feature-Based Structure Recommendations

## Overview

This document outlines the recommended feature-based structure for the PCDS 2030 Dashboard, transitioning from the current file-type-based organization to a feature-centric architecture. The new structure will improve maintainability, reduce coupling, and enhance developer experience while preserving all existing functionality.

## Current Structure Analysis

### Current File-Type-Based Organization
```
app/
├── ajax/           # All AJAX endpoints mixed together
├── api/            # All API endpoints mixed together
├── controllers/    # All controllers mixed together
├── lib/            # All business logic mixed together
├── views/          # All views with admin/agency separation
│   ├── admin/
│   └── agency/
└── helpers/        # All utilities mixed together

assets/
├── css/            # All styles with some admin/agency separation
│   ├── admin/
│   ├── agency/
│   └── components/
└── js/             # All JavaScript with some admin/agency separation
    ├── admin/
    └── agency/
```

### Problems with Current Structure
1. **Feature Coupling**: Related functionality scattered across multiple directories
2. **Code Discovery**: Difficult to find all files related to a specific feature
3. **Maintenance Burden**: Changes require touching multiple distant directories
4. **Shared Logic**: Unclear separation of shared vs feature-specific code
5. **Testing Complexity**: No clear boundaries for feature testing

## Recommended Feature-Based Structure

### Core Principles
1. **Feature Cohesion**: Group all related files (PHP, CSS, JS) by feature
2. **Clear Boundaries**: Separate agency and admin interfaces logically
3. **Shared Components**: Extract truly reusable code into shared modules
4. **Consistent Patterns**: Use the same structure across all features
5. **Migration Friendly**: Structure allows gradual migration

### Proposed Directory Structure

```
features/
├── dashboard/
│   ├── admin/
│   │   ├── controllers/
│   │   │   └── AdminDashboardController.php
│   │   ├── views/
│   │   │   ├── dashboard.php
│   │   │   └── partials/
│   │   │       ├── stats-overview.php
│   │   │       ├── recent-reports.php
│   │   │       └── programs-overview.php
│   │   ├── ajax/
│   │   │   ├── dashboard-data.php
│   │   │   └── stats-modal.php
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   ├── admin-dashboard.css
│   │   │   │   └── admin-dashboard-modern.css
│   │   │   └── js/
│   │   │       ├── admin-dashboard.js
│   │   │       ├── admin-dashboard-modern.js
│   │   │       └── dashboard-charts.js
│   │   └── lib/
│   │       ├── AdminDashboardStats.php
│   │       └── AdminReports.php
│   └── agency/
│       ├── controllers/
│       │   └── AgencyDashboardController.php
│       ├── views/
│       │   ├── dashboard.php
│       │   └── partials/
│       │       ├── bento-grid.php
│       │       ├── charts-section.php
│       │       └── programs-section.php
│       ├── ajax/
│       │   ├── dashboard-data.php
│       │   └── chart-data.php
│       ├── assets/
│       │   ├── css/
│       │   │   ├── dashboard.css
│       │   │   ├── bento-grid.css
│       │   │   └── charts.css
│       │   └── js/
│       │       ├── dashboard.js
│       │       ├── chart.js
│       │       └── logic.js
│       └── lib/
│           ├── AgencyDashboardStats.php
│           └── AgencyMetrics.php
├── programs/
│   ├── shared/
│   │   ├── lib/
│   │   │   ├── Program.php
│   │   │   ├── ProgramValidation.php
│   │   │   └── ProgramNumbering.php
│   │   ├── views/
│   │   │   └── components/
│   │   │       ├── program-card.php
│   │   │       ├── program-status-badge.php
│   │   │       └── program-rating-display.php
│   │   ├── api/
│   │   │   ├── programs.php
│   │   │   └── program-status.php
│   │   └── assets/
│   │       ├── css/
│   │       │   ├── program-shared.css
│   │       │   └── program-cards.css
│   │       └── js/
│   │           ├── program-utils.js
│   │           └── program-validation.js
│   ├── admin/
│   │   ├── controllers/
│   │   │   └── AdminProgramsController.php
│   │   ├── views/
│   │   │   ├── manage-programs.php
│   │   │   ├── edit-program.php
│   │   │   ├── view-program.php
│   │   │   └── partials/
│   │   │       ├── program-form.php
│   │   │       ├── programs-table.php
│   │   │       └── program-details.php
│   │   ├── ajax/
│   │   │   ├── save-program.php
│   │   │   ├── delete-program.php
│   │   │   └── get-programs-list.php
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   ├── admin-programs.css
│   │   │   │   └── admin-program-details.css
│   │   │   └── js/
│   │   │       ├── programs-management.js
│   │   │       ├── programs-list.js
│   │   │       └── bulk-operations.js
│   │   └── lib/
│   │       ├── AdminProgramsModel.php
│   │       └── ProgramManagement.php
│   └── agency/
│       ├── controllers/
│       │   └── AgencyProgramsController.php
│       ├── views/
│       │   ├── view-programs.php
│       │   ├── create-program.php
│       │   ├── edit-program.php
│       │   └── partials/
│       │       ├── programs-grid.php
│       │       ├── program-form.php
│       │       └── filters.php
│       ├── ajax/
│       │   ├── submit-program.php
│       │   ├── check-program-number.php
│       │   └── program-attachments.php
│       ├── assets/
│       │   ├── css/
│       │   │   ├── view-programs.css
│       │   │   ├── create-program.css
│       │   │   └── program-form.css
│       │   └── js/
│       │       ├── view-programs/
│       │       │   ├── dom.js
│       │       │   ├── logic.js
│       │       │   └── filters.js
│       │       ├── create-program.js
│       │       └── edit-program-logic.js
│       └── lib/
│           ├── AgencyPrograms.php
│           ├── ProgramDetailsData.php
│           └── ProgramPermissions.php
├── submissions/
│   ├── shared/
│   │   ├── lib/
│   │   │   ├── Submission.php
│   │   │   ├── SubmissionValidation.php
│   │   │   └── SubmissionWorkflow.php
│   │   ├── api/
│   │   │   ├── get-submission.php
│   │   │   └── submit-submission.php
│   │   └── assets/
│   │       ├── css/
│   │       │   └── submission-shared.css
│   │       └── js/
│   │           └── submission-utils.js
│   ├── admin/
│   │   ├── views/
│   │   │   ├── view-submissions.php
│   │   │   ├── edit-submission.php
│   │   │   └── partials/
│   │   │       ├── submissions-table.php
│   │   │       └── submission-details.php
│   │   ├── ajax/
│   │   │   ├── save-submission.php
│   │   │   └── unsubmit-submission.php
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   └── admin-submissions.css
│   │   │   └── js/
│   │   │       └── admin-submissions.js
│   │   └── lib/
│   │       └── AdminSubmissionData.php
│   └── agency/
│       ├── views/
│       │   ├── add-submission.php
│       │   ├── edit-submission.php
│       │   └── partials/
│       │       ├── submission-form.php
│       │       └── targets-table.php
│       ├── ajax/
│       │   ├── save-submission.php
│       │   ├── finalize-submission.php
│       │   └── simple-finalize.php
│       ├── assets/
│       │   ├── css/
│       │   │   ├── add-submission.css
│       │   │   └── edit-submission.css
│       │   └── js/
│       │       ├── edit-submission.js
│       │       ├── simple-finalize.js
│       │       └── submission-audit-history.js
│       └── lib/
│           └── SubmissionData.php
├── initiatives/
│   ├── shared/
│   │   ├── lib/
│   │   │   └── Initiative.php
│   │   ├── api/
│   │   │   └── initiatives.php
│   │   └── assets/
│   │       └── css/
│   │           └── initiatives-shared.css
│   ├── admin/
│   │   ├── views/
│   │   │   ├── manage-initiatives.php
│   │   │   ├── create-initiative.php
│   │   │   ├── edit-initiative.php
│   │   │   └── view-initiative.php
│   │   ├── ajax/
│   │   │   └── manage-initiatives.php
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   ├── manage-initiatives.css
│   │   │   │   ├── create-initiative.css
│   │   │   │   ├── edit-initiative.css
│   │   │   │   └── view-initiative.css
│   │   │   └── js/
│   │   │       ├── manage-initiatives.js
│   │   │       ├── create-initiative.js
│   │   │       ├── edit-initiative.js
│   │   │       └── view-initiative.js
│   │   └── lib/
│   │       └── AdminInitiatives.php
│   └── agency/
│       ├── views/
│       │   ├── initiatives.php
│       │   └── view-initiative.php
│       ├── assets/
│       │   ├── css/
│       │   │   ├── initiatives.css
│       │   │   ├── listing.css
│       │   │   └── view.css
│       │   └── js/
│       │       ├── initiatives.js
│       │       ├── listing.js
│       │       ├── logic.js
│       │       └── view.js
│       └── lib/
│           └── AgencyInitiatives.php
├── outcomes/
│   ├── shared/
│   │   ├── lib/
│   │   │   ├── Outcome.php
│   │   │   └── OutcomeAutomation.php
│   │   ├── api/
│   │   │   ├── get-outcomes.php
│   │   │   └── save-outcome.php
│   │   └── assets/
│   │       ├── css/
│   │       │   └── outcomes-shared.css
│   │       └── js/
│   │           └── outcome-utils.js
│   ├── admin/
│   │   ├── views/
│   │   │   ├── manage-outcomes.php
│   │   │   ├── edit-outcome.php
│   │   │   ├── edit-kpi.php
│   │   │   └── view-outcome.php
│   │   ├── ajax/
│   │   │   └── admin-outcomes.php
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   ├── manage-outcomes.css
│   │   │   │   └── edit-kpi.css
│   │   │   └── js/
│   │   │       ├── manage-outcomes.js
│   │   │       ├── edit-kpi.js
│   │   │       └── edit-kpi-logic.js
│   │   └── lib/
│   │       └── AdminOutcomes.php
│   └── agency/
│       ├── views/
│       │   ├── edit-outcome.php
│       │   ├── edit-kpi.php
│       │   ├── submit-outcomes.php
│       │   └── view-outcome.php
│       ├── ajax/
│       │   └── submit-outcome.php
│       ├── assets/
│       │   ├── css/
│       │   │   ├── edit.css
│       │   │   ├── submit.css
│       │   │   ├── view.css
│       │   │   ├── charts.css
│       │   │   └── tables.css
│       │   └── js/
│       │       ├── edit.js
│       │       ├── submit.js
│       │       ├── view.js
│       │       └── chart-manager.js
│       └── lib/
│           └── AgencyOutcomes.php
├── reports/
│   ├── shared/
│   │   ├── lib/
│   │   │   ├── ReportGenerator.php
│   │   │   └── ReportService.php
│   │   ├── api/
│   │   │   ├── generate-report.php
│   │   │   ├── save-report.php
│   │   │   └── report-data.php
│   │   └── assets/
│   │       ├── css/
│   │       │   └── reports-shared.css
│   │       └── js/
│   │           ├── report-generator.js
│   │           └── report-modules/
│   │               ├── report-api.js
│   │               ├── report-slide-populator.js
│   │               ├── report-slide-styler.js
│   │               └── report-ui.js
│   ├── admin/
│   │   ├── views/
│   │   │   └── generate-reports.php
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   └── reports.css
│   │   │   └── js/
│   │   │       └── reports.js
│   │   └── lib/
│   │       └── AdminReports.php
│   └── agency/
│       ├── views/
│       │   ├── public-reports.php
│       │   └── view-reports.php
│       ├── assets/
│       │   ├── css/
│       │   │   └── reports.css
│       │   └── js/
│       │       ├── public-reports.js
│       │       ├── view-reports.js
│       │       ├── ajax.js
│       │       └── logic.js
│       └── lib/
│           └── AgencyReports.php
├── users/
│   ├── shared/
│   │   ├── lib/
│   │   │   ├── UserModel.php
│   │   │   ├── UserFunctions.php
│   │   │   └── NotificationsCore.php
│   │   ├── api/
│   │   │   └── user-assignments.php
│   │   └── assets/
│   │       └── css/
│   │           └── users-shared.css
│   ├── admin/
│   │   ├── views/
│   │   │   ├── manage-users.php
│   │   │   ├── add-user.php
│   │   │   └── edit-user.php
│   │   ├── ajax/
│   │   │   ├── admin-user-tables.php
│   │   │   ├── get-user.php
│   │   │   └── process-user.php
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   └── users.css
│   │   │   └── js/
│   │   │       ├── manage-users.js
│   │   │       ├── simple-users.js
│   │   │       ├── user-form-manager.js
│   │   │       ├── user-table-manager.js
│   │   │       └── users.js
│   │   └── lib/
│   │       └── AdminUsers.php
│   └── agency/
│       ├── views/
│       │   ├── profile.php
│       │   ├── all-notifications.php
│       │   └── all-notifications-simple.php
│       ├── ajax/
│       │   ├── notifications.php
│       │   └── get-user-notifications.php
│       ├── assets/
│       │   ├── css/
│       │   │   ├── profile.css
│       │   │   └── notifications.css
│       │   └── js/
│       │       ├── profile.js
│       │       ├── notifications.js
│       │       ├── ajax.js
│       │       ├── interactions.js
│       │       └── logic.js
│       └── lib/
│           └── AgencyNotifications.php
└── shared/
    ├── components/
    │   ├── layouts/
    │   │   ├── base.php
    │   │   ├── navbar-modern.php
    │   │   ├── footer-modern.php
    │   │   └── page-header.php
    │   ├── forms/
    │   │   ├── input.php
    │   │   ├── select.php
    │   │   └── textarea.php
    │   ├── modals/
    │   │   ├── confirm-modal.php
    │   │   └── info-modal.php
    │   └── ui/
    │       ├── buttons.php
    │       ├── cards.php
    │       └── tables.php
    ├── controllers/
    │   ├── AuthController.php
    │   └── BaseController.php
    ├── lib/
    │   ├── database/
    │   │   ├── db-connect.php
    │   │   └── Database.php
    │   ├── auth/
    │   │   ├── session.php
    │   │   └── AuthService.php
    │   ├── utilities/
    │   │   ├── functions.php
    │   │   ├── utilities.php
    │   │   └── validation.php
    │   ├── audit/
    │   │   └── audit-log.php
    │   └── notifications/
    │       ├── email-notifications.php
    │       └── email-templates.php
    ├── api/
    │   ├── command-palette-data.php
    │   ├── login.php
    │   └── numbering.php
    ├── ajax/
    │   ├── get-reporting-periods.php
    │   ├── clear-session-message.php
    │   └── notifications.php
    ├── assets/
    │   ├── css/
    │   │   ├── base.css
    │   │   ├── variables.css
    │   │   ├── components/
    │   │   │   ├── buttons-modern.css
    │   │   │   ├── cards-modern.css
    │   │   │   ├── forms-modern.css
    │   │   │   ├── modals-modern.css
    │   │   │   ├── tables-modern.css
    │   │   │   ├── navbar-modern.css
    │   │   │   ├── footer-modern.css
    │   │   │   ├── page-header.css
    │   │   │   ├── command-palette.css
    │   │   │   ├── alerts-modern.css
    │   │   │   ├── notifications.css
    │   │   │   └── toast.css
    │   │   └── shared/
    │   │       └── login.css
    │   ├── js/
    │   │   ├── shared/
    │   │   │   ├── base-module.js
    │   │   │   ├── utils.js
    │   │   │   ├── form-validation.js
    │   │   │   ├── ui-helpers.js
    │   │   │   └── login-logic.js
    │   │   ├── components/
    │   │   │   ├── command-palette.js
    │   │   │   ├── notification-system.js
    │   │   │   ├── shared-modals.js
    │   │   │   └── responsive-navbar.js
    │   │   ├── utilities/
    │   │   │   ├── ajax-helpers.js
    │   │   │   ├── form-utils.js
    │   │   │   ├── form-validation.js
    │   │   │   ├── pagination.js
    │   │   │   ├── filter-utils.js
    │   │   │   ├── rating-utils.js
    │   │   │   └── responsive-tables.js
    │   │   └── charts/
    │   │       ├── chart-generator.js
    │   │       └── outcomes-chart.js
    │   └── images/
    │       ├── logo.png
    │       ├── sarawak-crest.png
    │       └── forest-icon.png
    └── config/
        ├── app.php
        ├── database.php
        └── routes.php
```

## Migration Strategy

### Phase 1: Setup Foundation
1. Create the new feature-based directory structure
2. Move shared components and utilities first
3. Update base.css to reference new shared CSS locations
4. Test shared functionality

### Phase 2: Migrate Core Features
1. Start with dashboard (least dependencies)
2. Move to programs (core functionality)
3. Migrate submissions (program dependency)
4. Validate inter-feature communication

### Phase 3: Migrate Supporting Features
1. Initiatives (program dependency)
2. Outcomes (program dependency)
3. Reports (multiple dependencies)
4. Users (cross-cutting concern)

### Phase 4: Integration and Testing
1. Update all file references and includes
2. Test all workflows end-to-end
3. Performance validation
4. Documentation updates

## Benefits of Feature-Based Structure

### Developer Experience
- **Faster Development**: All related files in one location
- **Easier Debugging**: Clear feature boundaries
- **Reduced Cognitive Load**: Less file searching
- **Better Testing**: Feature-level test isolation

### Maintainability
- **Clear Ownership**: Each feature is self-contained
- **Reduced Coupling**: Dependencies are explicit
- **Easier Refactoring**: Changes confined to feature boundaries
- **Better Documentation**: Feature-level documentation possible

### Scalability
- **Team Scaling**: Multiple developers can work on different features
- **Feature Isolation**: Changes don't affect unrelated features
- **Code Reuse**: Shared components are clearly identified
- **Deployment Flexibility**: Feature-level deployment possible

## Implementation Guidelines

### File Naming Conventions
- Use kebab-case for directories: `user-management`
- Use PascalCase for PHP classes: `UserController.php`
- Use kebab-case for view files: `edit-program.php`
- Use kebab-case for CSS/JS files: `admin-dashboard.css`

### Include Path Management
- Use relative paths within features: `../shared/lib/Database.php`
- Define feature-level constants for cross-feature references
- Maintain backward compatibility during migration

### Asset Management
- Each feature manages its own CSS/JS assets
- Shared assets remain in shared/assets/
- Update base.css to import feature-specific styles
- Maintain asset versioning for cache busting

### Documentation Requirements
- Each feature should have a README.md
- Document dependencies between features
- Maintain API documentation for shared components
- Update deployment documentation

This feature-based structure will significantly improve the maintainability and scalability of the PCDS 2030 Dashboard while preserving all existing functionality and user experience.
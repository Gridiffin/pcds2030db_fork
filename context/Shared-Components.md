# PCDS 2030 Dashboard - Shared Components Documentation

## Overview

This document catalogs all shared components, utilities, and assets that are used across multiple features in the PCDS 2030 Dashboard. These components form the foundation of the system and should be extracted to the `shared/` directory during the restructuring process.

## Shared Component Categories

### 1. Layout Components

#### Base Layout System
- **File**: `app/views/layouts/base.php`
- **Purpose**: Main application layout with HTML structure, head section, and script loading
- **Dependencies**: All pages depend on this layout
- **Features**: Dynamic title, meta tags, CSS/JS loading, session management

#### Navigation Components
- **Files**: 
  - `app/views/layouts/navbar-modern.php` - Modern responsive navigation
  - `app/views/layouts/admin-navbar-modern.php` - Admin-specific navigation
- **Purpose**: Primary navigation with role-based menu items
- **Features**: Command palette integration, responsive design, role-based visibility

#### Footer Components
- **File**: `app/views/layouts/footer-modern.php`
- **Purpose**: Consistent footer across all pages
- **Features**: Copyright, links, responsive design

#### Page Header Component
- **File**: `app/views/layouts/page_header.php`
- **Purpose**: Consistent page headers with breadcrumbs and actions
- **Features**: Breadcrumb navigation, page titles, action buttons

### 2. Authentication & Session Management

#### Authentication Controller
- **File**: `app/controllers/AuthController.php`
- **Purpose**: Handles login, logout, and session validation
- **Features**: Role-based authentication, session security, redirect handling

#### Session Management
- **File**: `app/lib/session.php`
- **Purpose**: Core session handling and security
- **Features**: Session security, user role management, timeout handling

#### Login Components
- **Files**:
  - `app/views/shared/login/login_form.php` - Login form component
  - `app/views/shared/login/login_header.php` - Login page header
  - `app/views/shared/login/login_footer.php` - Login page footer
  - `app/views/shared/login/login_messages.php` - Error/success messages
- **Purpose**: Reusable login interface components

### 3. Database & Data Access

#### Database Connection
- **File**: `app/lib/db_connect.php`
- **Purpose**: Centralized database connection and configuration
- **Features**: PDO connection, error handling, configuration management

#### Database Utilities
- **File**: `app/config/database_schema.php`
- **Purpose**: Database schema definitions and utilities
- **Features**: Table definitions, relationship mappings

#### Database Names Helper
- **File**: `app/lib/db_names_helper.php`
- **Purpose**: Centralized database table name management
- **Features**: Table name constants, environment-specific naming

### 4. Core Utilities

#### General Functions
- **File**: `app/lib/functions.php`
- **Purpose**: Global utility functions used throughout the application
- **Features**: Date formatting, string manipulation, data sanitization

#### Utilities Library
- **File**: `app/lib/utilities.php`
- **Purpose**: Additional utility functions and helpers
- **Features**: Array manipulation, file handling, data validation

#### Asset Helpers
- **File**: `app/lib/asset_helpers.php`
- **Purpose**: Asset management and URL generation
- **Features**: Asset versioning, path generation, cache busting

### 5. Validation & Form Processing

#### AJAX Helpers
- **File**: `app/helpers/ajax_helpers.php`
- **Purpose**: Common AJAX request processing utilities
- **Features**: Response formatting, error handling, data validation

#### Form Validation (JavaScript)
- **Files**:
  - `assets/js/shared/formValidation.js` - Client-side validation
  - `assets/js/utilities/form_validation.js` - Form validation utilities
  - `assets/js/utilities/form_utils.js` - Form manipulation utilities
- **Purpose**: Consistent form validation across the application

### 6. Audit & Logging

#### Audit Log System
- **File**: `app/lib/audit_log.php`
- **Purpose**: Comprehensive audit trail functionality
- **Features**: Field-level change tracking, user attribution, timestamp management

#### User Functions
- **File**: `app/lib/user_functions.php`
- **Purpose**: User-related utility functions
- **Features**: User lookup, role checking, permission validation

### 7. Notification System

#### Core Notifications
- **File**: `app/lib/notifications_core.php`
- **Purpose**: Core notification functionality
- **Features**: Notification creation, delivery, management

#### Email System
- **Files**:
  - `app/lib/email_notifications.php` - Email notification handling
  - `app/lib/email_templates.php` - Email template management
- **Purpose**: Email notification system

#### Toast Notifications
- **File**: `app/views/layouts/main_toast.php`
- **Purpose**: In-app toast notification display

### 8. UI Components

#### Modern CSS Framework
- **Files**:
  - `assets/css/base.css` - Central CSS orchestrator
  - `assets/css/variables.css` - CSS custom properties and design tokens
  - `assets/css/components/buttons-modern.css` - Modern button system
  - `assets/css/components/cards-modern.css` - Card component library
  - `assets/css/components/forms-modern.css` - Modern form styling
  - `assets/css/components/modals-modern.css` - Modal components
  - `assets/css/components/tables-modern.css` - Table styling
  - `assets/css/components/alerts-modern.css` - Alert components
  - `assets/css/components/navbar-modern.css` - Navigation styling
  - `assets/css/components/footer-modern.css` - Footer styling
- **Purpose**: Consistent UI framework across the application

#### Component JavaScript
- **Files**:
  - `assets/js/components/command-palette.js` - Global search functionality
  - `assets/js/components/notification-system.js` - Notification handling
  - `assets/js/components/shared-modals.js` - Modal management
  - `assets/js/responsive-navbar.js` - Responsive navigation behavior
- **Purpose**: Interactive component functionality

### 9. Chart & Visualization

#### Chart Libraries
- **Files**:
  - `assets/js/charts/chart_generator.js` - Chart creation utilities
  - `assets/js/charts/outcomes-chart.js` - Outcome-specific charts
- **Purpose**: Data visualization across different modules

### 10. Utility JavaScript

#### Core Utilities
- **Files**:
  - `assets/js/shared/utils.js` - General JavaScript utilities
  - `assets/js/shared/base-module.js` - Base module functionality
  - `assets/js/shared/uiHelpers.js` - UI manipulation helpers
  - `assets/js/ajax-helpers.js` - AJAX request utilities
  - `assets/js/url_helpers.js` - URL manipulation utilities
- **Purpose**: Common JavaScript functionality

#### Form & Interaction Utilities
- **Files**:
  - `assets/js/utilities/pagination.js` - Pagination component
  - `assets/js/utilities/filter_utils.js` - Filtering functionality
  - `assets/js/utilities/rating_utils.js` - Rating display utilities
  - `assets/js/utilities/responsive_tables.js` - Table responsiveness
  - `assets/js/utilities/mobile_dropdown_position.js` - Mobile UX utilities
- **Purpose**: Enhanced user interaction functionality

### 11. Business Logic Helpers

#### Rating System
- **File**: `app/lib/rating_helpers.php`
- **Purpose**: Program rating calculation and display
- **Features**: Rating calculation, color coding, badge generation

#### Breadcrumb System
- **File**: `app/lib/breadcrumb_helpers.php`
- **Purpose**: Dynamic breadcrumb navigation
- **Features**: Path generation, context-aware breadcrumbs

#### Numbering System
- **File**: `app/lib/numbering_helpers.php`
- **Purpose**: Automatic numbering for programs and initiatives
- **Features**: Hierarchical numbering, uniqueness validation

#### Program Status Helpers
- **File**: `app/lib/program_status_helpers.php`
- **Purpose**: Program status management utilities
- **Features**: Status validation, transition rules, display formatting

### 12. Reporting & Data Export

#### Period Management
- **Files**:
  - `app/lib/period_selector.php` - Period selection component
  - `app/lib/period_selector_edit.php` - Period editing component
- **Purpose**: Reporting period management across modules

### 13. Configuration & Environment

#### Core Configuration
- **File**: `app/config/config.php`
- **Purpose**: Application configuration and environment detection
- **Features**: Environment-specific settings, feature flags, database configuration

### 14. Static Assets

#### Images
- **Files**:
  - `assets/images/logo.png` - Application logo
  - `assets/images/sarawak_crest.png` - Official Sarawak crest
  - `assets/images/forest-icon.png` - Forest theme icon
  - `assets/images/favicon.ico` - Favicon
- **Purpose**: Shared visual assets

#### Fonts
- **Files**:
  - `assets/fonts/nunito/` - Nunito font family
  - `assets/fonts/fontawesome/` - Font Awesome icons
- **Purpose**: Typography and iconography

## Shared Component Dependencies

### High-Priority Dependencies
1. **Database Connection** - Required by all data-driven components
2. **Session Management** - Required by all authenticated pages
3. **Base Layout** - Required by all user-facing pages
4. **CSS Framework** - Required for consistent styling
5. **JavaScript Utilities** - Required for interactive functionality

### Medium-Priority Dependencies
1. **Audit System** - Used by most data modification operations
2. **Notification System** - Used across multiple features
3. **Form Validation** - Used by most input forms
4. **Navigation Components** - Used by most pages

### Low-Priority Dependencies
1. **Chart Libraries** - Used by specific data visualization features
2. **Email System** - Used for specific notification features
3. **Report Components** - Used by reporting features

## Migration Considerations

### Order of Migration
1. **Configuration and Database** - Move first as foundation
2. **Authentication and Session** - Critical for security
3. **Layout Components** - Required for page structure
4. **CSS/JS Framework** - Required for styling and interaction
5. **Utility Functions** - Required by business logic
6. **Business Logic Helpers** - Feature-specific but shared
7. **Specialized Components** - Charts, notifications, etc.

### Path Resolution Updates
- Update all include/require statements to use new shared paths
- Implement path constants for easier maintenance
- Test asset loading from new locations
- Validate CSS import chains

### Testing Strategy
- Test shared components independently
- Validate integration with each feature
- Performance testing for asset loading
- Cross-browser compatibility testing

## Shared Component API Guidelines

### PHP Components
- Use consistent parameter naming
- Implement proper error handling
- Document return values and exceptions
- Maintain backward compatibility during migration

### JavaScript Components
- Use module pattern for encapsulation
- Implement consistent event handling
- Provide clear initialization methods
- Document public APIs

### CSS Components
- Use BEM naming methodology
- Implement proper cascade hierarchy
- Provide customization variables
- Maintain responsive behavior

This documentation serves as the blueprint for extracting and organizing shared components during the restructuring process. All components listed here should be moved to the `shared/` directory and updated to work within the new feature-based architecture.
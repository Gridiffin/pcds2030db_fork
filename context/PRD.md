# PCDS 2030 Dashboard - Product Requirements Document

## Executive Summary

The PCDS 2030 Dashboard is a comprehensive forest management system designed for the Sarawak Forestry Corporation and its partner agencies. This document outlines the product requirements for the system restructuring from a file-type-based architecture to a feature-based architecture while preserving all existing functionality and business logic.

## Product Overview

### Vision Statement
To provide a modern, scalable, and maintainable forest management platform that supports strategic planning, program tracking, and comprehensive reporting for Sarawak's forest conservation initiatives.

### Mission
Modernize the existing production system architecture to improve developer experience and maintainability while ensuring zero functional regression and maintaining the current forest-themed user experience.

### Target Users
- **Agency Users**: Program managers and staff from STIDC, SFC, FDS, and other agencies
- **Focal Users**: Agency coordinators with enhanced permissions
- **Admin Users**: System administrators with full access to all modules
- **Management**: Executives requiring reporting and oversight capabilities

## Current System Analysis

### Core Functionalities
1. **Program Management** - Complete lifecycle management of forest programs
2. **Submission System** - Quarterly, half-yearly, and yearly reporting workflows
3. **Initiative Management** - Hierarchical organization of programs under initiatives
4. **Outcome Tracking** - KPI and outcome management with flexible data structures
5. **Reporting System** - Automated report generation and PowerPoint export
6. **User Management** - Role-based access control and permission assignment
7. **Audit System** - Comprehensive field-level change tracking
8. **Notification System** - Event-driven notifications for system actions

### Technical Architecture
- **Backend**: PHP 8.x with MySQL 8.0
- **Frontend**: Custom CSS framework with modern components and JavaScript modules
- **Infrastructure**: cPanel hosting with local file storage
- **Security**: Session-based authentication with role-based authorization

## Product Requirements

### Functional Requirements

#### FR-1: Program Management
- **FR-1.1**: Create, edit, and delete forest programs with hierarchical numbering
- **FR-1.2**: Status management (Draft, Active, On Hold, Completed, Cancelled)
- **FR-1.3**: Program validation with uniqueness checks and date validation
- **FR-1.4**: Bulk operations for program management
- **FR-1.5**: Program history tracking and versioning

#### FR-2: Submission Workflow
- **FR-2.1**: Draft submission creation with auto-save functionality
- **FR-2.2**: Target management with dynamic row addition/removal
- **FR-2.3**: Submission finalization with validation checks
- **FR-2.4**: Unsubmit capability for authorized users
- **FR-2.5**: Submission preview and review functionality

#### FR-3: Reporting System
- **FR-3.1**: Automated reporting period management (quarterly, half-yearly, yearly)
- **FR-3.2**: Real-time dashboard with statistical overviews
- **FR-3.3**: PowerPoint report generation from program data
- **FR-3.4**: Public report access for transparency
- **FR-3.5**: Export capabilities in multiple formats

#### FR-4: User Management
- **FR-4.1**: User account creation and management
- **FR-4.2**: Role assignment (Admin, Focal, Agency)
- **FR-4.3**: Program-specific user permissions
- **FR-4.4**: Session management with security controls
- **FR-4.5**: Notification preferences and management

#### FR-5: Initiative Management
- **FR-5.1**: Initiative creation and organization
- **FR-5.2**: Program-initiative relationship management
- **FR-5.3**: Initiative-level reporting and analytics
- **FR-5.4**: Cascade deletion and relationship integrity

#### FR-6: Outcome Tracking
- **FR-6.1**: Flexible outcome definition with custom fields
- **FR-6.2**: KPI tracking and measurement
- **FR-6.3**: Outcome-program linking
- **FR-6.4**: Historical outcome data preservation
- **FR-6.5**: Chart visualization for outcome trends

### Non-Functional Requirements

#### NFR-1: Performance
- **NFR-1.1**: Page load times under 3 seconds
- **NFR-1.2**: AJAX responses under 1 second
- **NFR-1.3**: Support for concurrent users (current: ~50 users)
- **NFR-1.4**: Database query optimization for large datasets

#### NFR-2: Security
- **NFR-2.1**: SQL injection prevention through prepared statements
- **NFR-2.2**: XSS protection via output escaping
- **NFR-2.3**: CSRF protection for form submissions
- **NFR-2.4**: Secure file upload with type validation
- **NFR-2.5**: Session security with timeout management

#### NFR-3: Compatibility
- **NFR-3.1**: cPanel hosting compatibility
- **NFR-3.2**: PHP 8.x requirement
- **NFR-3.3**: MySQL 8.0 compatibility
- **NFR-3.4**: Modern browser support (Chrome, Firefox, Safari, Edge)
- **NFR-3.5**: Mobile responsive design

#### NFR-4: Maintainability
- **NFR-4.1**: Feature-based code organization
- **NFR-4.2**: Shared component reusability
- **NFR-4.3**: Clear separation of concerns
- **NFR-4.4**: Comprehensive documentation
- **NFR-4.5**: Modular architecture

#### NFR-5: Reliability
- **NFR-5.1**: 99.5% uptime requirement
- **NFR-5.2**: Data backup and recovery procedures
- **NFR-5.3**: Error logging and monitoring
- **NFR-5.4**: Graceful error handling
- **NFR-5.5**: Transaction integrity

## Design Requirements

### User Experience
- **UX-1**: Preserve existing forest-themed design system
- **UX-2**: Maintain current navigation patterns and workflows
- **UX-3**: Responsive design for mobile and tablet access
- **UX-4**: Accessibility compliance (WCAG 2.1)
- **UX-5**: Command palette for enhanced navigation

### Visual Design
- **VD-1**: Forest color palette with deep, medium, light, and pale variations
- **VD-2**: Nunito font family with appropriate weight variations
- **VD-3**: Consistent 8pt spacing grid system
- **VD-4**: Modern card-based layouts with elevation
- **VD-5**: Chart.js integration with forest theming

## Technical Requirements

### Architecture Constraints
- **AC-1**: PHP-based backend with no framework dependencies
- **AC-2**: MySQL database with existing schema preservation
- **AC-3**: JavaScript without heavy framework requirements
- **AC-4**: CSS-first approach with minimal build tooling
- **AC-5**: cPanel deployment compatibility

### Migration Requirements
- **MR-1**: Zero downtime migration strategy
- **MR-2**: Database schema preservation
- **MR-3**: Asset and file migration
- **MR-4**: User session continuity
- **MR-5**: Rollback capability

## Feature-Based Structure Requirements

### Organization Principles
- **OP-1**: Group related functionality into feature modules
- **OP-2**: Separate agency and admin interfaces logically
- **OP-3**: Extract shared components into reusable modules
- **OP-4**: Maintain clear file naming conventions
- **OP-5**: Implement consistent import/export patterns

### Proposed Structure
```
features/
├── dashboard/
│   ├── agency/
│   └── admin/
├── programs/
│   ├── shared/
│   ├── agency/
│   └── admin/
├── submissions/
├── initiatives/
├── outcomes/
├── reports/
├── users/
└── shared/
    ├── components/
    ├── layouts/
    └── utilities/
```

### Shared Components
- **SC-1**: Authentication and session management
- **SC-2**: Database connection and query utilities
- **SC-3**: Form validation and processing
- **SC-4**: Notification system
- **SC-5**: Audit logging functionality

## Integration Requirements

### External Systems
- **ES-1**: SMTP integration for email notifications
- **ES-2**: File system integration for uploads and reports
- **ES-3**: Chart.js for data visualization
- **ES-4**: PowerPoint generation libraries

### API Requirements
- **API-1**: RESTful endpoints for AJAX operations
- **API-2**: Consistent error response formats
- **API-3**: Authentication middleware for API access
- **API-4**: Rate limiting for API protection
- **API-5**: Comprehensive API documentation

## Success Criteria

### Migration Success
- **MS-1**: All existing functionality preserved
- **MS-2**: No performance degradation
- **MS-3**: Improved code maintainability scores
- **MS-4**: Reduced file complexity metrics
- **MS-5**: Enhanced developer experience

### User Acceptance
- **UA-1**: Zero user training required
- **UA-2**: Consistent user experience
- **UA-3**: All workflows function identically
- **UA-4**: Performance improvements visible to users
- **UA-5**: No data loss or corruption

## Risk Assessment

### Technical Risks
- **TR-1**: Code refactoring introducing bugs
- **TR-2**: Asset reference breaks during migration
- **TR-3**: Database connection issues during restructuring
- **TR-4**: PHP include path complications
- **TR-5**: Session management disruption

### Mitigation Strategies
- **MIT-1**: Comprehensive testing at each migration phase
- **MIT-2**: Asset auditing and verification tools
- **MIT-3**: Database transaction integrity checks
- **MIT-4**: Path resolution testing and validation
- **MIT-5**: Session state preservation protocols

## Timeline and Phases

### Phase 1: Foundation (Week 1-2)
- Setup feature-based directory structure
- Migrate shared components and utilities
- Establish testing framework

### Phase 2: Core Features (Week 3-4)
- Migrate dashboard functionality
- Restructure program management
- Update submission workflows

### Phase 3: Supporting Features (Week 5-6)
- Migrate initiatives and outcomes
- Restructure reporting system
- Update user management

### Phase 4: Integration and Testing (Week 7-8)
- End-to-end testing
- Performance optimization
- Documentation completion

## Conclusion

This PRD provides the framework for successfully restructuring the PCDS 2030 Dashboard from a file-type-based to a feature-based architecture. The focus is on preserving all existing functionality while improving code organization and maintainability. The success of this project will result in a more scalable and developer-friendly codebase that maintains the robust functionality and user experience of the current system.
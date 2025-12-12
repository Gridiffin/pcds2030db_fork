# PCDS 2030 Dashboard - System Context Documentation

## Overview

This directory contains comprehensive context documentation for the PCDS 2030 Dashboard system, created to support the migration from a file-type-based architecture to a feature-based architecture. The documentation captures all critical aspects of the current production system to ensure seamless knowledge transfer and functionality preservation.

## Document Index

### 1. [System Architecture](./System-Architecture.md)
**Purpose**: High-level system architecture and technical overview  
**Content**:
- Current technology stack (PHP 8.x, MySQL 8.0, custom CSS framework)
- System architecture diagrams and component relationships
- Directory structure analysis
- Security architecture and authentication flow
- Performance characteristics and scalability considerations
- Migration readiness assessment

### 2. [Database Schema](./Database-Schema.md)
**Purpose**: Comprehensive database structure documentation  
**Content**:
- Complete table documentation (17 tables)
- Entity relationships and foreign key constraints
- Business logic reflected in schema design
- Data types, constraints, and validation rules
- Audit system architecture with field-level change tracking
- Performance indexes and optimization considerations

### 3. [UI Components](./UI-Components.md)
**Purpose**: Design system and user interface documentation  
**Content**:
- Forest-themed design system with color palette and typography
- Component library (buttons, cards, forms, modals, tables)
- Responsive design patterns and mobile optimization
- JavaScript interaction patterns and AJAX workflows
- CSS architecture and asset management
- Accessibility compliance and modern UI features

### 4. [Data Flow](./Data-Flow.md)
**Purpose**: Business logic and data processing workflows  
**Content**:
- Core business processes (program lifecycle, submission workflow)
- Data flow patterns and transaction management
- Validation logic and business rules
- Integration workflows and cross-module dependencies
- State management and caching strategies
- Audit trail and compliance requirements

### 5. [PRD (Product Requirements Document)](./PRD.md)
**Purpose**: Product requirements for the restructuring project  
**Content**:
- Functional requirements for all features
- Non-functional requirements (performance, security, compatibility)
- Design requirements and user experience guidelines
- Technical constraints and migration requirements
- Success criteria and risk assessment
- Timeline and implementation phases

### 6. [Feature-Based Structure](./Feature-Based-Structure.md)
**Purpose**: Detailed recommendations for the new architecture  
**Content**:
- Proposed feature-based directory structure
- Migration strategy and implementation phases
- File organization principles and naming conventions
- Dependency management and include path updates
- Benefits analysis and implementation guidelines
- Detailed feature breakdown with file mappings

### 7. [Shared Components](./Shared-Components.md)
**Purpose**: Catalog of reusable components and utilities  
**Content**:
- Layout components (navigation, footer, page header)
- Authentication and session management
- Database utilities and data access patterns
- Form validation and AJAX helpers
- UI framework and interactive components
- Business logic helpers and notification system

### 8. [API Endpoints](./API-Endpoints.md)
**Purpose**: Complete API documentation and integration patterns  
**Content**:
- AJAX endpoints catalog (35+ endpoints)
- REST API documentation (20+ endpoints)
- Security implementation and authentication mechanisms
- Request/response patterns and data formats
- Error handling and validation patterns
- Integration workflows and cross-feature APIs

## Key System Characteristics

### Production System Overview
- **Status**: Currently in production serving Sarawak Forestry Corporation
- **Users**: ~50 concurrent users across multiple agencies (STIDC, SFC, FDS)
- **Core Function**: Forest management program tracking and reporting
- **Architecture**: PHP-based with MySQL database, custom CSS framework
- **Hosting**: cPanel hosting environment

### Business Domain
- **Program Management**: Complete lifecycle management of forest programs
- **Submission Workflow**: Quarterly, half-yearly, and yearly reporting cycles
- **Initiative Management**: Hierarchical organization of programs under initiatives
- **Outcome Tracking**: KPI and outcome management with flexible data structures
- **Reporting System**: Automated PowerPoint report generation
- **Audit System**: Comprehensive field-level change tracking

### Technical Strengths
- **Robust Security**: Role-based access control with comprehensive audit logging
- **Modern UI**: Forest-themed design system with responsive components
- **Comprehensive Functionality**: Complete forest management workflow coverage
- **Data Integrity**: Strong validation and transaction management
- **Performance**: Optimized for current load with room for growth

## Migration Context

### Project Objective
Transform the current file-type-based structure to a feature-based architecture while:
- Preserving all existing functionality
- Maintaining the current user experience
- Improving code maintainability and developer experience
- Ensuring zero downtime during migration

### Critical Preservation Requirements
1. **Functionality**: All business workflows must remain identical
2. **User Experience**: No changes to user interfaces or interactions
3. **Data Integrity**: All audit trails and data relationships preserved
4. **Performance**: No degradation in system performance
5. **Security**: All authentication and authorization mechanisms preserved

### New Structure Benefits
- **Developer Experience**: Faster development with co-located related files
- **Maintainability**: Clear feature boundaries and reduced coupling
- **Scalability**: Better support for team development and feature isolation
- **Testing**: Feature-level test isolation and validation
- **Documentation**: Feature-specific documentation and ownership

## Usage Guidelines

### For System Migration
1. Start with the **System Architecture** document to understand the overall system
2. Review **Database Schema** to understand data relationships and constraints
3. Study **UI Components** to understand the design system and asset organization
4. Analyze **Data Flow** to understand business logic and validation requirements
5. Use **Feature-Based Structure** as the blueprint for reorganization
6. Reference **Shared Components** to identify reusable code patterns
7. Consult **API Endpoints** for integration and communication patterns

### For New Development
1. Use **PRD** for understanding functional requirements
2. Follow **UI Components** for design system compliance
3. Reference **Shared Components** for reusable functionality
4. Consult **API Endpoints** for API design patterns
5. Follow **Feature-Based Structure** for file organization

### For Maintenance
1. Use **Database Schema** for understanding data relationships
2. Reference **Data Flow** for business logic changes
3. Consult **API Endpoints** for integration debugging
4. Use **Shared Components** for cross-feature impact analysis

## Quality Assurance

This documentation was created through comprehensive analysis of:
- **17 database tables** with complete relationship mapping
- **200+ PHP files** across all application layers
- **100+ CSS files** with component and layout analysis
- **150+ JavaScript files** with interaction pattern documentation
- **55+ API endpoints** with security and integration analysis

All documentation is based on actual code analysis and production system behavior, ensuring accuracy and completeness for the migration project.

## Next Steps

1. **Review Documentation**: Team should review all documents for completeness
2. **Validate Requirements**: Ensure all functional requirements are captured
3. **Plan Migration**: Use the feature-based structure as migration blueprint
4. **Setup Testing**: Establish validation criteria based on preservation requirements
5. **Begin Implementation**: Start with shared components and progress through features

This context documentation provides the complete foundation needed for successfully restructuring the PCDS 2030 Dashboard while preserving its robust functionality and user experience.
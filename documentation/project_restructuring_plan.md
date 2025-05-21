# PCDS2030 Dashboard - Project Restructuring Plan

## Overview

This document outlines a systematic approach to restructuring the PCDS2030 Dashboard project. The restructuring will be implemented in phases to ensure stability and minimize disruption to the existing functionality.

*Plan created: May 21, 2025*

## Current Project Analysis

The PCDS2030 Dashboard is a PHP-based web application that:
- Serves as a reporting tool for Sarawak's forestry sector agencies
- Replaces Excel-based quarterly reporting with a web solution
- Allows agencies to submit sector-specific data and track programs
- Enables the Ministry to generate consolidated reports
- Uses a traditional PHP structure with direct file inclusion

## Restructuring Phases

### Phase 1: Code Organization and Foundation

**Goal:** Establish a modern PHP project structure without changing core functionality.

#### 1.1 Composer Integration and Autoloading
- [ ] Create `composer.json` with PSR-4 autoloading configuration
- [ ] Set up vendor directory and autoloader
- [ ] Begin migrating manual requires to autoloaded classes

```php
// Example composer.json
{
    "name": "pcds2030/dashboard",
    "description": "PCDS2030 Dashboard for Forestry Sector",
    "type": "project",
    "require": {
        "php": ">=7.4"
    },
    "autoload": {
        "psr-4": {
            "PCDS2030\\": "src/"
        },
        "files": [
            "includes/functions.php"
        ]
    }
}
```

#### 1.2 Directory Structure Updates
- [ ] Create `/src` directory for PHP classes
- [ ] Organize class files into logical namespaces
- [ ] Create `/public` directory for web-accessible files

```
/pcds2030_dashboard
├── /public            # Web root directory
│   ├── index.php      # Single entry point
│   └── /assets        # Public assets (copied from current assets)
├── /src               # Application source code
│   ├── /Controllers   # Controller classes
│   ├── /Models        # Model classes
│   ├── /Services      # Service classes
│   └── /Views         # View templates
├── /config            # Configuration files
├── /includes          # Legacy includes (gradually migrated)
├── /vendor            # Composer dependencies
└── composer.json      # Composer configuration
```

#### 1.3 Environment Configuration
- [ ] Create `.env` and `.env.example` files
- [ ] Move sensitive configuration to environment variables
- [ ] Create configuration loader that supports env vars with fallback

```php
// Example .env file
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=pcds2030_dashboard
APP_URL=http://localhost/pcds2030_dashboard
APP_ENV=development
```

#### 1.4 Entry Point Consolidation
- [ ] Create unified `/public/index.php` entry point
- [ ] Implement simple router that maps to existing files
- [ ] Set up proper error handling and logging

### Phase 2: Architecture Improvements (MVC Pattern)

**Goal:** Introduce MVC pattern and separate business logic from presentation.

#### 2.1 Controller Layer
- [ ] Create base Controller class
- [ ] Create controllers for main functional areas (Admin, Agency, Auth, etc.)
- [ ] Migrate direct PHP scripts to controller methods
- [ ] Document routing patterns from URLs to controllers

#### 2.2 Model Layer
- [ ] Create base Model class with common database operations
- [ ] Implement entity models (User, Program, Sector, etc.)
- [ ] Move database operations from procedural code to models
- [ ] Add data validation within model classes

#### 2.3 View Layer
- [ ] Set up templating system
- [ ] Create layout templates (header, footer, sidebar)
- [ ] Convert PHP output code to view templates
- [ ] Implement view helpers for common UI components

#### 2.4 Service Layer
- [ ] Create service classes for complex business logic
- [ ] Move functionality from functions.php to service classes
- [ ] Implement dependency injection for services

### Phase 3: Database Abstraction

**Goal:** Improve database interactions with proper abstraction.

#### 3.1 Database Connection Management
- [ ] Create Database class to handle connections
- [ ] Implement connection pooling (if necessary)
- [ ] Add support for transaction management
- [ ] Create logging for database operations

#### 3.2 Query Builder Implementation
- [ ] Introduce query builder pattern
- [ ] Replace direct SQL with builder methods
- [ ] Ensure all queries use parameterized statements
- [ ] Add query profiling for performance optimization

#### 3.3 Schema Migration System
- [ ] Create migration framework
- [ ] Convert current schema to migrations
- [ ] Document database schema thoroughly
- [ ] Implement version control for database schema

#### 3.4 Data Repository Pattern
- [ ] Create repository interfaces and implementations
- [ ] Centralize data access through repositories
- [ ] Add caching for frequently accessed data
- [ ] Implement data mappers where needed

### Phase 4: Security Enhancements

**Goal:** Strengthen security practices across the application.

#### 4.1 Authentication System
- [ ] Improve password hashing and storage
- [ ] Enhance session security measures
- [ ] Add proper user role and permission management
- [ ] Implement account security features (password reset, etc.)

#### 4.2 Input Validation & Sanitization
- [ ] Create comprehensive input validation
- [ ] Implement CSRF protection for all forms
- [ ] Add sanitization middleware for requests
- [ ] Create validation rule system for forms

#### 4.3 Access Control
- [ ] Implement robust permission checking
- [ ] Create middleware for role-based access
- [ ] Add audit logging for sensitive operations
- [ ] Implement IP-based security measures (if needed)

#### 4.4 Security Headers and Protection
- [ ] Add security headers to all responses
- [ ] Implement XSS protection measures
- [ ] Create proper error handling that doesn't leak info
- [ ] Add security scanning to deployment process

### Phase 5: UI/UX Modernization

**Goal:** Enhance user interface and experience.

#### 5.1 Asset Management
- [ ] Implement proper CSS/JS bundling
- [ ] Organize assets in a more structured way
- [ ] Add versioning for assets to improve caching
- [ ] Optimize asset loading for performance

#### 5.2 Responsive Design
- [ ] Ensure all pages work well on mobile devices
- [ ] Improve accessibility features
- [ ] Standardize UI components across the application
- [ ] Add print-friendly styles for reports

#### 5.3 JavaScript Enhancement
- [ ] Consider adding a frontend framework (Vue.js, etc.)
- [ ] Implement AJAX for smoother interactions
- [ ] Create consistent UI component library
- [ ] Add client-side validation

#### 5.4 Performance Optimization
- [ ] Implement caching strategies
- [ ] Optimize database queries
- [ ] Add performance monitoring
- [ ] Implement lazy loading where appropriate

## Implementation Strategy

### For Each Phase:

1. **Planning and Documentation**
   - Document existing functionality in detail
   - Create specific tickets/tasks for each component
   - Set clear success criteria for each change

2. **Development Approach**
   - Create a dedicated git branch for the phase
   - Implement changes incrementally with frequent commits
   - Maintain backward compatibility where possible
   - Write automated tests for new components

3. **Testing Strategy**
   - Conduct thorough testing after each component change
   - Perform regression testing on completed phases
   - Use both manual testing and automated tests
   - Document all test cases and results

4. **Deployment Approach**
   - Deploy gradually, starting with non-critical components
   - Have rollback plans ready for each deployment
   - Monitor system after deployment for issues
   - Collect feedback from users after each phase

### Risk Mitigation

- Keep parallel versions of critical components during transition
- Create comprehensive backups before each phase
- Document all changes thoroughly
- Allow for extended testing periods between phases
- Prioritize maintaining existing functionality over new features

## Getting Started with Phase 1

### Initial Setup Tasks

1. **Create composer.json**
   ```bash
   composer init --name="pcds2030/dashboard" --description="PCDS2030 Dashboard" --type="project" --require="php:>=7.4"
   ```

2. **Set up basic directory structure**
   ```bash
   mkdir -p src/{Controllers,Models,Services,Views}
   mkdir -p public/assets
   mkdir -p config/env
   ```

3. **Create .env file (from existing config)**
   - Copy database credentials from config.php
   - Add application settings as environment variables
   - Create .env.example with sample values

4. **Create entry point file**
   - Set up public/index.php as main entry point
   - Implement basic routing system
   - Include backward compatibility for direct file access

### Migration Strategy

Start with small, isolated components that have minimal dependencies:
1. First migrate utility functions to proper classes
2. Then create models for core entities (Users, Programs)
3. Finally implement controllers for main functionality

## Timeline and Milestones

- **Phase 1**: 4-6 weeks
  - Week 1-2: Setup Composer and directory structure
  - Week 3-4: Environment configuration and autoloading
  - Week 5-6: Entry point consolidation and testing

- **Phase 2**: 6-8 weeks
  - Week 1-2: Controller base implementation
  - Week 3-4: Model layer development
  - Week 5-6: View templating
  - Week 7-8: Service layer implementation

- **Phase 3**: 4-6 weeks
- **Phase 4**: 3-4 weeks
- **Phase 5**: 5-7 weeks

Total restructuring timeline: 22-31 weeks (5-7 months)

## Conclusion

This phased restructuring approach allows for a gradual modernization of the PCDS2030 Dashboard while maintaining functionality throughout the process. Each phase builds upon the previous one, resulting in a more maintainable, secure, and performant application.

By following this plan, the project can be transformed into a modern PHP application while minimizing disruption to users and reducing the risk of introducing bugs or regressions.

Regular reviews after each phase will ensure the project stays on track and aligns with business requirements.

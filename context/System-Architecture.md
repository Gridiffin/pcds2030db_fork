# PCDS 2030 Dashboard - System Architecture Documentation

## Project Overview

The PCDS 2030 Dashboard is a comprehensive forest management system built for the Sarawak Forestry Corporation. It manages programs, submissions, reporting periods, outcomes, and initiatives across multiple agencies (STIDC, SFC, FDS). The system is currently running in production and serves as a critical tool for forest management planning and reporting.

## Current Technology Stack

### Backend
- **Language**: PHP 8.x
- **Database**: MySQL 8.0 (pcds2030_db)
- **Server**: Apache (XAMPP/Laragon for development)
- **Session Management**: PHP sessions with enhanced security
- **Authentication**: Role-based access control (admin, focal, agency)

### Frontend
- **Markup**: HTML5 with PHP templating
- **Styling**: CSS3 with component-based architecture
- **JavaScript**: Vanilla JS with modular architecture
- **Charts**: Chart.js for data visualization
- **UI Framework**: Custom CSS framework with modern design patterns

### Infrastructure
- **Development**: XAMPP, Laragon (Windows)
- **Production**: cPanel hosting
- **File Storage**: Local filesystem for uploads
- **Asset Management**: Manual bundling with optional build tools

## System Architecture

### High-Level Architecture
```
┌─────────────────────────────────────────────┐
│               Frontend Layer                │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────┐│
│  │  HTML/CSS   │ │ JavaScript  │ │ Chart.js││
│  │ Components  │ │   Modules   │ │ Visuals ││
│  └─────────────┘ └─────────────┘ └─────────┘│
└─────────────────────────────────────────────┘
          │                    │
┌─────────────────────────────────────────────┐
│             Application Layer               │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────┐│
│  │  MVC-like   │ │  Business   │ │   API   ││
│  │ Controllers │ │    Logic    │ │Endpoints││
│  └─────────────┘ └─────────────┘ └─────────┘│
└─────────────────────────────────────────────┘
          │                    │
┌─────────────────────────────────────────────┐
│              Data Layer                     │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────┐│
│  │    MySQL    │ │   Audit     │ │  File   ││
│  │  Database   │ │   Logging   │ │ Storage ││
│  └─────────────┘ └─────────────┘ └─────────┘│
└─────────────────────────────────────────────┘
```

### Current Directory Structure
```
pcds2030_dashboard_fork/
├── index.php                    # Front controller
├── login.php                    # Authentication entry
├── app/                         # Core application logic
│   ├── ajax/                    # AJAX endpoints
│   ├── api/                     # REST API endpoints
│   ├── config/                  # Configuration files
│   ├── controllers/             # MVC Controllers
│   ├── lib/                     # Business logic libraries
│   ├── views/                   # PHP templates
│   ├── handlers/                # Request handlers
│   └── helpers/                 # Utility functions
├── assets/                      # Static assets
│   ├── css/                     # Stylesheets
│   ├── js/                      # JavaScript modules
│   └── images/                  # Static images
├── uploads/                     # User-uploaded files
└── docs/                        # Documentation
```

## Core Components

### 1. Authentication & Authorization
- **Location**: `app/lib/session.php`, `login.php`
- **Features**: Role-based access control (admin, focal, agency)
- **Security**: Session management, CSRF protection, input validation

### 2. Database Layer
- **Location**: `app/lib/db_connect.php`
- **Technology**: PDO with prepared statements
- **Features**: Connection pooling, transaction support, audit logging

### 3. Routing System
- **Entry Point**: `index.php`
- **Pattern**: Parameter-based routing (`?page=agency_dashboard`)
- **Controllers**: Located in `app/controllers/`

### 4. View System
- **Location**: `app/views/`
- **Architecture**: Component-based PHP templating
- **Layouts**: Separate layouts for admin and agency interfaces

### 5. Asset Management
- **CSS**: Component-based architecture with base.css imports
- **JavaScript**: Modular design with feature-specific bundles
- **Images**: Static assets with versioning

## Data Architecture

### Core Database Tables

#### 1. User Management
- `users` - User accounts and profiles
- `agency` - Organization entities
- `user_notifications` - Notification system

#### 2. Program Management
- `programs` - Main program entities
- `program_submissions` - Program submission data
- `program_attachments` - File attachments

#### 3. Outcome Tracking
- `outcomes` - Outcome definitions
- `outcome_data` - Flexible outcome data storage
- `program_outcome_links` - Program-outcome relationships

#### 4. Initiative Management
- `initiatives` - Initiative definitions
- `initiative_programs` - Initiative-program links

#### 5. Reporting System
- `reporting_periods` - Time-based reporting periods
- `reports` - Generated report metadata

#### 6. Audit System
- `audit_logs` - Comprehensive audit trail
- `audit_field_changes` - Detailed field-level changes

### Data Flow Patterns

#### 1. Program Lifecycle
```
Program Creation → Draft State → Submission → Review → Finalization
      ↓               ↓            ↓         ↓         ↓
   Audit Log    → Audit Log → Audit Log → Audit Log → Audit Log
```

#### 2. Outcome Management
```
Outcome Definition → Data Entry → Validation → Aggregation → Reporting
```

#### 3. Report Generation
```
Data Collection → Template Processing → PPTX Generation → File Storage
```

## Integration Points

### External Dependencies
- **Chart.js**: Data visualization
- **Font Awesome**: Icon library
- **Custom CSS Framework**: UI components

### File System Integration
- **Upload Directory**: `uploads/programs/attachments/`
- **Report Storage**: `app/reports/pptx/`
- **Static Assets**: `assets/` directory

### API Endpoints
- **AJAX**: `app/ajax/` - Form submissions and data updates
- **REST**: `app/api/` - Data retrieval and reporting

## Security Architecture

### Authentication Flow
1. Login validation via `login.php`
2. Session creation with user role
3. Page-level authorization checks
4. Resource-level permission validation

### Data Protection
- **SQL Injection**: PDO prepared statements
- **XSS**: HTML escaping in templates
- **CSRF**: Session-based token validation
- **File Upload**: Type and size validation

### Audit Trail
- Comprehensive logging of all data changes
- Field-level change tracking
- User action attribution
- Timestamp preservation

## Performance Characteristics

### Current Optimizations
- Database indexing on key relationships
- CSS/JS bundling via base.css imports
- Session optimization with minimal data storage
- File caching for static assets

### Known Bottlenecks
- N+1 query patterns in some views
- Large file handling without streaming
- Monolithic CSS/JS files
- Complex reporting queries

## Scalability Considerations

### Current Limitations
1. Single server architecture
2. File-based session storage
3. Local filesystem for uploads
4. Single MySQL instance

### Growth Capacity
- Current load: ~5 agencies, ~100 programs
- Database size: Moderate (under 1GB)
- File storage: Local filesystem adequate
- Concurrent users: Limited by session handling

## Migration Readiness

### Well-Architected Components
✅ Clear separation of concerns  
✅ Modular CSS architecture  
✅ RESTful API patterns  
✅ Comprehensive audit logging  
✅ Role-based access control  

### Areas for Modernization
❌ Mixed templating and business logic  
❌ Manual asset management  
❌ Limited error handling  
❌ No automated testing  
❌ Manual deployment process  

## Configuration Management

### Environment Detection
- Dynamic host-based configuration
- Development vs production settings
- Database connection management

### Feature Flags
- `ALLOW_OUTCOME_CREATION`: Controls outcome functionality
- Role-based feature availability
- Environment-specific debugging

This architecture serves as the foundation for understanding the current system structure and planning the migration to a feature-based architecture.
# PCDS 2030 Dashboard - Modernized PHP Architecture Documentation

## Overview

The PCDS 2030 Dashboard is being modernized to use contemporary PHP development practices while maintaining simplicity and cPanel hosting compatibility. This rebuild focuses on improving user experience and maintainability without introducing complex JavaScript frameworks.

## Modernized Technology Stack

### Backend (Unchanged Core)
- **Language**: PHP 8.x (enhanced with modern patterns)
- **Database**: MySQL 8.0 with optimized queries
- **Server**: Apache (XAMPP/Laragon for development)
- **Session Management**: Enhanced PHP sessions with security improvements
- **Authentication**: Role-based with improved PHP session storage

### Frontend (Modernized)
- **Markup**: Semantic HTML5 with improved PHP templating
- **Styling**: Tailwind CSS for utility-first design
- **JavaScript**: Alpine.js for reactive components (Vue.js-like simplicity)
- **UI Components**: Custom component library with Alpine.js + Tailwind
- **Charts**: Chart.js with Alpine.js integration
- **HTTP Requests**: Fetch API with Alpine.js data binding

### Infrastructure (cPanel Optimized)
- **Development**: XAMPP, Laragon (Windows)
- **Production**: cPanel hosting (no Node.js required)
- **File Storage**: Local filesystem for uploads (cPanel compatible)
- **Build Process**: Optional Vite for CSS/JS optimization only

## Application Architecture

### Simplified Modern Structure
```
┌─────────────────────────────────────────────┐
│            Modern PHP Frontend              │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────┐│
│  │  Alpine.js  │ │ Tailwind    │ │ Chart.js││
│  │ Components  │ │   CSS       │ │ Charts  ││
│  └─────────────┘ └─────────────┘ └─────────┘│
└─────────────────────────────────────────────┘
           │                    │
┌─────────────────────────────────────────────┐
│            Enhanced PHP Backend             │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────┐│
│  │  Improved   │ │  Cleaner    │ │  Better ││
│  │  Templates  │ │  Structure  │ │  APIs   ││
│  └─────────────┘ └─────────────┘ └─────────┘│
└─────────────────────────────────────────────┘
           │                    │
┌─────────────────────────────────────────────┐
│              MySQL Database                 │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────┐│
│  │    Core     │ │   Audit     │ │  Files  ││
│  │   Tables    │ │   Logs      │ │ Storage ││
│  └─────────────┘ └─────────────┘ └─────────┘│
└─────────────────────────────────────────────┘
```

### Modernized Directory Structure
```
pcds2030_dashboard_modernized/
├── app/                          # Enhanced application logic
│   ├── components/              # Reusable PHP components/templates
│   │   ├── layout/             # Layout components (header, nav, footer)
│   │   ├── forms/              # Form components
│   │   ├── cards/              # Card components
│   │   └── modals/             # Modal components
│   ├── controllers/            # Clean MVC Controllers
│   │   ├── AdminController.php
│   │   ├── AgencyController.php
│   │   └── ApiController.php
│   ├── services/               # Business logic services
│   │   ├── ProgramService.php
│   │   ├── UserService.php
│   │   └── ReportService.php
│   ├── models/                 # Data access layer
│   │   ├── Program.php
│   │   ├── User.php
│   │   └── Agency.php
│   ├── config/                 # Configuration files
│   │   ├── app.php            # Main app config
│   │   ├── database.php       # Database config
│   │   └── routes.php         # Route definitions
│   └── views/                  # Modern PHP templates
│       ├── admin/              # Admin interface views
│       ├── agency/             # Agency interface views
│       ├── components/         # Shared component templates
│       └── layouts/            # Base layouts
├── public/                      # Web-accessible files
│   ├── assets/                 # Optimized assets
│   │   ├── css/               # Compiled Tailwind CSS
│   │   │   └── app.css
│   │   ├── js/                # Alpine.js and custom JS
│   │   │   ├── alpine.min.js
│   │   │   ├── chart.min.js
│   │   │   └── app.js
│   │   └── images/            # Optimized images
│   ├── uploads/               # User-uploaded files
│   └── index.php              # Front controller
├── database/                    # Database related files
│   ├── migrations/            # Database migrations
│   └── seeds/                 # Sample data
├── scripts/                     # Build and utility scripts
│   ├── build.php             # Asset compilation
│   └── deploy.php            # Deployment script
└── docs/                        # Documentation
```

## Core Components

### 1. Enhanced Authentication System
**File**: `app/services/AuthService.php`
- Improved PHP session management with security
- Role-based access control (admin, focal, agency)
- Session timeout and CSRF protection
- Simple login/logout with better error handling

### 2. Modern Database Layer
**File**: `app/models/Database.php`
- PDO-based with prepared statements
- Connection pooling and error handling
- Query builder for common operations
- Transaction support

### 3. Simple Routing System
**Entry Point**: `public/index.php`
- Clean URL routing with .htaccess
- Controller-based routing
- Middleware for authentication
- API endpoint routing

### 4. Alpine.js Integration
**Files**: `public/assets/js/app.js`
- Component-based JavaScript with Alpine.js
- Reactive data binding
- AJAX requests with fetch API
- Form validation and submission

### 5. Modern View System
**Directory**: `app/views/`
- Component-based PHP templating
- Tailwind CSS utility classes
- Reusable component includes
- Clean separation of concerns

## Modern Data Flow Architecture

### 1. Simplified Request Lifecycle
```
User Request → Front Controller → Controller → Service Layer → Model → Database
                     ↓
Response ← View (PHP + Alpine.js) ← Template ← Data Processing ← Query Result
```

### 2. Alpine.js Component Interactions
```
User Interaction → Alpine.js Component → Fetch API → PHP Endpoint → Database
                        ↓
DOM Update ← Alpine.js Reactive Data ← JSON Response ← Processed Data
```

### 3. File Upload with Alpine.js
```
Alpine.js Form → FormData → Fetch API → PHP Handler → Validation → Storage
                   ↓
Progress Update ← Alpine.js State ← Upload Response ← Database Update
```

## Security Architecture

### Authentication Flow
1. **Login**: `login.php` validates credentials
2. **Session**: PHP session stores user data
3. **Authorization**: Role-based access checks on each request
4. **Logout**: `logout.php` destroys session

### Data Protection
- **SQL Injection**: PDO prepared statements
- **XSS**: HTML escaping in views
- **CSRF**: Session-based validation
- **File Upload**: Type and size validation

## Performance Considerations

### Current Optimizations
- **CSS/JS Bundling**: Vite-based asset compilation
- **Database Indexing**: Key relationships indexed
- **File Caching**: Static asset versioning
- **Session Optimization**: Minimal session data storage

### Known Performance Issues
- **N+1 Queries**: Some views make multiple database calls
- **Large File Handling**: No streaming for large uploads
- **Frontend Bundle Size**: Monolithic CSS/JS files
- **Database Queries**: Some complex joins without optimization

## Integration Points

### External Dependencies
- **Chart.js**: Data visualization library
- **Bootstrap**: UI framework
- **Font Awesome**: Icon library
- **jQuery**: DOM manipulation (legacy)

### File System Integration
- **Upload Directory**: `uploads/programs/attachments/`
- **Report Generation**: `app/reports/pptx/`
- **Static Assets**: `assets/` directory

## Configuration Management

### Environment-Specific Settings
**File**: `app/config/config.php`
```php
// Dynamic host detection
if ($current_host === 'www.sarawakforestry.com') {
    // Production settings
    define('DB_HOST', 'localhost:3306');
    define('DB_NAME', 'pcds2030_db');
} else {
    // Development settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'pcds2030_db');
}
```

### Feature Flags
- `ALLOW_OUTCOME_CREATION`: Controls outcome creation functionality
- Role-based feature availability
- Environment-specific error reporting

## Scalability Considerations

### Current Limitations
1. **Single Server Architecture**: No horizontal scaling
2. **File Storage**: Local filesystem only
3. **Database**: Single MySQL instance
4. **Session Storage**: PHP file-based sessions

### Bottlenecks
1. **Report Generation**: CPU-intensive PowerPoint creation
2. **File Uploads**: No streaming or chunked uploads
3. **Database Queries**: Some complex reporting queries
4. **Frontend Rendering**: Server-side template rendering

## Migration Readiness

### Well-Architected Components
- ✅ Clear separation of concerns
- ✅ Modular CSS architecture
- ✅ RESTful API patterns
- ✅ Comprehensive audit logging

### Areas Needing Modernization
- ❌ Mixed templating and logic in views
- ❌ jQuery-heavy frontend code
- ❌ No frontend state management
- ❌ Limited error handling
- ❌ No automated testing
- ❌ Manual deployment process

## Technology Debt

### Frontend
- **Legacy jQuery**: Heavy reliance on jQuery patterns
- **Inline Scripts**: JavaScript mixed with HTML
- **CSS Specificity**: Some overly specific selectors
- **Asset Management**: Manual CSS/JS import chains

### Backend
- **Mixed Concerns**: Business logic in view files
- **Error Handling**: Inconsistent error responses
- **Code Duplication**: Similar functions across different modules
- **Documentation**: Limited inline documentation

### Database
- **Schema Evolution**: Multiple migration patterns
- **Naming Conventions**: Inconsistent table/column naming
- **Relationships**: Some implicit relationships not enforced

## Recommended Migration Path

### Phase 1: Frontend Modernization
1. React + TypeScript implementation
2. Modern state management (React Query + Zustand)
3. Component-based UI architecture
4. Vite build optimization

### Phase 2: API Standardization
1. RESTful API consolidation
2. Consistent error handling
3. Input validation framework
4. Authentication modernization (JWT)

### Phase 3: Infrastructure Upgrade
1. Database optimization
2. File storage solutions
3. Performance monitoring
4. Automated deployment

This architecture documentation provides the foundation for understanding the current system and planning the migration to a modern Vite + React implementation.
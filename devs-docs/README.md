# Developer Documentation Hub

Welcome to the PCDS2030 Dashboard development documentation. This directory contains comprehensive technical documentation for developers working on or maintaining this system.

## 🎯 Project Overview

The PCDS2030 Dashboard is a production web-based system for the Sarawak Ministry of Natural Resources and Urban Development, specifically designed for forestry sector management. It replaces traditional Excel-based reporting with automated, web-based solutions featuring real-time dashboards and automated report generation.

**Current Status**: ✅ **Production Ready & In Use**
- **Environment**: Sarawak Government Production (sarawakforestry.com)
- **Users**: Ministry + 3 Forestry Agencies (FDS, SFC, STIDC)
- **Focus**: Quarterly/Half-yearly forestry program reporting

## 📋 Documentation Index

### 🚀 Getting Started
- **[SETUP.md](SETUP.md)** - Complete Windows development environment setup
- **[INSTALLATION.md](INSTALLATION.md)** - Step-by-step installation guide with troubleshooting
- **[ENVIRONMENT.md](ENVIRONMENT.md)** - Environment configuration and variables

### 🏗️ Architecture & Structure  
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - System architecture and design patterns
- **[DATABASE.md](DATABASE.md)** - Database schema and relationships
- **[FILE-STRUCTURE.md](FILE-STRUCTURE.md)** - Directory organization and conventions

### 🔧 Development
- **[API.md](API.md)** - API endpoints and usage
- **[TESTING.md](TESTING.md)** - Testing framework and procedures
- **[CODING-STANDARDS.md](CODING-STANDARDS.md)** - Code style and best practices

### 🚀 Deployment & Maintenance
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment procedures
- **[MAINTENANCE.md](MAINTENANCE.md)** - System maintenance and monitoring
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Common issues and solutions

## 🛠️ Technology Stack

### Backend
- **PHP 8.x** - Server-side logic and business rules
- **MySQL** - Database management with optimized queries
- **Apache** - Web server (XAMPP/Laragon development, cPanel production)

### Frontend
- **HTML5/CSS3** - Modern semantic markup and styling
- **JavaScript (ES6+)** - Interactive functionality and AJAX
- **Bootstrap 5** - Responsive UI framework
- **Chart.js** - Data visualization and analytics
- **FontAwesome** - Icon library

### Development & Build Tools
- **Vite** - Asset bundling and build optimization
- **Node.js 18+** - JavaScript runtime for build tools
- **NPM** - Package management
- **Composer** - PHP dependency management

### Testing & Quality Assurance
- **PHPUnit** - Backend PHP testing framework (15 tests)
- **Jest** - Frontend JavaScript testing framework (17 tests)
- **GitHub Actions** - Automated CI/CD pipeline

### Development Environment (Windows Focus)
- **Laragon** - Preferred local development environment
- **XAMPP** - Alternative local development environment
- **Git for Windows** - Version control
- **VS Code** - Recommended IDE with PHP and JavaScript extensions

## 🏛️ System Architecture Overview

### Core Components
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │    Backend      │    │   Database      │
│   (Bootstrap)   │◄──►│    (PHP)        │◄──►│   (MySQL)       │
│   JavaScript    │    │    Apache       │    │   Relational    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Key Workflows
1. **Agency Workflow**: Program creation → Data submission → Progress tracking
2. **Admin Workflow**: Period management → Multi-agency oversight → Report generation
3. **Reporting Cycle**: Quarterly periods → Data collection → Automated PPTX reports

### Data Flow
```
User Input → AJAX Requests → PHP Controllers → Business Logic → Database → JSON Responses → UI Updates
```

## 🔥 Critical Setup Requirements

### Windows Prerequisites
1. **Laragon** (Recommended) or XAMPP with PHP 8.1+
2. **Node.js 18+** with NPM
3. **Git for Windows**
4. **MySQL 8.0+**

### Environment Variables
```php
// Local Development
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = ''
DB_NAME = 'pcds2030_db' // pcds2030_dashboard is the old database structure

// Production (sarawakforestry.com)
DB_HOST = 'localhost:3306'
DB_USER = '' // depends on the one who handles the live hosting service
DB_NAME = 'pcds2030_db'
```

### Build Commands
```bash
# Development
npm run build      # Build production assets
npm test           # Run JavaScript tests
vendor/bin/phpunit # Run PHP tests
```

## 📊 Project Statistics

### Codebase Metrics
- **Total Files**: 500+ files
- **Lines of Code**: ~50,000+ lines
- **Test Coverage**: 32 automated tests (100% pass rate) NOTE: fully automated with AI
- **Languages**: PHP (60%), JavaScript (25%), CSS (15%)

### Database Structure
- **Tables**: 15+ core tables
- **Key Entities**: Users, Programs, Submissions, Targets, Periods, Outcomes
- **Storage**: Flexible JSON content storage for adaptability

## 🚨 Important Notes for Developers

### Security Considerations
- **Database**: All queries use parameterized statements
- **Sessions**: Secure session handling with timeout controls
- **Access Control**: Role-based permissions (Admin/Agency)
- **Audit Trail**: Comprehensive activity logging

### Performance Optimizations
- **Asset Bundling**: Vite-optimized CSS/JS bundles
- **Database**: Indexed queries and optimized joins
- **Caching**: Strategic caching for dashboard data
- **Mobile**: Responsive design with mobile-first approach

### Code Quality Standards
- **PHP**: PSR-12 coding standards
- **JavaScript**: ES6+ with consistent formatting
- **CSS**: BEM methodology with component-based structure
- **Testing**: Comprehensive test coverage for critical functionality

## 🔗 External Dependencies

### Production Dependencies
- **Chart.js**: Dashboard visualizations
- **Bootstrap 5**: UI framework
- **FontAwesome**: Icon library
- **MySQL**: Database engine

### Development Dependencies
- **Vite**: Build tool
- **Jest**: JavaScript testing
- **PHPUnit**: PHP testing
- **Babel**: JavaScript transpilation

## 📞 Support & Maintenance

### Documentation Updates
This documentation should be updated whenever:
- New features are added
- Architecture changes occur
- Deployment procedures change
- Dependencies are updated

### Code Review Checklist
- [ ] Follows established coding standards
- [ ] Includes appropriate tests
- [ ] Updates relevant documentation
- [ ] Maintains security best practices
- [ ] Optimizes for Windows development environment

---

**Next Steps**: Start with [SETUP.md](SETUP.md) for complete development environment configuration, then proceed to [ARCHITECTURE.md](ARCHITECTURE.md) for system understanding.
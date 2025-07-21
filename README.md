# PCDS2030 Dashboard - Forestry Sector Management System

[![CI Workflow](https://github.com/Gridiffin/pcds2030db_fork/actions/workflows/ci.yml/badge.svg)](https://github.com/Gridiffin/pcds2030db_fork/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%20%7C%208.2-blue.svg)](https://php.net)
[![Node.js Version](https://img.shields.io/badge/Node.js-18%20%7C%2020-green.svg)](https://nodejs.org)

A comprehensive web-based dashboard system for managing forestry sector programs and outcomes under the Sarawak Ministry of Natural Resources and Urban Development. This system replaces traditional Excel-based reporting with a streamlined, modern web solution.

## 🏛️ Project Overview

The PCDS2030 Dashboard is currently in **production use** by the Sarawak government, specifically designed for the **Forestry sector** and its three key agencies:
- **Forestry Department (FDS)**
- **Sarawak Forestry Corporation (SFC)**  
- **Sarawak Timber Industry Development Corporation (STIDC)**

The system transforms quarterly reporting from manual Excel processes to an automated, web-based solution with real-time dashboards, data visualization, and automated report generation.

## 🎯 Key Features

### For Agency Users
- **Program Management**: Create, update, and track forestry programs with targets and achievements
- **Real-time Dashboards**: Visual analytics showing program progress, submission status, and sector outcomes
- **Quarterly/Half-yearly Submissions**: Submit program data during active reporting periods
- **Outcomes Management**: Manage sector-specific outcomes with flexible JSON data storage
- **Progress Tracking**: Monitor program ratings (On Track, Delayed, Target Achieved, Not Started)
- **Interactive Charts**: Chart.js powered visualizations for program status distribution

### For Admin Users
- **Multi-Agency Oversight**: View and manage programs across all forestry agencies
- **Reporting Period Management**: Create and manage quarterly reporting cycles
- **User Management**: Manage agency users and permissions
- **Report Generation**: Generate PowerPoint reports automatically
- **Audit System**: Comprehensive audit logging for all user activities
- **Program Assignment**: Assign programs to specific agencies
- **Data Analytics**: Advanced statistics and sector-wide performance metrics

### Technical Features
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5
- **Real-time Updates**: AJAX-powered dynamic content loading
- **Flexible Data Storage**: JSON-based content storage for evolving requirements
- **Period-based Filtering**: Historical data access across different reporting periods
- **Export Capabilities**: PPTX and PDF report generation
- **Audit Trail**: Complete activity logging with IP tracking and timestamps

## 🛠️ Technology Stack

### Backend
- **PHP 8.x**: Server-side logic and business rules
- **MySQL**: Database management with optimized queries
- **Apache**: Web server (XAMPP development environment)

### Frontend
- **HTML5/CSS3**: Modern semantic markup and styling
- **JavaScript (ES6+)**: Interactive functionality and AJAX
- **Bootstrap 5**: Responsive UI framework
- **Chart.js**: Data visualization and analytics
- **FontAwesome**: Icon library

### Development Tools
- **XAMPP/Laragon**: Local development environment
- **cPanel**: Production hosting platform
- **Git**: Version control system

### Testing & CI/CD
- **PHPUnit**: Backend PHP testing framework
- **Jest**: Frontend JavaScript testing framework
- **GitHub Actions**: Automated CI/CD pipeline
- **Vite**: Asset bundling and build optimization

## 🧪 Automated Testing

The project includes comprehensive automated testing covering both frontend and backend:

### Test Coverage
- **Frontend Tests**: 17/17 Jest tests (100% pass rate)
- **Backend Tests**: 15/15 PHPUnit tests (100% pass rate)
- **Total Coverage**: 32 automated tests across the full stack

### Continuous Integration
GitHub Actions automatically runs on every push and pull request:
- ✅ Multi-environment testing (PHP 8.1/8.2, Node.js 18/20)
- ✅ Code quality checks and syntax validation
- ✅ Security vulnerability scanning
- ✅ Asset building and optimization
- ✅ Documentation compliance verification

### Running Tests Locally
```bash
# Frontend JavaScript tests
npm test

# Backend PHP tests  
vendor/bin/phpunit

# Build production assets
npm run build
```

## 📊 System Architecture

### Database Design
The system uses a robust relational database structure with key entities:

- **Users**: Agency and admin user accounts with role-based access
- **Programs**: Core forestry initiatives with flexible JSON content storage
- **Program Submissions**: Quarterly progress reports and status updates
- **Reporting Periods**: Quarterly cycles with open/closed status management
- **Sectors**: Organizational structure (currently focused on Forestry)
- **Agency Groups**: FDS, SFC, and STIDC organizational structure
- **Outcomes Data**: Sector-specific outcomes with JSON flexibility
- **Audit Logs**: Comprehensive activity tracking for security and compliance
- **Reports**: Generated PowerPoint and PDF report management

### Key Workflows

1. **Quarterly/Half-yearly Reporting Cycle**
   - Admin creates/opens reporting periods
   - Agencies submit program progress data
   - Real-time dashboard updates reflect submission status
   - Admin generates consolidated reports

2. **Program Management**
   - Agencies create and manage their programs
   - Admin can assign programs to specific agencies
   - Progress tracking with rating system
   - Historical data preservation across periods

3. **Outcomes Management**
   - Flexible outcome metrics creation
   - JSON-based data storage for adaptability
   - Multi-agency collaboration on sector outcomes
   - Version history tracking for all changes

## 🚀 Current Status

- **Status**: ✅ **Production Ready & In Use**
- **Environment**: Sarawak Government Production
- **Focus**: Forestry Sector Implementation
- **Users**: Ministry of Natural Resources and Urban Development + 3 Forestry Agencies
- **Access**: Restricted to sarawakforestry.com domain

## 📸 Screenshots

*[Screenshots will be added here to showcase the dashboard interface, charts, and reporting features]*

## 🏗️ Project Structure

```
pcds2030_dashboard/
├── app/
│   ├── api/                    # API endpoints for data operations
│   ├── config/                 # Application configuration
│   ├── controllers/            # MVC controllers
│   ├── database/              # Database schema and migrations
│   ├── lib/                   # Core business logic libraries
│   │   ├── admin/             # Admin-specific functions
│   │   ├── agencies/          # Agency-specific functions
│   │   └── utilities/         # Shared utility functions
│   └── views/                 # User interface templates
│       ├── admin/             # Admin dashboard and management
│       ├── agency/            # Agency dashboard and forms
│       └── layouts/           # Shared layout templates
├── assets/
│   ├── css/                   # Stylesheets (organized by component)
│   ├── js/                    # JavaScript files (organized by feature)
│   ├── images/                # Project images and icons
│   └── fonts/                 # Web fonts
├── documentation/             # Technical documentation
└── scripts/                   # Maintenance and utility scripts
```

## 🔧 Key Features Deep Dive

### Advanced Dashboard Analytics
- **Multi-dimensional Filtering**: Filter by program type, status, date ranges
- **Real-time Statistics**: Live updating cards showing submission progress
- **Interactive Charts**: Doughnut charts showing program rating distribution
- **Period Comparison**: Historical data analysis across reporting periods

### Flexible Data Management
- **JSON Content Storage**: Adaptable program data structure without schema changes
- **Audit Trail**: Complete activity logging with user actions and timestamps
- **Version Control**: Historical tracking of all data changes
- **Bulk Operations**: Efficient handling of multiple program submissions

### Report Generation
- **Automated PPTX Generation**: Template-based PowerPoint report creation
- **Custom Filtering**: Generate reports for specific periods, agencies, or programs
- **Template Management**: Customizable report templates for different audiences

### Security & Compliance
- **Role-based Access Control**: Admin and agency user roles with specific permissions
- **Session Management**: Secure session handling with timeout controls
- **SQL Injection Prevention**: Parameterized queries throughout the application
- **Activity Logging**: Comprehensive audit trail for compliance requirements

## 🌲 Forestry Sector Focus

The current implementation is specifically tailored for forestry management:

- **Timber Export Tracking**: Monitor timber export values and volumes
- **Forest Conservation**: Track protected areas and biodiversity programs
- **Sustainability Metrics**: Monitor sustainable forest management practices
- **Certification Progress**: Track FMU and FPMU certification status
- **Degraded Area Restoration**: Monitor forest restoration initiatives


## 📝 License

This project is a government system developed for the Sarawak Ministry of Natural Resources and Urban Development. 

## 🤝 Project Information

- **Type**: Government Digital Transformation Project
- **Client**: Sarawak Ministry of Natural Resources and Urban Development
- **Sector**: Forestry and Natural Resources Management
- **Status**: Production Deployment (Restricted Access)
- **Domain**: sarawakforestry.com (Government Domain)

---

**Note**: This system is currently in production use by the Sarawak government and access is restricted to authorized personnel within the sarawakforestry.com domain. The system has successfully replaced traditional Excel-based reporting workflows with a modern, efficient web-based solution.

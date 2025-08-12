# PCDS 2030 Dashboard - Modernized PHP Project Structure

## Overview

This document outlines the recommended project structure for the modernized PCDS 2030 Dashboard using PHP with Alpine.js and Tailwind CSS. The structure is designed for simplicity, maintainability, and cPanel hosting compatibility while providing a modern user experience.

## Project Root Structure

```
pcds2030-dashboard-modernized/
├── .htaccess                     # Apache configuration and URL rewriting
├── .env                          # Environment variables (not committed to git)
├── .env.example                  # Environment variables template
├── .gitignore                    # Git ignore patterns
├── README.md                     # Project documentation
├── composer.json                 # PHP dependencies (optional)
├── package.json                  # Frontend build tools (optional)
├── tailwind.config.js           # Tailwind CSS configuration
├── app/                         # Application core
├── public/                      # Web-accessible directory
├── database/                    # Database scripts and migrations
├── scripts/                     # Build and deployment scripts
├── storage/                     # File uploads and logs
└── docs/                        # Additional documentation
```

## Application Structure (`app/`)

```
app/
├── config/                      # Configuration files
│   ├── app.php                 # Main application configuration
│   ├── database.php            # Database configuration
│   └── routes.php              # Route definitions
├── controllers/                # Request handlers
│   ├── BaseController.php      # Base controller with common methods
│   ├── AuthController.php      # Authentication handling
│   ├── AdminController.php     # Admin functionality
│   ├── AgencyController.php    # Agency functionality
│   └── ApiController.php       # API endpoints
├── models/                     # Data access layer
│   ├── Database.php            # Database connection and query builder
│   ├── User.php                # User model
│   ├── Agency.php              # Agency model
│   ├── Program.php             # Program model
│   ├── Submission.php          # Submission model
│   └── AuditLog.php            # Audit logging model
├── services/                   # Business logic layer
│   ├── AuthService.php         # Authentication service
│   ├── ProgramService.php      # Program business logic
│   ├── SubmissionService.php   # Submission workflow
│   ├── ReportService.php       # Report generation
│   ├── NotificationService.php # Notification handling
│   └── FileService.php         # File upload/download
├── middleware/                 # Request middleware
│   ├── AuthMiddleware.php      # Authentication checks
│   ├── RoleMiddleware.php      # Role-based access control
│   └── CSRFMiddleware.php      # CSRF protection
├── helpers/                    # Utility functions
│   ├── functions.php           # Global helper functions
│   ├── validation.php          # Form validation helpers
│   └── formatting.php          # Data formatting utilities
├── views/                      # PHP templates
│   ├── layouts/                # Base layouts
│   │   ├── app.php            # Main application layout
│   │   ├── admin.php          # Admin layout
│   │   ├── agency.php         # Agency layout
│   │   └── auth.php           # Authentication layout
│   ├── components/             # Reusable component templates
│   │   ├── navigation/         # Navigation components
│   │   │   ├── admin-nav.php
│   │   │   ├── agency-nav.php
│   │   │   └── breadcrumb.php
│   │   ├── forms/              # Form components
│   │   │   ├── input.php
│   │   │   ├── select.php
│   │   │   ├── textarea.php
│   │   │   └── file-upload.php
│   │   ├── cards/              # Card components
│   │   │   ├── stat-card.php
│   │   │   ├── program-card.php
│   │   │   └── info-card.php
│   │   ├── tables/             # Table components
│   │   │   ├── data-table.php
│   │   │   ├── pagination.php
│   │   │   └── filters.php
│   │   └── modals/             # Modal components
│   │       ├── confirm-modal.php
│   │       ├── form-modal.php
│   │       └── info-modal.php
│   ├── admin/                  # Admin interface views
│   │   ├── dashboard.php
│   │   ├── programs/
│   │   │   ├── index.php
│   │   │   ├── view.php
│   │   │   ├── edit.php
│   │   │   └── create.php
│   │   ├── users/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   └── reports/
│   │       ├── index.php
│   │       └── generate.php
│   ├── agency/                 # Agency interface views
│   │   ├── dashboard.php
│   │   ├── programs/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   ├── edit.php
│   │   │   └── view.php
│   │   ├── submissions/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   └── profile.php
│   └── auth/                   # Authentication views
│       ├── login.php
│       └── logout.php
└── cache/                      # View cache (if implemented)
```

## Public Directory (`public/`)

```
public/
├── index.php                   # Front controller (entry point)
├── .htaccess                   # Web server configuration
├── assets/                     # Static assets
│   ├── css/                    # Stylesheets
│   │   ├── app.css            # Main compiled CSS (Tailwind)
│   │   ├── components.css     # Custom component styles
│   │   └── print.css          # Print styles
│   ├── js/                     # JavaScript files
│   │   ├── alpine.min.js      # Alpine.js library
│   │   ├── chart.min.js       # Chart.js library
│   │   ├── app.js             # Main application JS
│   │   ├── components/        # Alpine.js components
│   │   │   ├── program-manager.js
│   │   │   ├── data-table.js
│   │   │   ├── form-handler.js
│   │   │   └── chart-renderer.js
│   │   └── pages/             # Page-specific JavaScript
│   │       ├── admin-dashboard.js
│   │       ├── agency-dashboard.js
│   │       ├── program-form.js
│   │       └── report-generator.js
│   ├── images/                 # Static images
│   │   ├── logo.png
│   │   ├── sarawak-crest.png
│   │   ├── forest-icon.png
│   │   └── backgrounds/
│   ├── fonts/                  # Web fonts
│   │   └── nunito/
│   └── icons/                  # SVG icons
│       └── feather/
└── uploads/                    # User-uploaded files
    ├── programs/
    │   └── attachments/
    └── profiles/
        └── avatars/
```

## Configuration Files

### 1. Application Configuration

```php
<?php
// app/config/app.php

return [
    'name' => 'PCDS 2030 Dashboard',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'timezone' => 'Asia/Kuching',
    
    // Security
    'session_lifetime' => 120, // minutes
    'csrf_protection' => true,
    
    // File uploads
    'max_upload_size' => '10M',
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png'],
    
    // Pagination
    'items_per_page' => 20,
];
```

### 2. Database Configuration

```php
<?php
// app/config/database.php

return [
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_DATABASE'] ?? 'pcds2030_db',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
```

### 3. Front Controller

```php
<?php
// public/index.php

// Load configuration
require_once '../app/config/app.php';
require_once '../app/helpers/functions.php';

// Start session
session_start();

// Simple routing
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($request_uri, PHP_URL_PATH);

// Basic routing logic
switch ($path) {
    case '/':
    case '/dashboard':
        require '../app/controllers/DashboardController.php';
        break;
    case '/login':
        require '../app/controllers/AuthController.php';
        break;
    case '/admin/programs':
        require '../app/controllers/AdminController.php';
        break;
    // ... more routes
    default:
        http_response_code(404);
        require '../app/views/errors/404.php';
        break;
}
```

## Component Architecture

### 1. Alpine.js Component Example

```javascript
// public/assets/js/components/program-manager.js

function programManager() {
    return {
        programs: [],
        loading: false,
        search: '',
        filters: {
            status: '',
            agency: '',
            rating: ''
        },
        
        async init() {
            await this.loadPrograms();
        },
        
        async loadPrograms() {
            this.loading = true;
            try {
                const response = await fetch('/api/programs?' + this.buildQuery());
                this.programs = await response.json();
            } catch (error) {
                console.error('Error loading programs:', error);
            } finally {
                this.loading = false;
            }
        },
        
        buildQuery() {
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.filters.status) params.append('status', this.filters.status);
            if (this.filters.agency) params.append('agency', this.filters.agency);
            if (this.filters.rating) params.append('rating', this.filters.rating);
            return params.toString();
        },
        
        get filteredPrograms() {
            return this.programs.filter(program => {
                return program.program_name.toLowerCase()
                    .includes(this.search.toLowerCase());
            });
        }
    }
}
```

### 2. PHP Component Template

```php
<?php
// app/views/components/cards/program-card.php

function renderProgramCard($program, $options = []) {
    $canEdit = $options['can_edit'] ?? false;
    $canDelete = $options['can_delete'] ?? false;
    ?>
    
    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6">
        <!-- Program Header -->
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    <?= htmlspecialchars($program['program_name']) ?>
                </h3>
                <p class="text-sm text-gray-600">
                    <?= htmlspecialchars($program['program_number']) ?>
                </p>
            </div>
            <?php renderRatingBadge($program['rating']) ?>
        </div>
        
        <!-- Program Details -->
        <div class="space-y-2 mb-4">
            <div class="flex items-center text-sm text-gray-600">
                <span class="font-medium">Agency:</span>
                <span class="ml-2"><?= htmlspecialchars($program['agency_name']) ?></span>
            </div>
            <div class="flex items-center text-sm text-gray-600">
                <span class="font-medium">Status:</span>
                <span class="ml-2"><?php renderStatusBadge($program['status']) ?></span>
            </div>
        </div>
        
        <!-- Description -->
        <?php if (!empty($program['program_description'])): ?>
        <p class="text-sm text-gray-700 mb-4 line-clamp-3">
            <?= htmlspecialchars($program['program_description']) ?>
        </p>
        <?php endif; ?>
        
        <!-- Actions -->
        <div class="flex gap-2 pt-4 border-t border-gray-200">
            <a href="/programs/view/<?= $program['program_id'] ?>" 
               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View Details
            </a>
            <?php if ($canEdit): ?>
            <a href="/programs/edit/<?= $program['program_id'] ?>" 
               class="text-green-600 hover:text-green-800 text-sm font-medium">
                Edit
            </a>
            <?php endif; ?>
            <?php if ($canDelete): ?>
            <button @click="confirmDelete(<?= $program['program_id'] ?>)" 
                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                Delete
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
}
?>
```

## Build and Development Tools

### 1. Tailwind CSS Configuration

```javascript
// tailwind.config.js
module.exports = {
  content: [
    './app/views/**/*.php',
    './public/assets/js/**/*.js'
  ],
  theme: {
    extend: {
      colors: {
        forest: {
          50: '#f0f7ed',
          100: '#dcebd4',
          500: '#4a7c59',
          600: '#2d5016',
          700: '#1a3009',
        }
      },
      fontFamily: {
        sans: ['Nunito', 'system-ui', 'sans-serif'],
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ]
}
```

### 2. Package.json (Optional Build Tools)

```json
{
  "name": "pcds2030-dashboard",
  "version": "1.0.0",
  "description": "Modernized PCDS 2030 Dashboard",
  "scripts": {
    "build-css": "tailwindcss -i ./public/assets/css/input.css -o ./public/assets/css/app.css --watch",
    "build-css-prod": "tailwindcss -i ./public/assets/css/input.css -o ./public/assets/css/app.css --minify",
    "watch": "npm run build-css",
    "build": "npm run build-css-prod"
  },
  "devDependencies": {
    "tailwindcss": "^3.3.0",
    "@tailwindcss/forms": "^0.5.3",
    "@tailwindcss/typography": "^0.5.9"
  }
}
```

## Deployment for cPanel

### 1. File Structure for cPanel

```
public_html/                    # cPanel public directory
├── (contents of public/ folder)
├── app/                        # Application files (protected by .htaccess)
├── database/
├── storage/
└── .htaccess                   # Deny access to non-public files
```

### 2. .htaccess Configuration

```apache
# public_html/.htaccess

# Deny access to application files
<Directory "app">
    Require all denied
</Directory>

<Directory "database">
    Require all denied
</Directory>

<Directory "storage">
    Require all denied
</Directory>

# Redirect all requests to index.php
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

This structure provides a clean, maintainable PHP application with modern frontend capabilities while remaining compatible with cPanel hosting and being manageable by a single developer.
# PCDS 2030 Dashboard - PHP Modernization Guide

## Overview

This guide provides a step-by-step approach to modernizing the PCDS 2030 Dashboard using PHP with Alpine.js and Tailwind CSS. This approach maintains PHP backend while dramatically improving the user experience with modern frontend technologies that are easy to learn and maintain.

## Why This Approach?

### ✅ Benefits
- **Familiar Technology**: Keep using PHP (your expertise)
- **Easy Learning Curve**: Alpine.js is like jQuery but modern
- **cPanel Compatible**: No Node.js deployment complexity
- **Single Developer Friendly**: Manageable by one person
- **Gradual Migration**: Update page by page
- **Modern UX**: Reactive interfaces without complexity

### ❌ What We're Avoiding
- Complex React ecosystem
- TypeScript learning curve
- Build tool complexity
- Node.js deployment requirements
- State management libraries
- Testing framework overhead

## Migration Strategy: Gradual Enhancement

Instead of a complete rewrite, we'll enhance the existing system incrementally:

```
┌─────────────────────────────────────────────────────────────────┐
│                    Migration Approach                          │
├─────────────────────────────────────────────────────────────────┤
│ Phase 1: Foundation (Week 1-2)                                │
│ ├── Add Alpine.js and Tailwind CSS                            │
│ ├── Create basic component templates                          │
│ └── Modernize one page (login or dashboard)                   │
├─────────────────────────────────────────────────────────────────┤
│ Phase 2: Core Pages (Week 3-6)                                │
│ ├── Modernize dashboard pages                                 │
│ ├── Update program management interfaces                      │
│ └── Enhance form interactions                                 │
├─────────────────────────────────────────────────────────────────┤
│ Phase 3: Advanced Features (Week 7-8)                         │
│ ├── Interactive tables with search/filter                     │
│ ├── File upload improvements                                  │
│ └── Report generation enhancements                            │
└─────────────────────────────────────────────────────────────────┘
```

## Phase 1: Foundation Setup (Week 1-2)

### Day 1-2: Add Modern Frontend Stack

#### 1. Install Alpine.js and Tailwind CSS

**Option A: CDN (Quick Start)**
```html
<!-- Add to your main layout header -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PCDS 2030 Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js (for existing charts) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
```

**Option B: Local Files (Production)**
```bash
# Download files to public/assets/js/
curl -o public/assets/js/alpine.min.js https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js
curl -o public/assets/js/chart.min.js https://cdn.jsdelivr.net/npm/chart.js/dist/chart.min.js

# Install Tailwind CLI (optional, for custom builds)
npm install -D tailwindcss
npx tailwindcss init
```

#### 2. Create Base Layout Template

```php
<?php
// app/views/layouts/app.php
function renderLayout($title, $content, $scripts = []) {
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> - PCDS 2030 Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="/assets/js/alpine.min.js"></script>
    <script src="/assets/js/chart.min.js"></script>
    
    <!-- Custom CSS -->
    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<body class="h-full" x-data="app()">
    <!-- Navigation -->
    <?php if (isLoggedIn()): ?>
        <?php include 'components/navigation.php'; ?>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="<?= isLoggedIn() ? 'ml-64' : '' ?>">
        <?= $content ?>
    </main>
    
    <!-- Loading Overlay -->
    <div x-show="loading" x-cloak 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-forest-600"></div>
            <span>Loading...</span>
        </div>
    </div>
    
    <!-- Toast Notifications -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <span x-text="toast.message"></span>
    </div>
    
    <!-- Global Alpine.js Data -->
    <script>
        function app() {
            return {
                loading: false,
                toast: {
                    show: false,
                    message: ''
                },
                
                showToast(message) {
                    this.toast.message = message;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3000);
                },
                
                async fetchData(url, options = {}) {
                    this.loading = true;
                    try {
                        const response = await fetch(url, {
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            ...options
                        });
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        return await response.json();
                    } catch (error) {
                        console.error('Fetch error:', error);
                        this.showToast('An error occurred. Please try again.');
                        throw error;
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    
    <!-- Page-specific scripts -->
    <?php foreach ($scripts as $script): ?>
        <script src="<?= $script ?>"></script>
    <?php endforeach; ?>
</body>
</html>
<?php
}
?>
```

### Day 3-4: Create First Modern Page

#### Modern Login Page Example

```php
<?php
// app/views/auth/login.php

$content = '
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-forest-600 to-forest-700 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8" x-data="loginForm()">
        <!-- Header -->
        <div class="text-center">
            <img class="mx-auto h-20 w-auto" src="/assets/images/sarawak-crest.png" alt="Sarawak Crest">
            <h2 class="mt-6 text-3xl font-bold text-white">
                PCDS 2030 Dashboard
            </h2>
            <p class="mt-2 text-sm text-forest-100">
                Sign in to your account
            </p>
        </div>
        
        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="mt-8 space-y-6">
            <div class="rounded-md shadow-sm space-y-4">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-forest-100 mb-2">
                        Username
                    </label>
                    <input 
                        id="username" 
                        name="username" 
                        type="text" 
                        required 
                        x-model="form.username"
                        :disabled="loading"
                        class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-forest-500 focus:border-forest-500 focus:z-10 disabled:opacity-50 disabled:cursor-not-allowed"
                        placeholder="Enter your username"
                    >
                    <p x-show="errors.username" x-text="errors.username" class="mt-1 text-sm text-red-300"></p>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-forest-100 mb-2">
                        Password
                    </label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        required 
                        x-model="form.password"
                        :disabled="loading"
                        class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-forest-500 focus:border-forest-500 focus:z-10 disabled:opacity-50 disabled:cursor-not-allowed"
                        placeholder="Enter your password"
                    >
                    <p x-show="errors.password" x-text="errors.password" class="mt-1 text-sm text-red-300"></p>
                </div>
            </div>
            
            <!-- Error Message -->
            <div x-show="errorMessage" x-cloak class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <p x-text="errorMessage"></p>
            </div>
            
            <!-- Submit Button -->
            <div>
                <button 
                    type="submit" 
                    :disabled="loading"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-forest-600 hover:bg-forest-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-forest-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span x-show="!loading">Sign in</span>
                    <span x-show="loading" class="flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                        Signing in...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function loginForm() {
    return {
        loading: false,
        form: {
            username: "",
            password: ""
        },
        errors: {},
        errorMessage: "",
        
        async handleLogin() {
            // Reset errors
            this.errors = {};
            this.errorMessage = "";
            
            // Validate form
            if (!this.form.username) {
                this.errors.username = "Username is required";
                return;
            }
            
            if (!this.form.password) {
                this.errors.password = "Password is required";
                return;
            }
            
            this.loading = true;
            
            try {
                const response = await fetch("/api/auth/login", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Success - redirect to dashboard
                    window.location.href = data.redirect || "/dashboard";
                } else {
                    // Show error message
                    this.errorMessage = data.message || "Login failed. Please try again.";
                }
            } catch (error) {
                console.error("Login error:", error);
                this.errorMessage = "Network error. Please check your connection and try again.";
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
';

// Render the page
renderLayout('Login', $content);
?>
```

#### Corresponding PHP Login Handler

```php
<?php
// app/controllers/AuthController.php

class AuthController {
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Show login form
            include '../app/views/auth/login.php';
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle login submission
            $this->handleLoginSubmission();
            return;
        }
    }
    
    private function handleLoginSubmission() {
        // Check if it's an AJAX request
        $isAjax = $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        // Validate credentials
        $user = $this->validateCredentials($username, $password);
        
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['agency_id'] = $user['agency_id'];
            
            // Determine redirect URL based on role
            $redirectUrl = $user['role'] === 'admin' ? '/admin/dashboard' : '/agency/dashboard';
            
            if ($isAjax) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => $redirectUrl
                ]);
            } else {
                header("Location: $redirectUrl");
            }
        } else {
            if ($isAjax) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid username or password'
                ]);
            } else {
                // Redirect back with error
                header('Location: /login?error=1');
            }
        }
    }
    
    private function validateCredentials($username, $password) {
        // Your existing login validation logic
        // Return user array if valid, false if invalid
        
        $db = new Database();
        $stmt = $db->prepare("
            SELECT u.*, a.agency_name 
            FROM users u 
            JOIN agency a ON u.agency_id = a.agency_id 
            WHERE u.username = ? AND u.is_active = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['pw'])) {
            return $user;
        }
        
        return false;
    }
}
?>
```

## Phase 2: Modernize Core Pages (Week 3-6)

### Modern Dashboard with Alpine.js

```php
<?php
// app/views/agency/dashboard.php

$content = '
<div class="p-6" x-data="agencyDashboard()">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Agency Dashboard</h1>
        <p class="mt-2 text-gray-600">Overview of your programs and submissions</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <template x-for="stat in stats" :key="stat.key">
            <div class="bg-white overflow-hidden shadow-md rounded-lg hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-forest-100 rounded-md flex items-center justify-center">
                                <span x-html="stat.icon" class="text-forest-600"></span>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate" x-text="stat.label"></dt>
                                <dd class="text-2xl font-semibold text-gray-900" x-text="stat.value"></dd>
                                <dd class="flex items-center text-sm" :class="stat.change >= 0 ? \'text-green-600\' : \'text-red-600\'">
                                    <span x-text="stat.change >= 0 ? \'↑\' : \'↓\'"></span>
                                    <span x-text="Math.abs(stat.change) + \'%\'"></span>
                                    <span class="ml-1 text-gray-500">vs last quarter</span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    
    <!-- Charts and Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Programs Chart -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Program Status Distribution</h3>
            <canvas id="programStatusChart" width="400" height="200"></canvas>
        </div>
        
        <!-- Recent Submissions -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Submissions</h3>
            <div class="space-y-3">
                <template x-for="submission in recentSubmissions" :key="submission.submission_id">
                    <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-b-0">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900" x-text="submission.program_name"></p>
                            <p class="text-xs text-gray-500" x-text="submission.period_name"></p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span :class="submission.is_submitted ? \'bg-green-100 text-green-800\' : \'bg-yellow-100 text-yellow-800\'"
                                  class="px-2 py-1 text-xs rounded-full font-medium">
                                <span x-text="submission.is_submitted ? \'Submitted\' : \'Draft\'"></span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
            <div class="mt-4">
                <a href="/agency/submissions" class="text-sm text-forest-600 hover:text-forest-700 font-medium">
                    View all submissions →
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function agencyDashboard() {
    return {
        stats: [
            { key: "programs", label: "Active Programs", value: "0", change: 0, icon: "📊" },
            { key: "submissions", label: "Submissions", value: "0", change: 0, icon: "📋" },
            { key: "targets", label: "Targets Met", value: "0", change: 0, icon: "🎯" },
            { key: "rating", label: "Avg Rating", value: "0", change: 0, icon: "⭐" }
        ],
        recentSubmissions: [],
        
        async init() {
            await this.loadDashboardData();
            this.initializeChart();
        },
        
        async loadDashboardData() {
            try {
                const data = await this.$store.app.fetchData("/api/agency/dashboard-data");
                
                this.stats = [
                    { 
                        key: "programs", 
                        label: "Active Programs", 
                        value: data.programs_count, 
                        change: data.programs_change || 0, 
                        icon: "📊" 
                    },
                    { 
                        key: "submissions", 
                        label: "Submissions", 
                        value: data.submissions_count, 
                        change: data.submissions_change || 0, 
                        icon: "📋" 
                    },
                    { 
                        key: "targets", 
                        label: "Targets Met", 
                        value: data.targets_met + "%", 
                        change: data.targets_change || 0, 
                        icon: "🎯" 
                    },
                    { 
                        key: "rating", 
                        label: "Avg Rating", 
                        value: data.avg_rating, 
                        change: 0, 
                        icon: "⭐" 
                    }
                ];
                
                this.recentSubmissions = data.recent_submissions || [];
            } catch (error) {
                console.error("Error loading dashboard data:", error);
            }
        },
        
        initializeChart() {
            const ctx = document.getElementById("programStatusChart");
            new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Active", "On Hold", "Completed", "Delayed"],
                    datasets: [{
                        data: [12, 3, 8, 2],
                        backgroundColor: [
                            "#4a7c59",  // forest-500
                            "#fbbf24",  // yellow-400
                            "#10b981",  // green-500
                            "#ef4444"   // red-500
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom"
                        }
                    }
                }
            });
        }
    }
}
</script>
';

renderLayout('Dashboard', $content, ['/assets/js/pages/agency-dashboard.js']);
?>
```

## Phase 3: Interactive Components

### Modern Data Table with Search and Filters

```php
<?php
// app/views/components/data-table.php

function renderDataTable($config) {
    $tableId = $config['id'] ?? 'data-table';
    $apiEndpoint = $config['endpoint'] ?? '';
    $columns = $config['columns'] ?? [];
    $filters = $config['filters'] ?? [];
    $actions = $config['actions'] ?? [];
    ?>
    
    <div x-data="dataTable('<?= $tableId ?>', '<?= $apiEndpoint ?>')" class="bg-white shadow-md rounded-lg overflow-hidden">
        <!-- Header with Search and Filters -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0 md:space-x-4">
                <!-- Search -->
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <input type="text" 
                               x-model="search" 
                               @input.debounce.500ms="loadData()"
                               placeholder="Search..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-forest-500 focus:border-forest-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <?php if (!empty($filters)): ?>
                <div class="flex flex-wrap gap-3">
                    <?php foreach ($filters as $filter): ?>
                    <div>
                        <select x-model="filters.<?= $filter['key'] ?>" 
                                @change="loadData()" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-forest-500 focus:border-forest-500">
                            <option value=""><?= $filter['placeholder'] ?? 'All' ?></option>
                            <?php foreach ($filter['options'] as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <?php if (!empty($actions)): ?>
                <div class="flex space-x-2">
                    <?php foreach ($actions as $action): ?>
                    <button @click="<?= $action['click'] ?? '' ?>" 
                            class="<?= $action['class'] ?? 'bg-forest-600 text-white hover:bg-forest-700' ?> px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        <?= $action['label'] ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <?php foreach ($columns as $column): ?>
                        <th scope="col" 
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            @click="sort('<?= $column['key'] ?>')">
                            <div class="flex items-center space-x-1">
                                <span><?= $column['label'] ?></span>
                                <svg x-show="sortField === '<?= $column['key'] ?>' && sortDirection === 'asc'" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h7a1 1 0 100-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3z"></path>
                                </svg>
                                <svg x-show="sortField === '<?= $column['key'] ?>' && sortDirection === 'desc'" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM6 11a1 1 0 100 2h8a1 1 0 100-2H6z"></path>
                                </svg>
                            </div>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Loading State -->
                    <tr x-show="loading">
                        <td colspan="<?= count($columns) ?>" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex justify-center items-center space-x-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-forest-600"></div>
                                <span>Loading...</span>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Data Rows -->
                    <template x-for="row in data" :key="row.id">
                        <tr class="hover:bg-gray-50 cursor-pointer" @click="selectRow(row)">
                            <?php foreach ($columns as $column): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if (isset($column['render'])): ?>
                                    <?= $column['render'] ?>
                                <?php else: ?>
                                    <span x-text="row.<?= $column['key'] ?>"></span>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    </template>
                    
                    <!-- No Data State -->
                    <tr x-show="!loading && data.length === 0">
                        <td colspan="<?= count($columns) ?>" class="px-6 py-4 text-center text-gray-500">
                            No data available
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div x-show="pagination.totalPages > 1" class="bg-gray-50 px-6 py-3 flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="previousPage()" 
                        :disabled="pagination.currentPage === 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <button @click="nextPage()" 
                        :disabled="pagination.currentPage === pagination.totalPages"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <button @click="previousPage()" 
                                :disabled="pagination.currentPage === 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            Previous
                        </button>
                        
                        <template x-for="page in paginationRange" :key="page">
                            <button @click="goToPage(page)" 
                                    :class="page === pagination.currentPage ? 'bg-forest-50 border-forest-500 text-forest-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                <span x-text="page"></span>
                            </button>
                        </template>
                        
                        <button @click="nextPage()" 
                                :disabled="pagination.currentPage === pagination.totalPages"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            Next
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function dataTable(id, endpoint) {
        return {
            id: id,
            endpoint: endpoint,
            data: [],
            loading: false,
            search: '',
            filters: {},
            sortField: '',
            sortDirection: 'asc',
            pagination: {
                currentPage: 1,
                perPage: 20,
                totalPages: 1,
                total: 0,
                from: 0,
                to: 0
            },
            
            async init() {
                await this.loadData();
            },
            
            async loadData() {
                this.loading = true;
                
                try {
                    const params = new URLSearchParams({
                        page: this.pagination.currentPage,
                        per_page: this.pagination.perPage,
                        search: this.search,
                        sort_field: this.sortField,
                        sort_direction: this.sortDirection,
                        ...this.filters
                    });
                    
                    const response = await fetch(`${this.endpoint}?${params}`);
                    const result = await response.json();
                    
                    this.data = result.data || [];
                    this.pagination = {
                        ...this.pagination,
                        ...result.pagination
                    };
                } catch (error) {
                    console.error('Error loading data:', error);
                    this.$store.app.showToast('Error loading data');
                } finally {
                    this.loading = false;
                }
            },
            
            sort(field) {
                if (this.sortField === field) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortField = field;
                    this.sortDirection = 'asc';
                }
                this.pagination.currentPage = 1;
                this.loadData();
            },
            
            previousPage() {
                if (this.pagination.currentPage > 1) {
                    this.pagination.currentPage--;
                    this.loadData();
                }
            },
            
            nextPage() {
                if (this.pagination.currentPage < this.pagination.totalPages) {
                    this.pagination.currentPage++;
                    this.loadData();
                }
            },
            
            goToPage(page) {
                this.pagination.currentPage = page;
                this.loadData();
            },
            
            get paginationRange() {
                const range = [];
                const current = this.pagination.currentPage;
                const total = this.pagination.totalPages;
                
                let start = Math.max(1, current - 2);
                let end = Math.min(total, current + 2);
                
                for (let i = start; i <= end; i++) {
                    range.push(i);
                }
                
                return range;
            },
            
            selectRow(row) {
                this.$dispatch('row-selected', { row: row });
            }
        }
    }
    </script>
    
    <?php
}
?>
```

## Implementation Benefits

### What You'll Achieve

1. **Modern User Experience**: Smooth interactions, real-time updates, responsive design
2. **Easy Maintenance**: PHP backend you understand + simple Alpine.js components
3. **Performance**: No heavy JavaScript frameworks, optimized loading
4. **Mobile Friendly**: Responsive design that works on all devices
5. **Progressive Enhancement**: Works without JavaScript, better with it

### Learning Path

1. **Week 1**: Basic Alpine.js syntax (similar to Vue.js but simpler)
2. **Week 2**: Tailwind CSS utility classes (like Bootstrap but more flexible)
3. **Week 3**: Component patterns and data binding
4. **Week 4**: API integration and form handling

This approach gives you a modern, maintainable system while keeping the complexity manageable for a single developer with PHP expertise.
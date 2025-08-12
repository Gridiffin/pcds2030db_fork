# PCDS 2030 Dashboard - Modern UI Components with Alpine.js + Tailwind

## Overview

The modernized PCDS 2030 Dashboard features a component-based UI architecture using Alpine.js for interactive functionality and Tailwind CSS for styling. The system maintains the existing forestry theme while providing modern, responsive interfaces that work seamlessly on all devices.

## Design System Foundation

### Tailwind CSS Forest Theme Configuration

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
          200: '#bbd4a9',
          300: '#92b67a',
          400: '#6b9b52',
          500: '#4a7c59',    // Primary forest green
          600: '#2d5016',    // Deep forest green  
          700: '#1a3009',    // Dark forest green
          800: '#0f1a04',
          900: '#080d02',
        },
        earth: {
          500: '#8b4513',    // Earth brown
        },
        status: {
          success: '#28a745',
          warning: '#ffc107', 
          danger: '#dc3545',
          info: '#17a2b8',
        }
      },
      fontFamily: {
        sans: ['Nunito', 'system-ui', 'sans-serif'],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
      },
      animation: {
        'fade-in': 'fadeIn 0.2s ease-in-out',
        'slide-in': 'slideIn 0.3s ease-out', 
        'scale-in': 'scaleIn 0.2s ease-out',
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ]
}
```

### Design Tokens with Tailwind Classes

```html
<!-- Typography Scale (Tailwind utilities) -->
<p class="text-xs">     <!-- 12px -->
<p class="text-sm">     <!-- 14px -->
<p class="text-base">   <!-- 16px -->
<p class="text-lg">     <!-- 18px -->
<p class="text-xl">     <!-- 20px -->
<p class="text-2xl">    <!-- 24px -->
<p class="text-3xl">    <!-- 30px -->

<!-- Font Weights -->
<p class="font-normal">    <!-- 400 -->
<p class="font-medium">    <!-- 500 -->
<p class="font-semibold">  <!-- 600 -->
<p class="font-bold">      <!-- 700 -->

<!-- Spacing Scale (Tailwind utilities) -->
<div class="p-1">    <!-- 4px -->
<div class="p-2">    <!-- 8px -->
<div class="p-3">    <!-- 12px -->
<div class="p-4">    <!-- 16px -->
<div class="p-6">    <!-- 24px -->
<div class="p-8">    <!-- 32px -->
<div class="p-12">   <!-- 48px -->
```

## Modern Component Architecture

### 1. Layout Components with Alpine.js + Tailwind

#### A. Page Header Component

**PHP Template with Alpine.js**:
```php
<?php
// app/views/components/layout/page-header.php

function renderPageHeader($title, $subtitle = '', $actions = '', $variant = 'green') {
    $gradientClass = match($variant) {
        'green' => 'bg-gradient-to-r from-forest-600 to-forest-500',
        'blue' => 'bg-gradient-to-r from-blue-600 to-blue-500',
        'orange' => 'bg-gradient-to-r from-orange-600 to-orange-500',
        default => 'bg-gradient-to-r from-forest-600 to-forest-500'
    };
?>

<header class="<?= $gradientClass ?> text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <!-- Title Section -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl sm:text-3xl font-bold">
                    <?= htmlspecialchars($title) ?>
                </h1>
                <?php if ($subtitle): ?>
                <p class="mt-2 text-lg opacity-90">
                    <?= htmlspecialchars($subtitle) ?>
                </p>
                <?php endif; ?>
            </div>
            
            <!-- Actions Section -->
            <?php if ($actions): ?>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                <?= $actions ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php
}

// Usage example:
$actions = '
<button @click="openCreateModal()" 
        class="bg-white text-forest-600 hover:bg-forest-50 font-medium py-2 px-4 rounded-md transition-colors">
    <span class="flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Create New
    </span>
</button>';

renderPageHeader('Program Management', 'Manage your forestry programs and submissions', $actions);
?>
```

#### B. Responsive Grid Layout with Tailwind

**PHP Component with Tailwind Grid**:
```php
<?php
// app/views/components/layout/responsive-grid.php

function renderResponsiveGrid($items, $columns = 3) {
    $gridCols = match($columns) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 lg:grid-cols-2', 
        3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
    };
?>

<div class="grid <?= $gridCols ?> gap-6 p-6">
    <?php foreach ($items as $item): ?>
    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6 
                <?= $item['span'] ?? '' ?> <?= $item['class'] ?? '' ?>">
        <?= $item['content'] ?>
    </div>
    <?php endforeach; ?>
</div>

<?php
}

// Usage example:
$dashboardItems = [
    [
        'content' => '<h3 class="text-lg font-semibold mb-2">Statistics Overview</h3>
                     <p class="text-gray-600">Key metrics and numbers</p>',
        'span' => 'md:col-span-2' // Span 2 columns on medium screens and up
    ],
    [
        'content' => '<h3 class="text-lg font-semibold mb-2">Recent Activity</h3>
                     <p class="text-gray-600">Latest updates</p>'
    ],
    [
        'content' => '<h3 class="text-lg font-semibold mb-2">Quick Actions</h3>
                     <p class="text-gray-600">Common tasks</p>'
    ],
    [
        'content' => '<h3 class="text-lg font-semibold mb-2">Charts</h3>
                     <canvas id="myChart"></canvas>',
        'span' => 'lg:col-span-3' // Full width on large screens
    ]
];

renderResponsiveGrid($dashboardItems, 3);
?>
```

### 2. Interactive Components with Alpine.js + Tailwind

#### A. Statistics Cards with Alpine.js

**PHP Component with Alpine.js**:
```php
<?php
// app/views/components/cards/stat-card.php

function renderStatCard($value, $label, $change = null, $icon = '', $color = 'forest') {
    $borderColor = match($color) {
        'forest' => 'border-l-forest-600',
        'blue' => 'border-l-blue-600', 
        'green' => 'border-l-green-600',
        'orange' => 'border-l-orange-600',
        'red' => 'border-l-red-600',
        default => 'border-l-forest-600'
    };
    
    $textColor = match($color) {
        'forest' => 'text-forest-600',
        'blue' => 'text-blue-600',
        'green' => 'text-green-600', 
        'orange' => 'text-orange-600',
        'red' => 'text-red-600',
        default => 'text-forest-600'
    };
?>

<div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border-l-4 <?= $borderColor ?>">
    <!-- Icon and Value Row -->
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1">
            <div class="text-3xl font-bold <?= $textColor ?> leading-none mb-2">
                <?= htmlspecialchars($value) ?>
            </div>
            <div class="text-sm text-gray-600 uppercase tracking-wide font-medium">
                <?= htmlspecialchars($label) ?>
            </div>
        </div>
        <?php if ($icon): ?>
        <div class="ml-4 <?= $textColor ?> opacity-60">
            <?= $icon ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Change Indicator -->
    <?php if ($change): ?>
    <div class="mt-3 flex items-center text-sm">
        <?php 
        $changeColor = $change['value'] >= 0 ? 'text-green-600' : 'text-red-600';
        $changeIcon = $change['value'] >= 0 ? '↑' : '↓';
        ?>
        <span class="<?= $changeColor ?> font-medium">
            <?= $changeIcon ?> <?= abs($change['value']) ?>%
        </span>
        <span class="text-gray-500 ml-1">
            vs <?= htmlspecialchars($change['period'] ?? 'last period') ?>
        </span>
    </div>
    <?php endif; ?>
</div>

<?php
}

// Usage examples:
renderStatCard(
    '152', 
    'Active Programs', 
    ['value' => 12, 'period' => 'last quarter'],
    '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
    </svg>'
);
?>
```

#### B. Interactive Form Component with Alpine.js

**Modern Form with Validation**:
```php
<?php
// app/views/components/forms/program-form.php
?>

<form x-data="programForm()" @submit.prevent="submitForm()" class="space-y-6">
    <!-- Program Name -->
    <div>
        <label for="program_name" class="block text-sm font-medium text-gray-700 mb-2">
            Program Name <span class="text-red-500">*</span>
        </label>
        <input 
            type="text" 
            id="program_name" 
            x-model="form.program_name"
            :class="errors.program_name ? 'border-red-500' : 'border-gray-300'"
            class="block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-forest-500 focus:border-forest-500"
            placeholder="Enter program name"
            required
        >
        <p x-show="errors.program_name" x-text="errors.program_name" class="mt-1 text-sm text-red-600"></p>
    </div>

    <!-- Program Description -->
    <div>
        <label for="program_description" class="block text-sm font-medium text-gray-700 mb-2">
            Description
        </label>
        <textarea 
            id="program_description" 
            x-model="form.program_description"
            rows="4"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-forest-500 focus:border-forest-500"
            placeholder="Describe the program objectives and activities"
        ></textarea>
    </div>

    <!-- Status Selection -->
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
        <select 
            id="status" 
            x-model="form.status"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-forest-500 focus:border-forest-500"
        >
            <option value="active">Active</option>
            <option value="on_hold">On Hold</option>
            <option value="completed">Completed</option>
            <option value="delayed">Delayed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <!-- Submit Buttons -->
    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
        <button 
            type="button" 
            @click="resetForm()"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-forest-500"
        >
            Reset
        </button>
        <button 
            type="submit" 
            :disabled="loading || !isFormValid"
            :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-forest-600 hover:bg-forest-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-forest-500"
        >
            <span x-show="!loading">Save Program</span>
            <span x-show="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
            </span>
        </button>
    </div>
</form>

<script>
function programForm() {
    return {
        loading: false,
        form: {
            program_name: '',
            program_description: '',
            status: 'active'
        },
        errors: {},
        
        get isFormValid() {
            return this.form.program_name.length > 0;
        },
        
        async submitForm() {
            // Reset errors
            this.errors = {};
            
            // Validate
            if (!this.form.program_name) {
                this.errors.program_name = 'Program name is required';
                return;
            }
            
            this.loading = true;
            
            try {
                const response = await fetch('/api/programs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Success - redirect or update UI
                    this.$store.app.showToast('Program saved successfully!');
                    window.location.href = '/programs';
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.$store.app.showToast('Error saving program. Please try again.');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                this.$store.app.showToast('Network error. Please try again.');
            } finally {
                this.loading = false;
            }
        },
        
        resetForm() {
            this.form = {
                program_name: '',
                program_description: '',
                status: 'active'
            };
            this.errors = {};
        }
    }
}
</script>
```

## Component Usage Patterns

### 1. PHP Template Integration
```php
// In your main page template
include 'app/views/components/layout/page-header.php';
include 'app/views/components/cards/stat-card.php';
include 'app/views/components/forms/program-form.php';

// Render components
renderPageHeader('Dashboard', 'Welcome to your dashboard');
renderStatCard('25', 'Total Programs', ['value' => 5, 'period' => 'this month']);
```

### 2. Alpine.js Integration Pattern
```javascript
// Global Alpine.js store for shared state
document.addEventListener('alpine:init', () => {
    Alpine.store('app', {
        user: <?= json_encode($_SESSION['user'] ?? null) ?>,
        permissions: <?= json_encode($_SESSION['permissions'] ?? []) ?>,
        loading: false,
        
        showToast(message, type = 'success') {
            // Toast notification logic
        },
        
        async fetchData(url, options = {}) {
            this.loading = true;
            try {
                const response = await fetch(url, options);
                return await response.json();
            } finally {
                this.loading = false;
            }
        }
    });
});
```

## Responsive Design with Tailwind

### Mobile-First Approach
```html
<!-- Responsive grid that adapts to screen size -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <!-- Cards adapt automatically -->
</div>

<!-- Responsive navigation -->
<nav class="hidden md:flex md:space-x-6">
    <!-- Desktop navigation -->
</nav>
<div class="md:hidden">
    <!-- Mobile navigation -->
</div>
```

## Performance Considerations

### Optimized Loading
- **Alpine.js**: ~15KB minified + gzipped
- **Tailwind CSS**: Only includes used classes (~20KB typical)
- **No Build Step**: Optional build process for production optimization
- **Progressive Enhancement**: Works without JavaScript

This modern component architecture provides:
- ✅ **Easy Learning**: PHP developers can quickly understand Alpine.js
- ✅ **Modern UX**: Reactive interfaces without complexity
- ✅ **Mobile Responsive**: Works perfectly on all devices  
- ✅ **Maintainable**: Simple, clear code structure
- ✅ **Performance**: Lightweight and fast
- ✅ **cPanel Compatible**: No deployment complexity

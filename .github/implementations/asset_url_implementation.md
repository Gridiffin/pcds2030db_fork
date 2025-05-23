# Asset URL Implementation Plan

This document outlines the plan for updating asset references in the application to use the new `asset_url()` function.

## Files to Update

### Primary Layout Files

1. `app/views/layouts/header.php` - Contains CSS references
2. `app/views/layouts/footer.php` - Contains JavaScript references

### CSS References Types

- Main CSS files linked directly in layouts
- CSS files imported within stylesheets
- Inline style references to background images

### JavaScript References Types

- Core libraries in layouts
- Page-specific scripts
- Dynamic script loading

## Implementation Steps

1. [ ] Update CSS references in header.php
2. [ ] Update JS references in footer.php
3. [ ] Update image references in views
4. [ ] Update dynamic script loading

## CSS References Update Example

```php
<!-- Before -->
<link rel="stylesheet" href="assets/css/main.css">

<!-- After -->
<link rel="stylesheet" href="<?php echo asset_url('css', 'main.css'); ?>">
```

## JavaScript References Update Example

```php
<!-- Before -->
<script src="assets/js/main.js"></script>

<!-- After -->
<script src="<?php echo asset_url('js', 'main.js'); ?>"></script>
```

## Image References Update Example

```php
<!-- Before -->
<img src="assets/images/logo.png" alt="Logo">

<!-- After -->
<img src="<?php echo asset_url('images', 'logo.png'); ?>" alt="Logo">
```

## Background Image Update Example

```css
/* Before */
.hero {
    background-image: url('../images/background.jpg');
}

/* After - In PHP files */
.hero {
    background-image: url('<?php echo asset_url('images', 'background.jpg'); ?>');
}

/* After - In CSS files */
/* Use APP_URL as a prefix in the build process or keep relative paths in CSS files */
```

## Testing Plan

1. Check that all CSS files load without 404 errors
2. Verify all images load correctly
3. Test JavaScript functionality to ensure all scripts load correctly
4. Test on different browsers to ensure compatibility

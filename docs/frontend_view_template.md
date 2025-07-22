# Frontend View Template: Best Practices

This document provides the standard template and best practices for creating new frontend view files in this system. It ensures consistency, maintainability, and a sticky footer across all pages.

---

## 1. Base Layout Structure (`app/views/layouts/base.php`)

> **Do not include navbar or footer directly in your view files.**
> The base layout handles global structure, asset imports, navbar, and footer.

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... meta, title, asset imports ... -->
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/admin_nav.php'; // or agency_nav.php, etc. ?>

    <main class="flex-fill">
        <?php
        // Main content injected here
        if (isset($contentFile)) {
            include $contentFile;
        }
        ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
```

**Key Points:**
- `body` uses `d-flex flex-column min-vh-100` (Bootstrap 5) for full-height flexbox.
- `main` uses `flex-fill` to take up all available space, pushing the footer down.
- Navbar and footer are included as partials.

---

## 2. View File Template (For Developers)

> **Only output main content in your view file.**
> Do not include `<html>`, `<head>`, `<body>`, navbar, or footer.

```php
<?php
// Set these variables at the top of your view file
$pageTitle = "Page Title Here";
$cssBundle = "your_bundle_name"; // Optional: for page-specific CSS
$jsBundle = "your_bundle_name";  // Optional: for page-specific JS
// $contentFile = __FILE__; // Only if using partial injection
?>

<!-- Main Content (do not include navbar/footer here) -->
<div class="container-fluid py-4">
    <!-- Alerts/Messages (optional) -->
    <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-info" role="alert">
            <?= htmlspecialchars($alertMessage) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><?= htmlspecialchars($pageTitle) ?></h1>
            <p>Page-specific content goes here.</p>
        </div>
    </div>
</div>
```

**Tips:**
- Use Bootstrap 5 grid and utility classes for layout.
- Use PHP variables for dynamic content.
- Placeholders for alerts/messages and main content.
- Optionally include partials for repeated UI.

---

## 3. Footer Partial Example (`app/views/layouts/footer.php`)

```php
<footer class="mt-auto bg-light py-3 border-top">
    <div class="container text-center">
        <small>&copy; <?= date('Y') ?> Your Organization. All rights reserved.</small>
    </div>
</footer>
```
- `mt-auto` ensures the footer is pushed to the bottom by flexbox.
- `bg-light`, `py-3`, `border-top` for styling.

---

## 4. Best Practices

- **Never include navbar or footer in individual view files.**
- **Always use the base layout for global structure and asset imports.**
- **Use Bootstrap 5 flexbox classes (`d-flex flex-column min-vh-100`) on `<body>` for sticky footer.**
- **Main content should be wrapped in a container (e.g., `container-fluid py-4`).**
- **Use partials for repeated UI elements (alerts, headers, etc.).**
- **Set `$pageTitle`, `$cssBundle`, `$jsBundle` at the top of your view file as needed.**

---

## 5. Troubleshooting Sticky Footer Issues

- **Footer floats in the middle of the page?**
  - Ensure `<body>` has `d-flex flex-column min-vh-100`.
  - Ensure `<main>` has `flex-fill`.
  - Footer partial should use `mt-auto`.
  - Do not set fixed heights on containers that could break flexbox.
  - Avoid unnecessary wrappers between `<body>` and `<main>`/footer.

- **Content overflows the footer?**
  - Use `container` or `container-fluid` for main content.
  - Let content grow naturally; flexbox will push the footer down.

- **Navbar or footer not showing?**
  - Check that the base layout includes the correct partials.
  - Ensure `$contentFile` is set correctly in your view file.

---

## 6. Example: How It All Fits Together

**View File:**
```php
<?php
$pageTitle = "Dashboard";
$cssBundle = "dashboard";
$jsBundle = "dashboard";
// $contentFile = __FILE__;
?>
<div class="container-fluid py-4">
    <h1 class="mb-4">Dashboard</h1>
    <p>Welcome to the dashboard!</p>
</div>
```

**Base Layout:**
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... -->
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/admin_nav.php'; ?>
    <main class="flex-fill">
        <?php if (isset($contentFile)) include $contentFile; ?>
    </main>
    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
```

**Footer Partial:**
```php
<footer class="mt-auto bg-light py-3 border-top">
    <div class="container text-center">
        <small>&copy; <?= date('Y') ?> Your Organization. All rights reserved.</small>
    </div>
</footer>
```

---

**By following this template, all new frontend pages will have a consistent structure, and the footer will always stay at the bottom of the page.** 
# Project Structure Best Practices

This document outlines the recommended file structure and best practices for organizing a large, complex PHP web project. Use this as a reference for future refactoring and scaling.

---

## 🗂️ Recommended File Structure

```
app/
  controllers/
    DashboardController.php
    AuthController.php
    ...
  views/
    admin/
      login.php
      dashboard.php
      partials/
        login_form.php
        login_header.php
        login_footer.php
        login_messages.php
        login_social_buttons.php
        ...
    agency/
      ...
    shared/
      header.php
      footer.php
      navbar.php
      ...
assets/
  css/
    admin/
      login.css                <-- Main login CSS (imports below)
      login/
        form.css
        messages.css
        social-buttons.css
        container.css
      dashboard.css
      ...
    agency/
      ...
    base/
      reset.css
      typography.css
      utilities.css
    main.css                   <-- Imports all main/section CSS
  js/
    admin/
      login.js                <-- DOM/AJAX for login page
      loginLogic.js           <-- Pure functions for login (testable)
      dashboard.js
      ...
    agency/
      ...
    shared/
      utils.js
      ...
  images/
    ...
  fonts/
    ...
docs/
  bugs_tracker.md
  system_context.md
  ...
mock_features/
  ...                         <-- For isolated experiments/demos
tests/
  admin/
    loginLogic.test.js        <-- Jest or PHPUnit tests for logic
    ...
  agency/
    ...
  shared/
    ...
vendor/
  ...                         <-- Composer dependencies (if using)
uploads/
  ...                         <-- User-uploaded files
.github/
  implementations/
    ...                       <-- Implementation plans/to-do docs
index.php
login.php
logout.php
README.md
package.json
composer.json
project_structure_best_practices.md
```

---

## 📦 Key Principles

- **Separation of Concerns:** Views, logic, assets, and tests are all separated.
- **Modularization:** Each feature/page has its own folder for partials, JS, and CSS.
- **Testability:** Pure logic is separated for easy unit testing.
- **Scalability:** Easy to add new features, pages, or modules without clutter.
- **Maintainability:** Each file/folder has a clear, focused purpose.

---

## 📝 Example: Login Feature

| File/Folder                                 | Purpose                                 |
|---------------------------------------------|-----------------------------------------|
| app/views/admin/login.php                   | Main login page, includes partials      |
| app/views/admin/partials/login_form.php     | Login form HTML                         |
| app/views/admin/partials/login_header.php   | Head/meta/CSS includes                  |
| app/views/admin/partials/login_footer.php   | Footer                                  |
| assets/css/admin/login.css                  | Main login CSS (imports below)          |
| assets/css/admin/login/form.css             | Form-specific styles                    |
| assets/css/admin/login/messages.css         | Message styles                          |
| assets/js/admin/login.js                    | DOM, events, AJAX                       |
| assets/js/admin/loginLogic.js               | Pure, testable logic                    |
| tests/admin/loginLogic.test.js              | Unit tests for loginLogic.js            |

---

## 🛠️ How to Expand

- For each new feature/page, create:
  - A main view file (e.g., dashboard.php)
  - A partials/ folder for reusable components
  - A CSS file (and subfolder if needed)
  - A JS file (split logic/DOM as needed)
  - A test file for pure logic

---

## 🏆 Why This Works

- Easy to find and update code.
- Reduces bugs and merge conflicts.
- Supports team development and future scaling.
- Keeps code DRY (Don’t Repeat Yourself) and organized.

---

## 📚 How To: Example for Each File Type

### 1. View File (login.php)
```php
<?php include __DIR__ . '/partials/login_header.php'; ?>
<div class="login-container">
    <?php include __DIR__ . '/partials/login_messages.php'; ?>
    <?php include __DIR__ . '/partials/login_form.php'; ?>
    <?php include __DIR__ . '/partials/login_social_buttons.php'; ?>
</div>
<?php include __DIR__ . '/partials/login_footer.php'; ?>
```

### 2. Partial File (login_form.php)
```php
<form id="loginForm" method="post">
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
</form>
```

### 3. CSS File (form.css)
```css
.login-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.login-form__input {
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}
```

### 4. JS File (loginLogic.js)
```js
function validateEmail(email) {
    return /^[^@]+@[^@]+\.[^@]+$/.test(email);
}
function validatePassword(password) {
    return password.length >= 8;
}
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { validateEmail, validatePassword };
} else {
    window.validateEmail = validateEmail;
    window.validatePassword = validatePassword;
}
```

### 5. JS File (login.js)
```js
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = form.email.value;
        const password = form.password.value;
        if (!window.validateEmail(email)) {
            // Show error
            return;
        }
        if (!window.validatePassword(password)) {
            // Show error
            return;
        }
        // Proceed with AJAX login
    });
});
```

### 6. Test File (loginLogic.test.js)
```js
const { validateEmail, validatePassword } = require('../../assets/js/admin/loginLogic');

test('valid email passes', () => {
    expect(validateEmail('test@example.com')).toBe(true);
});
test('invalid email fails', () => {
    expect(validateEmail('bademail')).toBe(false);
});
test('password length', () => {
    expect(validatePassword('12345678')).toBe(true);
    expect(validatePassword('short')).toBe(false);
});
```

---

## 🔗 How to Import Modular CSS and JS into Centralized/Main Files

### CSS: Using @import in Main CSS Files

**assets/css/admin/login.css**
```css
@import 'login/form.css';
@import 'login/messages.css';
@import 'login/social-buttons.css';
@import 'login/container.css';
```

**assets/css/main.css** (for global/section-wide imports)
```css
@import '../admin/login.css';
@import '../admin/dashboard.css';
@import '../agency/agency.css';
/* ...other imports... */
```

### JS: Including Modular JS in the Main View

**app/views/admin/login.php**
```php
<!-- In the <head> or before </body> -->
<link rel="stylesheet" href="/assets/css/admin/login.css">
<script src="/assets/js/admin/loginLogic.js"></script>
<script src="/assets/js/admin/login.js"></script>
```

- Always include pure logic JS (e.g., loginLogic.js) before the DOM/interaction JS (e.g., login.js).
- For shared/global JS, include in your main layout/header/footer as needed.

### PHP: Including Partials in Main View Files

**app/views/admin/login.php**
```php
<?php include __DIR__ . '/partials/login_header.php'; ?>
<!-- ... -->
<?php include __DIR__ . '/partials/login_form.php'; ?>
<!-- ... -->
<?php include __DIR__ . '/partials/login_footer.php'; ?>
```

---

## ⚡ Using Vite for Modern Asset Bundling (Recommended)

Vite is a fast, modern build tool that makes it easy to bundle modular JS and CSS for each page or section. This is the recommended approach for scalable, maintainable asset management in large projects.

### Step-by-Step: Setting Up Vite

1. **Install Vite**
   ```sh
   npm install --save-dev vite
   ```

2. **Example Project Structure**
   ```
   assets/
     js/
       admin/
         login.js        // Entry point for login page
         dashboard.js    // Entry point for dashboard
     css/
       admin/
         login.css
         dashboard.css
   ```

3. **Create vite.config.js**
   ```js
   import { defineConfig } from 'vite';

   export default defineConfig({
     build: {
       rollupOptions: {
         input: {
           login: 'assets/js/admin/login.js',
           dashboard: 'assets/js/admin/dashboard.js',
           // Add more entry points as needed
         },
         output: {
           entryFileNames: 'js/[name].bundle.js',
           assetFileNames: 'css/[name].bundle.css',
         }
       },
       outDir: 'dist',
       emptyOutDir: true,
     },
     css: {
       preprocessorOptions: {
         // If you use SCSS, LESS, etc.
       }
     }
   });
   ```

4. **Import CSS in Entry JS Files**
   ```js
   // assets/js/admin/login.js
   import '../../css/admin/login.css';
   // ...other JS code
   ```

5. **Add Scripts to package.json**
   ```json
   "scripts": {
     "dev": "vite",
     "build": "vite build"
   }
   ```

6. **Build for Production**
   ```sh
   npm run build
   ```
   Bundled files will be output to the `dist/` directory.

7. **Reference Bundles in PHP**
   ```php
   <link rel="stylesheet" href="/dist/js/login.bundle.css">
   <script src="/dist/js/login.bundle.js"></script>
   ```

8. **Development Mode (Optional)**
   ```sh
   npm run dev
   ```
   Vite will serve assets locally with hot reload.

---

### 🏆 Why Use Vite?
- Loads only the code needed for each page (better performance)
- Modular development, efficient production bundles
- Supports modern JS/ES6 features, fast builds, and hot reload
- Scales easily as your project grows

---

**For large, modular projects, using Vite (or a similar build tool) is highly recommended for maintainable and high-performance asset management.**

---

## 🧩 Using a Base Layout Template with Dynamic Asset Injection

When using per-page JS/CSS bundles (e.g., with Vite), you should avoid duplicating HTML structure and asset references across all pages. Instead, use a base layout (template) file and inject the correct assets dynamically for each page.

### 1. Create a Base Layout (e.g., layouts/base.php)
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'My App') ?></title>
    <?php if (!empty($cssBundle)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($cssBundle) ?>">
    <?php endif; ?>
    <!-- Add global CSS here if needed -->
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>

    <main>
        <?php include $contentFile; ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>

    <?php if (!empty($jsBundle)): ?>
        <script src="<?= htmlspecialchars($jsBundle) ?>"></script>
    <?php endif; ?>
    <!-- Add global JS here if needed -->
</body>
</html>
```

### 2. Use the Template in Each Page
```php
<?php
// admin/login.php
$pageTitle = 'Login';
$cssBundle = '/dist/js/login.bundle.css';
$jsBundle  = '/dist/js/login.bundle.js';
$contentFile = __DIR__ . '/partials/login_form.php';
include __DIR__ . '/../layouts/base.php';
```

- Each page sets the `$cssBundle` and `$jsBundle` variables to the correct bundle for that page.
- The base layout takes care of including the right assets and structure.

### 3. Benefits
- No code duplication: All common HTML is in one place.
- Easy to update: Change the layout or add global assets in one file.
- Flexible: Each page can specify its own bundles, title, and content.

### 4. Optional: Asset Path Helpers
- You can write a helper function to map page names to bundle paths, or scan the `dist/` directory for available bundles to avoid hardcoding.

---

**This approach keeps your code DRY, works perfectly with Vite or any build tool, and is the standard way to handle per-page assets in PHP projects.**

---

## 🚫 Database Operations in Views: Why You Should Avoid It

### Never perform raw database operations (queries, SQL, DB access) directly in your view files.

### Why?
- **Separation of Concerns:**
  - Views should only display data.
  - Controllers/handlers fetch/process data.
  - Models/helpers handle all database operations.
- **Security:**
  - Reduces risk of SQL injection and data leaks.
  - Easier to sanitize and validate input.
- **Maintainability:**
  - Easier to update, debug, and refactor code.
  - UI changes don't affect DB logic and vice versa.
- **Testability:**
  - Easier to test and mock data when DB logic is separated.

### 🟢 Best Practice: Use Controller/Model Pattern

#### Example Flow
1. **Controller/Handler:** Receives request, fetches data from the database (using a model/helper), and passes it to the view.
2. **View:** Receives only the data it needs and renders the HTML.

#### Example

**Controller (admin/login.php or a controller class):**
```php
require_once __DIR__ . '/../lib/UserModel.php';

$userModel = new UserModel();
$user = $userModel->getUserById($id);

$pageTitle = 'User Profile';
$contentFile = __DIR__ . '/partials/user_profile.php';
include __DIR__ . '/../layouts/base.php';
```

**Model (lib/UserModel.php):**
```php
class UserModel {
    public function getUserById($id) {
        // Use prepared statements!
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
```

**View (partials/user_profile.php):**
```php
<!-- Only display data, no DB code here! -->
<h1><?= htmlspecialchars($user['name']) ?></h1>
<p>Email: <?= htmlspecialchars($user['email']) ?></p>
```

#### 🚫 What NOT to Do
```php
<!-- BAD: Direct DB query in view -->
<?php
$result = $db->query('SELECT * FROM users');
while ($row = $result->fetch_assoc()) {
    echo $row['name'];
}
?>
```

### 🏆 Summary
- Never put DB queries or logic in your view files.
- Always fetch/process data in controllers or models, then pass it to the view for rendering.
- This keeps your code secure, maintainable, and professional.

---

## 🌀 Hybrid Approach: Controllers/Views + RESTful APIs

This project uses (or is recommended to use) a **hybrid approach** for backend/frontend communication and rendering. This means:
- Use traditional controllers and views for main page rendering (server-side HTML).
- Use RESTful APIs for dynamic, asynchronous data fetching and updates (AJAX, frontend JS, etc.).

### How It Works
- **Controllers/Views:**
  - Handle full page loads, form submissions, and server-rendered HTML.
  - Good for SEO, simple pages, and initial loads.
- **RESTful APIs:**
  - Handle AJAX requests, dynamic tables, live updates, and frontend JS interactions.
  - Return JSON or other data formats, not HTML.

### Pros
- Best of both worlds: SEO-friendly, simple server-rendered pages + dynamic, modern user experience.
- Easier to migrate/modernize legacy PHP apps.
- Can incrementally add more dynamic features without rewriting everything.
- APIs can be reused by mobile apps or other systems.

### Cons
- Slightly more complex than pure traditional or pure API-first approaches.
- Need to maintain both views and API endpoints.

### Example
- **Page load:**
  - User visits `/admin/dashboard.php` → Controller loads data, renders HTML view.
- **Dynamic update:**
  - JS on the page calls `/api/get_dashboard_stats.php` via AJAX → API returns JSON → JS updates the page without reload.

### Summary Table
| Approach         | Backend Renders HTML? | Frontend Renders HTML? | Uses RESTful API? | Typical Use Case                |
|------------------|:--------------------:|:---------------------:|:-----------------:|---------------------------------|
| Traditional      | Yes                  | No                    | Sometimes         | Classic PHP sites, blogs        |
| API-First / SPA  | No                   | Yes                   | Yes (always)      | Modern apps, React/Vue/Angular  |
| Hybrid           | Yes                  | Yes (for dynamic)     | Yes (for AJAX)    | Most modern PHP web apps        |

---

**Note:** This file is now a general information dump for refactoring context and future reference. Add any new best practices, patterns, or explanations here as your project evolves. 

---

## 📁 What are lib and handlers Folders?

### lib Folder
- **Purpose:**
  - The `lib` (library) folder is for reusable code, helper functions, utility classes, and business logic that can be shared across your application.
- **What goes here?**
  - Database connection classes (e.g., `db_connect.php`)
  - Helper functions (e.g., `functions.php`, `asset_helpers.php`)
  - Business logic classes (e.g., `UserModel.php`, `admin_functions.php`)
  - Utility classes (e.g., for formatting, validation, etc.)
- **Not for:**
  - Controllers (which handle HTTP requests and responses)
  - Views (which render HTML)
- **Summary:** Think of `lib` as the “toolbox” for your app.

### handlers Folder
- **Purpose:**
  - The `handlers` folder is for scripts that process specific actions or requests, often related to form submissions, AJAX calls, or admin actions.
- **What goes here?**
  - Scripts that process POST/GET requests (e.g., `process_user.php`)
  - AJAX handlers (e.g., `get_user.php`)
  - Action-specific logic that doesn’t fit cleanly into a controller or model
- **Not for:**
  - General-purpose functions or classes (those go in `lib`)
  - Rendering full pages (those are controllers/views)
- **Summary:** Think of `handlers` as “action processors” for specific tasks.

### Are They Controllers?
- **lib:**
  - No, it’s not for controllers. It’s for reusable code and logic.
- **handlers:**
  - Not full controllers, but sometimes act as “mini-controllers” for specific AJAX or form actions.
  - In a more structured MVC app, you might move handler logic into controllers or API endpoints.

### Summary Table
| Folder     | Purpose/Contents                                  | Example Files                |
|------------|---------------------------------------------------|------------------------------|
| lib/       | Reusable code, helpers, business logic, utilities | db_connect.php, functions.php, UserModel.php |
| handlers/  | Action processors, AJAX/form handlers             | process_user.php, get_user.php |

### Best Practice
- Keep your business logic and helpers in `lib` for reusability.
- Use `handlers` for scripts that process specific actions, but consider moving to controllers or API endpoints as your app grows. 

---

## ⚡ AJAX: What, When, and How to Structure It

### What is AJAX?
- **AJAX** (Asynchronous JavaScript and XML) lets your web page send/receive data from the server **without reloading the whole page**.
- Most AJAX today uses JSON, not XML.

### When Should You Use AJAX?
- When you want to update part of a page (like a table, form, or notification) without a full page reload.
- For dynamic features: live search, filtering, submitting forms, loading more data, etc.
- For a smoother, faster user experience.

### How Should You Structure AJAX in Your Project?

#### a) Backend: Create API or Handler Endpoints
- Write a PHP file (in `app/api/`, `app/ajax/`, or `handlers/`) that receives AJAX requests and returns data (usually JSON).
- This file should NOT output HTML—just data.

**Example:**
`app/ajax/get_user_data.php`
```php
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/UserModel.php';

$userModel = new UserModel();
$user = $userModel->getUserById($_GET['id']);
echo json_encode($user);
```

#### b) Frontend: Write Modular JS for AJAX
- Use JavaScript (vanilla, jQuery, or fetch API) to send requests to your backend endpoint.
- Update the page with the response.

**Example (using fetch):**
```js
// assets/js/admin/user.js
function loadUserData(userId) {
    fetch(`/app/ajax/get_user_data.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('userName').textContent = data.name;
            // ...update other fields
        });
}
```

#### c) Where to Put AJAX Code?
- **Modular JS files**: Place AJAX code in a JS file specific to the page or feature (e.g., `user.js` for user page).
- **Shared AJAX helpers**: If you have common AJAX logic, put it in a shared JS file (e.g., `assets/js/ajax-helpers.js`).

#### d) Does Each Page Need Its Own AJAX?
- **Not necessarily.**
  - If a page has unique dynamic features, it should have its own JS file with AJAX code for those features.
  - If multiple pages share AJAX logic (like notifications, search, etc.), put that logic in a shared JS file and import it where needed.

### Example Structure
```
assets/
  js/
    admin/
      user.js           // AJAX for user page
      dashboard.js      // AJAX for dashboard page
    ajax-helpers.js     // Shared AJAX functions
app/
  ajax/
    get_user_data.php   // Endpoint for user data
    get_dashboard_stats.php // Endpoint for dashboard stats
```

### Summary Table
| File/Folder                  | Purpose                                 |
|------------------------------|-----------------------------------------|
| app/ajax/get_user_data.php   | Backend endpoint for AJAX (returns JSON)|
| assets/js/admin/user.js      | Page-specific JS with AJAX code         |
| assets/js/ajax-helpers.js    | Shared AJAX helper functions            |

### Best Practices
- **Keep AJAX endpoints separate from page-rendering PHP files.**
- **Return only data (JSON), not HTML, from AJAX endpoints.**
- **Keep JS modular: one file per page/feature, plus shared helpers.**
- **Use fetch API or a library (like jQuery) for AJAX calls.**
- **Handle errors gracefully in your JS.**

--- 
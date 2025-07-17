# Example Workflow: Implementing a Login Feature (Backend to Frontend)

This document demonstrates a best-practice workflow for implementing a login feature in a modular PHP project, following the hybrid approach and modern asset management.

---

## 🗂️ Example Project Structure for Login Feature

```
app/
  controllers/
    AuthController.php           # Handles login logic, form processing
  views/
    admin/
      login.php                 # Main login page (uses partials)
      partials/
        login_form.php          # Login form HTML
        login_messages.php      # Error/success messages
        login_header.php        # Head/meta/CSS includes
        login_footer.php        # Footer
  api/
    login.php                   # RESTful API endpoint for AJAX login
lib/
  UserModel.php                 # User DB logic (find by email, verify password)
assets/
  js/
    admin/
      login.js                  # Handles DOM, AJAX, uses loginLogic.js
      loginLogic.js             # Pure validation logic (testable)
  css/
    admin/
      login.css                 # Main login CSS (imports below)
      login/
        form.css
        messages.css
        container.css
  js/
    ajax-helpers.js             # Shared AJAX helpers (optional)
tests/
  admin/
    loginLogic.test.js          # Unit tests for loginLogic.js
```

---

## 1. **Backend: Model (lib/UserModel.php)**
```php
class UserModel {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function findByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password_hash']);
    }
}
```

---

## 2. **Backend: Controller (app/controllers/AuthController.php)**
```php
require_once __DIR__ . '/../../lib/UserModel.php';
require_once __DIR__ . '/../../lib/db_connect.php';

class AuthController {
    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new UserModel($db);
            $user = $userModel->findByEmail($_POST['email']);
            if ($user && $userModel->verifyPassword($user, $_POST['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: /admin/dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
        $pageTitle = 'Login';
        $contentFile = __DIR__ . '/../views/admin/partials/login_form.php';
        include __DIR__ . '/../views/layouts/base.php';
    }
}
```

---

## 3. **Backend: RESTful API Endpoint (app/api/login.php)**
```php
header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/UserModel.php';
require_once __DIR__ . '/../../lib/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userModel = new UserModel($db);
    $user = $userModel->findByEmail($data['email']);
    if ($user && $userModel->verifyPassword($user, $data['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
    exit;
}
echo json_encode(['success' => false, 'error' => 'Invalid request']);
```

---

## 4. **Frontend: View (app/views/admin/login.php)**
```php
<?php
$pageTitle = 'Login';
$cssBundle = '/dist/js/login.bundle.css';
$jsBundle  = '/dist/js/login.bundle.js';
$contentFile = __DIR__ . '/partials/login_form.php';
include __DIR__ . '/../layouts/base.php';
```

---

## 5. **Frontend: Partial (app/views/admin/partials/login_form.php)**
```php
<form id="loginForm" method="post">
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
    <div id="loginError"></div>
</form>
```

---

## 6. **Frontend: JS Logic (assets/js/admin/loginLogic.js)**
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

---

## 7. **Frontend: JS (assets/js/admin/login.js)**
```js
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = form.email.value;
        const password = form.password.value;
        if (!window.validateEmail(email)) {
            showError('Invalid email format');
            return;
        }
        if (!window.validatePassword(password)) {
            showError('Password must be at least 8 characters');
            return;
        }
        fetch('/app/api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/admin/dashboard.php';
            } else {
                showError(data.error || 'Login failed');
            }
        });
    });
    function showError(msg) {
        document.getElementById('loginError').textContent = msg;
    }
});
```

---

## 8. **Frontend: CSS (assets/css/admin/login.css & submodules)**
```css
/* login.css */
@import 'login/form.css';
@import 'login/messages.css';
@import 'login/container.css';
```

---

## 9. **Testing: Unit Test for Logic (tests/admin/loginLogic.test.js)**
```js
const { validateEmail, validatePassword } = require('../../../assets/js/admin/loginLogic');

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

## 📝 **Workflow Summary**

1. **Model**: Handles DB logic (find user, verify password).
2. **Controller**: Handles form POST, uses model, loads view.
3. **API**: Handles AJAX login, returns JSON.
4. **View**: Loads partials, sets up asset bundles.
5. **Partials**: Form, messages, etc.
6. **JS**: Handles form validation, AJAX, uses logic module.
7. **CSS**: Modular, imported into main login.css.
8. **Tests**: Unit tests for validation logic.

---

**This example demonstrates a clean, modular, and testable approach to implementing a login feature in a modern PHP project.** 
<form id="loginForm" method="post">
    <div class="form-group mb-3">
        <label for="username">Username</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
    </div>
    <div class="form-group mb-4">
        <label for="password">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            <span class="input-group-text toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                <i class="far fa-eye"></i>
            </span>
        </div>
    </div>
    <div id="loginError"></div>
    <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn btn-primary material-btn" id="loginBtn">
            <span class="login-text">Sign In</span>
            <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
        </button>
    </div>
</form> 
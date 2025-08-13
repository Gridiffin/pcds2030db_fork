<?php
/**
 * AJAX Helpers
 *
 * Lightweight utilities to standardize AJAX endpoints without forcing
 * response shapes immediately. Designed to be non-invasive so existing
 * endpoints can adopt incrementally.
 */

// Ensure no BOM/whitespace before output in including scripts

/**
 * Set JSON Content-Type header (idempotent).
 */
function ajax_set_json_header(): void {
    // Avoid duplicate headers; PHP allows multiple, but idempotent set is fine
    header('Content-Type: application/json; charset=UTF-8');
}

/**
 * Check if the current HTTP method is allowed.
 *
 * @param array $allowedMethods E.g., ['GET'], ['POST']
 * @return bool True if allowed, false otherwise
 */
function ajax_method_allowed(array $allowedMethods): bool {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    foreach ($allowedMethods as $allowed) {
        if (strcasecmp($method, $allowed) === 0) {
            return true;
        }
    }
    return false;
}

/**
 * Determine if the current user has one of the allowed roles.
 *
 * Fallbacks to reading $_SESSION['role'] to avoid hard dependencies
 * on project-specific helpers during progressive adoption.
 *
 * @param array $allowedRoles E.g., ['admin'], ['agency','focal','admin']
 * @return bool
 */
function ajax_user_has_role(array $allowedRoles): bool {
    if (!isset($_SESSION) || !isset($_SESSION['role'])) {
        return false;
    }
    $role = $_SESSION['role'];
    foreach ($allowedRoles as $allowed) {
        if (strcasecmp($role, $allowed) === 0) {
            return true;
        }
        // Special-case: agency pages often allow focal
        if ($allowed === 'agency' && strcasecmp($role, 'focal') === 0) {
            return true;
        }
    }
    return false;
}

/**
 * Validate that required parameters exist in the provided source array.
 *
 * @param array $paramNames Required parameter names
 * @param array|null $source Typically $_GET or $_POST; if null, defaults to $_REQUEST
 * @return array Missing parameter names (empty array if none missing)
 */
function ajax_missing_params(array $paramNames, ?array $source = null): array {
    $src = $source ?? $_REQUEST ?? [];
    $missing = [];
    foreach ($paramNames as $name) {
        if (!isset($src[$name]) || $src[$name] === '' || $src[$name] === null) {
            $missing[] = $name;
        }
    }
    return $missing;
}

/**
 * Convenience: parse JSON body into associative array. Returns empty array on failure.
 *
 * @return array
 */
function ajax_get_json_body(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Send a standardized JSON error (optional helper). Not mandatory to use.
 *
 * @param string $message
 * @param int $httpCode
 * @param array|null $details
 */
function ajax_send_error(string $message, int $httpCode = 400, ?array $details = null): void {
    ajax_set_json_header();
    http_response_code($httpCode);
    $payload = ['success' => false, 'error' => $message];
    if ($details !== null) {
        $payload['details'] = $details;
    }
    echo json_encode($payload);
}

/**
 * Send a standardized JSON success (optional helper). Not mandatory to use.
 *
 * @param array $data
 * @param string|null $message
 * @param int $httpCode
 */
function ajax_send_success(array $data = [], ?string $message = null, int $httpCode = 200): void {
    ajax_set_json_header();
    http_response_code($httpCode);
    $payload = ['success' => true, 'data' => $data];
    if ($message !== null) {
        $payload['message'] = $message;
    }
    echo json_encode($payload);
}

?>



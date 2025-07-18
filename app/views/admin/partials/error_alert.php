<?php
/**
 * Error Alert Partial
 * Usage: include this file and set $errorMessage, $errorVisible, $errorId (optional)
 * Example:
 *   $errorMessage = 'Something went wrong';
 *   $errorVisible = true;
 *   include __DIR__ . '/error_alert.php';
 */
if (!isset($errorMessage)) $errorMessage = 'An error occurred.';
if (!isset($errorVisible)) $errorVisible = false;
if (!isset($errorId)) $errorId = 'errorAlert';
?>
<div class="row mb-4<?= $errorVisible ? '' : ' d-none' ?>" id="<?= htmlspecialchars($errorId) ?>">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <span id="errorMessageContent"><?= htmlspecialchars($errorMessage) ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div> 
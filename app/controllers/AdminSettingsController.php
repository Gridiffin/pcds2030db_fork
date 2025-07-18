<?php
/**
 * AdminSettingsController
 * Handles logic for admin system settings (outcome creation toggle, etc.)
 */

require_once __DIR__ . '/../lib/db_connect.php';
require_once __DIR__ . '/../lib/session.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../lib/admins/settings.php';

class AdminSettingsController {
    public $message = '';
    public $messageType = '';
    public $allow_outcome_creation_enabled = false;

    public function __construct() {
        // Get current settings
        $this->allow_outcome_creation_enabled = get_outcome_creation_setting();
    }

    public function handlePost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // TODO: Add CSRF protection here
            if (isset($_POST['allow_outcome_creation'])) {
                $allow_outcome_creation = ($_POST['allow_outcome_creation'] === '1');
                $result = update_outcome_creation_setting($allow_outcome_creation);
                if (isset($result['error'])) {
                    $this->message = $result['error'];
                    $this->messageType = 'danger';
                } elseif (isset($result['warning'])) {
                    $this->message = $result['warning'];
                    $this->messageType = 'warning';
                } elseif (isset($result['success'])) {
                    $this->message = $result['message'];
                    $this->messageType = 'success';
                }
                // Refresh setting after update
                $this->allow_outcome_creation_enabled = get_outcome_creation_setting();
            }
        }
    }
} 
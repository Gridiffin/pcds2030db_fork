<?php
/**
 * Email Notifications System
 * 
 * Handles email notification sending, templates, and queue management.
 * Integrates with the notification system to send email alerts.
 * 
 * Supports both PHPMailer (when available) and PHP's built-in mail() function as fallback.
 */

// Try to load PHPMailer if available
$phpmailer_available = false;
if (file_exists(PROJECT_ROOT_PATH . 'vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    require_once PROJECT_ROOT_PATH . 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once PROJECT_ROOT_PATH . 'vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once PROJECT_ROOT_PATH . 'vendor/phpmailer/phpmailer/src/Exception.php';
    $phpmailer_available = true;
}

// Email configuration class
class EmailConfig {
    // Default SMTP configuration (can be overridden)
    public static $smtp_host = 'localhost';
    public static $smtp_port = 587;
    public static $smtp_username = '';
    public static $smtp_password = '';
    public static $smtp_secure = 'tls'; // 'tls' or 'ssl'
    public static $from_email = 'notifications@pcds2030.com';
    public static $from_name = 'PCDS 2030 Dashboard';
    public static $reply_to = 'noreply@pcds2030.com';
    
    // Email features
    public static $email_enabled = true;
    public static $use_queue = true;
    public static $max_retry_attempts = 3;
    public static $retry_delay_hours = 1;
}

/**
 * Initialize email configuration from database or config file
 */
function init_email_config() {
    global $conn;
    
    // Try to load configuration from database
    $config_query = "SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'email_%'";
    $stmt = $conn->prepare($config_query);
    
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $key = str_replace('email_', '', $row['setting_key']);
            $property = 'smtp_' . $key;
            
            if (property_exists('EmailConfig', $property)) {
                EmailConfig::$$property = $row['setting_value'];
            } elseif ($key === 'enabled') {
                EmailConfig::$email_enabled = (bool)$row['setting_value'];
            }
        }
    }
}

/**
 * Create email queue table if it doesn't exist
 */
function create_email_queue_table() {
    global $conn;
    
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS email_queue (
            queue_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            recipient_email VARCHAR(255) NOT NULL,
            recipient_name VARCHAR(255) NULL,
            subject VARCHAR(500) NOT NULL,
            body_html TEXT NOT NULL,
            body_text TEXT NULL,
            template_name VARCHAR(100) NULL,
            template_data JSON NULL,
            priority TINYINT DEFAULT 5,
            status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
            attempts INT DEFAULT 0,
            max_attempts INT DEFAULT 3,
            error_message TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            scheduled_at TIMESTAMP NULL,
            sent_at TIMESTAMP NULL,
            INDEX idx_status_priority (status, priority),
            INDEX idx_user_id (user_id),
            INDEX idx_scheduled_at (scheduled_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if (!$conn->query($create_table_sql)) {
        error_log("Failed to create email_queue table: " . $conn->error);
        return false;
    }
    
    return true;
}

/**
 * Queue an email for sending
 * @param int $user_id User ID
 * @param string $recipient_email Email address
 * @param string $recipient_name Recipient name
 * @param string $subject Email subject
 * @param string $body_html HTML body
 * @param string $body_text Plain text body (optional)
 * @param string $template_name Template used (for tracking)
 * @param array $template_data Template data (for re-processing if needed)
 * @param int $priority Priority (1-10, lower = higher priority)
 * @param string $scheduled_at Schedule for later sending (MySQL datetime format)
 * @return int|false Queue ID on success, false on failure
 */
function queue_email($user_id, $recipient_email, $recipient_name, $subject, $body_html, $body_text = null, $template_name = null, $template_data = null, $priority = 5, $scheduled_at = null) {
    global $conn;
    
    if (!EmailConfig::$email_enabled) {
        return false;
    }
    
    // Create table if it doesn't exist
    create_email_queue_table();
    
    $query = "INSERT INTO email_queue 
              (user_id, recipient_email, recipient_name, subject, body_html, body_text, 
               template_name, template_data, priority, scheduled_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare email queue query: " . $conn->error);
        return false;
    }
    
    $template_data_json = $template_data ? json_encode($template_data) : null;
    
    $stmt->bind_param('isssssssss', 
        $user_id, 
        $recipient_email, 
        $recipient_name, 
        $subject, 
        $body_html, 
        $body_text, 
        $template_name, 
        $template_data_json, 
        $priority, 
        $scheduled_at
    );
    
    if ($stmt->execute()) {
        $queue_id = $conn->insert_id;
        log_audit_action('email_queued', "Email queued for $recipient_email (Queue ID: $queue_id)");
        return $queue_id;
    } else {
        error_log("Failed to queue email: " . $stmt->error);
        return false;
    }
}

/**
 * Send an email immediately (bypassing queue)
 * @param string $recipient_email Email address
 * @param string $recipient_name Recipient name
 * @param string $subject Email subject
 * @param string $body_html HTML body
 * @param string $body_text Plain text body (optional)
 * @return bool Success status
 */
function send_email_immediate($recipient_email, $recipient_name, $subject, $body_html, $body_text = null) {
    global $phpmailer_available;
    
    if (!EmailConfig::$email_enabled) {
        return false;
    }
    
    // Initialize email configuration
    init_email_config();
    
    // Use PHPMailer if available, otherwise fall back to PHP mail()
    if ($phpmailer_available) {
        return send_email_with_phpmailer($recipient_email, $recipient_name, $subject, $body_html, $body_text);
    } else {
        return send_email_with_mail($recipient_email, $recipient_name, $subject, $body_html, $body_text);
    }
}

/**
 * Send email using PHPMailer
 */
function send_email_with_phpmailer($recipient_email, $recipient_name, $subject, $body_html, $body_text = null) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = EmailConfig::$smtp_host;
        $mail->SMTPAuth   = !empty(EmailConfig::$smtp_username);
        $mail->Username   = EmailConfig::$smtp_username;
        $mail->Password   = EmailConfig::$smtp_password;
        $mail->SMTPSecure = EmailConfig::$smtp_secure;
        $mail->Port       = EmailConfig::$smtp_port;
        
        // Recipients
        $mail->setFrom(EmailConfig::$from_email, EmailConfig::$from_name);
        $mail->addAddress($recipient_email, $recipient_name);
        $mail->addReplyTo(EmailConfig::$reply_to, EmailConfig::$from_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        
        if ($body_text) {
            $mail->AltBody = $body_text;
        }
        
        $mail->send();
        
        log_audit_action('email_sent', "Email sent successfully to $recipient_email (PHPMailer)");
        return true;
        
    } catch (PHPMailer\PHPMailer\Exception $e) {
        error_log("Failed to send email to $recipient_email via PHPMailer: " . $mail->ErrorInfo);
        log_audit_action('email_failed', "Failed to send email to $recipient_email via PHPMailer: " . $mail->ErrorInfo, 'failure');
        return false;
    } catch (Exception $e) {
        error_log("Failed to send email to $recipient_email via PHPMailer: " . $e->getMessage());
        log_audit_action('email_failed', "Failed to send email to $recipient_email via PHPMailer: " . $e->getMessage(), 'failure');
        return false;
    }
}

/**
 * Send email using PHP's built-in mail() function
 */
function send_email_with_mail($recipient_email, $recipient_name, $subject, $body_html, $body_text = null) {
    try {
        // Build headers
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . EmailConfig::$from_name . ' <' . EmailConfig::$from_email . '>';
        $headers[] = 'Reply-To: ' . EmailConfig::$reply_to;
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        
        // Build recipient
        $to = !empty($recipient_name) ? "$recipient_name <$recipient_email>" : $recipient_email;
        
        // Send email
        $success = mail($to, $subject, $body_html, implode("\r\n", $headers));
        
        if ($success) {
            log_audit_action('email_sent', "Email sent successfully to $recipient_email (PHP mail)");
            return true;
        } else {
            error_log("Failed to send email to $recipient_email via PHP mail()");
            log_audit_action('email_failed', "Failed to send email to $recipient_email via PHP mail()", 'failure');
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Failed to send email to $recipient_email via PHP mail(): " . $e->getMessage());
        log_audit_action('email_failed', "Failed to send email to $recipient_email via PHP mail(): " . $e->getMessage(), 'failure');
        return false;
    }
}

/**
 * Process email queue (should be called by cron job)
 * @param int $batch_size Number of emails to process in one batch
 * @return array Processing results
 */
function process_email_queue($batch_size = 10) {
    global $conn;
    
    if (!EmailConfig::$email_enabled) {
        return ['processed' => 0, 'sent' => 0, 'failed' => 0, 'message' => 'Email disabled'];
    }
    
    // Initialize email configuration
    init_email_config();
    
    // Get pending emails, prioritized by priority and creation date
    $query = "SELECT * FROM email_queue 
              WHERE status = 'pending' 
              AND (scheduled_at IS NULL OR scheduled_at <= NOW())
              AND attempts < max_attempts
              ORDER BY priority ASC, created_at ASC 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $batch_size);
    $stmt->execute();
    $emails = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $results = ['processed' => 0, 'sent' => 0, 'failed' => 0];
    
    foreach ($emails as $email) {
        $results['processed']++;
        
        // Mark as processing
        $update_query = "UPDATE email_queue SET status = 'processing', attempts = attempts + 1 WHERE queue_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('i', $email['queue_id']);
        $update_stmt->execute();
        
        // Attempt to send email
        $success = send_email_immediate(
            $email['recipient_email'],
            $email['recipient_name'],
            $email['subject'],
            $email['body_html'],
            $email['body_text']
        );
        
        if ($success) {
            // Mark as sent
            $final_query = "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE queue_id = ?";
            $final_stmt = $conn->prepare($final_query);
            $final_stmt->bind_param('i', $email['queue_id']);
            $final_stmt->execute();
            
            $results['sent']++;
        } else {
            // Check if max attempts reached
            if ($email['attempts'] + 1 >= $email['max_attempts']) {
                $final_query = "UPDATE email_queue SET status = 'failed', error_message = 'Max attempts reached' WHERE queue_id = ?";
            } else {
                // Reset to pending for retry
                $final_query = "UPDATE email_queue SET status = 'pending' WHERE queue_id = ?";
            }
            
            $final_stmt = $conn->prepare($final_query);
            $final_stmt->bind_param('i', $email['queue_id']);
            $final_stmt->execute();
            
            $results['failed']++;
        }
    }
    
    if ($results['processed'] > 0) {
        log_audit_action('email_queue_processed', 
            "Processed {$results['processed']} emails: {$results['sent']} sent, {$results['failed']} failed");
    }
    
    return $results;
}

/**
 * Generate email content from template
 * @param string $template_name Template name
 * @param array $data Template data
 * @param int $user_id User ID for personalization
 * @return array|false Array with 'subject', 'html', 'text' or false on error
 */
function generate_email_from_template($template_name, $data, $user_id) {
    // Get user information for personalization
    $user_info = get_user_email_info($user_id);
    if (!$user_info) {
        return false;
    }
    
    // Merge user info with template data
    $template_data = array_merge($data, [
        'user_name' => $user_info['fullname'] ?: $user_info['username'],
        'user_email' => $user_info['email'],
        'agency_name' => $user_info['agency_name'],
        'app_url' => APP_URL,
        'app_name' => 'PCDS 2030 Dashboard'
    ]);
    
    switch ($template_name) {
        case 'program_created':
            return generate_program_created_email($template_data);
        case 'program_edited':
            return generate_program_edited_email($template_data);
        case 'program_deleted':
            return generate_program_deleted_email($template_data);
        case 'submission_created':
            return generate_submission_created_email($template_data);
        case 'submission_edited':
            return generate_submission_edited_email($template_data);
        case 'submission_finalized':
            return generate_submission_finalized_email($template_data);
        case 'program_assignment':
            return generate_program_assignment_email($template_data);
        case 'system_notification':
            return generate_system_notification_email($template_data);
        default:
            error_log("Unknown email template: $template_name");
            return false;
    }
}

/**
 * Get user email information
 * @param int $user_id User ID
 * @return array|false User email info or false if not found
 */
function get_user_email_info($user_id) {
    global $conn;
    
    $query = "SELECT u.username, u.fullname, u.email, a.agency_name 
              FROM users u 
              LEFT JOIN agency a ON u.agency_id = a.agency_id 
              WHERE u.user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Send notification email for program creation
 * @param int $user_id User to notify
 * @param array $program_data Program information
 * @return bool Success status
 */
function send_program_created_email($user_id, $program_data) {
    $user_info = get_user_email_info($user_id);
    if (!$user_info || empty($user_info['email'])) {
        return false;
    }
    
    $email_content = generate_email_from_template('program_created', $program_data, $user_id);
    if (!$email_content) {
        return false;
    }
    
    return queue_email(
        $user_id,
        $user_info['email'],
        $user_info['fullname'] ?: $user_info['username'],
        $email_content['subject'],
        $email_content['html'],
        $email_content['text'],
        'program_created',
        $program_data
    );
}

/**
 * Send notification email for submission finalization
 * @param int $user_id User to notify
 * @param array $submission_data Submission information
 * @return bool Success status
 */
function send_submission_finalized_email($user_id, $submission_data) {
    $user_info = get_user_email_info($user_id);
    if (!$user_info || empty($user_info['email'])) {
        return false;
    }
    
    $email_content = generate_email_from_template('submission_finalized', $submission_data, $user_id);
    if (!$email_content) {
        return false;
    }
    
    return queue_email(
        $user_id,
        $user_info['email'],
        $user_info['fullname'] ?: $user_info['username'],
        $email_content['subject'],
        $email_content['html'],
        $email_content['text'],
        'submission_finalized',
        $submission_data,
        3 // Higher priority for finalization notifications
    );
}

/**
 * Enhanced notification function that includes email
 * @param int $user_id User to notify
 * @param string $message Notification message
 * @param string $type Notification type
 * @param string|null $action_url Action URL
 * @param array $email_data Additional data for email template
 * @return bool Success status
 */
function create_notification_with_email($user_id, $message, $type = 'system', $action_url = null, $email_data = []) {
    // Create regular notification
    $notification_success = create_notification($user_id, $message, $type, $action_url);
    
    // Send email if user has email preferences enabled
    $email_success = true; // Default to true in case email is disabled
    
    if (should_send_email_for_type($user_id, $type)) {
        $user_info = get_user_email_info($user_id);
        if ($user_info && !empty($user_info['email'])) {
            $email_data['notification_message'] = $message;
            $email_data['action_url'] = $action_url;
            
            $email_content = generate_email_from_template($type, $email_data, $user_id);
            if ($email_content) {
                $email_success = queue_email(
                    $user_id,
                    $user_info['email'],
                    $user_info['fullname'] ?: $user_info['username'],
                    $email_content['subject'],
                    $email_content['html'],
                    $email_content['text'],
                    $type,
                    $email_data
                );
            }
        }
    }
    
    return $notification_success && $email_success;
}

/**
 * Check if user wants email notifications for a specific type
 * @param int $user_id User ID
 * @param string $type Notification type
 * @return bool Whether to send email
 */
function should_send_email_for_type($user_id, $type) {
    global $conn;
    
    // Check user email preferences (if table exists)
    $pref_query = "SELECT email_enabled, email_types FROM user_email_preferences WHERE user_id = ?";
    $stmt = $conn->prepare($pref_query);
    
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($pref = $result->fetch_assoc()) {
            if (!$pref['email_enabled']) {
                return false;
            }
            
            if ($pref['email_types']) {
                $enabled_types = json_decode($pref['email_types'], true);
                return in_array($type, $enabled_types);
            }
        }
    }
    
    // Default: send emails for important notifications
    $important_types = ['submission_finalized', 'program_assignment', 'system_notification'];
    return in_array($type, $important_types);
}

/**
 * Clean up old email queue entries
 * @param int $days_to_keep Number of days to keep completed entries
 * @return int Number of entries deleted
 */
function cleanup_email_queue($days_to_keep = 7) {
    global $conn;
    
    $query = "DELETE FROM email_queue 
              WHERE status IN ('sent', 'failed') 
              AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $days_to_keep);
    $stmt->execute();
    
    $deleted_count = $stmt->affected_rows;
    
    if ($deleted_count > 0) {
        log_audit_action('email_queue_cleanup', "Cleaned up $deleted_count old email queue entries");
    }
    
    return $deleted_count;
}

// Include email templates
require_once __DIR__ . '/email_templates.php';
?>
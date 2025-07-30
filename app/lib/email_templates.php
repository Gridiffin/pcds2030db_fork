<?php
/**
 * Email Templates
 * 
 * HTML and text templates for various notification types.
 * Uses a consistent design with the application branding.
 */

/**
 * Base email template wrapper
 * @param string $title Email title
 * @param string $content Main email content
 * @param array $data Template data
 * @return string Complete HTML email
 */
function get_base_email_template($title, $content, $data) {
    $app_url = $data['app_url'] ?? APP_URL;
    $app_name = $data['app_name'] ?? 'PCDS 2030 Dashboard';
    $user_name = $data['user_name'] ?? 'User';
    
    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>$title</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f5f7fa;
                color: #333333;
                line-height: 1.6;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .email-header {
                background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .email-header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 600;
            }
            .email-body {
                padding: 40px 30px;
            }
            .email-greeting {
                font-size: 18px;
                margin-bottom: 20px;
                color: #333;
            }
            .email-content {
                font-size: 16px;
                margin-bottom: 30px;
                color: #555;
            }
            .notification-box {
                background-color: #f8f9fa;
                border-left: 4px solid #0d6efd;
                padding: 20px;
                margin: 20px 0;
                border-radius: 0 4px 4px 0;
            }
            .notification-box.warning {
                border-left-color: #ffc107;
                background-color: #fff3cd;
            }
            .notification-box.success {
                border-left-color: #28a745;
                background-color: #d4edda;
            }
            .notification-box.danger {
                border-left-color: #dc3545;
                background-color: #f8d7da;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background-color: #0d6efd;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 500;
                margin: 10px 0;
                transition: background-color 0.3s ease;
            }
            .btn:hover {
                background-color: #0b5ed7;
                color: white;
                text-decoration: none;
            }
            .btn-secondary {
                background-color: #6c757d;
            }
            .btn-secondary:hover {
                background-color: #545b62;
            }
            .email-footer {
                background-color: #f8f9fa;
                padding: 30px;
                text-align: center;
                border-top: 1px solid #dee2e6;
                font-size: 14px;
                color: #6c757d;
            }
            .email-footer a {
                color: #0d6efd;
                text-decoration: none;
            }
            .details-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .details-table th,
            .details-table td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #dee2e6;
            }
            .details-table th {
                background-color: #f8f9fa;
                font-weight: 600;
                color: #495057;
            }
            .timestamp {
                font-size: 14px;
                color: #6c757d;
                font-style: italic;
            }
            @media only screen and (max-width: 600px) {
                .email-container {
                    width: 100% !important;
                }
                .email-header,
                .email-body,
                .email-footer {
                    padding: 20px !important;
                }
                .btn {
                    display: block;
                    text-align: center;
                    margin: 15px 0;
                }
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='email-header'>
                <h1>$app_name</h1>
            </div>
            <div class='email-body'>
                <div class='email-greeting'>
                    Hello $user_name,
                </div>
                <div class='email-content'>
                    $content
                </div>
            </div>
            <div class='email-footer'>
                <p>This is an automated notification from the $app_name system.</p>
                <p>Please do not reply to this email. If you have questions, please contact your system administrator.</p>
                <p><a href='$app_url'>Visit Dashboard</a> | <a href='$app_url/settings'>Notification Settings</a></p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Program Created Email Template
 */
function generate_program_created_email($data) {
    $program_name = $data['program_name'] ?? 'Unknown Program';
    $creator_name = $data['creator_name'] ?? 'Unknown User';
    $agency_name = $data['agency_name'] ?? 'Unknown Agency';
    $action_url = $data['action_url'] ?? '#';
    
    $content = "
        <div class='notification-box success'>
            <h3 style='margin-top: 0; color: #155724;'>New Program Created</h3>
            <p>A new program has been created in your agency and you have been notified as a stakeholder.</p>
        </div>
        
        <table class='details-table'>
            <tr>
                <th>Program Name:</th>
                <td><strong>$program_name</strong></td>
            </tr>
            <tr>
                <th>Created By:</th>
                <td>$creator_name</td>
            </tr>
            <tr>
                <th>Agency:</th>
                <td>$agency_name</td>
            </tr>
            <tr>
                <th>Date Created:</th>
                <td class='timestamp'>" . date('F j, Y \a\t g:i A') . "</td>
            </tr>
        </table>
        
        <p>You can view the program details and start working on submissions by clicking the button below.</p>
        
        <a href='$action_url' class='btn'>View Program Details</a>
        
        <p><small>If you believe you received this notification in error, please contact your system administrator.</small></p>
    ";
    
    $html = get_base_email_template('New Program Created', $content, $data);
    
    $text = "
Hello {$data['user_name']},

A new program has been created in your agency:

Program Name: $program_name
Created By: $creator_name
Agency: $agency_name
Date Created: " . date('F j, Y \a\t g:i A') . "

You can view the program details at: $action_url

Best regards,
PCDS 2030 Dashboard Team
    ";
    
    return [
        'subject' => "New Program Created: $program_name",
        'html' => $html,
        'text' => trim($text)
    ];
}

/**
 * Program Edited Email Template
 */
function generate_program_edited_email($data) {
    $program_name = $data['program_name'] ?? 'Unknown Program';
    $editor_name = $data['editor_name'] ?? 'Unknown User';
    $changes = $data['changes'] ?? [];
    $action_url = $data['action_url'] ?? '#';
    
    $changes_list = '';
    if (!empty($changes)) {
        $changes_list = '<ul>';
        foreach ($changes as $field => $change) {
            $changes_list .= "<li><strong>" . ucwords(str_replace('_', ' ', $field)) . "</strong>: $change</li>";
        }
        $changes_list .= '</ul>';
    }
    
    $content = "
        <div class='notification-box'>
            <h3 style='margin-top: 0; color: #004085;'>Program Updated</h3>
            <p>A program you're associated with has been updated.</p>
        </div>
        
        <table class='details-table'>
            <tr>
                <th>Program Name:</th>
                <td><strong>$program_name</strong></td>
            </tr>
            <tr>
                <th>Updated By:</th>
                <td>$editor_name</td>
            </tr>
            <tr>
                <th>Date Updated:</th>
                <td class='timestamp'>" . date('F j, Y \a\t g:i A') . "</td>
            </tr>
        </table>
        
        " . (!empty($changes_list) ? "
        <h4>Changes Made:</h4>
        $changes_list
        " : "") . "
        
        <p>Review the updated program details to stay current with the latest information.</p>
        
        <a href='$action_url' class='btn'>View Updated Program</a>
    ";
    
    $html = get_base_email_template('Program Updated', $content, $data);
    
    $text = "
Hello {$data['user_name']},

A program you're associated with has been updated:

Program Name: $program_name
Updated By: $editor_name
Date Updated: " . date('F j, Y \a\t g:i A') . "

View the updated program at: $action_url

Best regards,
PCDS 2030 Dashboard Team
    ";
    
    return [
        'subject' => "Program Updated: $program_name",
        'html' => $html,
        'text' => trim($text)
    ];
}

/**
 * Program Deleted Email Template
 */
function generate_program_deleted_email($data) {
    $program_name = $data['program_name'] ?? 'Unknown Program';
    $deleter_name = $data['deleter_name'] ?? 'Unknown User';
    $agency_name = $data['agency_name'] ?? 'Unknown Agency';
    
    $content = "
        <div class='notification-box danger'>
            <h3 style='margin-top: 0; color: #721c24;'>Program Deleted</h3>
            <p>A program has been permanently deleted from the system.</p>
        </div>
        
        <table class='details-table'>
            <tr>
                <th>Program Name:</th>
                <td><strong>$program_name</strong></td>
            </tr>
            <tr>
                <th>Deleted By:</th>
                <td>$deleter_name</td>
            </tr>
            <tr>
                <th>Agency:</th>
                <td>$agency_name</td>
            </tr>
            <tr>
                <th>Date Deleted:</th>
                <td class='timestamp'>" . date('F j, Y \a\t g:i A') . "</td>
            </tr>
        </table>
        
        <p><strong>Important:</strong> All associated data including submissions, targets, and attachments have been permanently removed.</p>
        
        <p>If you need to recover this data or believe this deletion was made in error, please contact your system administrator immediately.</p>
    ";
    
    $html = get_base_email_template('Program Deleted', $content, $data);
    
    $text = "
Hello {$data['user_name']},

A program has been deleted from the system:

Program Name: $program_name
Deleted By: $deleter_name
Agency: $agency_name
Date Deleted: " . date('F j, Y \a\t g:i A') . "

IMPORTANT: All associated data has been permanently removed.

If you need assistance, please contact your system administrator.

Best regards,
PCDS 2030 Dashboard Team
    ";
    
    return [
        'subject' => "Program Deleted: $program_name",
        'html' => $html,
        'text' => trim($text)
    ];
}

/**
 * Submission Created Email Template
 */
function generate_submission_created_email($data) {
    $program_name = $data['program_name'] ?? 'Unknown Program';
    $creator_name = $data['creator_name'] ?? 'Unknown User';
    $period_text = $data['period_text'] ?? 'Unknown Period';
    $action_url = $data['action_url'] ?? '#';
    
    $content = "
        <div class='notification-box success'>
            <h3 style='margin-top: 0; color: #155724;'>New Submission Created</h3>
            <p>A new submission has been created for a program you're associated with.</p>
        </div>
        
        <table class='details-table'>
            <tr>
                <th>Program Name:</th>
                <td><strong>$program_name</strong></td>
            </tr>
            <tr>
                <th>Reporting Period:</th>
                <td>$period_text</td>
            </tr>
            <tr>
                <th>Created By:</th>
                <td>$creator_name</td>
            </tr>
            <tr>
                <th>Date Created:</th>
                <td class='timestamp'>" . date('F j, Y \a\t g:i A') . "</td>
            </tr>
        </table>
        
        <p>The submission is currently in draft status. You can review and edit it before finalization.</p>
        
        <a href='$action_url' class='btn'>View Submission</a>
    ";
    
    $html = get_base_email_template('New Submission Created', $content, $data);
    
    $text = "
Hello {$data['user_name']},

A new submission has been created:

Program Name: $program_name
Reporting Period: $period_text
Created By: $creator_name
Date Created: " . date('F j, Y \a\t g:i A') . "

View the submission at: $action_url

Best regards,
PCDS 2030 Dashboard Team
    ";
    
    return [
        'subject' => "New Submission Created: $program_name ($period_text)",
        'html' => $html,
        'text' => trim($text)
    ];
}

/**
 * Submission Edited Email Template
 */
function generate_submission_edited_email($data) {
    $program_name = $data['program_name'] ?? 'Unknown Program';
    $editor_name = $data['editor_name'] ?? 'Unknown User';
    $period_text = $data['period_text'] ?? 'Unknown Period';
    $status = $data['status'] ?? 'Draft';
    $action_url = $data['action_url'] ?? '#';
    
    $content = "
        <div class='notification-box'>
            <h3 style='margin-top: 0; color: #004085;'>Submission Updated</h3>
            <p>A submission for a program you're associated with has been updated.</p>
        </div>
        
        <table class='details-table'>
            <tr>
                <th>Program Name:</th>
                <td><strong>$program_name</strong></td>
            </tr>
            <tr>
                <th>Reporting Period:</th>
                <td>$period_text</td>
            </tr>
            <tr>
                <th>Status:</th>
                <td><strong>$status</strong></td>
            </tr>
            <tr>
                <th>Updated By:</th>
                <td>$editor_name</td>
            </tr>
            <tr>
                <th>Date Updated:</th>
                <td class='timestamp'>" . date('F j, Y \a\t g:i A') . "</td>
            </tr>
        </table>
        
        <p>Review the updated submission to stay current with the latest changes.</p>
        
        <a href='$action_url' class='btn'>View Updated Submission</a>
    ";
    
    $html = get_base_email_template('Submission Updated', $content, $data);
    
    $text = "
Hello {$data['user_name']},

A submission has been updated:

Program Name: $program_name
Reporting Period: $period_text
Status: $status
Updated By: $editor_name
Date Updated: " . date('F j, Y \a\t g:i A') . "

View the updated submission at: $action_url

Best regards,
PCDS 2030 Dashboard Team
    ";
    
    return [
        'subject' => "Submission Updated: $program_name ($period_text)",
        'html' => $html,
        'text' => trim($text)
    ];
}

/**
 * Submission Finalized Email Template
 */
function generate_submission_finalized_email($data) {
    $program_name = $data['program_name'] ?? 'Unknown Program';
    $finalizer_name = $data['finalizer_name'] ?? 'Unknown User';
    $period_text = $data['period_text'] ?? 'Unknown Period';
    $agency_name = $data['agency_name'] ?? 'Unknown Agency';
    $action_url = $data['action_url'] ?? '#';
    
    $content = "
        <div class='notification-box success'>
            <h3 style='margin-top: 0; color: #155724;'>Submission Finalized</h3>
            <p>A submission has been finalized and is now ready for reporting.</p>
        </div>
        
        <table class='details-table'>
            <tr>
                <th>Program Name:</th>
                <td><strong>$program_name</strong></td>
            </tr>
            <tr>
                <th>Reporting Period:</th>
                <td>$period_text</td>
            </tr>
            <tr>
                <th>Agency:</th>
                <td>$agency_name</td>
            </tr>
            <tr>
                <th>Finalized By:</th>
                <td>$finalizer_name</td>
            </tr>
            <tr>
                <th>Date Finalized:</th>
                <td class='timestamp'>" . date('F j, Y \a\t g:i A') . "</td>
            </tr>
        </table>
        
        <p><strong>Important:</strong> This submission is now locked and cannot be edited. It will be included in the next reporting cycle.</p>
        
        <a href='$action_url' class='btn'>View Finalized Submission</a>
    ";
    
    $html = get_base_email_template('Submission Finalized', $content, $data);
    
    $text = "
Hello {$data['user_name']},

A submission has been finalized:

Program Name: $program_name
Reporting Period: $period_text
Agency: $agency_name
Finalized By: $finalizer_name
Date Finalized: " . date('F j, Y \a\t g:i A') . "

IMPORTANT: This submission is now locked and ready for reporting.

View the submission at: $action_url

Best regards,
PCDS 2030 Dashboard Team
    ";
    
    return [
        'subject' => "Submission Finalized: $program_name ($period_text)",
        'html' => $html,
        'text' => trim($text)
    ];
}

/**
 * Program Assignment Email Template
 */
function generate_program_assignment_email($data) {
    $program_name = $data['program_name'] ?? 'Unknown Program';
    $assignment_type = $data['assignment_type'] ?? 'editor';
    $assigner_name = $data['assigner_name'] ?? 'Unknown User';
    $action_url = $data['action_url'] ?? '#';
    
    $content = "
        <div class='notification-box'>
            <h3 style='margin-top: 0; color: #004085;'>Program Assignment</h3>
            <p>You have been assigned as " . ucfirst($assignment_type) . " for a program.</p>
        </div>
        
        <table class='details-table'>
            <tr>
                <th>Program Name:</th>
                <td><strong>$program_name</strong></td>
            </tr>
            <tr>
                <th>Your Role:</th>
                <td><strong>" . ucfirst($assignment_type) . "</strong></td>
            </tr>
            <tr>
                <th>Assigned By:</th>
                <td>$assigner_name</td>
            </tr>
            <tr>
                <th>Date Assigned:</th>
                <td class='timestamp'>" . date('F j, Y \a\t g:i A') . "</td>
            </tr>
        </table>
        
        <p>As " . ucfirst($assignment_type) . ", you can " . 
        ($assignment_type === 'editor' ? 'view and edit program details, create submissions, and manage targets' : 'view program details and submissions') . ".</p>
        
        <a href='$action_url' class='btn'>Access Program</a>
    ";
    
    $html = get_base_email_template('Program Assignment', $content, $data);
    
    $text = "
Hello {$data['user_name']},

You have been assigned as " . ucfirst($assignment_type) . " for a program:

Program Name: $program_name
Your Role: " . ucfirst($assignment_type) . "
Assigned By: $assigner_name
Date Assigned: " . date('F j, Y \a\t g:i A') . "

Access the program at: $action_url

Best regards,
PCDS 2030 Dashboard Team
    ";
    
    return [
        'subject' => "Program Assignment: $program_name",
        'html' => $html,
        'text' => trim($text)
    ];
}

/**
 * System Notification Email Template
 */
function generate_system_notification_email($data) {
    $notification_message = $data['notification_message'] ?? 'System notification';
    $action_url = $data['action_url'] ?? null;
    
    $content = "
        <div class='notification-box warning'>
            <h3 style='margin-top: 0; color: #856404;'>System Notification</h3>
            <p>You have received an important system notification.</p>
        </div>
        
        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin: 0; font-size: 16px; line-height: 1.5;'>$notification_message</p>
        </div>
        
        <p class='timestamp'>Sent: " . date('F j, Y \a\t g:i A') . "</p>
        
        " . ($action_url ? "<a href='$action_url' class='btn'>Take Action</a>" : "") . "
    ";
    
    $html = get_base_email_template('System Notification', $content, $data);
    
    $text = "
Hello {$data['user_name']},

You have received a system notification:

$notification_message

Sent: " . date('F j, Y \a\t g:i A') . "

" . ($action_url ? "Take action at: $action_url" : "") . "

Best regards,
PCDS 2030 Dashboard Team
    ";
    
    return [
        'subject' => "System Notification - PCDS 2030 Dashboard",
        'html' => $html,
        'text' => trim($text)
    ];
}
?>
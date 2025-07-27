<?php
/**
 * Admin View Programs - Overhauled Version
 * 
 * Interface for admin users to view all finalized programs across agencies.
 * Modular structure with base.php layout following agency side patterns.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get message from session if available
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'info';

// Clear message from session
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Initialize program arrays
$programs = [];
$programs_with_submissions = [];

// Get all finalized programs across all agencies (admin sees everything)
$query = "SELECT DISTINCT p.*, 
                 i.initiative_name,
                 i.initiative_number,
                 i.initiative_id,
                 latest_sub.is_draft,
                 latest_sub.period_id,
                 latest_sub.submission_id as latest_submission_id,
                 latest_sub.submitted_at,
                 latest_sub.submitted_by,
                 rp.period_type,
                 rp.period_number,
                 rp.year as period_year,
                 a.agency_name,
                 su.fullname as submitted_by_name,
                 COALESCE(latest_sub.submitted_at, p.created_at) as updated_at
          FROM programs p 
          LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
          LEFT JOIN agency a ON p.agency_id = a.agency_id
          LEFT JOIN (
              SELECT ps1.*
              FROM program_submissions ps1
              INNER JOIN (
                  SELECT program_id, MAX(submission_id) as max_submission_id
                  FROM program_submissions
                  WHERE is_deleted = 0 AND is_draft = 0
                  GROUP BY program_id
              ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
              WHERE ps1.is_draft = 0
          ) latest_sub ON p.program_id = latest_sub.program_id
          LEFT JOIN reporting_periods rp ON latest_sub.period_id = rp.period_id
          LEFT JOIN users su ON latest_sub.submitted_by = su.user_id
          WHERE p.is_deleted = 0 
          AND latest_sub.submission_id IS NOT NULL
          ORDER BY a.agency_name, p.program_name";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $programs[] = $row;
    $programs_with_submissions[] = $row;
}

// Get all agencies for filtering
$agencies = [];
$agencies_query = "SELECT agency_id, agency_name FROM agency ORDER BY agency_name";
$result = $conn->query($agencies_query);
while ($agency = $result->fetch_assoc()) {
    $agencies[] = $agency;
}

// Get all active initiatives for filtering
$active_initiatives = [];
$initiatives_query = "SELECT initiative_id, initiative_name, initiative_number FROM initiatives ORDER BY initiative_name";
$result = $conn->query($initiatives_query);
while ($initiative = $result->fetch_assoc()) {
    $active_initiatives[] = $initiative;
}


// Set up base layout variables
$pageTitle = 'Admin Programs Overview';
$cssBundle = 'admin-view-programs'; // Vite bundle for admin view programs page
$jsBundle = 'admin-view-programs';

// Configure modern page header
$header_config = [
    'title' => 'Programs Overview',
    'subtitle' => 'View and manage finalized programs across all agencies',
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => APP_URL . '/index.php?page=admin_dashboard'
        ],
        [
            'text' => 'Programs',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'green'
];

// Set content file for base layout
$contentFile = __DIR__ . '/partials/programs_content.php';

// Include base layout - it will render header, nav, content, and footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';
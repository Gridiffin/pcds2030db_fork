<?php
/**
 * View Programs - Refactored with Best Practices
 * 
 * Interface for agency users to view their programs.
 * Modular structure with base.php layout and Vite bundling.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get message from session if available
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'info';

// Check if showing created program message
$show_created_message = isset($_GET['created']) && $_GET['created'] == '1';

// Clear message from session
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Get active initiatives for filtering
$active_initiatives = get_initiatives_for_select(true);

// Initialize program arrays
$programs = [];
$programs_with_drafts = [];
$programs_with_submissions = [];
$programs_without_submissions = [];

// Get programs for the current agency user
$agency_id = $_SESSION['agency_id'] ?? null;

if ($agency_id !== null) {
    // Build query - both focal and regular users see programs their agency has access to
    $query = "SELECT DISTINCT p.*, 
                     i.initiative_name,
                     i.initiative_number,
                     i.initiative_id,
                     latest_sub.is_draft,
                     latest_sub.period_id,
                     latest_sub.submission_id as latest_submission_id,
                     latest_sub.submitted_at,
                     rp.period_type,
                     rp.period_number,
                     rp.year as period_year,
                     COALESCE(latest_sub.submitted_at, p.created_at) as updated_at
              FROM programs p 
              LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
              LEFT JOIN (
                  SELECT ps1.*
                  FROM program_submissions ps1
                  INNER JOIN (
                      SELECT program_id, MAX(submission_id) as max_submission_id
                      FROM program_submissions
                      WHERE is_deleted = 0
                      GROUP BY program_id
                  ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
              ) latest_sub ON p.program_id = latest_sub.program_id
              LEFT JOIN reporting_periods rp ON latest_sub.period_id = rp.period_id
              WHERE p.is_deleted = 0 AND p.agency_id = ?
              ORDER BY p.program_name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
}

// Process programs and separate into appropriate arrays
foreach ($programs as $program) {
    if (isset($program['latest_submission_id']) && $program['latest_submission_id']) {
        if (isset($program['is_draft']) && $program['is_draft']) {
            $programs_with_drafts[] = $program;
        } else {
            $programs_with_submissions[] = $program;
        }
    } else {
        $programs_without_submissions[] = $program;
    }
}

// Set up base layout variables
$pageTitle = 'Agency Programs';
$cssBundle = 'agency-view-programs'; // Vite bundle for view programs page
$jsBundle = 'agency-view-programs';

// Configure modern page header
$header_config = [
    'title' => 'Agency Programs',
    'subtitle' => 'View and manage your agency\'s programs',
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => APP_URL . '/index.php?page=agency_dashboard'
        ],
        [
            'text' => 'My Programs',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'green'
];

// Set content file for base layout
$contentFile = __DIR__ . '/view_programs_content.php';

// Add finalization tutorial script 
// Load for all users since it contains submission selection functionality used by everyone
$additionalScripts = [];
$additionalScripts[] = APP_URL . '/assets/js/agency/finalization-tutorial.js';

// Include base layout - it will render header, nav, content, and footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';

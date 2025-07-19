<?php
/**
 * Agency Initiative Details View - Refactored
 * 
 * Full-page view for initiative details with programs and complete information.
 * Uses modular structure with base.php layout and partials.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/initiatives.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/asset_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_names_helper.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get initiative ID
$initiative_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$initiative_id) {
    $_SESSION['message'] = 'Invalid initiative ID.';
    $_SESSION['message_type'] = 'error';
    header('Location: initiatives.php');
    exit;
}

// Get initiative details
$agency_id = $_SESSION['agency_id'] ?? null;
$initiative = get_agency_initiative_details($initiative_id, $agency_id);

if (!$initiative) {
    $_SESSION['message'] = 'Initiative not found or access denied.';
    $_SESSION['message_type'] = 'error';
    header('Location: initiatives.php');
    exit;
}

// Get programs under this initiative with their latest ratings
$programs = [];
$latest_ratings_sql = "
    SELECT 
        p.program_id,
        p.program_name,
        p.program_number,
        p.agency_id,
        a.agency_name,
        p.rating,
        p.updated_at,
        p.status
    FROM programs p
    LEFT JOIN agency a ON p.agency_id = a.agency_id
    WHERE p.initiative_id = ?
    ORDER BY p.program_id
";

$stmt = $conn->prepare($latest_ratings_sql);
$stmt->bind_param('i', $initiative_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $programs[] = $row;
}

// Calculate initiative health score based on program status (not rating)
$health_score = 50; // Default neutral score
$health_description = 'No Data';
$health_color = '#6c757d';

if (!empty($programs)) {
    $total_programs = count($programs);
    $score_sum = 0;
    
    foreach ($programs as $program) {
        $status = $program['status'] ?? 'active';
        // Normalize status using helper
        require_once PROJECT_ROOT_PATH . 'app/lib/program_status_helpers.php';
        $normalized = [
            'not-started' => 'active',
            'not_started' => 'active',
            'delayed' => 'delayed',
            'on-hold' => 'on_hold',
            'on_hold' => 'on_hold',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'completed' => 'completed',
            'active' => 'active'
        ];
        
        $normalized_status = $normalized[strtolower($status)] ?? 'active';
        
        switch ($normalized_status) {
            case 'completed':
                $score_sum += 100;
                break;
            case 'active':
                $score_sum += 75;
                break;
            case 'on_hold':
                $score_sum += 50;
                break;
            case 'delayed':
                $score_sum += 25;
                break;
            case 'cancelled':
                $score_sum += 10;
                break;
            default:
                $score_sum += 50; // Default for unknown status
                break;
        }
    }
    
    $health_score = round($score_sum / $total_programs);
    
    // Determine description and color based on score
    if ($health_score >= 80) {
        $health_description = 'Excellent - Programs performing well';
        $health_color = '#28a745';
    } elseif ($health_score >= 60) {
        $health_description = 'Good - Most programs are active';
        $health_color = '#28a745';
    } elseif ($health_score >= 40) {
        $health_description = 'Fair - Some programs on hold or delayed';
        $health_color = '#ffc107';
    } else {
        $health_description = 'Poor - Programs need improvement';
        $health_color = '#dc3545';
    }
}

// Get column names using db_names helper
$initiative_id_col = get_column_name('initiatives', 'id');
$initiative_name_col = get_column_name('initiatives', 'name');
$initiative_number_col = get_column_name('initiatives', 'number');
$initiative_description_col = get_column_name('initiatives', 'description');
$start_date_col = get_column_name('initiatives', 'start_date');
$end_date_col = get_column_name('initiatives', 'end_date');
$is_active_col = get_column_name('initiatives', 'is_active');

// Configure page for base.php layout
$pageTitle = 'Initiative Progress Tracker';
$cssBundle = 'initiatives';
$jsBundle = 'initiatives';

// Configure breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '../../agency/dashboard.php'],
    ['title' => 'Initiatives', 'url' => 'initiatives.php'],
    ['title' => 'Initiative Details']
];

// Configure the modern page header
$header_config = [
    'title' => 'Initiative Progress Tracker',
    'subtitle' => 'Comprehensive tracking of initiative objectives and program progress',
    'variant' => 'blue',
    'breadcrumbs' => $breadcrumbs,
    'actions' => [
        [
            'text' => 'Back to Initiatives',
            'url' => 'initiatives.php',
            'class' => 'btn-outline-light-active',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Set content file for base layout to include
$contentFile = __DIR__ . '/partials/view_initiative_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';

<?php
/**
 * Agency Initiative Details View
 * 
 * Full-page view for initiative details with programs and complete information.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/initiatives.php';
require_once PROJECT_ROOT_PATH . 'lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'lib/asset_helpers.php';
require_once PROJECT_ROOT_PATH . 'lib/db_names_helper.php';

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
    header('Location: view_initiatives.php');
    exit;
}

// Get initiative details
$agency_id = $_SESSION['agency_id'] ?? null;
$initiative = get_agency_initiative_details($initiative_id, $agency_id);

if (!$initiative) {
    $_SESSION['message'] = 'Initiative not found or access denied.';
    $_SESSION['message_type'] = 'error';
    header('Location: view_initiatives.php');
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
        p.updated_at
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

// Calculate initiative health score based on program performance
$health_score = 50; // Default neutral score
$health_description = 'No Data';
$health_color = '#6c757d';

if (!empty($programs)) {
    $total_programs = count($programs);
    $score_sum = 0;
    
    foreach ($programs as $program) {
        $status = convert_legacy_rating($program['rating'] ?? 'not_started');
        
        // Assign scores based on status
        switch ($status) {
            case 'target-achieved':
            case 'completed':
                $score_sum += 100;
                break;
            case 'on-track':
            case 'on-track-yearly':
                $score_sum += 75;
                break;
            case 'delayed':
                $score_sum += 50;
                break;
            case 'severe-delay':
                $score_sum += 25;
                break;
            default: // not-started
                $score_sum += 10;
                break;
        }
    }
    
    $health_score = round($score_sum / $total_programs);
    
    // Determine description and color based on score
    if ($health_score >= 80) {
        $health_description = 'Excellent - Programs performing well';
        $health_color = '#28a745';
    } elseif ($health_score >= 60) {
        $health_description = 'Good - Based on program performance';
        $health_color = '#28a745';
    } elseif ($health_score >= 40) {
        $health_description = 'Fair - Some programs need attention';
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

// Set page title
$pageTitle = 'Initiative Progress Tracker';

// Include additional JavaScript for initiative view
$additionalScripts = [
    asset_url('js', 'agency/initiative-view.js')
];

// Include header
require_once '../../layouts/header.php';

// Configure breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '../../agency/dashboard.php'],
    ['title' => 'Initiatives', 'url' => 'view_initiatives.php'],
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
            'url' => 'view_initiatives.php',
            'class' => 'btn-outline-light-active',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<main class="flex-fill">

<!-- Initiative Overview Section -->
<div class="container-fluid">
    <div class="initiative-overview">
        <div class="initiative-title">
            <i class="fas fa-leaf"></i>
            <?php echo htmlspecialchars($initiative[$initiative_name_col]); ?>
            <?php if (!empty($initiative[$initiative_number_col])): ?>
                <span class="badge bg-primary ms-3" style="font-size: 0.6em; padding: 0.5rem 1rem; vertical-align: middle;">
                    #<?php echo htmlspecialchars($initiative[$initiative_number_col]); ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="initiative-meta">
            <div class="meta-item">
                <i class="fas fa-calendar"></i>
                <span>
                    <?php 
                    if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                        echo date('Y-m-d', strtotime($initiative[$start_date_col])) . ' to ' . date('Y-m-d', strtotime($initiative[$end_date_col]));
                        
                        // Calculate duration in years
                        $start = new DateTime($initiative[$start_date_col]);
                        $end = new DateTime($initiative[$end_date_col]);
                        $interval = $start->diff($end);
                        $years = $interval->y + ($interval->m / 12) + ($interval->d / 365);
                        echo ' (' . round($years, 1) . ' years)';
                    } else {
                        echo 'Timeline not specified';
                    }
                    ?>
                </span>
            </div>
            <div class="meta-item">
                <i class="fas fa-clock"></i>
                <span>
                    <?php 
                    if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                        $start = new DateTime($initiative[$start_date_col]);
                        $end = new DateTime($initiative[$end_date_col]);
                        $now = new DateTime();
                        
                        $total_duration = $start->diff($end);
                        $elapsed = $start->diff($now);
                        
                        $total_days = $total_duration->days;
                        $elapsed_days = $elapsed->days;
                        
                        $elapsed_years = round($elapsed_days / 365, 1);
                        $remaining_years = round(($total_days - $elapsed_days) / 365, 1);
                        
                        echo $elapsed_years . ' years elapsed, ' . $remaining_years . ' years remaining';
                    } else {
                        echo 'Timeline not available';
                    }
                    ?>
                </span>
            </div>
            <div class="meta-item">
                <?php if ($initiative[$is_active_col]): ?>
                    <span class="badge">Status: Active</span>
                <?php else: ?>
                    <span class="badge" style="background-color: #6c757d;">Status: Inactive</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Core Initiative Metrics -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="metric-card text-center">
                <div class="metric-value" style="color: #ffc107;">
                    <?php 
                    // Calculate timeline progress percentage
                    if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                        $start = new DateTime($initiative[$start_date_col]);
                        $end = new DateTime($initiative[$end_date_col]);
                        $now = new DateTime();
                        
                        $total_duration = $start->diff($end)->days;
                        $elapsed = $start->diff($now)->days;
                        
                        if ($elapsed < 0) {
                            $progress = 0;
                        } elseif ($elapsed > $total_duration) {
                            $progress = 100;
                        } else {
                            $progress = round(($elapsed / $total_duration) * 100);
                        }
                        
                        echo $progress . '%';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </div>
                <div class="metric-label">Initiative Timeline Progress</div>
                <div class="metric-sublabel">
                    <i class="fas fa-hourglass-half"></i>
                    <?php 
                    if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                        $start = new DateTime($initiative[$start_date_col]);
                        $end = new DateTime($initiative[$end_date_col]);
                        $now = new DateTime();
                        
                        $total_years = round($start->diff($end)->days / 365, 1);
                        $elapsed_years = round($start->diff($now)->days / 365, 1);
                        
                        // Ensure elapsed doesn't exceed total
                        if ($elapsed_years > $total_years) {
                            $elapsed_years = $total_years;
                        }
                        
                        echo $elapsed_years . ' of ' . $total_years . ' years completed';
                    } else {
                        echo 'Timeline not available';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="metric-card text-center">
                <div class="health-score-circle" style="background: conic-gradient(<?php echo $health_color; ?> 0deg <?php echo ($health_score * 3.6); ?>deg, #e9ecef <?php echo ($health_score * 3.6); ?>deg 360deg);">
                    <div class="health-score-inner">
                        <div class="health-score-value"><?php echo $health_score; ?></div>
                        <div class="health-score-label">Health</div>
                    </div>
                </div>
                <div class="metric-label">Overall Initiative Health</div>
                <div class="health-description" style="color: <?php echo $health_color; ?>;">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $health_description; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="metric-card text-center">
                <div class="status-active">ACTIVE</div>
                <div class="metric-label">Current Status</div>
                <div class="status-programs">
                    <i class="fas fa-star"></i>
                    <?php echo $initiative['agency_program_count']; ?> programs running
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Initiative Information Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">
                        <i class="fas fa-lightbulb me-2"></i>Initiative Information
                    </h5>
                    <div>
                        <?php if ($initiative['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Description -->
                <?php if (!empty($initiative[$initiative_description_col])): ?>
                <div class="initiative-description mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-align-left me-1"></i>Description
                    </h6>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($initiative[$initiative_description_col])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Timeline Information -->
                <div class="mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-calendar-alt me-1"></i>Timeline
                    </h6>
                    <div class="timeline-info p-3 bg-light rounded">
                        <?php if (!empty($initiative[$start_date_col]) || !empty($initiative[$end_date_col])): ?>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-check me-2 text-success"></i>
                                <span>
                                    <?php 
                                    if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                                        echo date('M j, Y', strtotime($initiative[$start_date_col])) . ' - ' . date('M j, Y', strtotime($initiative[$end_date_col]));
                                        
                                        // Calculate duration
                                        $start = new DateTime($initiative[$start_date_col]);
                                        $end = new DateTime($initiative[$end_date_col]);
                                        $interval = $start->diff($end);
                                        echo ' <span class="text-muted">(' . $interval->days . ' days)</span>';
                                    } elseif (!empty($initiative[$start_date_col])) {
                                        echo 'Started: ' . date('M j, Y', strtotime($initiative[$start_date_col]));
                                    } elseif (!empty($initiative[$end_date_col])) {
                                        echo 'Due: ' . date('M j, Y', strtotime($initiative[$end_date_col]));
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                No timeline information available
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Program Statistics -->
                <div class="mb-0">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-chart-bar me-1"></i>Program Overview
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-1"><?php echo $initiative['agency_program_count']; ?></h3>
                                    <div class="small">Your Programs</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-1"><?php echo $initiative['total_program_count']; ?></h3>
                                    <div class="small">Total Programs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Program Rating Distribution Chart -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">
                        <i class="fas fa-chart-pie me-2"></i>Program Rating Distribution
                    </h5>
                    <span class="badge bg-secondary">
                        <?php echo count($programs); ?> programs
                    </span>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($programs)): ?>
                    <?php
                    // Calculate rating distribution for initiative programs
                    $rating_distribution = [
                        'target-achieved' => 0,
                        'on-track' => 0,
                        'on-track-yearly' => 0,
                        'delayed' => 0,
                        'severe-delay' => 0,
                        'completed' => 0,
                        'not-started' => 0
                    ];
                    
                    foreach ($programs as $program) {
                        $status = convert_legacy_rating($program['rating'] ?? 'not_started');
                        if (isset($rating_distribution[$status])) {
                            $rating_distribution[$status]++;
                        } else {
                            $rating_distribution['not-started']++;
                        }
                    }
                    
                    $total_programs = count($programs);
                    
                    // Define display labels and colors
                    $rating_config = [
                        'target-achieved' => ['label' => 'Target Achieved', 'color' => 'success', 'icon' => 'fas fa-check-circle'],
                        'completed' => ['label' => 'Completed', 'color' => 'success', 'icon' => 'fas fa-check-circle'],
                        'on-track' => ['label' => 'On Track', 'color' => 'warning', 'icon' => 'fas fa-clock'],
                        'on-track-yearly' => ['label' => 'On Track (Yearly)', 'color' => 'warning', 'icon' => 'fas fa-calendar-check'],
                        'delayed' => ['label' => 'Delayed', 'color' => 'danger', 'icon' => 'fas fa-exclamation-triangle'],
                        'severe-delay' => ['label' => 'Severe Delay', 'color' => 'danger', 'icon' => 'fas fa-exclamation-circle'],
                        'not-started' => ['label' => 'Not Started', 'color' => 'secondary', 'icon' => 'fas fa-pause-circle']
                    ];
                    ?>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="chart-container" style="position: relative; height:300px; width:100%">
                                <canvas id="initiativeRatingChart"></canvas>
                                <!-- Hidden element for rating data (used by JavaScript) -->
                                <div id="ratingData" style="display: none;">
                                    <?php echo json_encode($rating_distribution); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="rating-stats">
                                <h6 class="text-muted mb-3">Rating Breakdown</h6>
                                
                                <?php foreach ($rating_config as $status => $config): ?>
                                    <?php if ($rating_distribution[$status] > 0): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="<?php echo $config['icon']; ?> me-2 text-<?php echo $config['color']; ?>"></i>
                                            <span><?php echo $config['label']; ?></span>
                                        </div>
                                        <div>
                                            <span class="badge bg-<?php echo $config['color']; ?> me-2">
                                                <?php echo $rating_distribution[$status]; ?>
                                            </span>
                                            <small class="text-muted">
                                                (<?php echo $total_programs > 0 ? round(($rating_distribution[$status] / $total_programs) * 100) : 0; ?>%)
                                            </small>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-chart-pie fa-2x mb-3"></i>
                        <div>No programs found to display rating distribution.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Related Programs -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-tasks me-2"></i>Related Programs
                    <span class="badge bg-secondary ms-2"><?php echo count($programs); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($programs)): ?>
                    <div class="programs-list" style="max-height: 500px; overflow-y: auto;">
                        <?php
                        // Set ownership flag for each program
                        $current_agency_id = $_SESSION['agency_id'] ?? null;
                        foreach ($programs as &$program) {
                            $program['is_owned_by_agency'] = ($program['agency_id'] == $current_agency_id);
                        }
                        unset($program); // break reference
                        ?>
                        <?php foreach ($programs as $program): ?>
                            <div class="program-item mb-3 <?php echo $program['is_owned_by_agency'] ? 'owned' : 'other-agency'; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-medium mb-1">
                                            <?php if (!empty($program['program_number'])): ?>
                                                <span class="badge bg-info me-2" style="font-size: 0.7em;">
                                                    <?php echo htmlspecialchars($program['program_number']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                        </div>
                                        <div class="small text-muted mb-2">
                                            <i class="fas fa-building me-1"></i>
                                            <?php echo htmlspecialchars($program['agency_name']); ?>
                                        </div>
                                        <?php if ($program['is_owned_by_agency']): ?>
                                            <span class="badge bg-primary mb-2" style="font-size: 0.7em;">
                                                <i class="fas fa-star me-1"></i>Your Program
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-2">
                                        <?php
                                        // Use the rating helper to render the status badge
                                        echo get_rating_badge($program['rating'] ?? 'not_started');
                                        ?>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <?php if (!$program['is_owned_by_agency']): ?>
                                        <span class="text-muted small me-2"><i class="fas fa-lock"></i> View-only (other agency)</span>
                                    <?php endif; ?>
                                    <a href="../programs/program_details.php?id=<?php echo (int)$program['program_id']; ?>" class="btn btn-outline-primary btn-sm ms-auto">
                                        <i class="fas fa-eye me-1"></i> View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <div>No programs found under this initiative.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-clock me-2"></i>Recent Activity Feed
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Get recent activities for this initiative using audit logs
                $recent_activities = [];
                
                // Query for recent program-related activities for this initiative
                $activity_sql = "SELECT 
    al.action,
    al.details,
    al.created_at,
    al.user_id,
    a.agency_name,
    u.username
FROM audit_logs al
JOIN users u ON al.user_id = u.user_id
LEFT JOIN agency a ON u.agency_id = a.agency_id
WHERE al.action IN (
    'program_submitted', 'program_draft_saved', 'update_program', 
    'outcome_updated', 'outcome_submitted', 'admin_program_edited',
    'program_finalized', 'resubmit_program'
)
AND al.details REGEXP 'Program (ID|Name):'
ORDER BY al.created_at DESC
LIMIT 15";
                
                $stmt = $conn->prepare($activity_sql);
                $stmt->execute();
                $result = $stmt->get_result();
                
                // Filter activities for this initiative and extract program info
                while ($row = $result->fetch_assoc()) {
                    // Extract program ID from details
                    if (preg_match('/Program ID: (\d+)/', $row['details'], $matches)) {
                        $program_id = intval($matches[1]);
                        
                        // Check if this program belongs to our initiative
                        $program_check_sql = "SELECT program_name, program_number FROM programs WHERE program_id = ? AND initiative_id = ?";
                        $program_stmt = $conn->prepare($program_check_sql);
                        $program_stmt->bind_param('ii', $program_id, $initiative_id);
                        $program_stmt->execute();
                        $program_result = $program_stmt->get_result();
                        
                        if ($program_data = $program_result->fetch_assoc()) {
                            $row['program_name'] = $program_data['program_name'];
                            $row['program_number'] = $program_data['program_number'];
                            $row['program_id'] = $program_id;
                            $recent_activities[] = $row;
                        }
                    } else if (preg_match('/Program Name: ([^|]+)/', $row['details'], $matches)) {
                        $program_name = trim($matches[1]);
                        
                        // Check if this program belongs to our initiative
                        $program_check_sql = "SELECT program_id, program_number FROM programs WHERE program_name = ? AND initiative_id = ?";
                        $program_stmt = $conn->prepare($program_check_sql);
                        $program_stmt->bind_param('si', $program_name, $initiative_id);
                        $program_stmt->execute();
                        $program_result = $program_stmt->get_result();
                        
                        if ($program_data = $program_result->fetch_assoc()) {
                            $row['program_name'] = $program_name;
                            $row['program_number'] = $program_data['program_number'];
                            $row['program_id'] = $program_data['program_id'];
                            $recent_activities[] = $row;
                        }
                    }
                    
                    // Limit to 10 activities for this initiative
                    if (count($recent_activities) >= 10) {
                        break;
                    }
                }
                
                // Function to format activity details
                function formatActivityDescription($action, $details) {
                    switch ($action) {
                        case 'program_submitted':
                            return 'Program submission completed';
                        case 'program_draft_saved':
                            return 'Program draft saved';
                        case 'update_program':
                            return 'Program information updated';
                        case 'outcome_updated':
                            // Extract outcome name from details if available
                            if (preg_match("/Updated.*?outcome '([^']+)'/", $details, $matches)) {
                                return 'Outcome updated: ' . $matches[1];
                            }
                            return 'Program outcome updated';
                        case 'outcome_submitted':
                            return 'Program outcome submitted';
                        case 'admin_program_edited':
                            return 'Program edited by administrator';
                        case 'program_finalized':
                            return 'Program finalized';
                        case 'resubmit_program':
                            return 'Program resubmitted';
                        default:
                            return ucwords(str_replace('_', ' ', $action));
                    }
                }
                
                // Function to get activity icon and color
                function getActivityIcon($action) {
                    switch ($action) {
                        case 'program_submitted':
                        case 'outcome_submitted':
                            return ['icon' => 'fas fa-check-circle', 'color' => 'text-success'];
                        case 'program_draft_saved':
                            return ['icon' => 'fas fa-save', 'color' => 'text-warning'];
                        case 'update_program':
                        case 'outcome_updated':
                            return ['icon' => 'fas fa-edit', 'color' => 'text-primary'];
                        case 'admin_program_edited':
                            return ['icon' => 'fas fa-user-shield', 'color' => 'text-info'];
                        case 'program_finalized':
                            return ['icon' => 'fas fa-lock', 'color' => 'text-success'];
                        case 'resubmit_program':
                            return ['icon' => 'fas fa-redo', 'color' => 'text-secondary'];
                        default:
                            return ['icon' => 'fas fa-file-alt', 'color' => 'text-muted'];
                    }
                }
                ?>
                
                <?php if (!empty($recent_activities)): ?>
                    <div class="activity-list" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($recent_activities as $activity): ?>
                            <?php 
                            $iconData = getActivityIcon($activity['action']);
                            $description = formatActivityDescription($activity['action'], $activity['details']);
                            ?>
                            <div class="activity-item mb-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-medium mb-1">
                                            <i class="<?php echo $iconData['icon']; ?> me-2 <?php echo $iconData['color']; ?>"></i>
                                            <?php echo htmlspecialchars($description); ?>
                                        </div>
                                        
                                        <?php if (!empty($activity['program_name'])): ?>
                                        <div class="small text-muted mb-1">
                                            <?php if (!empty($activity['program_number'])): ?>
                                                <span class="badge bg-info me-2" style="font-size: 0.7em;">
                                                    <?php echo htmlspecialchars($activity['program_number']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($activity['program_name']); ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="small text-muted">
                                            <i class="fas fa-building me-1"></i>
                                            <?php echo htmlspecialchars($activity['agency_name']); ?>
                                            <span class="ms-2">
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($activity['username']); ?>
                                            </span>
                                        </div>
                                        
                                        <!-- Show additional details for complex actions -->
                                        <?php if (in_array($activity['action'], ['outcome_updated', 'admin_program_edited']) && !empty($activity['details'])): ?>
                                        <div class="small text-muted mt-1 fst-italic">
                                            <?php 
                                            // Clean up details for display
                                            $cleanDetails = $activity['details'];
                                            $cleanDetails = preg_replace('/\(ID: \d+\)/', '', $cleanDetails);
                                            $cleanDetails = preg_replace('/\(Metric ID: \d+\)/', '', $cleanDetails);
                                            $cleanDetails = trim($cleanDetails);
                                            if (strlen($cleanDetails) > 80) {
                                                $cleanDetails = substr($cleanDetails, 0, 77) . '...';
                                            }
                                            echo htmlspecialchars($cleanDetails);
                                            ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-2 text-end">
                                        <div class="small text-muted">
                                            <?php echo date('M j, Y', strtotime($activity['created_at'])); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php echo date('g:i A', strtotime($activity['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <div>No recent activity found for this initiative.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</main>

<!-- Status Grid Chart Section -->
<div class="container-fluid mb-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-line me-2"></i>Initiative Status Grid
            </h5>
        </div>
        <div class="card-body p-0">
            <div id="status_grid_here">
                <div class="status-grid-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading status grid...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Grid Component -->
<script src="<?php echo asset_url('js', 'components/status-grid.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get initiative ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const initiativeId = urlParams.get('id');
    
    if (initiativeId) {
        // Initialize StatusGrid component with status grid data API
        const apiUrl = "<?php echo rtrim(BASE_URL, '/'); ?>/app/api/simple_gantt_data.php?initiative_id=" + initiativeId;
        const statusGrid = new StatusGrid('status_grid_here', apiUrl);
        
        // Store reference globally for debugging
        window.statusGrid = statusGrid;
        
        // Add a test function to check status data
        window.testStatusData = function() {
            console.log('=== STATUS DATA TEST ===');
            const data = statusGrid.data;
            if (!data || !data.programs) {
                console.log('No data or programs found');
                return;
            }
            
            data.programs.forEach((program, pi) => {
                console.log(`\nProgram ${pi}: ${program.program_name}`);
                if (!program.targets || program.targets.length === 0) {
                    console.log('  No targets');
                    return;
                }
                
                program.targets.forEach((target, ti) => {
                    console.log(`  Target ${ti}: ${target.target_text}`);
                    console.log(`    status_by_period:`, target.status_by_period);
                    
                    if (target.status_by_period) {
                        Object.entries(target.status_by_period).forEach(([periodId, status]) => {
                            console.log(`      Period ${periodId}: "${status}"`);
                        });
                    }
                });
            });
            
            console.log('\nTimeline periods_map:', data.timeline?.periods_map);
        };
        
        // Run the test after a short delay
        setTimeout(() => {
            window.testStatusData();
        }, 1000);
    } else {
        document.getElementById('status_grid_here').innerHTML = 
            '<div class="status-grid-error">No initiative ID provided.</div>';
    }
});
</script>



<?php
// Include footer
require_once '../../layouts/footer.php';
?>

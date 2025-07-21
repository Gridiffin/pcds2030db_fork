<?php
/**
 * Activity Feed Partial
 * Shows recent activities related to this initiative
 */

// Include activity helper functions
require_once PROJECT_ROOT_PATH . 'app/lib/activity_helpers.php';

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
?>

<!-- Recent Activity Feed -->
<div class="card shadow-sm mt-4">
    <div class="card-header">
        <h5 class="card-title m-0">
            <i class="fas fa-clock me-2"></i>Recent Activity Feed
        </h5>
    </div>
    <div class="card-body">
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

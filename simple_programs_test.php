<?php
/**
 * Simplified View Programs Test
 * Tests if basic page loading works without advanced features
 */

// Include necessary files using correct paths
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/db_connect.php';
require_once __DIR__ . '/app/lib/session.php';
require_once __DIR__ . '/app/lib/functions.php';
require_once __DIR__ . '/app/lib/agencies/index.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$agency_id = $_SESSION['agency_id'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Programs Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Simple Programs Test</h2>
        
        <div class="alert alert-info">
            <strong>User Info:</strong><br>
            - User ID: <?php echo $_SESSION['user_id'] ?? 'N/A'; ?><br>
            - Agency ID: <?php echo $agency_id ?? 'N/A'; ?><br>
            - Role: <?php echo $_SESSION['role'] ?? 'N/A'; ?><br>
        </div>
        
        <?php if ($agency_id): ?>
            <h3>Programs for Agency <?php echo $agency_id; ?></h3>
            
            <?php
            // Simple query without complex joins
            $simple_query = "SELECT p.program_id, p.program_name, paa.role 
                           FROM programs p 
                           LEFT JOIN program_agency_assignments paa ON p.program_id = paa.program_id 
                           WHERE paa.agency_id = ? AND paa.is_active = 1 AND p.is_deleted = 0
                           ORDER BY p.program_name";
            
            $stmt = $conn->prepare($simple_query);
            $stmt->bind_param("i", $agency_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Program ID</th>
                            <th>Program Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($program = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($program['program_id']); ?></td>
                                <td><?php echo htmlspecialchars($program['program_name']); ?></td>
                                <td><?php echo htmlspecialchars($program['role']); ?></td>
                                <td>
                                    <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
                                       class="btn btn-sm btn-primary">View Details</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">No programs found for your agency.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-danger">Agency ID not found in session.</div>
        <?php endif; ?>
        
        <hr>
        <div class="mt-3">
            <a href="app/views/agency/programs/view_programs.php" class="btn btn-secondary">Try Full Programs Page</a>
            <a href="debug_programs.php" class="btn btn-info">Run Debug Script</a>
            <a href="login.php" class="btn btn-warning">Logout</a>
        </div>
    </div>
    
    <!-- Test JavaScript Console -->
    <script>
        console.log('Simple programs page loaded successfully');
        console.log('Session data:', {
            user_id: '<?php echo $_SESSION['user_id'] ?? 'N/A'; ?>',
            agency_id: '<?php echo $agency_id ?? 'N/A'; ?>',
            role: '<?php echo $_SESSION['role'] ?? 'N/A'; ?>'
        });
        
        // Test clicking on links
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                console.log('Link clicked:', this.href);
                // Don't prevent default, let the link work normally
            });
        });
    </script>
</body>
</html>

<?php
/**
 * Create Program Content Partial
 * Main content for program creation page
 */

// Get active initiatives for dropdown
$active_initiatives = get_initiatives_for_select(true);

// Get users in current agency for assignment
$agency_id = $_SESSION['agency_id'] ?? null;
$agency_users = [];
if ($agency_id) {
    $stmt = $conn->prepare("
        SELECT user_id, username, fullname 
        FROM users 
        WHERE agency_id = ? AND role = 'agency' AND is_active = 1
        ORDER BY fullname, username
    ");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $agency_users[] = $row;
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Initiative data for JavaScript -->
            <script>
                window.initiativeData = <?php echo json_encode($active_initiatives); ?>;
            </script>

            <!-- Simple Program Creation Form -->
            <div class="card-modern card-elevated-modern create-program-card">
                <div class="card-header-modern">
                    <h5 class="card-title-modern">
                        <div class="card-icon-modern text-forest-deep">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        Create New Program
                    </h5>
                </div>
                <div class="card-body-modern">
                    <form method="post" id="createProgramForm" class="form-modern program-form">
                        <div class="row">
                            <div class="col-md-8">
                                <?php require 'program_form.php'; ?>
                            </div>

                            <div class="col-md-4">
                                <?php require 'timeline_section.php'; ?>
                                <?php require 'permissions_section.php'; ?>
                                <?php require 'info_section.php'; ?>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="view_programs.php" class="btn-modern btn-outline-secondary-modern">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn-modern btn-primary-modern">
                                <i class="fas fa-save me-2"></i>
                                Create Program
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 
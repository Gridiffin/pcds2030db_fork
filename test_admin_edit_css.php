<?php
// Simple test page to verify CSS loading for admin edit program
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Edit Program CSS</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    
    <!-- Admin Programs CSS Bundle -->
    <link rel="stylesheet" href="/pcds2030_dashboard_fork/dist/css/admin-programs.bundle.css">
</head>
<body class="admin-layout">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1>Test Admin Edit Program CSS</h1>
                
                <!-- Test Bootstrap Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Test Form Card
                        </h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label for="test_input" class="form-label">Test Input</label>
                                <input type="text" class="form-control" id="test_input" placeholder="This should be styled">
                            </div>
                            <div class="mb-3">
                                <label for="test_select" class="form-label">Test Select</label>
                                <select class="form-select" id="test_select">
                                    <option>Test option</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Test Button
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Test Badges -->
                <div class="mb-3">
                    <span class="badge bg-info me-2">Test Badge</span>
                    <span class="badge bg-secondary">Agency Badge</span>
                </div>
                
                <!-- Test Alert -->
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    CSS is loading correctly if this looks good!
                </div>
            </div>
        </div>
    </div>
</body>
</html>

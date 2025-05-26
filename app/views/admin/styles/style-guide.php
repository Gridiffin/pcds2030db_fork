<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/asset_helpers.php';

$pageTitle = 'Style Guide';

// Add custom styles for the style guide page
$additionalStyles = '
<style>
    .color-swatch {
        height: 100px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    
    .component-example {
        padding: 1.5rem;
        border: 1px solid rgba(var(--forest-deep-rgb), 0.1);
        border-radius: 8px;
        margin-bottom: 1rem;
        background-color: rgba(var(--forest-light-rgb), 0.05);
    }
    
    .status-indicator-example {
        display: inline-block;
        margin: 0 1rem 1rem 0;
    }
    
    .code-block {
        background-color: #f7f9fc;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid var(--forest-medium);
    }
    
    .card-example {
        max-width: 350px;
        margin-bottom: 1.5rem;
    }
    
    .section-divider {
        margin: 3rem 0;
        border-top: 1px solid rgba(var(--forest-medium-rgb), 0.2);
    }
    
    .layout-example {
        background-color: rgba(var(--forest-light-rgb), 0.15);
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 6px;
        text-align: center;
    }
</style>
';

require_once __DIR__ . '/../../views/layouts/header.php';
require_once __DIR__ . '/../../views/layouts/admin_nav.php';

// Set up the dashboard header
$title = "Style Guide";
$subtitle = "A comprehensive guide to the PCDS 2030 Dashboard forestry theme design system";
$headerStyle = 'light';

require_once ROOT_PATH . 'app/lib/dashboard_header.php';
?>

<div class="container-fluid px-4">
    <!-- Quick navigation -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Quick Navigation</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <nav>
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#colors">Colors</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#typography">Typography</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#buttons">Buttons</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#cards">Cards</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#tables">Tables</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#badges">Badges</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#progress">Progress</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#forms">Forms</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#layout">Layout</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#dashboard">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="#programs">Programs</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Colors -->
    <section class="mb-5" id="colors">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Colors</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">The forestry theme uses a natural color palette inspired by forest environments, emphasizing greens with complementary earth tones.</p>
                
                <h5 class="mb-3">Primary Palette</h5>
                <div class="row g-4">
                    <!-- Forest Theme Colors -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="color-swatch" style="background-color: var(--forest-deep);"></div>
                                <h5 class="mt-3">Forest Deep</h5>
                                <code>#537D5D</code>
                                <p class="small text-muted">Primary actions, emphasis</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="color-swatch" style="background-color: var(--forest-medium);"></div>
                                <h5 class="mt-3">Forest Medium</h5>
                                <code>#73946B</code>
                                <p class="small text-muted">Secondary elements</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="color-swatch" style="background-color: var(--forest-light);"></div>
                                <h5 class="mt-3">Forest Light</h5>
                                <code>#9EBC8A</code>
                                <p class="small text-muted">Backgrounds, accents</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="color-swatch" style="background-color: var(--forest-pale);"></div>
                                <h5 class="mt-3">Forest Pale</h5>
                                <code>#D2D0A0</code>
                                <p class="small text-muted">Subtle highlights</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5 class="mb-3 mt-4">Status Colors</h5>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="color-swatch" style="background-color: var(--success-color);"></div>
                                <h5 class="mt-3">Success</h5>
                                <code>var(--success-color)</code>
                                <p class="small text-muted">Completed actions, success states</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="color-swatch" style="background-color: var(--warning-color);"></div>
                                <h5 class="mt-3">Warning</h5>
                                <code>var(--warning-color)</code>
                                <p class="small text-muted">Alerts, on-track states</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="color-swatch" style="background-color: var(--danger-color);"></div>
                                <h5 class="mt-3">Danger</h5>
                                <code>var(--danger-color)</code>
                                <p class="small text-muted">Errors, delays</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="color-swatch" style="background-color: var(--info-color);"></div>
                                <h5 class="mt-3">Info</h5>
                                <code>var(--info-color)</code>
                                <p class="small text-muted">Information, neutral states</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Accessibility Notes -->
                <div class="alert alert-info mt-4">
                    <h5><i class="fas fa-universal-access me-2"></i>Accessibility Considerations</h5>
                    <p>When using the forestry theme colors, consider these WCAG 2.1 accessibility guidelines:</p>
                    <ul class="mb-0">
                        <li><strong>Forest Deep (#537D5D):</strong> Meets AA standards with 5.3:1 contrast against white backgrounds. Suitable for all text.</li>
                        <li><strong>Forest Medium (#73946B):</strong> 3.6:1 contrast against white. Only use for large text (18pt+) or bold headings on white backgrounds.</li>
                        <li><strong>Forest Light (#9EBC8A):</strong> Low contrast against white. Best used as a background color with dark text.</li>
                        <li><strong>Forest Pale (#D2D0A0):</strong> Poor contrast against white. Only use as a background with dark text or in decorative elements.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Typography -->
    <section class="mb-5" id="typography">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Typography</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">The dashboard uses a modern, clean font system for optimal readability across devices.</p>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3">Font Family</h4>
                                <p class="mb-2"><strong>Primary Font:</strong> Inter</p>
                                <p class="mb-2"><strong>Secondary Font:</strong> Inter</p>
                                <p class="small text-muted">Uses system fallbacks when Inter is not available.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3">Font Weights</h4>
                                <p class="fw-light">Light (300)</p>
                                <p class="fw-normal">Regular (400)</p>
                                <p class="fw-medium">Medium (500)</p>
                                <p class="fw-semibold">Semibold (600)</p>
                                <p class="fw-bold">Bold (700)</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3">Headings</h4>
                                <h1>Heading 1</h1>
                                <h2>Heading 2</h2>
                                <h3>Heading 3</h3>
                                <h4>Heading 4</h4>
                                <h5>Heading 5</h5>
                                <h6>Heading 6</h6>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3">Text Utilities</h4>
                                <p class="text-primary">Primary Text</p>
                                <p class="text-secondary">Secondary Text</p>
                                <p class="text-success">Success Text</p>
                                <p class="text-danger">Danger Text</p>
                                <p class="text-warning">Warning Text</p>
                                <p class="text-info">Info Text</p>
                                <p class="text-muted">Muted Text</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Buttons -->
    <section class="mb-5" id="buttons">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Buttons</h5>
            </div>
            <div class="card-body">
                <h5 class="mb-3">Basic Buttons</h5>
                <div class="component-example mb-4">
                    <button class="btn btn-primary me-2 mb-2">Primary</button>
                    <button class="btn btn-secondary me-2 mb-2">Secondary</button>
                    <button class="btn btn-success me-2 mb-2">Success</button>
                    <button class="btn btn-danger me-2 mb-2">Danger</button>
                    <button class="btn btn-warning me-2 mb-2">Warning</button>
                    <button class="btn btn-info me-2 mb-2">Info</button>
                    <button class="btn btn-light me-2 mb-2">Light</button>
                    <button class="btn btn-dark me-2 mb-2">Dark</button>
                </div>

                <h5 class="mb-3">Outline Buttons</h5>
                <div class="component-example mb-4">
                    <button class="btn btn-outline-primary me-2 mb-2">Outline Primary</button>
                    <button class="btn btn-outline-secondary me-2 mb-2">Outline Secondary</button>
                    <button class="btn btn-outline-success me-2 mb-2">Outline Success</button>
                    <button class="btn btn-outline-danger me-2 mb-2">Outline Danger</button>
                    <button class="btn btn-outline-warning me-2 mb-2">Outline Warning</button>
                    <button class="btn btn-outline-info me-2 mb-2">Outline Info</button>
                </div>

                <h5 class="mb-3">Button Sizes</h5>
                <div class="component-example mb-4">
                    <button class="btn btn-primary btn-sm me-2 mb-2">Small</button>
                    <button class="btn btn-primary me-2 mb-2">Regular</button>
                    <button class="btn btn-primary btn-lg me-2 mb-2">Large</button>
                </div>

                <h5 class="mb-3">Icon Buttons</h5>
                <div class="component-example mb-4">
                    <button class="btn btn-primary me-2 mb-2">
                        <i class="fas fa-save me-2"></i>Save
                    </button>
                    <button class="btn btn-success me-2 mb-2">
                        <i class="fas fa-check me-2"></i>Approve
                    </button>
                    <button class="btn btn-danger me-2 mb-2">
                        <i class="fas fa-trash me-2"></i>Delete
                    </button>
                    <button class="btn btn-warning me-2 mb-2">
                        <i class="fas fa-edit me-2"></i>Edit
                    </button>
                </div>

                <h5 class="mb-3">Button States</h5>
                <div class="component-example">
                    <button class="btn btn-primary me-2 mb-2">Normal</button>
                    <button class="btn btn-primary me-2 mb-2 active">Active</button>
                    <button class="btn btn-primary me-2 mb-2" disabled>Disabled</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Cards -->
    <section class="mb-5" id="cards">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Cards</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">Cards contain content and actions about a single subject.</p>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h5 class="mb-3">Basic Card</h5>
                        <div class="card card-example">
                            <div class="card-body">
                                <h5 class="card-title">Card Title</h5>
                                <p class="card-text">This is a basic card with only a body section. It can contain text, buttons, and other content.</p>
                                <a href="#" class="btn btn-primary">Action</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <h5 class="mb-3">Card with Header</h5>
                        <div class="card card-example">
                            <div class="card-header">
                                <h5 class="card-title m-0">Featured</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">This card has a header with a gradient background using the forest theme colors.</p>
                                <a href="#" class="btn btn-outline-primary">Action</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h5 class="mb-3">Card with Footer</h5>
                        <div class="card card-example">
                            <div class="card-body">
                                <h5 class="card-title">Card Title</h5>
                                <p class="card-text">This card has a footer section which is ideal for actions or additional information.</p>
                            </div>
                            <div class="card-footer">
                                <small class="text-muted">Last updated 3 mins ago</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <h5 class="mb-3">Complete Card</h5>
                        <div class="card card-example">
                            <div class="card-header">
                                <h5 class="card-title m-0">Complete Card</h5>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Secondary Title</h5>
                                <p class="card-text">A complete card with header, body, and footer sections, demonstrating the full forestry theme styling.</p>
                                <a href="#" class="btn btn-primary">Primary Action</a>
                                <a href="#" class="btn btn-outline-secondary ms-2">Secondary</a>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <small class="text-muted">Last updated 3 mins ago</small>
                                <small><a href="#">View details</a></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tables -->
    <section class="mb-5" id="tables">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Tables</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">Tables display information in a grid-like format and allow users to scan and compare data.</p>
                
                <h5 class="mb-3">Basic Table</h5>
                <div class="component-example mb-4">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Program</th>
                                    <th scope="col">Agency</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">1</th>
                                    <td>Reforestation Initiative</td>
                                    <td>Department of Environment</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <th scope="row">2</th>
                                    <td>Wildlife Protection Plan</td>
                                    <td>Conservation Society</td>
                                    <td><span class="badge bg-warning">On Track</span></td>
                                </tr>
                                <tr>
                                    <th scope="row">3</th>
                                    <td>Watershed Management</td>
                                    <td>Water Resources Agency</td>
                                    <td><span class="badge bg-danger">Delayed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <h5 class="mb-3">Striped Table</h5>
                <div class="component-example mb-4">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Program</th>
                                    <th scope="col">Agency</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">1</th>
                                    <td>Reforestation Initiative</td>
                                    <td>Department of Environment</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <th scope="row">2</th>
                                    <td>Wildlife Protection Plan</td>
                                    <td>Conservation Society</td>
                                    <td><span class="badge bg-warning">On Track</span></td>
                                </tr>
                                <tr>
                                    <th scope="row">3</th>
                                    <td>Watershed Management</td>
                                    <td>Water Resources Agency</td>
                                    <td><span class="badge bg-danger">Delayed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <h5 class="mb-3">Hover Table</h5>
                <div class="component-example">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Program</th>
                                    <th scope="col">Agency</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">1</th>
                                    <td>Reforestation Initiative</td>
                                    <td>Department of Environment</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <th scope="row">2</th>
                                    <td>Wildlife Protection Plan</td>
                                    <td>Conservation Society</td>
                                    <td><span class="badge bg-warning">On Track</span></td>
                                </tr>
                                <tr>
                                    <th scope="row">3</th>
                                    <td>Watershed Management</td>
                                    <td>Water Resources Agency</td>
                                    <td><span class="badge bg-danger">Delayed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Badges -->
    <section class="mb-5" id="badges">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Badges</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">Badges are small count and labeling components, typically used to highlight or categorize items.</p>
                
                <h5 class="mb-3">Standard Badges</h5>
                <div class="component-example mb-4">
                    <span class="badge bg-primary me-2 mb-2">Primary</span>
                    <span class="badge bg-secondary me-2 mb-2">Secondary</span>
                    <span class="badge bg-success me-2 mb-2">Success</span>
                    <span class="badge bg-danger me-2 mb-2">Danger</span>
                    <span class="badge bg-warning text-dark me-2 mb-2">Warning</span>
                    <span class="badge bg-info me-2 mb-2">Info</span>
                    <span class="badge bg-light text-dark me-2 mb-2">Light</span>
                    <span class="badge bg-dark me-2 mb-2">Dark</span>
                </div>
                
                <h5 class="mb-3">Program Status Badges</h5>
                <div class="component-example mb-4">
                    <span class="badge bg-success me-2 mb-2">Completed</span>
                    <span class="badge bg-warning text-dark me-2 mb-2">On Track</span>
                    <span class="badge bg-danger me-2 mb-2">Delayed</span>
                    <span class="badge bg-danger me-2 mb-2">Severe Delay</span>
                    <span class="badge bg-info me-2 mb-2">Target Achieved</span>
                    <span class="badge bg-secondary me-2 mb-2">Not Started</span>
                </div>
                
                <h5 class="mb-3">Badges with Icons</h5>
                <div class="component-example mb-4">
                    <span class="badge bg-success me-2 mb-2"><i class="fas fa-check-circle me-1"></i>Completed</span>
                    <span class="badge bg-warning text-dark me-2 mb-2"><i class="fas fa-chart-line me-1"></i>On Track</span>
                    <span class="badge bg-danger me-2 mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Delayed</span>
                    <span class="badge bg-secondary me-2 mb-2"><i class="fas fa-clock me-1"></i>Not Started</span>
                </div>
                
                <h5 class="mb-3">Badge in Buttons</h5>
                <div class="component-example">
                    <button class="btn btn-primary me-2 mb-2">
                        Messages <span class="badge bg-light text-dark ms-2">4</span>
                    </button>
                    <button class="btn btn-success me-2 mb-2">
                        Notifications <span class="badge bg-light text-dark ms-2">7</span>
                    </button>
                    <button class="btn btn-outline-primary me-2 mb-2">
                        Pending <span class="badge bg-primary ms-2">2</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Progress -->
    <section class="mb-5" id="progress">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Progress Indicators</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">Progress indicators show the status of a process or task completion rate.</p>
                
                <h5 class="mb-3">Basic Progress Bars</h5>
                <div class="component-example mb-4">
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">50%</div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">75%</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">100%</div>
                    </div>
                </div>
                
                <h5 class="mb-3">Progress with Label</h5>
                <div class="component-example mb-4">
                    <div class="progress-wrapper">
                        <div class="progress-label">
                            <span>Reforestation Progress</span>
                            <span class="progress-value">65%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                
                <h5 class="mb-3">Progress Bar Variants</h5>
                <div class="component-example mb-4">
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">Success</div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">Info</div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">Warning</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">Danger</div>
                    </div>
                </div>
                
                <h5 class="mb-3">Striped Progress Bars</h5>
                <div class="component-example">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Forms -->
    <section class="mb-5" id="forms">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Forms</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">Form components collect user input in a structured manner.</p>
                
                <h5 class="mb-3">Basic Form Controls</h5>
                <div class="component-example mb-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="exampleFormControlInput1" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="exampleFormControlSelect1" class="form-label">Select menu</label>
                            <select class="form-select" id="exampleFormControlSelect1">
                                <option>Option 1</option>
                                <option>Option 2</option>
                                <option>Option 3</option>
                                <option>Option 4</option>
                                <option>Option 5</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="exampleFormControlTextarea1" class="form-label">Remarks</label>
                        <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                    </div>
                </div>
                
                <h5 class="mb-3">Form Validation States</h5>
                <div class="component-example mb-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="validationServer01" class="form-label">Valid input</label>
                            <input type="text" class="form-control is-valid" id="validationServer01" value="Mark Johnson" required>
                            <div class="valid-feedback">
                                Looks good!
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="validationServer02" class="form-label">Invalid input</label>
                            <input type="text" class="form-control is-invalid" id="validationServer02" required>
                            <div class="invalid-feedback">
                                Please provide a valid input.
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5 class="mb-3">Form Input Groups</h5>
                <div class="component-example mb-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text">@</span>
                        <input type="text" class="form-control" placeholder="Username">
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Recipient's username" aria-label="Recipient's username" aria-describedby="basic-addon2">
                        <span class="input-group-text" id="basic-addon2">@example.com</span>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control" aria-label="Amount (to the nearest dollar)">
                        <span class="input-group-text">.00</span>
                    </div>
                </div>
                
                <h5 class="mb-3">Form Checkboxes and Radios</h5>
                <div class="component-example">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="mb-2">Checkboxes</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                <label class="form-check-label" for="flexCheckDefault">
                                    Default checkbox
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" checked>
                                <label class="form-check-label" for="flexCheckChecked">
                                    Checked checkbox
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDisabled" disabled>
                                <label class="form-check-label" for="flexCheckDisabled">
                                    Disabled checkbox
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="mb-2">Radio Buttons</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">
                                <label class="form-check-label" for="flexRadioDefault1">
                                    Default radio
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" checked>
                                <label class="form-check-label" for="flexRadioDefault2">
                                    Default checked radio
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="flexRadioDisabled" id="flexRadioDisabled" disabled>
                                <label class="form-check-label" for="flexRadioDisabled">
                                    Disabled radio
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Layout Components -->
    <section class="mb-5" id="layout">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Layout Components</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">Key layout components used throughout the dashboard.</p>
                
                <h5 class="mb-3">Headers</h5>
                <div class="component-example mb-4">
                    <div class="simple-header light p-3 mb-3">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="header-title">Light Header</h3>
                                    <p class="header-subtitle mb-0">Used on inner pages</p>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary btn-sm">Action</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="simple-header primary p-3">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="header-title">Primary Header</h3>
                                    <p class="header-subtitle mb-0">Used on main dashboard pages</p>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-light btn-sm">Action</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5 class="mb-3">Grid System</h5>
                <div class="component-example mb-4">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="layout-example">Full Width (col-md-12)</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="layout-example">Half Width (col-md-6)</div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="layout-example">Half Width (col-md-6)</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="layout-example">One Third (col-md-4)</div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="layout-example">One Third (col-md-4)</div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="layout-example">One Third (col-md-4)</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <div class="layout-example">Quarter (col-md-3)</div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="layout-example">Quarter (col-md-3)</div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="layout-example">Quarter (col-md-3)</div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="layout-example">Quarter (col-md-3)</div>
                        </div>
                    </div>
                </div>
                
                <h5 class="mb-3">Navigation</h5>
                <div class="component-example">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3">Main Navigation</h6>
                            <nav class="navbar navbar-expand-lg navbar-light bg-light rounded">
                                <div class="container-fluid">
                                    <a class="navbar-brand" href="#">PCDS 2030</a>
                                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarExample" aria-controls="navbarExample" aria-expanded="false" aria-label="Toggle navigation">
                                        <span class="navbar-toggler-icon"></span>
                                    </button>
                                    <div class="collapse navbar-collapse" id="navbarExample">
                                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                            <li class="nav-item">
                                                <a class="nav-link active" aria-current="page" href="#">Dashboard</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="#">Programs</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="#">Reports</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="#">Settings</a>
                                            </li>
                                        </ul>
                                        <div class="d-flex">
                                            <button class="btn btn-outline-primary btn-sm">Log Out</button>
                                        </div>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">Secondary Navigation (Tabs)</h6>
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" aria-current="page" href="#">Overview</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">Details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">Timeline</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">Reports</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dashboard Components -->
    <section class="mb-5" id="dashboard">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Dashboard Components</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">Components specific to dashboard views within the application.</p>
                
                <h5 class="mb-3">Stat Cards</h5>
                <div class="component-example mb-4">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card primary">
                                <div class="card-body">
                                    <div class="icon-container">
                                        <i class="fas fa-clipboard-list stat-icon"></i>
                                    </div>
                                    <div class="stat-card-content">
                                        <div class="stat-title">Total Programs</div>
                                        <div class="stat-value">24</div>
                                        <div class="stat-subtitle">
                                            <i class="fas fa-check me-1"></i>
                                            All Programs
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stat-card warning">
                                <div class="card-body">
                                    <div class="icon-container">
                                        <i class="fas fa-calendar-check stat-icon"></i>
                                    </div>
                                    <div class="stat-card-content">
                                        <div class="stat-title">On Track Programs</div>
                                        <div class="stat-value">16</div>
                                        <div class="stat-subtitle">
                                            <i class="fas fa-chart-line me-1"></i>
                                            67% of total
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stat-card danger">
                                <div class="card-body">
                                    <div class="icon-container">
                                        <i class="fas fa-exclamation-triangle stat-icon"></i>
                                    </div>
                                    <div class="stat-card-content">
                                        <div class="stat-title">Delayed Programs</div>
                                        <div class="stat-value">5</div>
                                        <div class="stat-subtitle">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            21% of total
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="stat-card success">
                                <div class="card-body">
                                    <div class="icon-container">
                                        <i class="fas fa-check-circle stat-icon"></i>
                                    </div>
                                    <div class="stat-card-content">
                                        <div class="stat-title">Completed Programs</div>
                                        <div class="stat-value">3</div>
                                        <div class="stat-subtitle">
                                            <i class="fas fa-flag-checkered me-1"></i>
                                            12% of total
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5 class="mb-3">Period Selector</h5>
                <div class="component-example mb-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">Current Reporting Period</h5>
                                    <p class="text-muted mb-0">Q3 2023 (Jul 1 - Sep 30, 2023)</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="input-group">
                                        <select class="form-select form-select-sm">
                                            <option value="current">Q3 2023</option>
                                            <option value="previous">Q2 2023</option>
                                            <option value="previous">Q1 2023</option>
                                        </select>
                                        <button class="btn btn-outline-secondary btn-sm" type="button">View</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5 class="mb-3">Filter Controls</h5>
                <div class="component-example">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title m-0">
                                <i class="fas fa-filter me-2"></i>Filter Programs
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="filterStatus" class="form-label">Status</label>
                                    <select id="filterStatus" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="completed">Completed</option>
                                        <option value="on-track">On Track</option>
                                        <option value="delayed">Delayed</option>
                                        <option value="not-started">Not Started</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filterAgency" class="form-label">Agency</label>
                                    <select id="filterAgency" class="form-select">
                                        <option value="">All Agencies</option>
                                        <option value="1">Department of Environment</option>
                                        <option value="2">Conservation Society</option>
                                        <option value="3">Water Resources Agency</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filterType" class="form-label">Program Type</label>
                                    <select id="filterType" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="agency-created">Agency Created</option>
                                    </select>
                                </div>
                                <div class="col-12 text-end">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Apply Filters
                                    </button>
                                    <button class="btn btn-outline-secondary ms-2">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Program Management Components -->
    <section class="mb-5" id="programs">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Program Management Components</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">Components specific to program management features within the application.</p>
                
                <h5 class="mb-3">Program Status Indicators</h5>
                <div class="component-example mb-4">
                    <div class="status-indicator-example">
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>Completed
                        </span>
                    </div>
                    <div class="status-indicator-example">
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-chart-line me-1"></i>On Track
                        </span>
                    </div>
                    <div class="status-indicator-example">
                        <span class="badge bg-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>Delayed
                        </span>
                    </div>
                    <div class="status-indicator-example">
                        <span class="badge bg-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>Severe Delay
                        </span>
                    </div>
                    <div class="status-indicator-example">
                        <span class="badge bg-secondary">
                            <i class="fas fa-clock me-1"></i>Not Started
                        </span>
                    </div>
                </div>
                
                <h5 class="mb-3">Program Timeline</h5>
                <div class="component-example mb-4">
                    <ul class="timeline">
                        <li class="timeline-item">
                            <div class="timeline-marker bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h5 class="mb-1">Program Created</h5>
                                <p class="text-muted mb-0">January 15, 2023</p>
                                <p>Initial program setup and configuration</p>
                            </div>
                        </li>
                        <li class="timeline-item">
                            <div class="timeline-marker bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h5 class="mb-1">First Report Submitted</h5>
                                <p class="text-muted mb-0">March 30, 2023</p>
                                <p>Q1 program metrics submitted and approved</p>
                            </div>
                        </li>
                        <li class="timeline-item">
                            <div class="timeline-marker bg-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="timeline-content">
                                <h5 class="mb-1">Second Report Due</h5>
                                <p class="text-muted mb-0">June 30, 2023</p>
                                <p>Pending submission for Q2 metrics</p>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <h5 class="mb-3">Program Metrics Display</h5>
                <div class="component-example">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title m-0">Program Metrics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="metric-box">
                                        <span class="metric-label">Target</span>
                                        <span class="metric-value">1,500</span>
                                        <span class="metric-unit">Hectares</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-box">
                                        <span class="metric-label">Achievement</span>
                                        <span class="metric-value">950</span>
                                        <span class="metric-unit">Hectares</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="progress-wrapper">
                                        <div class="progress-label">
                                            <span>Completion Rate</span>
                                            <span class="progress-value">63.3%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 63.3%" aria-valuenow="63.3" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // Simple script to make the navigation pills work
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('.nav-pills .nav-link');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('href').substring(1);
                document.querySelectorAll('.nav-pills .nav-link').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
                document.getElementById(target).scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Activate the first pill by default
        if (links.length > 0) {
            links[0].classList.add('active');
        }
    });
</script>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>

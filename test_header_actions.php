<?php
/**
 * Test Page Header Actions
 * 
 * Simple test to verify header actions functionality works correctly
 */

// Define the project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include config
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Set page title
$pageTitle = 'Header Actions Test';

// Set up variables for base layout
$cssBundle = 'main';
$jsBundle = null;

// Configure test header with actions
$header_config = [
    'title' => 'Test Page Header Actions',
    'subtitle' => 'This page tests the action buttons functionality',
    'breadcrumb' => [
        ['text' => 'Home', 'url' => '/'],
        ['text' => 'Test', 'url' => null]
    ],
    'actions' => [
        [
            'text' => 'Add New User',
            'url' => APP_URL . '/app/views/admin/users/add_user.php',
            'class' => 'btn-light',
            'icon' => 'fas fa-user-plus'
        ],
        [
            'text' => 'Settings',
            'url' => '#',
            'class' => 'btn-light',
            'icon' => 'fas fa-cog'
        ],
        [
            'html' => '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Actions Working</span>'
        ]
    ]
];

// Set content
$contentFile = null;

include PROJECT_ROOT_PATH . '/app/views/layouts/base.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Header Actions Test</h5>
                    <p class="card-text">
                        If the header actions are working correctly, you should see:
                    </p>
                    <ul>
                        <li>"Add New User" button with user-plus icon</li>
                        <li>"Settings" button with cog icon</li>
                        <li>Green badge saying "Actions Working"</li>
                    </ul>
                    <p class="card-text">
                        All actions should be aligned to the right side of the header, next to the title and subtitle.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

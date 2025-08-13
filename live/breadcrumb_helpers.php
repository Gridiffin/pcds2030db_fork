<?php
/**
 * Breadcrumb Helper Functions
 * 
 * Provides standardized breadcrumb generation for consistent navigation
 * across admin and agency pages.
 */

/**
 * Generate breadcrumb for admin pages
 * 
 * @param string $current_page Current page name
 * @param array $additional_items Additional breadcrumb items
 * @return array Breadcrumb configuration
 */
function get_admin_breadcrumb($current_page, $additional_items = []) {
    $breadcrumb = [
        [
            'text' => 'Home',
            'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'
        ]
    ];
    
    // Add additional items
    foreach ($additional_items as $item) {
        $breadcrumb[] = $item;
    }
    
    // Add current page
    $breadcrumb[] = [
        'text' => $current_page,
        'url' => null // Current page, no link
    ];
    
    return $breadcrumb;
}

/**
 * Generate breadcrumb for agency pages
 * 
 * @param string $current_page Current page name
 * @param array $additional_items Additional breadcrumb items
 * @return array Breadcrumb configuration
 */
function get_agency_breadcrumb($current_page, $additional_items = []) {
    $breadcrumb = [
        [
            'text' => 'Home',
            'url' => APP_URL . '/app/views/agency/dashboard/dashboard.php'
        ]
    ];
    
    // Add additional items
    foreach ($additional_items as $item) {
        $breadcrumb[] = $item;
    }
    
    // Add current page
    $breadcrumb[] = [
        'text' => $current_page,
        'url' => null // Current page, no link
    ];
    
    return $breadcrumb;
}

/**
 * Generate breadcrumb for program-related pages
 * 
 * @param string $user_type 'admin' or 'agency'
 * @param string $program_name Program name (optional)
 * @param array $additional_items Additional breadcrumb items
 * @return array Breadcrumb configuration
 */
function get_program_breadcrumb($user_type, $program_name = null, $additional_items = []) {
    if ($user_type === 'admin') {
        $breadcrumb = [
            [
                'text' => 'Home',
                'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'
            ],
            [
                'text' => 'Programs',
                'url' => APP_URL . '/app/views/admin/programs/programs.php'
            ]
        ];
    } else {
        $breadcrumb = [
            [
                'text' => 'Home',
                'url' => APP_URL . '/app/views/agency/dashboard/dashboard.php'
            ],
            [
                'text' => 'My Programs',
                'url' => APP_URL . '/app/views/agency/programs/view_programs.php'
            ]
        ];
    }
    
    // Add additional items
    foreach ($additional_items as $item) {
        $breadcrumb[] = $item;
    }
    
    // Add program name if provided
    if ($program_name) {
        $breadcrumb[] = [
            'text' => $program_name,
            'url' => null
        ];
    }
    
    return $breadcrumb;
}

/**
 * Generate breadcrumb for outcome-related pages
 * 
 * @param string $user_type 'admin' or 'agency'
 * @param string $outcome_name Outcome name (optional)
 * @param array $additional_items Additional breadcrumb items
 * @return array Breadcrumb configuration
 */
function get_outcome_breadcrumb($user_type, $outcome_name = null, $additional_items = []) {
    if ($user_type === 'admin') {
        $breadcrumb = [
            [
                'text' => 'Home',
                'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'
            ],
            [
                'text' => 'Outcomes',
                'url' => APP_URL . '/app/views/admin/outcomes/manage_outcomes.php'
            ]
        ];
    } else {
        $breadcrumb = [
            [
                'text' => 'Home',
                'url' => APP_URL . '/app/views/agency/dashboard/dashboard.php'
            ],
            [
                'text' => 'Outcomes',
                'url' => APP_URL . '/app/views/agency/outcomes/submit_outcomes.php'
            ]
        ];
    }
    
    // Add additional items
    foreach ($additional_items as $item) {
        $breadcrumb[] = $item;
    }
    
    // Add outcome name if provided
    if ($outcome_name) {
        $breadcrumb[] = [
            'text' => $outcome_name,
            'url' => null
        ];
    }
    
    return $breadcrumb;
}

/**
 * Generate breadcrumb for initiative-related pages
 * 
 * @param string $user_type 'admin' or 'agency'
 * @param string $initiative_name Initiative name (optional)
 * @param array $additional_items Additional breadcrumb items
 * @return array Breadcrumb configuration
 */
function get_initiative_breadcrumb($user_type, $initiative_name = null, $additional_items = []) {
    if ($user_type === 'admin') {
        $breadcrumb = [
            [
                'text' => 'Home',
                'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'
            ],
            [
                'text' => 'Initiatives',
                'url' => APP_URL . '/app/views/admin/initiatives/manage_initiatives.php'
            ]
        ];
    } else {
        $breadcrumb = [
            [
                'text' => 'Home',
                'url' => APP_URL . '/app/views/agency/dashboard/dashboard.php'
            ],
            [
                'text' => 'Initiatives',
                'url' => APP_URL . '/app/views/agency/initiatives/initiatives.php'
            ]
        ];
    }
    
    // Add additional items
    foreach ($additional_items as $item) {
        $breadcrumb[] = $item;
    }
    
    // Add initiative name if provided
    if ($initiative_name) {
        $breadcrumb[] = [
            'text' => $initiative_name,
            'url' => null
        ];
    }
    
    return $breadcrumb;
}
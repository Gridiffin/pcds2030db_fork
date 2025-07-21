<?php

function vite_assets() {
    $manifest_path = 'public/dist/manifest.json';
    $is_dev = getenv('APP_ENV') === 'development';

    if ($is_dev) {
        return '
            <script type_ ="module" src="http://localhost:3000/@vite/client"></script>
            <script type="module" src="http://localhost:3000/frontend/main.js"></script>
        ';
    }

    if (!file_exists($manifest_path)) {
        return '';
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);
    $html = '';

    if (isset($manifest['frontend/main.js']['file'])) {
        $html .= '<script type="module" src="/public/dist/' . $manifest['frontend/main.js']['file'] . '"></script>';
    }

    if (isset($manifest['frontend/main.js']['css'])) {
        foreach ($manifest['frontend/main.js']['css'] as $css_file) {
            $html .= '<link rel="stylesheet" href="/public/dist/' . $css_file . '">';
        }
    }

    return $html;
} 
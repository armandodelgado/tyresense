<?php
/**
 * TyreSense AI — Entry point
 * Corre con: php -S localhost:8080
 * Rutea /api/* al router de agentes.
 * El resto sirve el mockup PHP existente.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (str_starts_with($uri, '/api/')) {
    require __DIR__ . '/api/router.php';
    exit;
}

// Fallback al mockup PHP existente
$screen = $_GET['screen'] ?? 'home';
$validScreens = ['home','scan','result','history','supply'];
if (!in_array($screen, $validScreens)) $screen = 'home';

require __DIR__ . '/index_original.php';

<?php declare(strict_types=1);

/**
 * Shared hosting fallback when .htaccess rewrites are unavailable.
 *
 * Access routes using:
 *   /index.php/
 *   /index.php/articles
 *   /index.php/articles/my-slug
 */

// Normalize to make existing router logic work.
$path = '/';
if (!empty($_SERVER['PATH_INFO'])) {
    $path = $_SERVER['PATH_INFO'];
} else {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $uriPath = parse_url($uri, PHP_URL_PATH) ?: '/';
    $path = preg_replace('#^/index\.php#', '', $uriPath);
    $path = $path ?: '/';
}

// Make downstream parse_url($_SERVER['REQUEST_URI']) see the desired path.
$_SERVER['REQUEST_URI'] = $path;

require __DIR__ . '/index.htaccess.php';

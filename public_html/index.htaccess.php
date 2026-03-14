<?php declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$routes = require __DIR__ . '/../routes/web.php';

// normalize path
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? $path : '/';
$path = rtrim($path, '/');
if ($path === '') $path = '/';

/**
 * Article detail by SLUG:
 *  /articles/my-news-slug
 */
if (preg_match('~^/articles/([a-z0-9\-]+)$~i', $path, $m)) {
    $controller = new ArticlesController($config, $http, $view);
    $controller->show(['slug' => $m[1]]);
    exit;
}
/**
 * Department detail by SLUG:
 *  /departments/operations
 */
if (preg_match('~^/departments/([a-z0-9\-]+)$~i', $path, $m)) {
    $controller = new DepartmentsController($config, $http, $view);
    $controller->show(['slug' => $m[1]]);
    exit;
}



if (!isset($routes[$path])) {
    http_response_code(404);
    echo "404 Not Found";
    exit;
}

[$class, $method] = $routes[$path];

$controller = new $class($config, $http, $view);
$controller->$method();

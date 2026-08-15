<?php
session_start();
require_once 'config.php';

// Simple Router implementation
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);

// Base Path Configuration
$base_url = "http://localhost/webpresensi";
define('BASE_URL', $base_url);

// Global Error Handler for Production vs Development
define('ENVIRONMENT', 'development'); // set to 'production' for live
if (ENVIRONMENT == 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Router Logic
require_once 'app/core/Controller.php';
$routes = require_once 'routes/web.php';

$method = $_SERVER['REQUEST_METHOD'];

$routeFound = false;
foreach ($routes as $route) {
    if ($route['method'] == $method) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $route['url']);
        $pattern = "@^" . $pattern . "$@D";
        
        if (preg_match($pattern, $url, $matches)) {
            array_shift($matches); // remove the full match
            
            // Check middleware
            if (isset($route['middleware'])) {
                $middlewares = (array) $route['middleware'];
                foreach ($middlewares as $middleware) {
                    if ($middleware == 'auth' && !isset($_SESSION['user_id'])) {
                        header("Location: " . BASE_URL . "/login");
                        exit;
                    }
                    if (strpos($middleware, 'role:') === 0) {
                        $role = explode(':', $middleware)[1];
                        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != $role) {
                            die("Akses Ditolak. Halaman ini hanya untuk " . $role);
                        }
                    }
                }
            }

            // Call the controller action
            list($controller, $action) = explode('@', $route['action']);
            require_once "app/controllers/" . $controller . ".php";
            $controllerInstance = new $controller($pdo);
            call_user_func_array([$controllerInstance, $action], $matches);
            
            $routeFound = true;
            break;
        }
    }
}

if (!$routeFound) {
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found";
}

<?php
// Front Controller - index.php
// Handles all incoming requests and routes them to appropriate controllers

// Define base paths
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');
define('CONFIG_PATH', BASE_PATH . '/config');

define('PUBLIC_URL', '/'); // adjust if deployed in subfolder

// Load configuration
require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';

// Start secure session after ini configurations are applied
session_start();

// If Composer autoloader is present, load it (enables third-party packages)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Load global helpers
if (file_exists(BASE_PATH . '/App/helpers.php')) {
    require_once BASE_PATH . '/App/helpers.php';
}

// Autoloader for classes under app/, core/, config/
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\', '/', $class);
    $paths = [
        BASE_PATH . '/' . $classPath . '.php',
        APP_PATH . '/' . $classPath . '.php',
        CORE_PATH . '/' . $classPath . '.php',
        CONFIG_PATH . '/' . $classPath . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Simple router based on URL path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if (strpos($requestUri, $scriptName) === 0) {
    $requestUri = substr($requestUri, strlen($scriptName));
}
$requestUri = trim($requestUri, '/');

// Default route
if ($requestUri === '' || $requestUri === 'home') {
    $controller = new App\Controllers\HomeController();
    $controller->index();
    exit;
}

// Parse controller/action/params
$segments = explode('/', $requestUri);
$controllerName = ucfirst(array_shift($segments)) . 'Controller';
$action = array_shift($segments) ?? 'index';
$params = $segments;

$controllerClass = 'App\\Controllers\\' . $controllerName;
if (!class_exists($controllerClass)) {
    http_response_code(404);
    echo 'Controller not found';
    exit;
}
$controller = new $controllerClass();
if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo 'Action not found';
    exit;
}
call_user_func_array([$controller, $action], $params);
?>

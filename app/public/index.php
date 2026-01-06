<?php
require __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;
use Services\AuthService;
use Dotenv\Dotenv;

// 1. Load Environment Variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$router = new Router();
$auth = new AuthService();

// --- Public Routes ---
$router->post('/login', 'Controllers\AuthController@login');

// --- Protected Routes (Middleware) ---
$router->before('GET|POST|PUT|DELETE', '/api/.*', function() use ($auth) {
    // Validates token for all /api/ routes
    $auth->validateToken();
});

// Grouping API routes
$router->mount('/api', function() use ($router, $auth) {

    // Users Resource
    $router->mount('/users', function() use ($router, $auth) {

        // GET /api/users (Admin only example)
        $router->get('/', function() use ($auth) {
            $auth->requireRole('admin');
            (new \Controllers\UserController())->index();
        });

        // POST /api/users
        $router->post('/', 'Controllers\UserController@store');

        // Add PUT and DELETE mappings here...
    });
});

// 404 Handler
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Endpoint not found']);
});

$router->run();

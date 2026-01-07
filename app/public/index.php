<?php
require __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;
use Services\AuthService;
use Dotenv\Dotenv;

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

// --- Protected Routes ---
$router->before('GET|POST|PUT|DELETE', '/api/.*', function() use ($auth) {
    // Validates token for all /api/ routes
    $auth->validateToken();
});

// Grouping API routes
$router->mount('/api', function() use ($router, $auth) {

    // Users Resource
    $router->mount('/users', function() use ($router, $auth) {

        $router->get('/', 'Controllers\UserController@index');
        $router->post('/', 'Controllers\UserController@store');

        // New Routes using {id} parameter
        $router->put('/(\d+)', 'Controllers\UserController@update');   // matches /api/users/1
        $router->delete('/(\d+)', 'Controllers\UserController@destroy'); // matches /api/users/1
    });

    $router->mount('/families', function() use ($router, $auth) {
        // Publicly readable? Or protected?
        // Assuming protected based on requirements:
        $router->get('/', function() use ($auth) {
            $auth->validateToken();
            (new \Controllers\FamilyController())->index();
        });
    });

    $router->mount('/transactions', function() use ($router, $auth) {
        $router->post('/', function() use ($auth) {
            $auth->validateToken();
            // Optional: Check if user is admin before allowing cash updates
            // $auth->requireRole('admin');
            (new \Controllers\FamilyController())->transaction();
        });
    });

    // Optional history endpoint
    $router->get('/history', function() use ($auth) {
        $auth->validateToken();
        (new \Controllers\FamilyController())->history();
    });
});

// 404 Handler
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Endpoint not found']);
});

$router->run();

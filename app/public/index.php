<?php
require __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;
use Services\AuthService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

date_default_timezone_set('Europe/Amsterdam');

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
$router->get('/ping', 'Controllers\Controller@register');
$router->get('/diagnostics', 'Controllers\Controller@diagnostics');

// --- Protected Routes ---
$router->before('GET|POST|PUT|DELETE', '/api/.*', function() use ($auth) {
    // Validates token for all /api/ routes
    $auth->validateToken();
});

// Grouping API routes
$router->mount('/api', function() use ($router, $auth) {

    // Users Resource
    $router->mount('/users', function() use ($router, $auth) {
        $router->get('/', 'Controllers\UserController@getAll');
        $router->get('/{id}', 'Controllers\UserController@getById');
        $router->post('/', 'Controllers\UserController@newUser');
        $router->put('/{id}', 'Controllers\UserController@updateUser');
        $router->delete('/{id}', 'Controllers\UserController@deleteUser');
    });

    $router->mount('/companies', function() use ($router, $auth) {
        $router->get('/', 'Controllers\CompanyController@getAll');
        $router->get('/{id}', 'Controllers\CompanyController@getById');
    });

    $router->mount('/tasks', function() use ($router, $auth) {
        $router->get('/', 'Controllers\TaskController@getAll');
        $router->post('/complete', 'Controllers\TaskController@complete');
    });

    $router->mount('/history', function() use ($router, $auth) {
        $router->get('/{dateTime}', 'Controllers\HistoryController@getHistorySince');
        $router->post('/save', 'Controllers\HistoryController@saveHistory');
    });

    $router->mount('/transactions', function() use ($router, $auth) {
        $router->get('/', 'Controllers\TransactionController@history');
        $router->post('/', 'Controllers\TransactionController@create');
        $router->post('/transfer', 'Controllers\TransactionController@transfer');
    });

    $router->mount('/stocks', function() use ($router, $auth) {
        $router->get('/', 'Controllers\StockController@getAllStocks');
        $router->get('/bank', 'Controllers\StockController@getBankStocks');
        $router->get('/company/{companyId}', 'Controllers\StockController@getCompanyStocks');
        $router->post('/trade', 'Controllers\StockController@trade');
    });
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Endpoint not found']);
});

$router->run();

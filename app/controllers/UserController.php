<?php
namespace Controllers;

use Config\Database;
use Repositories\UserRepository;
use Services\AuthService;

class UserController {
    private UserRepository $userRepo;

    public function __construct() {
        $this->userRepo = new UserRepository(Database::getConnection());
    }

    public function index() {
        // 1. Pagination Logic
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // 2. Filtering Logic
        $filters = [];
        if (isset($_GET['role'])) $filters['role'] = $_GET['role'];

        // 3. Fetch Data
        $users = $this->userRepo->findAll($filters, $limit, $offset);

        // 4. Return Response
        header('Content-Type: application/json');
        echo json_encode([
            'data' => $users,
            'meta' => [
                'page' => $page,
                'limit' => $limit
            ]
        ]);
    }

    public function store() {
        $input = json_decode(file_get_contents('php://input'), true);

        // Basic Validation
        if (!isset($input['email']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        try {
            $newId = $this->userRepo->create($input);
            http_response_code(201); // Created
            echo json_encode([
                'message' => 'User created',
                'id' => $newId,
                'user' => $input // In production, exclude sensitive fields like password
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

<?php
namespace Controllers;

use Config\Database;
use Repositories\UserRepository;
use Services\UserService;

class UserController {
    private UserService $userService;

    public function __construct() {
        // Dependency Injection Setup
        $db = Database::getConnection();
        $repo = new UserRepository($db);
        $this->userService = new UserService($repo);
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

        if (!isset($input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        try {
            $newId = $this->userService->registerUser($input);
            http_response_code(201);
            echo json_encode(['message' => 'User created', 'id' => $newId]);
        } catch (\Exception $e) {
            http_response_code(400); // 400 Bad Request usually better for "Email exists"
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // PUT /api/users/{id}
    public function update($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        // 1. Check if user exists
        $user = $this->userRepo->findById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        // 2. Perform Update
        try {
            $this->userRepo->update($id, $input);

            // 3. Return updated object
            $updatedUser = $this->userRepo->findById($id);
            echo json_encode([
                'message' => 'User updated',
                'data' => $updatedUser
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Update failed: ' . $e->getMessage()]);
        }
    }

    // DELETE /api/users/{id}
    public function destroy($id) {
        $user = $this->userRepo->findById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        try {
            $this->userRepo->delete($id);
            http_response_code(200);
            echo json_encode(['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Deletion failed']);
        }
    }
}

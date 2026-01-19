<?php
namespace Controllers;

use Models\DTO\UserManyRequest;
use Services\UserService;
use Exception;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    /**
     * @throws Exception
     */
    public function getAll() {
        try {
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $role = $_GET['role'] ?? null;

            $request = UserManyRequest::Create($limit, $offset, $role);

            $users = $this->userService->getAllUsers($request);
        } catch (Exception $e){
            $this->respondWithError($e->getCode(), $e->getMessage());
        }

        if(!empty($users)) {
            $this->respond($users);
        } else {
            $this->respondWithError(204, "No users found");
        }
    }

    public function newUser() {
        $newUser = $this->requestObjectFromPostedJson(UserCreateRequest::class);

        if (!isset($input['email'], $input['password'])) {
            $this->respondWithError(400, "Missing email or password");
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
        $user = $this->userService->findById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        // 2. Perform Update
        try {
            $this->userService->update($id, $input);

            // 3. Return updated object
            $updatedUser = $this->userService->findById($id);
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
        $user = $this->userService->findById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        try {
            $this->userService->delete($id);
            http_response_code(200);
            echo json_encode(['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Deletion failed']);
        }
    }
}

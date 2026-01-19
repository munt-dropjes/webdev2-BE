<?php
namespace Controllers;

use Models\DTO\UserCreateRequest;
use Models\DTO\UserManyRequest;
use Models\DTO\UserUpdateRequest;
use models\User;
use Services\UserService;
use Exception;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    public function getAll() {
        try {
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
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

    public function getById(int $id) {
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $user = $this->userService->getById($id);
            if ($user) {
                $this->respond($user);
            } else {
                $this->respondWithError(404, "User not found");
            }
        } catch (Exception $e) {
            $this->respondWithError($e->getCode(), $e->getMessage());
        }
    }

    public function newUser() {
        $newUserRequest = $this->requestObjectFromPostedJson(UserCreateRequest::class);

        if (!$newUserRequest->email || !$newUserRequest->password || !$newUserRequest->username){
            $this->respondWithError(400, "Missing fields: email, username, password are required");
        }

        try {
            $newUser = $this->userService->registerUser($newUserRequest);
            $this->respond($newUser);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode(), $e->getMessage());
        }
    }

    public function updateUser(int $id) {
        try {
            $inputUser = $this->requestObjectFromPostedJson(UserUpdateRequest::class);

            $this->respond($this->userService->updateUser($id, $inputUser));
        } catch (Exception $e) {
            $this->respondWithError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function deleteUser($id) {
        $user = $this->userService->getById($id);
        if (!$user) {
            $this->respondWithError(404, "User not found");
        }

        try {
            $this->userService->deleteUser($id);
            $this->respond(['message' => 'User deleted successfully']);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode(), $e->getMessage());
        }
    }
}

<?php
namespace Services;

use Models\DTO\UserCreateRequest;
use Models\DTO\UserManyRequest;
use Models\DTO\UserResponse;
use Models\DTO\UserUpdateRequest;
use Repositories\UserRepository;
use Exception;

class UserService {
    private UserRepository $userRepo;

    public function __construct() {
        $this->userRepo = new UserRepository();
    }

    /**
     * @throws Exception
     */
    public function getAllUsers(UserManyRequest $request): array {
        $users = $this->userRepo->findAll($request);

        $response = [];
        foreach ($users as $user) {
            $response[] = UserResponse::CreateFromUser($user);
        }

        return $response;
    }

    /**
     * @throws Exception
     */
    public function getById(int $id): ?UserResponse {
        return UserResponse::CreateFromUser($this->userRepo->findById($id));
    }

    /**
     * @throws Exception
     */
    public function registerUser(UserCreateRequest $request): UserResponse {
        try {
            if ($this->userRepo->findByUsername($request->username)) {
                throw new Exception("Username already exists", 400);
            }

            $request->email = strtolower($request->email);
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format", 400);
            }

            $request->password = password_hash($request->password, PASSWORD_DEFAULT);

            $created = $this->userRepo->create($request);
            if (!$created) {
                throw new Exception("User creation failed", 500);
            }

            return UserResponse::CreateFromUser($this->userRepo->findByUsername($request->username));
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function updateUser(int $id, UserUpdateRequest $request): UserResponse {
        try {
            $user = $this->userRepo->findById($id);
            if (!$user) {
                throw new Exception("User not found", 404);
            }

            // Business Logic: If updating password, hash it first
            if (!empty($request->password)) {
                $request->password = password_hash($request->password, PASSWORD_DEFAULT);
            }

            $updatingUser = new UserUpdateRequest();
            $updatingUser->id = $id;
            $updatingUser->username = $request->username ?? $user->username;
            $updatingUser->email = $request->email ?? $user->email;
            $updatingUser->password = $request->password ?? $user->password;
            $updatingUser->role = $request->role ?? $user->role;

            $this->userRepo->update($updatingUser);
            return UserResponse::CreateFromUser($this->userRepo->findById($id));
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function deleteUser(int $id): void {
        try{
            $this->userRepo->delete($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }
}

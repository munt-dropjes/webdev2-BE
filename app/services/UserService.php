<?php
namespace Services;

use Models\DTO\UserCreateRequest;
use Models\DTO\UserManyRequest;
use models\User;
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
        return $this->userRepo->findAll($request);
    }

    /**
     * @throws Exception
     */
    public function getById(int $id): ?User {
        return $this->userRepo->findById($id);
    }

    /**
     * @throws Exception
     */
    public function registerUser(UserCreateRequest $request): User {
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

            return $this->userRepo->findByUsername($request->username);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function modifyUser(int $id, array $data): array {
        $user = $this->userRepo->findById($id);
        if (!$user) {
            throw new Exception("User not found", 400);
        }

        // Business Logic: If updating password, hash it first
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $this->userRepo->update($id, $data);
        return $this->userRepo->findById($id);
    }

    /**
     * @throws Exception
     */
    public function deleteUser(int $id): void {
        if (!$this->userRepo->delete($id)) {
            throw new Exception("User not found or could not be deleted", 400);
        }
    }
}

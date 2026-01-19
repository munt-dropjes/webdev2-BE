<?php
namespace Services;

use Models\DTO\UserManyRequest;
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
    public function registerUser(array $data): int {
        if ($this->userRepo->findByUsername($data['username'])) {
            throw new Exception("Username already exists", 400);
        }

        // Business Logic: Hash Password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->userRepo->create($data);
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

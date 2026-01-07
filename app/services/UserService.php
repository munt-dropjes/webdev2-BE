<?php
namespace Services;

use Repositories\UserRepository;
use Exception;

class UserService {
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo) {
        $this->userRepo = $userRepo;
    }

    public function getAllUsers(array $filters, int $page, int $limit): array {
        $offset = ($page - 1) * $limit;
        return $this->userRepo->findAll($filters, $limit, $offset);
    }

    public function registerUser(array $data): int {
        // Business Logic: Check if email exists
        if ($this->userRepo->findByEmail($data['email'])) {
            throw new Exception("Email already in use");
        }

        // Business Logic: Hash Password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->userRepo->create($data);
    }

    public function modifyUser(int $id, array $data): array {
        $user = $this->userRepo->findById($id);
        if (!$user) {
            throw new Exception("User not found");
        }

        // Business Logic: If updating password, hash it first
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $this->userRepo->update($id, $data);
        return $this->userRepo->findById($id);
    }

    public function deleteUser(int $id): void {
        if (!$this->userRepo->delete($id)) {
            throw new Exception("Could not delete user");
        }
    }
}

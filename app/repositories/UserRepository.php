<?php
namespace Repositories;

use Models\DTO\UserCreateRequest;
use Models\DTO\UserManyRequest;
use Models\User;
use PDO;
use Exception;

class UserRepository extends Repository {
    /**
     * @throws Exception
     */
    public function findAll(UserManyRequest $request): array {
        try {
            $sql = "SELECT id, username, email, role, created_at FROM users WHERE 1=1";
            $params = [];

            // Filtering
            if (isset($filters['role'])) {
                $sql .= " AND role = :role";
                $params[':role'] = $filters['role'];
            }

            // Pagination
            $sql .= " LIMIT :limit OFFSET :offset";

            $stmt = $this->connection->prepare($sql);

            // Bind params manually for Limit/Offset as they must be integers
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $request->limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $request->offset, PDO::PARAM_INT);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function findByUsername(string $username) : User {
        try {
            $stmt = $this->connection->prepare("SELECT id, username, email, password, role, created_at FROM users WHERE username = :username");
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function findById(int $id) {
        try {
            $stmt = $this->connection->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function create(UserCreateRequest $request): bool {
        try {
            $sql = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(":username", $request->username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $request->email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $request->password, PDO::PARAM_STR);
            $stmt->bindParam(":role", $request->role, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function update(User $user): bool {
        try {
            $stmt = $this->connection->prepare("UPDATE users SET username = :username, email = :email, password = :password, role = :role WHERE id = :id");
            $stmt->bindParam(":username", $user->username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $user->email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $user->password, PDO::PARAM_STR);
            $stmt->bindParam(":role", $user->role, PDO::PARAM_STR);
            $stmt->bindParam(":id", $user->id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function delete(int $id) : bool {
        try {
            $stmt = $this->connection->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }
}

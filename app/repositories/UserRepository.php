<?php
namespace Repositories;

use PDO;

class UserRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findAll(array $filters, int $limit, int $offset): array {
        $sql = "SELECT id, name, email, role FROM users WHERE 1=1";
        $params = [];

        // Filtering
        if (isset($filters['role'])) {
            $sql .= " AND role = :role";
            $params[':role'] = $filters['role'];
        }

        // Pagination
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // Bind params manually for Limit/Offset as they must be integers
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => $data['password'],
            ':role' => $data['role'] ?? 'user'
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByEmail(string $email) {
        $stmt = $this->db->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(); // Returns false if not found
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT id, name, email, role FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function update(int $id, array $data): bool {
        // Dynamic query construction allows updating specific fields
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = :role";
            $params[':role'] = $data['role'];
        }
        // Note: Password update logic usually requires separate handling for hashing

        if (empty($fields)) {
            return false; // Nothing to update
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}

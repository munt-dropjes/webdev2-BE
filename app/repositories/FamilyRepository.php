<?php
namespace Repositories;

use PDO;
use Exception;

class FamilyRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findAll(): array {
        $stmt = $this->db->query("SELECT id, name, color, cash FROM families");
        return $stmt->fetchAll();
    }

    public function getTransactionHistory(int $limit = 50): array {
        $sql = "SELECT t.id, t.amount, t.reason, t.created_at, f.name as family_name 
                FROM transactions t
                JOIN families f ON t.family_id = f.id
                ORDER BY t.created_at DESC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Updates cash balance and creates a transaction record atomically.
     */
    public function updateCash(int $familyId, float $amount, string $reason): bool {
        try {
            $this->db->beginTransaction();

            // 1. Update Family Balance
            $stmt = $this->db->prepare("UPDATE families SET cash = cash + :amount WHERE id = :id");
            $stmt->execute([':amount' => $amount, ':id' => $familyId]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Family not found");
            }

            // 2. Log Transaction
            $stmt = $this->db->prepare("INSERT INTO transactions (family_id, amount, reason) VALUES (:fid, :amount, :reason)");
            $stmt->execute([
                ':fid' => $familyId,
                ':amount' => $amount,
                ':reason' => $reason
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

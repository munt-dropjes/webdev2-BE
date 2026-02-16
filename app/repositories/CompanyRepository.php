<?php
namespace Repositories;

use Models\Company;
use PDO;
use Exception;

class CompanyRepository extends Repository {

    /**
     * @throws Exception
     */
    public function findAll(): array {
        try {
            $stmt = $this->connection->prepare("SELECT id, name, cash, color, is_npc, created_at FROM companies");
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, Company::class);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?Company {
        try {
            $stmt = $this->connection->prepare("SELECT id, name, cash, color, is_npc, created_at FROM companies WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, Company::class);
            $result = $stmt->fetch();
            return $result === false ? null : $result;
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }
}

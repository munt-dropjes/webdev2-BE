<?php
namespace Repositories;

use models\Company;
use Models\DTO\BaseManyRequest;
use Models\DTO\TransactionCreateRequest;
use models\Transaction;
use PDO;
use Exception;

class CompanyRepository extends Repository {

    /**
     * @throws Exception
     */
    public function findAll(): array {
        try {
            $stmt = $this->connection->prepare("SELECT id, name, color, cash, created_at FROM companies");
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
            $stmt = $this->connection->prepare("SELECT id, name, color, cash FROM families WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, Company::class);
            $result = $stmt->fetch();
            return $result === false ? null : $result;
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function createTransaction(TransactionCreateRequest $request): bool {
        try {
            $this->connection->beginTransaction();

            $stmtUpdate = $this->connection->prepare("UPDATE companies SET cash = cash + :amount WHERE id = :id");
            $stmtUpdate->bindParam(':amount', $request->amount);
            $stmtUpdate->bindParam(':id', $request->company_id, PDO::PARAM_INT);
            $stmtUpdate->execute();

            if ($stmtUpdate->rowCount() === 0) {
                $this->connection->rollBack();
                return false;
            }

            $stmtLog = $this->connection->prepare("INSERT INTO transactions (company_id, amount, description) VALUES (:cid, :amount, :description)");
            $stmtLog->bindParam(':cid', $request->company_id, PDO::PARAM_INT);
            $stmtLog->bindParam(':amount', $request->amount);
            $stmtLog->bindParam(':description', $request->description, PDO::PARAM_STR);
            $stmtLog->execute();

            $this->connection->commit();
            return true;
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw new Exception("Database Transaction Failed: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function getHistory(int $limit = 50): array {
        try {
            $sql = "SELECT * FROM transactions ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, Transaction::class);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }
}

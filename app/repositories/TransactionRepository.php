<?php

namespace Repositories;

use Exception;
use Models\DTO\TransactionCreateRequest;
use Models\DTO\TransactionManyRequest;
use Models\Transaction;
use PDO;

class TransactionRepository extends Repository
{
    /**
     * @throws Exception
     */
    public function getByCompany(TransactionManyRequest $request): array {
        try {
            $sql = "SELECT * FROM transactions WHERE company_id = :cid ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':cid', $request->user->company_id);
            $stmt->bindParam(':limit', $request->limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $request->offset, PDO::PARAM_INT);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, Transaction::class);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }

    }

    /**
     * @throws Exception
     */
    public function getAll(TransactionManyRequest $request): array {
        try {
            $sql = "SELECT * FROM transactions ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':limit', $request->limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $request->offset, PDO::PARAM_INT);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, Transaction::class);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function create(TransactionCreateRequest $request): bool {
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
}

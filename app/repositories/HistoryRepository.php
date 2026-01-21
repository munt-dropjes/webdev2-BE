<?php

namespace Repositories;

use Exception;
use PDO;

class HistoryRepository extends Repository
{
    /**
     * @throws Exception
     */
    public function hasHistoryFor(string $timestamp): bool
    {
        try {
            $stmt = $this->connection->prepare("SELECT id FROM companies_history WHERE recorded_at = :ts LIMIT 1");
            $stmt->execute([':ts' => $timestamp]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            // If DB fails, we can't check, so we assume false or throw.
            // Throwing is safer to prevent duplicate data on DB errors.
            throw new Exception("Failed to check snapshot history: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function saveHistoryFor(array $companies, string $timestamp): void {
        try {
            $this->connection->beginTransaction();

            $sql = "INSERT INTO companies_history (company_id, net_worth, stock_price, recorded_at) VALUES (:cid, :nw, :sp, :ts)";
            $stmt = $this->connection->prepare($sql);

            foreach ($companies as $c) {
                $stmt->execute([
                    ':cid' => $c->id,
                    ':nw' => $c->net_worth,
                    ':sp' => $c->stock_price,
                    ':ts' => $timestamp
                ]);
            }

            $this->connection->commit();
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw new Exception("Snapshot save failed: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getHistorySince(string $dateTime): array {
        try {
            $sql = "SELECT h.company_id, c.name, h.net_worth, h.stock_price, h.recorded_at 
                    FROM companies_history h
                    JOIN companies c ON h.company_id = c.id
                    WHERE h.recorded_at >= :dt
                    ORDER BY h.recorded_at ASC";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':dt', $dateTime);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Database Exception: " . $e->getMessage(), 500);
        }
    }
}

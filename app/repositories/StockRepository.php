<?php
namespace Repositories;

use Models\DTO\StockTradeRequest;
use Models\Stock;
use PDO;
use Exception;

class StockRepository extends Repository {

    public function getPortfolio(?int $ownerId): array {
        // Condition: owner_id IS NULL for Bank, = :id for companies
        $condition = ($ownerId === null) ? "s.owner_id IS NULL" : "s.owner_id = :oid";

        $sql = "SELECT s.company_id, c.name as company_name, s.amount, 
                GREATEST(1, FLOOR(c.cash / 100)) as current_price 
                FROM shares s 
                JOIN companies c ON s.company_id = c.id 
                WHERE $condition";

        $stmt = $this->connection->prepare($sql);
        if ($ownerId !== null) $stmt->bindParam(':oid', $ownerId);

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, Stock::class);
        return $stmt->fetchAll();
    }

    public function getShareAmount(int $stockCompanyId, ?int $ownerId): int {
        $sql = "SELECT amount FROM shares WHERE company_id = :sid AND ";
        $sql .= ($ownerId === null) ? "owner_id IS NULL" : "owner_id = :oid";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':sid', $stockCompanyId);
        if ($ownerId !== null) $stmt->bindParam(':oid', $ownerId);

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * @throws Exception
     */
    public function executeTrade(StockTradeRequest $req, int $totalCost): void {
        try {
            $this->connection->beginTransaction();

            // 1. Transfer Cash
            $this->connection->prepare("UPDATE companies SET cash = cash - ? WHERE id = ?")
                ->execute([$totalCost, $req->buyer_id]);

            if ($req->seller_id !== null) {
                $this->connection->prepare("UPDATE companies SET cash = cash + ? WHERE id = ?")
                    ->execute([$totalCost, $req->seller_id]);
            }

            // 2. Transfer Stock (Remove from Seller)
            $sqlSub = "UPDATE shares SET amount = amount - :amt WHERE company_id = :sid AND ";
            $sqlSub .= ($req->seller_id === null) ? "owner_id IS NULL" : "owner_id = :oid";
            $stmtSub = $this->connection->prepare($sqlSub);
            $stmtSub->bindValue(':amt', $req->amount);
            $stmtSub->bindValue(':sid', $req->stock_company_id);
            if ($req->seller_id !== null) $stmtSub->bindValue(':oid', $req->seller_id);
            $stmtSub->execute();

            // 3. Transfer Stock (Add to Buyer)
            $sqlAdd = "INSERT INTO shares (company_id, owner_id, amount) VALUES (:sid, :bid, :amt)
                       ON DUPLICATE KEY UPDATE amount = amount + :amt";
            $this->connection->prepare($sqlAdd)->execute([
                ':sid' => $req->stock_company_id,
                ':bid' => $req->buyer_id,
                ':amt' => $req->amount
            ]);

            // 4. Log Transaction
            $sellerName = $req->seller_id ? "Company " . $req->seller_id : "The Bank";
            $this->connection->prepare("INSERT INTO transactions (company_id, amount, description) VALUES (?, ?, ?)")
                ->execute([$req->buyer_id, -$totalCost, "Bought {$req->amount} shares of Company {$req->stock_company_id} from $sellerName"]);

            if ($req->seller_id !== null) {
                $this->connection->prepare("INSERT INTO transactions (company_id, amount, description) VALUES (?, ?, ?)")
                    ->execute([$req->seller_id, $totalCost, "Sold {$req->amount} shares of Company {$req->stock_company_id} to Company {$req->buyer_id}"]);
            }

            $this->connection->commit();
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            throw new Exception("Trade failed: " . $e->getMessage(), 500);
        }
    }

    public function getAllActiveShares(): array {
        $sql = "SELECT owner_id, company_id, amount FROM shares WHERE owner_id IS NOT NULL";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllShares(): array {
        $sql = "SELECT owner_id, company_id, amount FROM shares";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

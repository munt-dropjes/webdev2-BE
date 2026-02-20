<?php

namespace Repositories;

use Exception;
use Models\DTO\TradeOfferRequest;
use Models\TradeOffer;
use PDO;

class TradeOfferRepository extends Repository
{
    /**
     * @throws Exception
     */
    public function createOffer(int $buyerId, TradeOfferRequest $request): void {
        try {
            $sql = "INSERT INTO trade_offers (buyer_id, seller_id, target_company_id, amount, total_price) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                $buyerId,
                $request->seller_id,
                $request->target_company_id,
                $request->amount,
                $request->total_price
            ]);
        } catch (Exception $e) {
            throw new Exception("Database Error: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function getPendingOffers(int $companyId): array {
        try {
            $sql = "SELECT t.*, b.name as buyer_name, c.name as target_company_name 
                    FROM trade_offers t
                    JOIN companies b ON t.buyer_id = b.id
                    JOIN companies c ON t.target_company_id = c.id
                    WHERE (t.seller_id = ? OR t.buyer_id = ?) AND t.status = 'pending'
                    ORDER BY t.created_at DESC";

            $stmt = $this->connection->prepare($sql);

            // We geven het $companyId twee keer mee: één keer voor verkoper, één keer voor koper
            $stmt->execute([$companyId, $companyId]);

            $stmt->setFetchMode(PDO::FETCH_CLASS, TradeOffer::class);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Database Error: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     */
    public function getById(int $id): ?TradeOffer {
        $stmt = $this->connection->prepare("SELECT * FROM trade_offers WHERE id = ?");
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, TradeOffer::class);
        $offer = $stmt->fetch();
        return $offer ?: null;
    }

    /**
     * @throws Exception
     */
    public function updateStatus(int $id, string $status): void {
        $stmt = $this->connection->prepare("UPDATE trade_offers SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    /**
     * @throws Exception
     */
    public function executeAcceptOffer(TradeOffer $offer, string $buyerDesc, string $sellerDesc): void {
        try {
            $this->connection->beginTransaction();

            // 1. Mark as Accepted
            $stmt = $this->connection->prepare("UPDATE trade_offers SET status = 'accepted' WHERE id = ? AND status = 'pending'");
            $stmt->execute([$offer->id]);
            if ($stmt->rowCount() === 0) {
                throw new Exception("Offer was already processed.", 400);
            }

            // 2. Swap Cash
            $stmt = $this->connection->prepare("UPDATE companies SET cash = cash - ? WHERE id = ?");
            $stmt->execute([$offer->total_price, $offer->buyer_id]);

            $stmt = $this->connection->prepare("UPDATE companies SET cash = cash + ? WHERE id = ?");
            $stmt->execute([$offer->total_price, $offer->seller_id]);

            // 3. Deduct Shares from Seller
            $stmt = $this->connection->prepare("UPDATE shares SET amount = amount - ? WHERE company_id = ? AND owner_id = ?");
            $stmt->execute([$offer->amount, $offer->target_company_id, $offer->seller_id]);

            // 4. Add Shares to Buyer
            $stmt = $this->connection->prepare("
                INSERT INTO shares (company_id, owner_id, amount) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE amount = amount + ?
            ");
            $stmt->execute([$offer->target_company_id, $offer->buyer_id, $offer->amount, $offer->amount]);

            // 5. Log Transactions
            $stmt = $this->connection->prepare("INSERT INTO transactions (company_id, amount, description) VALUES (?, ?, ?)");
            $stmt->execute([$offer->buyer_id, -$offer->total_price, $buyerDesc]);
            $stmt->execute([$offer->seller_id, $offer->total_price, $sellerDesc]);

            $this->connection->commit();
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw new Exception("Trade execution failed: " . $e->getMessage(), 500);
        }
    }
}

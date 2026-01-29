<?php

namespace Services;

use Exception;
use Models\DTO\StockTradeRequest;
use Repositories\StockRepository;

class StockService
{
    private StockRepository $stockRepo;
    private CompanyService $companyService;

    public function __construct(){
        $this->stockRepo = new StockRepository();
        $this->companyService = new CompanyService();
    }

    public function getBankStocks(): array {
        return $this->stockRepo->getPortfolio(null);
    }

    public function getCompanyStocks(int $companyId): array {
        return $this->stockRepo->getPortfolio($companyId);
    }

    public function getAllStocks(): array {
        return $this->stockRepo->getAllShares();
    }

    /**
     * @throws Exception
     */
    public function tradeStock(StockTradeRequest $request): void
    {
        if ($request->amount <= 0)
            throw new Exception("Amount must be greater than zero", 400);
        if ($request->buyer_id === $request->seller_id)
            throw new Exception("Buyer and seller cannot be the same", 400);

        $stockCompany = $this->companyService->getCompanyModelById($request->stock_company_id);
        if (!$stockCompany) throw new Exception("Stock company not found", 404);
        $request->stock_company_name = $stockCompany->name;

        $buyer = $this->companyService->getCompanyModelById($request->buyer_id);
        if (!$buyer) throw new Exception("Buyer not found", 404);
        $request->buyer_name = $buyer->name;

        if ($request->seller_id !== null) {
            $seller = $this->companyService->getCompanyModelById($request->seller_id);
            if (!$seller) throw new Exception("Seller not found", 404);

            $sellerStockAmount = $this->stockRepo->getShareAmount($request->stock_company_id, $request->seller_id);
            if ($sellerStockAmount < $request->amount) {
                throw new Exception("Seller has insufficient stock amount", 400);
            }
        } else {
            // Seller is the Bank
            $bankStockAmount = $this->stockRepo->getShareAmount($request->stock_company_id, null);
            if ($bankStockAmount < $request->amount) {
                throw new Exception("Bank has insufficient stock amount", 400);
            }
        }

        // Logic check
        $totalCost = $stockCompany->stock_price * $request->amount;

        if ($buyer->cash < $totalCost) {
            throw new Exception("Buyer has insufficient funds", 400);
        }

        $this->stockRepo->executeTrade($request, $totalCost);
    }
}

<?php

namespace Controllers;

use Exception;
use Models\DTO\TradeStockRequest;
use Services\StockService;

class StockController extends Controller
{
    private StockService  $stockService;

    public function __construct()
    {
        $this->stockService = new StockService();
    }

    public function getBankStocks(){
        $this->respond($this->stockService->getBankStocks());
    }

    public function getCompanyStocks(int $companyId){
        $this->respond($this->stockService->getCompanyStocks($companyId));
    }

    public function getAllStocks(){
        $this->respond($this->stockService->getAllStocks());
    }

    public function trade(){
        try {
            $request = $this->requestObjectFromPostedJson(TradeStockRequest::class);
            if (!isset($request->buyer_id, $request->stock_company_id, $request->amount)) {
                $this->respondWithError(400, "Missing fields");
            }

            $this->stockService->tradeStock($request);
            $this->respond(["message" => "Trade executed successfully"]);
        } catch (Exception $e){
            $this->respondWithError($e->getCode(), $e->getMessage());
        }
    }
}

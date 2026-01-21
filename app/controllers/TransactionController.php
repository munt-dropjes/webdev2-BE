<?php

namespace Controllers;

use Exception;
use Models\DTO\TransactionCreateRequest;
use Services\TransactionService;

class TransactionController extends Controller
{
    private TransactionService  $transactionService;

    function __construct()
    {
        $this->transactionService = new TransactionService();
    }

    public function create(){
        try {
            $request = $this->requestObjectFromPostedJson(TransactionCreateRequest::class);

            $this->transactionService->processTransaction($request);

            $this->respond(["message" => "Transaction processed successfully"]);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function history(){
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

            $history = $this->transactionService->getTransactionHistory($limit);

            $this->respond($history);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }
}

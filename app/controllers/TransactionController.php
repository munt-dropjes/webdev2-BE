<?php

namespace Controllers;

use Exception;
use Models\DTO\TransactionCreateRequest;
use Models\DTO\TransactionManyRequest;
use Models\DTO\TransactionTransferRequest;
use Services\AuthService;
use Services\TransactionService;

class TransactionController extends Controller
{
    private TransactionService  $transactionService;
    private AuthService $authService;

    function __construct()
    {
        $this->transactionService = new TransactionService();
        $this->authService = new AuthService();
    }

    public function create(){
        try {
            $user = $this->authService->getCurrentUserFromTokenPayload();
            if ($user->role !== "admin") {
                $this->respondWithError(401, "Unauthorized");
            }

            $request = $this->requestObjectFromPostedJson(TransactionCreateRequest::class);

            $this->transactionService->processTransaction($request);

            $this->respond(["message" => "Transaction processed successfully"]);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function history(){
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $user = $this->authService->getCurrentUserFromTokenPayload();

            $transactionManyRequest = TransactionManyRequest::Create($limit, $offset, $user);

            $history = $this->transactionService->getTransactionHistory($transactionManyRequest);

            $this->respond($history);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    public function transfer() {
        try {
            $user = $this->authService->getCurrentUserFromTokenPayload();
            $request = $this->requestObjectFromPostedJson(TransactionTransferRequest::class);
            if (!isset($request->sender_id) || !isset($request->receiver_id) || !isset($request->amount) || !isset($request->description)) {
                $this->respondWithError(400, "Missing required fields: sender_id, receiver_id, amount, description");
                return;
            }

            $this->transactionService->transfer($request, $user);

            $this->respond(["message" => "Transfer completed successfully"]);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }
}

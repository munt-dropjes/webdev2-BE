<?php

namespace Controllers;

use Exception;
use Models\DTO\TransactionCreateRequest;
use Models\DTO\TransactionManyRequest;
use Models\User;
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
            $user = $this->createUserFromTokenPayload($this->authService->validateToken());

            $transactionManyRequest = TransactionManyRequest::Create($limit, $offset, $user);

            $history = $this->transactionService->getTransactionHistory($transactionManyRequest);

            $this->respond($history);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode() ?: 500, $e->getMessage());
        }
    }

    private function createUserFromTokenPayload($payload): User
    {
        $jwtData = $payload->data;

        $user = new User();
        $user->id = $jwtData->id;
        $user->username = $jwtData->username;
        $user->role = $jwtData->role;
        // Handle nullable company_id safely
        $user->company_id = isset($jwtData->company_id) ? (int)$jwtData->company_id : null;
        return $user;
    }
}

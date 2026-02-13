<?php
namespace Services;

use Models\DTO\TransactionCreateRequest;
use Models\DTO\TransactionManyRequest;
use Models\DTO\TransactionTransferRequest;
use Models\User;
use Repositories\CompanyRepository;
use Exception;
use Repositories\TransactionRepository;

class TransactionService {
    private TransactionRepository $transactionRepo;
    private CompanyService $companyService;

    public function __construct() {
        $this->transactionRepo = new TransactionRepository();
        $this->companyService = new CompanyService();
    }

    /**
     * @throws Exception
     */
    public function processTransaction(TransactionCreateRequest $request): void {
        // Business Logic: Validation
        if ($request->amount == 0) {
            throw new Exception("Amount cannot be zero", 400);
        }

        if (empty($request->description)) {
            throw new Exception("Description is required", 400);
        }

        // Business Logic: Check if company exists
        $company = $this->companyService->getCompanyModelById($request->company_id);
        if (!$company) {
            throw new Exception("Company not found", 404);
        }

        // Prevent negative balance
        if ($company->cash < abs($request->amount)) {
            throw new Exception("Insufficient funds", 400);
        }

        // Execute
        $success = $this->transactionRepo->create($request);
        if (!$success) {
            throw new Exception("Transaction could not be processed", 500);
        }
    }

    /**
     * @throws Exception
     */
    public function getTransactionHistory(TransactionManyRequest $request): array {
        // 1. If Admin, show everything
        if (isset($request->user->role) && $request->user->role === 'admin') {
            return $this->transactionRepo->getAll($request);
        }

        // 2. If Company User, show only their transactions
        if (isset($request->user->company_id) && $request->user->company_id) {
            return $this->transactionRepo->getByCompany($request);
        }

        // 3. Fallback (e.g., user exists but not linked to company)
        return [];
    }

    /**
     * @throws Exception
     */
    public function transfer(TransactionTransferRequest $request, User $user): void {
        if ($request->amount <= 0) {
            throw new Exception("Amount must be greater than zero", 400);
        }
        if ($request->sender_id === $request->receiver_id) {
            throw new Exception("Sender and receiver cannot be the same", 400);
        }

        // Security / Auth Check
        if ($user->role !== 'admin' && $user->company_id !== $request->sender_id) {
            throw new Exception("Unauthorized: You can only send money from your own company.", 403);
        }
        $sender = $this->companyService->getCompanyModelById($request->sender_id);
        if (!$sender)
            throw new Exception("Sender company not found.", 404);
        $receiver = $this->companyService->getCompanyModelById($request->receiver_id);
        if (!$receiver)
            throw new Exception("Receiver company not found.", 404);

        // Balance Check
        if ($sender->cash < $request->amount) {
            throw new Exception("Insufficient funds. You only have ƒ " . $sender->cash, 400);
        }

        // Format descriptions for the double-entry ledger
        $senderDesc = "Aan {$receiver->name}: {$request->description}";
        $receiverDesc = "Van {$sender->name}: {$request->description}";

        $this->transactionRepo->executeTransfer($request, $senderDesc, $receiverDesc);
    }
}

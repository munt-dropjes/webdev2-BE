<?php
namespace Services;

use Models\DTO\TransactionCreateRequest;
use Models\DTO\TransactionManyRequest;
use Repositories\CompanyRepository;
use Exception;
use Repositories\TransactionRepository;

class TransactionService {
    private CompanyRepository $companyRepo;
    private TransactionRepository $transactionRepo;

    public function __construct() {
        $this->companyRepo = new CompanyRepository();
        $this->transactionRepo = new TransactionRepository();
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
        $company = $this->companyRepo->findById($request->company_id);
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
}

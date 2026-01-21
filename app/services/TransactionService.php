<?php
namespace Services;

use Models\Company;
use Models\DTO\BaseManyRequest;
use Models\DTO\TransactionCreateRequest;
use Repositories\CompanyRepository;
use Exception;

class TransactionService {
    private CompanyRepository $companyRepo;

    public function __construct() {
        $this->companyRepo = new CompanyRepository();
    }

    /**
     * @throws Exception
     */
    public function processTransaction(TransactionCreateRequest $request): void {
        // Business Logic: Validation
        if ($request->amount == 0) {
            throw new Exception("Amount cannot be zero", 400);
        }

        if (empty($request->reason)) {
            throw new Exception("Reason is required", 400);
        }

        // Business Logic: Check if company exists
        $company = $this->companyRepo->findById($request->company_id);
        if (!$company) {
            throw new Exception("Family not found", 404);
        }

        // Optional Rule: Prevent negative balance?
        // if ($family->cash + $request->amount < 0) { throw new Exception("Insufficient funds", 400); }

        // Execute
        $success = $this->companyRepo->createTransaction($request);
        if (!$success) {
            throw new Exception("Transaction could not be processed", 500);
        }
    }

    /**
     * @throws Exception
     */
    public function getTransactionHistory(int $limit): array {
        return $this->companyRepo->getHistory($limit);
    }
}

<?php
namespace Services;

use Repositories\FamilyRepository;
use Exception;

class TransactionService {
    private FamilyRepository $familyRepo;

    public function __construct(FamilyRepository $familyRepo) {
        $this->familyRepo = $familyRepo;
    }

    public function processTransaction(int $familyId, float $amount, string $reason): void {
        // Business Logic: Validation
        if ($amount == 0) {
            throw new Exception("Transaction amount cannot be zero");
        }

        // Business Logic: Check Family existence
        // (We could check if family exists here, or let the repo handle the exception)

        // Example Rule: Prevent debt (Optional)
        // $currentBalance = $this->familyRepo->getBalance($familyId);
        // if ($currentBalance + $amount < 0) throw new Exception("Insufficient funds");

        // Delegate atomic update to Repository
        $success = $this->familyRepo->updateCash($familyId, $amount, $reason);

        if (!$success) {
            throw new Exception("Transaction failed to process");
        }
    }

    public function getHistory(): array {
        return $this->familyRepo->getTransactionHistory();
    }
}

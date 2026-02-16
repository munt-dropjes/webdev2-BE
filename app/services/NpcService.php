<?php

namespace Services;

use Config\NpcConfig;
use Exception;
use Models\DTO\TransactionCreateRequest;
use Repositories\CompanyRepository;
use Repositories\TransactionRepository;

class NpcService
{
    private CompanyRepository $companyRepo;
    private TransactionRepository $transactionRepo;

    public function __construct() {
        $this->companyRepo = new CompanyRepository();
        $this->transactionRepo = new TransactionRepository();
    }

    /**
     * @throws Exception
     */
    public function processTick(): void {
        $companies = $this->companyRepo->findAll();
        $npcs = array_filter($companies, fn($c) => $c->is_npc == 1);

        foreach ($npcs as $npc) {
            $roll = rand(1, NpcConfig::getRollMax());

            if ($roll <= NpcConfig::getThresholdIdle()) {
                continue; // Do nothing
            }
            elseif ($roll <= NpcConfig::getThresholdSubsidy()) {
                // Config handles the math and randomization
                $amount = NpcConfig::calculateRandomSubsidy();

                $this->applyNpcEvent($npc->id, $amount, NpcConfig::getDescSubsidy());
            }
            else {
                // Config handles the math and randomization for expenses
                $amount = NpcConfig::calculateRandomExpense();

                if (($npc->cash + $amount) < NpcConfig::getMinBankruptcySafeguard()) {
                    continue; // Prevent bankruptcy
                }

                $this->applyNpcEvent($npc->id, $amount, NpcConfig::getDescExpense());
            }
        }
    }

    private function applyNpcEvent(int $companyId, int $amount, string $description): void {
        try {
            $request = new TransactionCreateRequest();
            $request->company_id = $companyId;
            $request->amount = $amount;
            $request->description = $description;

            $this->transactionRepo->create($request);
        } catch (Exception $e) {
            error_log("NPC Tick Failed for Company ID {$companyId}: " . $e->getMessage());
        }
    }
}

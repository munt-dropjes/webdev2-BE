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
    private TradeOfferService $offerService;

    public function __construct() {
        $this->companyRepo = new CompanyRepository();
        $this->transactionRepo = new TransactionRepository();
        $this->offerService = new TradeOfferService();
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

    public function executeVultureProtocol(int $strugglingCompanyId): void {
        try {
            $target = $this->companyRepo->findById($strugglingCompanyId);
            if (!$target || $target->is_npc) return;

            $companies = $this->companyRepo->findAll();
            $npcs = array_filter($companies, fn($c) => $c->is_npc == 1);
            $deStaf = reset($npcs);
            if (!$deStaf) return;

            // Algorithm 1 Math: Value = Cash / 100
            $stockPrice = max(1, (int)($target->cash / 100));
            $amountToBuy = 5;
            $marketValue = $stockPrice * $amountToBuy;

            // 30% discount lowball
            $lowballPrice = (int)($marketValue * 0.70);
            if ($lowballPrice < 1000) $lowballPrice = 1000;

            if ($deStaf->cash < $lowballPrice) return;

            $request = new \Models\DTO\TradeOfferRequest();
            $request->seller_id = $strugglingCompanyId;
            $request->target_company_id = $strugglingCompanyId;
            $request->amount = $amountToBuy;
            $request->total_price = $lowballPrice;

            $this->offerService->createOffer($deStaf->id, $request);

        } catch (Exception $e) {
            error_log("Vulture Protocol Failed: " . $e->getMessage());
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

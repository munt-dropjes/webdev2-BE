<?php

namespace Services;

use Exception;
use Models\Company;
use Repositories\CompanyRepository;
use Repositories\HistoryRepository;
use Repositories\StockRepository;

class CompanyService
{
    private CompanyRepository $companyRepo;
    private StockRepository $stockRepo;

    public function __construct() {
        $this->companyRepo = new CompanyRepository();
        $this->stockRepo = new StockRepository();
    }
    /**
     * @return Company[]
     * @throws Exception
     */
    public function getAllCompanies(): array {
        $companies = $this->companyRepo->findAll();
        $shares = $this->stockRepo->getAllActiveShares();

        return $this->calculateValuations($companies, $shares);
    }

    /**
     * @throws Exception
     */
    public function getById(int $id): ?Company {
        $company = $this->companyRepo->findById($id);
        if (!$company) return null;

        $allCompanies = $this->companyRepo->findAll();
        $shares = $this->stockRepo->getAllActiveShares();

        $enrichedAll = $this->calculateValuations($allCompanies, $shares);

        foreach ($enrichedAll as $c) {
            if ($c->id === $id) return $c;
        }
        return $company;
    }

    private function calculateValuations(array $companies, array $shares): array {
        $cashMap = [];
        foreach ($companies as $c) {
            $cashMap[$c->id] = $c->cash;
        }

        foreach ($companies as $company) {
            $portfolioValue = 0;

            foreach ($shares as $share) {
                // If this company owns the share
                if ($share['owner_id'] == $company->id) {
                    $targetId = $share['company_id'];
                    $amount = $share['amount'];

                    // LOGIC: Intrinsic Value = Target Cash / 100
                    $targetCash = $cashMap[$targetId] ?? 0;
                    $targetBasePrice = max(1, floor($targetCash / 100));

                    $portfolioValue += ($amount * $targetBasePrice);
                }
            }

            // LOGIC: Net Worth = Cash + Portfolio
            $company->net_worth = $company->cash + $portfolioValue;

            // LOGIC: Stock Price = Net Worth / 100
            $company->stock_price = max(1, floor($company->net_worth / 100));
        }

        return $companies;
    }
}

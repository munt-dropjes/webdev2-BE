<?php

namespace Services;

use Exception;
use Models\Company;
use Models\DTO\CompanyResponse;
use Models\User;
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
     * @return CompanyResponse[]
     * @throws Exception
     */
    public function getAllCompanies(User $currentUser): array {
        $companies = $this->companyRepo->findAll();
        $shares = $this->stockRepo->getAllActiveShares();

        $valuatedCompanies = $this->calculateValuations($companies, $shares);

        $response =[];
        foreach ($valuatedCompanies as $company) {
            $response[] = $this->createResponse($company, $currentUser);
        }
        return $response;
    }

    /**
     * @throws Exception
     */
    public function getAllCompanyModels(): array {
        $companies = $this->companyRepo->findAll();
        $shares = $this->stockRepo->getAllActiveShares();

        return $this->calculateValuations($companies, $shares);
    }

    /**
     * @throws Exception
     */
    public function getById(int $id, User $currentUser): ?CompanyResponse {
        $company = $this->companyRepo->findById($id);
        if (!$company) return null;

        $allCompanies = $this->companyRepo->findAll();
        $shares = $this->stockRepo->getAllActiveShares();

        $valuatedCompanies = $this->calculateValuations($allCompanies, $shares);

        foreach ($valuatedCompanies as $c) {
            if ($c->id === $id) {
                $company = $c;
                break;
            }
        }
        return $this->createResponse($company, $currentUser);
    }

    /**
     * @throws Exception
     * INTERNAL / SERVICE Method: Returns the Raw Model (with calculated price)
     *  Used by StockService to check funds and prices.
     */
    public function getCompanyModelById(int $id): ?Company {
        $company = $this->companyRepo->findById($id);
        if (!$company)
            return null;

        $allCompanies = $this->companyRepo->findAll();
        $shares = $this->stockRepo->getAllActiveShares();
        $valuatedCompanies = $this->calculateValuations($allCompanies, $shares);
        foreach ($valuatedCompanies as $c) {
            if ($c->id === $id) {
                return $c;
            }
        }
        return null;
    }

    /**
     * - Target Stock Price = Target Cash / 100
     * - Portfolio Value = Amount * Target Stock Price
     * - Net Worth = Own Cash + Portfolio Value
     * - Own Stock Price = Net Worth / 100
     */
    private function calculateValuations(array $companies, array $shares): array {
        // 1. Create a fast lookup for CASH only
        $cashMap = [];
        $companyMap = [];

        foreach ($companies as $c) {
            $cashMap[$c->id] = $c->cash;
            $c->net_worth = $c->cash;
            $c->stock_price = 0;
            $companyMap[$c->id] = $c;
        }
        // 2. Group Shares by Owner
        $portfolios = [];
        foreach ($shares as $share) {
            $ownerId = $share['owner_id'];
            if (!isset($portfolios[$ownerId])) $portfolios[$ownerId] = [];
            $portfolios[$ownerId][] = $share;
        }
        // 3. Calculate Valuations
        foreach ($companyMap as $id => $company) {
            $portfolioValue = 0;

            if (isset($portfolios[$id])) {
                foreach ($portfolios[$id] as $share) {
                    $targetId = $share['company_id'];
                    $amount = $share['amount'];

                    $targetCash = $cashMap[$targetId] ?? 0;
                    $targetBasePrice = max(1, floor($targetCash / 100));

                    $portfolioValue += ($amount * $targetBasePrice);
                }
            }
            $company->net_worth = $company->cash + $portfolioValue;
            $company->stock_price = max(1, floor($company->net_worth / 100));
        }
        return array_values($companyMap);
    }

    private function createResponse($company, User $currentUser): CompanyResponse {
        $isAdmin = ($currentUser->role === 'admin');
        $isOwnCompany = (isset($currentUser->company_id) && $currentUser->company_id === $company->id);

        // Logic: Admin or Owner sees Cash. Everyone else sees null.
        $showCash = $isAdmin || $isOwnCompany;

        return CompanyResponse::CreateFromCompany($company, $showCash);
    }
}

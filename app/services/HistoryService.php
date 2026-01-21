<?php

namespace Services;

use Exception;
use Repositories\HistoryRepository;

class HistoryService
{
    private HistoryRepository $historyRepo;
    private CompanyService  $companyService;

    public function __construct() {
        $this->historyRepo = new HistoryRepository();
        $this->companyService = new CompanyService();
    }

    /**
     * @throws Exception
     */
    public function saveHistory(): array {
        $timestamp = date('Y-m-d H:i:00');

        if ($this->historyRepo->hasHistoryFor($timestamp)) {
            return [
                'status' => 'skipped',
                'message' => 'History for ' . $timestamp . ' already exists.'
            ];
        }

        $companies = $this->companyService->getAllCompanies();
        $this->historyRepo->saveHistoryFor($companies, $timestamp);

        return [
            'status' => 'saved',
            'message' => 'History for ' . $timestamp . ' saved successfully.'
        ];
    }

    /**
     * @throws Exception
     */
    public function getHistorySince(string $dateTime): array {
        return $this->historyRepo->getHistorySince($dateTime);
    }
}

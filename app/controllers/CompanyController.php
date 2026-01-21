<?php
namespace Controllers;

use Exception;
use Services\CompanyService;
use Services\TransactionService;

class CompanyController extends Controller{
    private TransactionService $transactionService;
    private CompanyService $companyService;

    function __construct()
    {
        $this->transactionService = new TransactionService();
        $this->companyService = new CompanyService();
    }

    public function getAll()
    {
        try {
            $companies = $this->companyService->getAllCompanies();
            $this->respond($companies);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

    }
}

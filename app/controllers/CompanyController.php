<?php
namespace Controllers;

use Exception;
use Services\CompanyService;

class CompanyController extends Controller{
    private CompanyService $companyService;

    function __construct()
    {
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

    public function getById($id){
        try {
            $company = $this->companyService->getById((int)$id);
            if (!$company) {
                $this->respondWithError(404, "Company not found");
            }
            $this->respond($company);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
}

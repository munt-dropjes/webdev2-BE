<?php
namespace Controllers;

use Exception;
use Services\AuthService;
use Services\CompanyService;

class CompanyController extends Controller{
    private CompanyService $companyService;
    private AuthService $authService;

    function __construct()
    {
        $this->companyService = new CompanyService();
        $this->authService = new AuthService();
    }

    public function getAll()
    {
        try {
            $user = $this->authService->getCurrentUserFromTokenPayload();
            $companies = $this->companyService->getAllCompanies($user);
            $this->respond($companies);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getById($id){
        try {
            $user = $this->authService->getCurrentUserFromTokenPayload();
            $company = $this->companyService->getById((int)$id, $user);
            if (!$company) {
                $this->respondWithError(404, "Company not found");
            }
            $this->respond($company);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }
}

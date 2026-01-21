<?php

namespace Services;

use Exception;
use models\Company;
use Repositories\CompanyRepository;

class CompanyService
{
    private CompanyRepository $companyRepo;

    public function __construct() {
        $this->companyRepo = new CompanyRepository();
    }
    /**
     * @return Company[]
     * @throws Exception
     */
    public function getAllCompanies(): array {
        return $this->companyRepo->findAll();
    }
}

<?php
namespace Models\DTO;

use Models\Company;

class CompanyResponse {
    public int $id;
    public string $name;
    public string $color;
    public ?int $cash;     // Nullable (Hidden for others)
    public int $net_worth;
    public int $stock_price;
    public string $created_at;

    public static function CreateFromCompany(Company $company, bool $showCash): self {
        $response = new self();
        $response->id = $company->id;
        $response->name = $company->name;
        $response->color = $company->color;
        $response->created_at = $company->created_at;

        // Calculated fields
        $response->net_worth = $company->net_worth ?? 0;
        $response->stock_price = $company->stock_price ?? 0;

        // Privacy Logic
        $response->cash = $showCash ? $company->cash : null;

        return $response;
    }
}

<?php

namespace Models;

class Stock
{
    public int $company_id;
    public string $company_name;
    public ?int $owner_id;
    public ?string $owner_name;
    public int $amount;
    public int $current_price;
}

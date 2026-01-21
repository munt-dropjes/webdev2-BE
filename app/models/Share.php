<?php

namespace Models;

class Share
{
    public int $company_id;
    public string $company_name;
    public ?int $owner_id;
    public ?string $owner_name;
    public int $amount;
    public int $current_price;
}

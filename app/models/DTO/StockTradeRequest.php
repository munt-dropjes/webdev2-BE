<?php

namespace Models\DTO;

class StockTradeRequest
{
    public int $buyer_id;
    public ?string $buyer_name;
    public ?int $seller_id;
    public int $stock_company_id;
    public ?string $stock_company_name;
    public int $amount;
}

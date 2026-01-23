<?php

namespace Models\DTO;

class StockTradeRequest
{
    public int $buyer_id;
    public ?int $seller_id;
    public int $stock_company_id; // Which company stock is being traded
    public int $amount;
}

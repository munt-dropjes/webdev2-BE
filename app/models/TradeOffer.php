<?php

namespace Models;

class TradeOffer
{
    public int $id;
    public int $buyer_id;
    public int $seller_id;
    public int $target_company_id;
    public int $amount;
    public int $total_price;
    public string $status;
    public ?string $created_at = null;

    public ?string $buyer_name = null;
    public ?string $target_company_name = null;
}

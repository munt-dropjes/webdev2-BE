<?php

namespace Models\DTO;

class TradeOfferRequest
{
    public ?int $buyer_id = null;
    public int $seller_id;
    public int $target_company_id;
    public int $amount;
    public int $total_price;
}

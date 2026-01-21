<?php

namespace Models\DTO;

class TransactionCreateRequest
{
    public int $company_id;
    public int $amount;
    public string $description;
}

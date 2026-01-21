<?php

namespace models;

class Transaction
{
    public int $id;
    public int $company_id;
    public int $amount;
    public string $description;
    public string $created_at;
}

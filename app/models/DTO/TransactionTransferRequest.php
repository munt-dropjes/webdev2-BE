<?php

namespace Models\DTO;

class TransactionTransferRequest
{
    public int $sender_id;
    public int $receiver_id;
    public int $amount;
    public string $description;
}

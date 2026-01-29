<?php

namespace Models\DTO;

abstract class BaseManyRequest
{
    public ?int $limit;
    public ?int $offset;
}

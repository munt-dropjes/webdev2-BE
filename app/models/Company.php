<?php

namespace Models;

use JsonSerializable;

class Company implements JsonSerializable
{
    public int $id;
    public string $name;
    public string $color;
    public int $cash;
    public string $created_at;

    public int $net_worth = 0;
    public int $stock_price = 0;

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'cash' => $this->cash,
            'net_worth' => $this->net_worth,
            'stock_price' => $this->stock_price,
            'created_at' => $this->created_at
        ];
    }
}

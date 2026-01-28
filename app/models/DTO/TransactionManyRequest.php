<?php

namespace Models\DTO;

use Models\User;

class TransactionManyRequest extends BaseManyRequest
{
    public User $user;

    public static function Create(int $limit, int $offset, User $user) : TransactionManyRequest {
        $request = new TransactionManyRequest();
        $request->limit = $limit;
        $request->offset = $offset;
        $request->user = $user;
        return $request;
    }
}

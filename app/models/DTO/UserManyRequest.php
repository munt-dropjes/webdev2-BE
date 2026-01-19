<?php

namespace Models\DTO;

class UserManyRequest extends BaseManyRequest
{
    public ?string $role;

    public static function Create(int $limit, int $offset, string $role) : UserManyRequest {
        $request = new UserManyRequest();
        $request->limit = $limit;
        $request->offset = $offset;
        $request->role = $role;
        return $request;
    }
}

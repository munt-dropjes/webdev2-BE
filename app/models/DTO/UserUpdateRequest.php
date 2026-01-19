<?php

namespace Models\DTO;

class UserUpdateRequest
{
    public int $id;
    public ?string $email = null;
    public ?string $username = null;
    public ?string $password = null;
    public ?string $role = null;
}

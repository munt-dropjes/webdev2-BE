<?php

namespace Models\DTO;

class UserCreateRequest
{
    public string $email;
    public string $username;
    public string $password;
    public string $role = 'user';
}

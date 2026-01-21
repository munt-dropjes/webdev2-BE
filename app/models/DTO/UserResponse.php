<?php

namespace Models\DTO;

use Models\User;

class UserResponse
{
    public int $id;
    public string $username;
    public string $email;
    public string $role;
    public string $createdAt;

    public static function CreateFromUser(?User $user): ?UserResponse
    {
        if ($user === null) {
            return null;
        }

        $response = new UserResponse();
        $response->id = $user->id;
        $response->username = $user->username;
        $response->email = $user->email;
        $response->role = $user->role;
        $response->createdAt = $user->created_at;

        return $response;
    }
}

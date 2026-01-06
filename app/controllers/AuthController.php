<?php
namespace Controllers;

use Config\Database;
use Config\JwtConfig;
use Repositories\UserRepository;
use Firebase\JWT\JWT;

class AuthController {
    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        $repo = new UserRepository(Database::getConnection());

        $user = $repo->findByEmail($input['email'] ?? '');

        if (!$user || !password_verify($input['password'] ?? '', $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $payload = [
            'iss' => 'your-app-name',
            'iat' => time(),
            'exp' => time() + (60 * 60),
            'sub' => $user['id'],
            'role' => $user['role']
        ];

        $jwt = JWT::encode($payload, JwtConfig::getSecret(), JwtConfig::getAlgo());

        echo json_encode([
            'token' => $jwt,
            'expires_in' => 3600
        ]);
    }
}

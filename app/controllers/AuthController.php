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

        if (!isset($input['email']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $user = $repo->findByEmail($input['email']);

        if (!$user || !password_verify($input['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid password or email']);
            return;
        }

        $payload = [
            'iss' => 'family-game-api',
            'iat' => time(),
            'exp' => time() + (3600 * 4),
            'sub' => $user['id'],
            'role' => $user['role']
        ];

        try {
            $jwt = JWT::encode($payload, JwtConfig::getSecret(), JwtConfig::getAlgo());
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Token generation failed']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'token' => $jwt,
            'role'  => $user['role']
        ]);
    }
}

<?php
namespace Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\JwtConfig;
use Exception;
use http\Exception\BadMessageException;
use JetBrains\PhpStorm\NoReturn;
use Models\DTO\UserLoginRequest;
use Models\User;
use Repositories\UserRepository;

class AuthService {
    private UserRepository $userRepository;

    function __construct() {
        $this->userRepository = new UserRepository();
    }

    /**
     * Validates the Bearer token and returns the decoded payload.
     */
    public function validateToken(): object {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->abort(400, 'Token not found');
        }

        $jwt = $matches[1];

        try {
            return JWT::decode($jwt, new Key(JwtConfig::getSecret(), JwtConfig::getAlgo()));
        } catch (Exception $e) {
            $this->abort(401, 'Invalid Token: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function login (UserLoginRequest $userRequest): User {
        if (!isset($userRequest->username) || !isset($userRequest->password)) {
            throw new Exception("Missing required fields", 400);
        }

        try {
            $user = $this->userRepository->findByUsername($userRequest->username);

            if (!$user) {
                throw new Exception("Invalid username", 401);
            }

            if (!password_verify($userRequest->password, $user->password)) {
                throw new Exception("Invalid password", 401);
            }

            return $user;
        } catch (Exception $e) {
            throw new Exception('Internal server error: ' . $e->getMessage(), 500);
        }
    }

    #[NoReturn]
    private function abort(int $code, string $message): void {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }
}

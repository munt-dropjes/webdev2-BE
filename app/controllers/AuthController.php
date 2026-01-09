<?php
namespace Controllers;

use Config\JwtConfig;
use Models\DTO\UserLoginRequest;
use Firebase\JWT\JWT;
use Exception;
use Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function login() {
        try {
            $user = $this->requestObjectFromPostedJson(UserLoginRequest::class);

            $user = $this->authService->login($user);
        } catch (Exception $e) {
            $this->respondWithError($e->getCode(), $e->getMessage());
            return;
        }

        $jwt = $this->generateToken($user);

        $this->respond(
            array(
                "message" => "Login successful",
                "token" => $jwt,
                "user" => $user,
                "expireAt" => date('d-m-Y H:i:s', (time() + JWTConfig::getExpireTime()))
            )
        );
    }

    private function generateToken($user): string
    {
        $payload = [
            "iss" => JWTConfig::getIssuer(),
            "iat" => time(),
            "exp" => time() + JWTConfig::getExpireTime(),
            "data" => [
                "id" => $user->id,
                "username" => $user->username,
                "role" => $user->role
            ]
        ];

        try {
            return JWT::encode($payload, JwtConfig::getSecret(), JwtConfig::getAlgo());
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Token generation failed']);
            return;
        }
    }
}

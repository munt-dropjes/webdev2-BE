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

        try{
            $jwt = $this->generateToken($user);
        } catch (Exception $e) {
            $this->respondWithError(500, 'Token generation failed: ' . $e->getMessage());
            return;
        }

        $this->respond(
            array(
                "message" => "Login successful",
                "token" => $jwt,
                "user" => $user,
                "expireAt" => date('d-m-Y H:i:s', (time() + JwtConfig::getExpireTime()))
            )
        );
    }

    /**
     * @throws Exception
     */
    private function generateToken($user): string
    {
        $payload = [
            "iss" => JwtConfig::getIssuer(),
            "iat" => time(),
            "exp" => time() + JwtConfig::getExpireTime(),
            "data" => [
                "id" => $user->id,
                "username" => $user->username,
                "role" => $user->role,
                "company_id" => $user->company_id
            ]
        ];

        try {
            return JWT::encode($payload, JwtConfig::getSecret(), JwtConfig::getAlgo());
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

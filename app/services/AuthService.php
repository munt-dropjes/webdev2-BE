<?php
namespace Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\JwtConfig;
use Exception;

class AuthService {
    /**
     * Validates the Bearer token and returns the decoded payload.
     */
    public function validateToken(): object {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->abort(401, 'Token not found');
        }

        $jwt = $matches[1];

        try {
            return JWT::decode($jwt, new Key(JwtConfig::getSecret(), JwtConfig::getAlgo()));
        } catch (Exception $e) {
            $this->abort(401, 'Invalid Token: ' . $e->getMessage());
        }
        return (object)[]; // Unreachable but keeps IDE happy
    }

    /**
     * Middleware to check if user has specific role
     */
    public function requireRole(string $requiredRole): void {
        $decoded = $this->validateToken();
        if (($decoded->role ?? '') !== $requiredRole) {
            $this->abort(403, 'Forbidden: Insufficient permissions');
        }
    }

    private function abort(int $code, string $message): void {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }
}

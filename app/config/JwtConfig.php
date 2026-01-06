<?php
namespace Config;

class JwtConfig {
    public static function getSecret(): string {
        return $_ENV['JWT_SECRET'] ?? 'default_secret_for_dev';
    }

    public static function getAlgo(): string {
        return $_ENV['JWT_ALGO'] ?? 'HS256';
    }
}

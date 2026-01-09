<?php
namespace Config;

class JwtConfig {
    public static function getSecret(): string {
        return $_ENV['JWT_SECRET'] ?? 'default_secret_for_dev';
    }

    public static function getIssuer(): string {
        return $_ENV['JWT_ISSUER'] ?? 'http://localhost/api';
    }

    public static function getExpireTime(): int {
        return isset($_ENV['JWT_EXPIRE_TIME']) ? (int)$_ENV['JWT_EXPIRE_TIME'] : 3600; // default 1 hour
    }

    public static function getAlgo(): string {
        return $_ENV['JWT_ALGO'] ?? 'HS256';
    }
}

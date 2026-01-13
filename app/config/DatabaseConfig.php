<?php

namespace Config;

class DatabaseConfig
{
    public static function getType(): string {
        return $_ENV['DB_TYPE'] ?? 'mysql';
    }

    public static function getServerName(): string {
        return $_ENV['DB_SERVER'] ?? 'mysql';
    }

    public static function getUsername(): string {
        return $_ENV['DB_USER'] ?? 'developer';
    }

    public static function getPassword(): string {
        return $_ENV['DB_PASS'] ?? 'secret123';
    }

    public static function getDatabase(): string {
        return $_ENV['DB_NAME'] ?? 'developmentdb';
    }

    public static function getPort(): string {
        return $_ENV['DB_PORT'] ?? '8080';
    }
}

<?php

namespace Repositories;

use Config\DatabaseConfig;
use PDO;
use PDOException;

class Repository
{
    protected PDO $connection;

    function __construct()
    {
        $type = DatabaseConfig::getType();
        $servername = DatabaseConfig::getServerName();
        $username = DatabaseConfig::getUsername();
        $password = DatabaseConfig::getPassword();
        $database = DatabaseConfig::getDatabase();
        $port = DatabaseConfig::getPort();

        try {
            $this->connection = new PDO("mysql:host=mysql;dbname=developmentdb", "developer", "secret123");

            // set the PDO error mode to exception
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }
}

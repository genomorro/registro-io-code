<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class ConnectionService
{
    private $connection;

    /**
     * @throws Exception
     */
    public function __construct(
        string $dbName,
        string $dbUser,
        string $dbPassword,
        string $dbHost,
        string $dbDriver
    ) {
        $connectionParams = [
            'dbname' => $dbName,
            'user' => $dbUser,
            'password' => $dbPassword,
            'host' => $dbHost,
            'driver' => $dbDriver,
            'charset' => 'utf8mb4',
        ];

        $this->connection = DriverManager::getConnection($connectionParams);
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}

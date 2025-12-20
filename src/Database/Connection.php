<?php

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private PDO $pdo;

    public function __construct(string $dsn, string $user, string $pass)
    {
        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
            exit;
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}

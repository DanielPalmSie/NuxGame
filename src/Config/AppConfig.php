<?php

namespace App\Config;

class AppConfig
{
    public function __construct()
    {
        Env::load(__DIR__ . '/../../.env');
    }

    public function getBaseUrl(): string
    {
        return rtrim((string) Env::get('APP_URL', 'http://localhost:8080'), '/');
    }

    public function getPdoDsn(): string
    {
        $host = Env::get('DB_HOST', 'mysql');
        $port = Env::get('DB_PORT', '3306');
        $db = Env::get('DB_NAME', 'app');
        return "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    }

    public function getDbUser(): string
    {
        return (string) Env::get('DB_USER', 'app');
    }

    public function getDbPass(): string
    {
        return (string) Env::get('DB_PASS', 'app');
    }
}

<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

use App\Config\AppConfig;
use App\Database\Connection;

$config = new AppConfig();
$connection = new Connection($config->getPdoDsn(), $config->getDbUser(), $config->getDbPass());
$pdo = $connection->pdo();

$schema = file_get_contents(__DIR__ . '/../src/Database/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "Unable to read schema file\n");
    exit(1);
}

$pdo->exec($schema);

echo "Database schema applied." . PHP_EOL;

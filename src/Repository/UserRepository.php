<?php

namespace App\Repository;

use App\Domain\Entity\User;
use DateTimeImmutable;
use PDO;

class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUsernameAndPhone(string $username, string $phone): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username AND phone = :phone LIMIT 1');
        $stmt->execute(['username' => $username, 'phone' => $phone]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function create(string $username, string $phone): User
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, phone, created_at) VALUES (:username, :phone, :created_at)');
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $stmt->execute([
            'username' => $username,
            'phone' => $phone,
            'created_at' => $now,
        ]);

        return new User((int) $this->pdo->lastInsertId(), $username, $phone, new DateTimeImmutable($now));
    }

    private function hydrate(array $row): User
    {
        return new User(
            (int) $row['id'],
            $row['username'],
            $row['phone'],
            new DateTimeImmutable($row['created_at'])
        );
    }
}

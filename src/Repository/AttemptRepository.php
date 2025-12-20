<?php

namespace App\Repository;

use App\Domain\Entity\Attempt;
use DateTimeImmutable;
use PDO;

class AttemptRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $userId, int $linkId, int $number, string $result, float $amount): Attempt
    {
        $now = new DateTimeImmutable();
        $stmt = $this->pdo->prepare('INSERT INTO attempts (user_id, link_id, number, result, amount, created_at) VALUES (:user_id, :link_id, :number, :result, :amount, :created_at)');
        $stmt->execute([
            'user_id' => $userId,
            'link_id' => $linkId,
            'number' => $number,
            'result' => $result,
            'amount' => $amount,
            'created_at' => $now->format('Y-m-d H:i:s'),
        ]);

        return new Attempt((int) $this->pdo->lastInsertId(), $userId, $linkId, $number, $result, $amount, $now);
    }

    /** @return Attempt[] */
    public function lastAttemptsForUser(int $userId, int $limit = 3): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM attempts WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return array_map(fn(array $row) => $this->hydrate($row), $rows);
    }

    private function hydrate(array $row): Attempt
    {
        return new Attempt(
            (int) $row['id'],
            (int) $row['user_id'],
            (int) $row['link_id'],
            (int) $row['number'],
            $row['result'],
            (float) $row['amount'],
            new DateTimeImmutable($row['created_at'])
        );
    }
}

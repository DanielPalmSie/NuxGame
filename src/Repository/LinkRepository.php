<?php

namespace App\Repository;

use App\Domain\Entity\Link;
use DateTimeImmutable;
use PDO;

class LinkRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByToken(string $token): ?Link
    {
        $stmt = $this->pdo->prepare('SELECT * FROM links WHERE token = :token LIMIT 1');
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function create(int $userId, string $token, DateTimeImmutable $expiresAt): Link
    {
        $now = new DateTimeImmutable();
        $stmt = $this->pdo->prepare('INSERT INTO links (user_id, token, is_active, expires_at, created_at) VALUES (:user_id, :token, 1, :expires_at, :created_at)');
        $stmt->execute([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'created_at' => $now->format('Y-m-d H:i:s'),
        ]);

        return new Link((int) $this->pdo->lastInsertId(), $userId, $token, true, $expiresAt, $now);
    }

    public function deactivate(Link $link, ?int $replacedByLinkId = null): void
    {
        $stmt = $this->pdo->prepare('UPDATE links SET is_active = 0, replaced_by_link_id = :replaced WHERE id = :id');
        $stmt->execute([
            'replaced' => $replacedByLinkId,
            'id' => $link->getId(),
        ]);
    }

    public function deactivateActiveByUser(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE links SET is_active = 0 WHERE user_id = :user_id AND is_active = 1');
        $stmt->execute(['user_id' => $userId]);
    }

    private function hydrate(array $row): Link
    {
        return new Link(
            (int) $row['id'],
            (int) $row['user_id'],
            $row['token'],
            (bool) $row['is_active'],
            new DateTimeImmutable($row['expires_at']),
            new DateTimeImmutable($row['created_at']),
            $row['replaced_by_link_id'] !== null ? (int) $row['replaced_by_link_id'] : null
        );
    }
}

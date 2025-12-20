<?php

namespace App\Domain\Entity;

use DateTimeImmutable;

class Link
{
    public function __construct(
        private int $id,
        private int $userId,
        private string $token,
        private bool $isActive,
        private DateTimeImmutable $expiresAt,
        private DateTimeImmutable $createdAt,
        private ?int $replacedByLinkId = null
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getReplacedByLinkId(): ?int
    {
        return $this->replacedByLinkId;
    }
}

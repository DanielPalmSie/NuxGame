<?php

namespace App\Domain\Entity;

use DateTimeImmutable;

class Attempt
{
    public function __construct(
        private int $id,
        private int $userId,
        private int $linkId,
        private int $number,
        private string $result,
        private float $amount,
        private DateTimeImmutable $createdAt
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

    public function getLinkId(): int
    {
        return $this->linkId;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}

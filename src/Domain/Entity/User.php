<?php

namespace App\Domain\Entity;

use DateTimeImmutable;

class User
{
    public function __construct(
        private int $id,
        private string $username,
        private string $phone,
        private DateTimeImmutable $createdAt
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}

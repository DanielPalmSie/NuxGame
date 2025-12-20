<?php

namespace App\Service;

use App\Domain\Entity\Attempt;
use App\Repository\AttemptRepository;

class HistoryService
{
    public function __construct(private AttemptRepository $attempts, private LinkService $links)
    {
    }

    /** @return Attempt[] */
    public function lastAttempts(string $token, int $limit = 3): array
    {
        $link = $this->links->requireActiveLink($token);
        return $this->attempts->lastAttemptsForUser($link->getUserId(), $limit);
    }
}

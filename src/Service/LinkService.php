<?php

namespace App\Service;

use App\Domain\Entity\Link;
use App\Repository\LinkRepository;
use App\Util\HttpException;
use DateInterval;
use DateTimeImmutable;

class LinkService
{
    public function __construct(private LinkRepository $links)
    {
    }

    public function requireActiveLink(string $token): Link
    {
        $link = $this->links->findByToken($token);
        if (!$link || !$link->isActive()) {
            throw new HttpException('Link not found or inactive', 404);
        }

        if ($link->getExpiresAt() < new DateTimeImmutable()) {
            $this->links->deactivate($link);
            throw new HttpException('Link expired', 404);
        }

        return $link;
    }

    public function regenerate(string $token): Link
    {
        $current = $this->requireActiveLink($token);
        $newToken = bin2hex(random_bytes(32));
        $expires = (new DateTimeImmutable())->add(new DateInterval('P7D'));

        $newLink = $this->links->create($current->getUserId(), $newToken, $expires);
        $this->links->deactivate($current, $newLink->getId());

        return $newLink;
    }

    public function deactivate(string $token): void
    {
        $link = $this->requireActiveLink($token);
        $this->links->deactivate($link);
    }
}

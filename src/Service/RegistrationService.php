<?php

namespace App\Service;

use App\Config\AppConfig;
use App\Domain\Entity\Link;
use App\Domain\Entity\User;
use App\Repository\LinkRepository;
use App\Repository\UserRepository;
use App\Validation\RegistrationValidator;
use App\Validation\ValidationException;
use DateInterval;
use DateTimeImmutable;

class RegistrationService
{
    public function __construct(
        private UserRepository $users,
        private LinkRepository $links,
        private RegistrationValidator $validator,
        private AppConfig $config
    ) {
    }

    /**
     * @return array{user: User, link: Link}
     * @throws ValidationException
     */
    public function register(string $username, string $phone): array
    {
        $this->validator->validate($username, $phone);

        $user = $this->users->findByUsernameAndPhone($username, $phone);
        if (!$user) {
            $user = $this->users->create($username, $phone);
        }

        // Ensure only one active link per user
        $this->links->deactivateActiveByUser($user->getId());

        $token = $this->generateToken();
        $expiresAt = (new DateTimeImmutable())->add(new DateInterval('P7D'));
        $link = $this->links->create($user->getId(), $token, $expiresAt);

        return ['user' => $user, 'link' => $link];
    }

    public function buildLinkUrl(Link $link): string
    {
        return $this->config->getBaseUrl() . '/link/' . $link->getToken();
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

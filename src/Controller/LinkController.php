<?php

namespace App\Controller;

use App\Config\AppConfig;
use App\Http\Response;
use App\Service\HistoryService;
use App\Service\LinkService;
use App\Service\LuckyService;
use App\Util\Escape;
use App\Util\HttpException;
use DateTimeInterface;

class LinkController
{
    public function __construct(
        private LinkService $links,
        private LuckyService $lucky,
        private HistoryService $history,
        private AppConfig $config
    ) {
    }

    public function show(string $token): void
    {
        try {
            $link = $this->links->requireActiveLink($token);
        } catch (HttpException $e) {
            $html = $this->renderError($e->getMessage());
            Response::html($html, 404);
            return;
        }

        $expires = $link->getExpiresAt()->format(DateTimeInterface::ATOM);
        $html = $this->renderLinkPage($token, $expires);
        Response::html($html);
    }

    public function regenerate(string $token): void
    {
        try {
            $newLink = $this->links->regenerate($token);
            $url = $this->config->getBaseUrl() . '/link/' . $newLink->getToken();
            Response::json(['link' => $url, 'token' => $newLink->getToken()]);
        } catch (HttpException $e) {
            Response::json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function deactivate(string $token): void
    {
        try {
            $this->links->deactivate($token);
            Response::json(['message' => 'Link deactivated']);
        } catch (HttpException $e) {
            Response::json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function lucky(string $token): void
    {
        try {
            $attempt = $this->lucky->tryLuck($token);
            Response::json([
                'number' => $attempt->getNumber(),
                'result' => $attempt->getResult(),
                'amount' => $attempt->getAmount(),
                'created_at' => $attempt->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        } catch (HttpException $e) {
            Response::json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function history(string $token): void
    {
        try {
            $attempts = $this->history->lastAttempts($token, 3);
            $payload = array_map(fn($attempt) => [
                'number' => $attempt->getNumber(),
                'result' => $attempt->getResult(),
                'amount' => $attempt->getAmount(),
                'created_at' => $attempt->getCreatedAt()->format('Y-m-d H:i:s'),
            ], $attempts);
            Response::json(['attempts' => $payload]);
        } catch (HttpException $e) {
            Response::json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    private function renderLinkPage(string $token, string $expiresAt): string
    {
        $safeToken = Escape::e($token);
        $safeExpires = Escape::e($expiresAt);
        $baseUrl = Escape::e($this->config->getBaseUrl());

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Management</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<div class="container">
    <h1>Link Management</h1>
    <div class="card">
        <p><strong>Token:</strong> <span id="token-text">{$safeToken}</span></p>
        <p><strong>Expires at:</strong> {$safeExpires}</p>
        <div id="status" class="alert"></div>
        <div class="actions">
            <button id="btn-regen">Regenerate link</button>
            <button id="btn-deactivate" class="danger">Deactivate link</button>
            <button id="btn-lucky">Imfeelinglucky</button>
            <button id="btn-history">History</button>
        </div>
        <div id="link-display" class="info"></div>
        <div id="lucky-result" class="info"></div>
        <div id="history" class="info"></div>
    </div>
</div>
<script>
    const appConfig = { baseUrl: '{$baseUrl}', token: '{$safeToken}' };
</script>
<script src="/assets/js/link.js"></script>
</body>
</html>
HTML;
    }

    private function renderError(string $message): string
    {
        $safe = Escape::e($message);
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Link unavailable</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<div class="container">
    <div class="alert danger">{$safe}</div>
    <p><a href="/">Back to registration</a></p>
</div>
</body>
</html>
HTML;
    }
}

<?php

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Service\RegistrationService;
use App\Util\Escape;
use App\Validation\ValidationException;

class HomeController
{
    public function __construct(private RegistrationService $registrationService)
    {
    }

    public function index(array $data = []): void
    {
        $message = $data['message'] ?? '';
        $linkUrl = $data['linkUrl'] ?? '';
        $errors = $data['errors'] ?? [];
        $old = $data['old'] ?? [];

        $html = $this->renderHome($message, $linkUrl, $errors, $old);
        Response::html($html);
    }

    public function register(Request $request): void
    {
        $username = (string) $request->input('username', '');
        $phone = (string) $request->input('phone', '');

        try {
            $result = $this->registrationService->register($username, $phone);
            $linkUrl = $this->registrationService->buildLinkUrl($result['link']);
            $this->index([
                'message' => 'Registration successful! Your unique link is ready.',
                'linkUrl' => $linkUrl,
            ]);
        } catch (ValidationException $e) {
            $this->index([
                'errors' => $e->getErrors(),
                'old' => ['username' => $username, 'phone' => $phone],
            ]);
        }
    }

    private function renderHome(string $message, string $linkUrl, array $errors, array $old): string
    {
        $usernameVal = Escape::e($old['username'] ?? '');
        $phoneVal = Escape::e($old['phone'] ?? '');
        $messageHtml = $message ? '<div class="alert success">' . Escape::e($message) . '</div>' : '';
        $linkHtml = $linkUrl ? '<p><strong>Your link:</strong> <a href="' . Escape::e($linkUrl) . '">' . Escape::e($linkUrl) . '</a></p>' : '';
        $usernameError = isset($errors['username']) ? '<div class="error">' . Escape::e($errors['username']) . '</div>' : '';
        $phoneError = isset($errors['phone']) ? '<div class="error">' . Escape::e($errors['phone']) . '</div>' : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<div class="container">
    <h1>Register</h1>
    {$messageHtml}
    {$linkHtml}
    <form method="POST" action="/register" class="card">
        <label for="username">Username</label>
        <input id="username" name="username" value="{$usernameVal}" required minlength="3" maxlength="50">
        {$usernameError}

        <label for="phone">Phonenumber</label>
        <input id="phone" name="phone" value="{$phoneVal}" required minlength="8" maxlength="20" pattern="^\+?[0-9]{8,20}$">
        {$phoneError}

        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>
HTML;
    }
}

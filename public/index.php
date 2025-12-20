<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

use App\Config\AppConfig;
use App\Database\Connection;
use App\Http\Request;
use App\Http\Response;
use App\Controller\HomeController;
use App\Controller\LinkController;
use App\Repository\AttemptRepository;
use App\Repository\LinkRepository;
use App\Repository\UserRepository;
use App\Service\HistoryService;
use App\Service\LinkService;
use App\Service\LuckyService;
use App\Service\RegistrationService;
use App\Validation\RegistrationValidator;

$config = new AppConfig();
$connection = new Connection($config->getPdoDsn(), $config->getDbUser(), $config->getDbPass());
$pdo = $connection->pdo();

$userRepository = new UserRepository($pdo);
$linkRepository = new LinkRepository($pdo);
$attemptRepository = new AttemptRepository($pdo);

$registrationValidator = new RegistrationValidator();
$registrationService = new RegistrationService($userRepository, $linkRepository, $registrationValidator, $config);
$linkService = new LinkService($linkRepository);
$luckyService = new LuckyService($attemptRepository, $linkService);
$historyService = new HistoryService($attemptRepository, $linkService);

$homeController = new HomeController($registrationService);
$linkController = new LinkController($linkService, $luckyService, $historyService, $config);

$request = new Request();
$path = $request->path();
$method = $request->method();

if ($method === 'GET' && $path === '/') {
    $homeController->index();
    return;
}

if ($method === 'POST' && $path === '/register') {
    $homeController->register($request);
    return;
}

if (preg_match('#^/link/([A-Za-z0-9]+)$#', $path, $matches) && $method === 'GET') {
    $linkController->show($matches[1]);
    return;
}

if (preg_match('#^/link/([A-Za-z0-9]+)/regen$#', $path, $matches) && $method === 'POST') {
    $linkController->regenerate($matches[1]);
    return;
}

if (preg_match('#^/link/([A-Za-z0-9]+)/deactivate$#', $path, $matches) && $method === 'POST') {
    $linkController->deactivate($matches[1]);
    return;
}

if (preg_match('#^/link/([A-Za-z0-9]+)/lucky$#', $path, $matches) && $method === 'POST') {
    $linkController->lucky($matches[1]);
    return;
}

if (preg_match('#^/link/([A-Za-z0-9]+)/history$#', $path, $matches) && $method === 'GET') {
    $linkController->history($matches[1]);
    return;
}

Response::html('<h1>404 Not Found</h1><p>The page you requested was not found.</p><p><a href="/">Back to home</a></p>', 404);

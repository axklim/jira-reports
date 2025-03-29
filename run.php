<?php

require __DIR__ . '/vendor/autoload.php';

use JiraReport\Application;

// Load environment variables if a .env file exists
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$app = new Application();
$app->run();

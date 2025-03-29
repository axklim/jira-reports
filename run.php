<?php

require __DIR__ . '/vendor/autoload.php';

use Aleksey\Jira\Application;

// Load environment variables if a .env file exists
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} else {
    // For demonstration purposes only - in production use .env file
    $_ENV['JIRA_BASE_URL'] = 'https://yourdomain.atlassian.net';
    $_ENV['JIRA_EMAIL'] = 'your-email@example.com';
    $_ENV['JIRA_API_TOKEN'] = 'your-api-token';
}

$app = new Application();
$app->run();
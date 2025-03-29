<?php

namespace JiraReport;

use DI\Container;
use DI\ContainerBuilder;

class Application
{
    private Container $container;

    public function __construct()
    {
        $this->initContainer();
    }

    private function initContainer(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            // Environment configuration
            'jira.base_url' => $_ENV['JIRA_BASE_URL'] ?? '',
            'jira.email' => $_ENV['JIRA_EMAIL'] ?? '',
            'jira.api_token' => $_ENV['JIRA_API_TOKEN'] ?? '',
            
            // Services
            JiraClient::class => function (Container $c) {
                return new JiraClient(
                    $c->get('jira.base_url'),
                    $c->get('jira.email'),
                    $c->get('jira.api_token')
                );
            },
            
            TableRenderer::class => function () {
                return new TableRenderer();
            },
        ]);
        
        $this->container = $builder->build();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function run(): void
    {
        try {
            // Example: Get an epic from command line
            $epicKey = $_SERVER['argv'][1] ?? null;
            
            if (!$epicKey) {
                echo "Usage: php run.php EPIC-KEY\n";
                return;
            }
            
            $jiraClient = $this->container->get(JiraClient::class);
            $issues = $jiraClient->getIssuesInEpic($epicKey);
            
            echo "Issues in Epic \033[1;33m$epicKey\033[0m:\n\n";
            
            // Format issues as a color table
            $tableRenderer = $this->container->get(TableRenderer::class);
            $headers = ['Key', 'Summary', 'Status', 'Story Points'];
            $rows = [];
            $totalPoints = 0;
            
            foreach ($issues['issues'] as $issue) {
                $key = $issue['key'];
                $summary = $issue['fields']['summary'];
                $status = $issue['fields']['status']['name'];
                $storyPoints = $issue['fields']['customfield_10406'] ?? 0;
                $totalPoints += $storyPoints;
                
                // Color-code status
                $statusFormatted = match ($status) {
                    'Done' => "\033[1;32m$status\033[0m", // Green
                    'In Progress' => "\033[1;33m$status\033[0m", // Yellow
                    'To Do' => "\033[1;36m$status\033[0m", // Cyan
                    default => $status,
                };
                
                $rows[] = [
                    $key,
                    $summary,
                    $statusFormatted,
                    $storyPoints,
                ];
            }
            
            $tableOptions = [
                'headerStyle' => "\033[1;94m", // Bold blue
                'borderColor' => "\033[0;36m", // Cyan
                'evenRowColor' => "\033[0;37m", // Light gray
            ];
            
            echo $tableRenderer->renderColorTable($headers, $rows, $tableOptions);
            
            // Calculate total story points and status summary
            echo "\n\033[1;37mTotal Story Points: \033[1;32m$totalPoints\033[0m\n";
            
            // Status summary
            $statusCounts = [];
            foreach ($issues['issues'] as $issue) {
                $status = $issue['fields']['status']['name'];
                $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
            }
            
            echo "\n\033[1;37mStatus Summary:\033[0m\n";
            foreach ($statusCounts as $status => $count) {
                $statusColor = match ($status) {
                    'Done' => "\033[1;32m", // Green
                    'In Progress' => "\033[1;33m", // Yellow
                    'To Do' => "\033[1;36m", // Cyan
                    default => "\033[1;37m", // White
                };
                echo "$statusColor$status\033[0m: $count\n";
            }
            
        } catch (\Exception $e) {
            echo "\033[1;31mError: " . $e->getMessage() . "\033[0m\n";
        }
    }
}
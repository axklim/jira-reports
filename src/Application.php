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
        // Example: Get an epic from command line
        $epicKey = $_SERVER['argv'][1] ?? null;
        $epicKey = $epicKey ?? $_ENV['JIRA_EPIC_KEY'] ?? null;

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

        foreach ($issues as $issue) {
            $key = $issue->key;
            $summary = $issue->summary;
            $status = $issue->status;
            $storyPoints = $issue->storyPoints;
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

        // Status summary
        $donePoints = 0;
        $inProgressPoints = 0;
        $toDoPoints = 0;
        foreach ($issues as $issue) {
            switch ($issue->status) {
                case 'Done':
                    $donePoints += $issue->storyPoints;
                    break;
                case 'In Progress':
                    $inProgressPoints += $issue->storyPoints;
                    break;
                case 'To Do':
                    $toDoPoints += $issue->storyPoints;
                    break;
            }
        }

        // Summary Report
        $toDoPercent =round($toDoPoints*100/$totalPoints);
        $donePercent = round($donePoints*100/$totalPoints);
        $inProgressPercent=round($inProgressPoints*100/$totalPoints);
        $BothPercentage= round($inProgressPercent+$donePercent);
        $BothPoints= ($inProgressPoints+$donePoints);
        echo "\nTotal Story Points: $totalPoints\n";
        echo "===============================\n";
        echo "To Do:        $toDoPoints points  ($toDoPercent%)\n";
        echo "Done:         $donePoints points  ($donePercent%)\n";
        echo "In Progress:  $inProgressPoints points  ($inProgressPercent%)\n";
        echo "-------------------------------\n";
        echo "Done & In Progress: $BothPoints points  ($BothPercentage%)\n";
        echo "===============================\n\n";
    }
}
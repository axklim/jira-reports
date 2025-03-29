<?php

namespace Aleksey\Jira;

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
            
            echo "Issues in Epic $epicKey:\n";
            foreach ($issues['issues'] as $issue) {
                echo "- " . 
                    $issue['key'] . 
                    ": " . 
                    $issue['fields']['summary'] . 
                    " (" . 
                    $issue['fields']['status']['name'] . 
                    ")\n";
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
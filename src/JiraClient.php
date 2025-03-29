<?php

namespace JiraReport;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class JiraClient
{
    private Client $client;
    private string $baseUrl;

    public function __construct(string $baseUrl, string $email, string $apiToken)
    {
        $this->baseUrl = $baseUrl;
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'auth' => [$email, $apiToken],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getIssuesInEpic(string $epicKey, int $startAt = 0, int $maxResults = 100): array
    {
        $jqlQuery = [
            'jql' => "'parent' = $epicKey",
            'startAt' => $startAt,
            'maxResults' => $maxResults,
            'fields' => ['key', 'summary', 'status', 'customfield_10406'],
        ];
        
        try {
            $response = $this->client->post('/rest/api/2/search', [
                'json' => $jqlQuery,
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Failed to fetch issues: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
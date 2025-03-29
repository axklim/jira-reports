<?php

namespace JiraReport;

class Issue
{
    public function __construct(
        public readonly string $key,
        public readonly string $summary,
        public readonly string $status,
        public readonly int $storyPoints,
    ) {
    }

    public static function make($payload): self
    {
        return new self(
            $payload['key'],
            $payload['fields']['summary'],
            $payload['fields']['status']['name'],
            (int) ($payload['fields']['customfield_10406'] ?? 0),
        );
    }
}

<?php

// JIRA API Credentials

// JIRA API URL for JQL Search
$jira_api_url = "$jira_base_url/rest/api/2/search";

// JQL query to find all tickets in the given epic
$jql_query = [
    "jql" => "'parent' = $epic_key",
    "startAt" => 0,
    "maxResults" => 100,
    "fields" => ["key", "summary", "status", "customfield_10406"],
];

// cURL request setup
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $jira_api_url);
curl_setopt($ch, CURLOPT_USERPWD, "$jira_email:$jira_api_token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jql_query));

// Execute request
$response = curl_exec($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check response status
if ($status_code == 200) {
    $tickets = json_decode($response, true);
    echo "Tickets in Epic $epic_key:\n";
    print_r($tickets);
    foreach ($tickets["issues"] as $issue) {
        echo "- " .
            $issue["key"] .
            ": " .
            $issue["fields"]["summary"] .
            " (" .
            $issue["fields"]["status"]["name"] .
            ")\n";
    }
} else {
    echo "Error fetching tickets. HTTP Status Code: $status_code\n";
}

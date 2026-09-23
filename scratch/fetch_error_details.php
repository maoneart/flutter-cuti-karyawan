<?php
$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: PHP-Script\r\nAccept: application/vnd.github+json\r\n",
        'follow_location' => 1
    ]
]);

$runId = '35827834451';
$url = "https://api.github.com/repos/maoneart/flutter-cuti-karyawan/actions/runs/{$runId}/jobs";
$res = file_get_contents($url, false, $context);
if ($res) {
    $data = json_decode($res, true);
    foreach ($data['jobs'] as $job) {
        echo "=== JOB: {$job['name']} (ID: {$job['id']}, Status: {$job['conclusion']}) ===\n";
        foreach ($job['steps'] as $step) {
            echo "  - Step: {$step['name']} [{$step['conclusion']}]\n";
        }
    }
} else {
    echo "Failed to fetch jobs.\n";
}

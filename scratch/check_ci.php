<?php
$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: PHP-Script\r\n"
    ]
]);
$raw = file_get_contents('https://api.github.com/repos/maoneart/flutter-cuti-karyawan/actions/runs?per_page=5', false, $context);
if (!$raw) {
    echo "Error fetching runs\n";
    exit;
}
$data = json_decode($raw, true);
foreach ($data['workflow_runs'] as $run) {
    echo "RUN ID: " . $run['id'] . "\n";
    echo "Status: " . $run['status'] . "\n";
    echo "Conclusion: " . ($run['conclusion'] ?? 'in_progress') . "\n";
    echo "Commit: " . $run['head_commit']['message'] . "\n";
    echo "URL: " . $run['html_url'] . "\n";

    // Fetch jobs for this run
    $jobsRaw = @file_get_contents($run['jobs_url'], false, $context);
    if ($jobsRaw) {
        $jobsData = json_decode($jobsRaw, true);
        foreach ($jobsData['jobs'] as $job) {
            echo "  JOB: " . $job['name'] . " -> " . ($job['conclusion'] ?? $job['status']) . "\n";
        }
    }
    echo "----------------------------------------\n";
}

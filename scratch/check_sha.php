<?php
$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: PHP-Script\r\n"
    ]
]);
$raw = file_get_contents('https://api.github.com/repos/maoneart/flutter-cuti-karyawan/actions/runs?head_sha=ff586b4b14d02ac8c005f93d5944ed3d31248de4', false, $context);
if ($raw) {
    $data = json_decode($raw, true);
    foreach ($data['workflow_runs'] as $run) {
        echo "RUN ID: " . $run['id'] . "\n";
        echo "Status: " . $run['status'] . "\n";
        echo "Conclusion: " . ($run['conclusion'] ?? 'in_progress') . "\n";
        echo "Commit: " . $run['head_commit']['message'] . "\n";
        echo "URL: " . $run['html_url'] . "\n";

        $jobsRaw = @file_get_contents($run['jobs_url'], false, $context);
        if ($jobsRaw) {
            $jobsData = json_decode($jobsRaw, true);
            foreach ($jobsData['jobs'] as $job) {
                echo "  JOB: " . $job['name'] . " -> " . ($job['conclusion'] ?? $job['status']) . "\n";
                foreach ($job['steps'] as $step) {
                    echo "    Step: " . $step['name'] . " -> " . ($step['conclusion'] ?? $step['status']) . "\n";
                }
            }
        }
    }
}

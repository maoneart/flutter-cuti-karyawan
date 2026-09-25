<?php
$url = 'https://api.github.com/repos/maoneart/flutter-cuti-karyawan/actions/runs?per_page=1';
$opts = [
    'http' => [
        'method' => 'GET',
        'header' => ['User-Agent: Antigravity-Agent']
    ]
];
$context = stream_context_create($opts);
$res = @file_get_contents($url, false, $context);
if ($res) {
    $data = json_decode($res, true);
    if (!empty($data['workflow_runs'][0])) {
        $run = $data['workflow_runs'][0];
        echo "Run ID: " . $run['id'] . "\n";
        echo "Status: " . $run['status'] . "\n";
        echo "Conclusion: " . ($run['conclusion'] ?? 'null') . "\n";
        echo "Jobs URL: " . $run['jobs_url'] . "\n";

        // Fetch jobs
        $jobsRes = @file_get_contents($run['jobs_url'], false, $context);
        if ($jobsRes) {
            $jobsData = json_decode($jobsRes, true);
            foreach ($jobsData['jobs'] as $job) {
                echo "\nJob: " . $job['name'] . " | Status: " . $job['status'] . " | Conclusion: " . ($job['conclusion'] ?? 'null') . "\n";
                foreach ($job['steps'] as $step) {
                    if (($step['conclusion'] ?? '') === 'failure') {
                        echo "  FAILED STEP: " . $step['name'] . "\n";
                    }
                }
            }
        }
    }
}

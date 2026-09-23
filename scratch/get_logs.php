<?php
$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: PHP-Script\r\n",
        'follow_location' => 1
    ]
]);
$raw = file_get_contents('https://api.github.com/repos/maoneart/flutter-cuti-karyawan/actions/runs/35824744949/jobs', false, $context);
if ($raw) {
    $data = json_decode($raw, true);
    foreach ($data['jobs'] as $job) {
        if ($job['name'] === 'Build Android Release APK') {
            echo "Fetching logs for Job ID: " . $job['id'] . "\n";
            $logUrl = "https://api.github.com/repos/maoneart/flutter-cuti-karyawan/actions/jobs/{$job['id']}/logs";
            $logs = @file_get_contents($logUrl, false, $context);
            if ($logs) {
                // Find lines with error or exception
                $lines = explode("\n", $logs);
                $slice = array_slice($lines, -60);
                echo implode("\n", $slice);
            } else {
                echo "Could not fetch log stream (requires auth or redirect)\n";
            }
        }
    }
}

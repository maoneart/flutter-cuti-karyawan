<?php
$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: PHP-Script\r\n"
    ]
]);
$raw = file_get_contents('https://api.github.com/repos/maoneart/flutter-cuti-karyawan/actions/runs/35836737355/jobs', false, $context);
$data = json_decode($raw, true);
foreach ($data['jobs'] as $job) {
    echo "Job: " . $job['name'] . " - " . $job['conclusion'] . "\n";
    foreach ($job['steps'] as $step) {
        echo "  Step: " . $step['name'] . " - " . $step['conclusion'] . "\n";
    }
}

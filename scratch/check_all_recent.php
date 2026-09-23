<?php
$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: PHP-Script\r\n"
    ]
]);
$raw = file_get_contents('https://api.github.com/repos/maoneart/flutter-cuti-karyawan/actions/runs?per_page=5', false, $context);
$data = json_decode($raw, true);
foreach ($data['workflow_runs'] as $r) {
    echo "ID: " . $r['id'] . " | " . $r['status'] . " | " . ($r['conclusion'] ?? 'in_progress') . " | " . $r['head_sha'] . " | " . $r['head_commit']['message'] . "\n";
    echo "  URL: " . $r['html_url'] . "\n";
}

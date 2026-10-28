<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/content.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (!isset($argv[1])) {
    throw new InvalidArgumentException('Usage: php benchmark.php OUTPUT.json [REFERENCE.json] [BASE_URL]');
}
$output = $argv[1];
$reference = $argv[2] ?? null;
$base_url = $argv[3] ?? 'http://localhost:8003';

$pages = [
    '/', '/about.php', '/thanks.php', '/bands_grid.php', '/bands_list.php',
    '/bands_table.php', '/artists_grid.php', '/artists_list.php',
    '/artists_table.php', '/artists_table_detailed.php', '/stats.php',
    '/calendar.php', '/tree.php', '/timeline.php', '/members.php',
    '/releases_band_grid.php', '/releases_band_list.php', '/releases_band_table.php',
    '/releases_related_grid.php', '/releases_related_list.php', '/releases_related_table.php',
    '/artist.php?a=dan_reed', '/band.php?a=shogun_mojo_2',
    '/artist.php?a=missing_migration_test', '/band.php?a=missing_migration_test',
    '/release_band.php?a=missing_migration_test', '/release_related.php?a=missing_migration_test',
];
$measured = [
    '/', '/artists_grid.php', '/artists_table_detailed.php', '/bands_grid.php',
    '/bands_table.php', '/tree.php', '/stats.php', '/calendar.php',
    '/artist.php?a=dan_reed', '/band.php?a=shogun_mojo_2',
    '/releases_band_table.php', '/releases_related_table.php',
];

function request_page(string $path): array
{
    global $base_url;
    $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 120]]);
    $start = hrtime(true);
    $body = file_get_contents($base_url . $path, false, $context);
    $elapsed = (hrtime(true) - $start) / 1e6;
    if ($body === false) {
        throw new RuntimeException('Request failed: ' . $path);
    }
    $headers = http_get_last_response_headers();
    preg_match('/HTTP\/\S+ (\d+)/', $headers[0], $matches);
    $status = (int) $matches[1];
    // Source-file timestamps change during migration; content timestamps must not.
    $normalized = preg_replace(
        '/(Document updated: |Version: )\d{2}-\d{2}-\d{4}/',
        '$1SOURCE-DATE',
        $body
    );
    return [
        'status' => $status,
        'hash' => hash('sha256', $normalized),
        'bytes' => strlen($body),
        'milliseconds' => $elapsed,
        'errors' => preg_match('/(?:Warning|Fatal error|Parse error|Notice):/', $body) === 1,
    ];
}

if ($reference === null) {
    foreach (['artists' => 'artist', 'bands' => 'band', 'releases/band' => 'release_band', 'releases/related' => 'release_related'] as $directory => $page) {
        foreach (array_keys(content_records($directory)) as $slug) {
            $pages[] = '/' . $page . '.php?a=' . rawurlencode($slug);
        }
    }
} else {
    $before = json_decode(file_get_contents($reference), true, 512, JSON_THROW_ON_ERROR);
    $pages = array_keys($before['pages']);
}

$result = ['php' => PHP_VERSION, 'base_url' => $base_url, 'warmups' => 2, 'samples' => 25, 'pages' => [], 'timings' => []];
foreach (array_unique($pages) as $path) {
    $response = request_page($path);
    if ($response['errors'] || $response['status'] >= 500) {
        throw new RuntimeException('Page failed validation: ' . $path);
    }
    unset($response['milliseconds']);
    $result['pages'][$path] = $response;
}
echo 'Captured ' . count($result['pages']) . " page responses.\n";
foreach ($measured as $path) {
    request_page($path);
    request_page($path);
    $samples = [];
    for ($i = 0; $i < 25; $i++) {
        $samples[] = request_page($path)['milliseconds'];
    }
    sort($samples, SORT_NUMERIC);
    $result['timings'][$path] = [
        'median_ms' => $samples[12],
        'p95_ms' => $samples[23],
        'min_ms' => $samples[0],
        'samples_ms' => $samples,
    ];
    printf("%-42s median %8.2f ms  p95 %8.2f ms\n", $path, $samples[12], $samples[23]);
}
$json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
if (file_put_contents($output, $json) !== strlen($json)) {
    throw new RuntimeException('Cannot write benchmark results: ' . $output);
}
echo "Saved $output\n";
if ($reference !== null) {
    $differences = [];
    foreach ($before['pages'] as $path => $expected) {
        if ($expected['status'] !== $result['pages'][$path]['status']
            || $expected['hash'] !== $result['pages'][$path]['hash']) {
            $differences[] = $path;
        }
    }
    if ($differences !== []) {
        throw new RuntimeException("Changed page responses:\n" . implode("\n", $differences));
    }
    echo count($pages) . " page responses match the reference.\n";
}

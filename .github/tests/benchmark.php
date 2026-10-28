<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $diagnostics = [
        '/warning-plain' => ['0', E_USER_WARNING],
        '/warning-html' => ['1', E_USER_WARNING],
        '/notice-html' => ['1', E_USER_NOTICE],
        '/deprecated-plain' => ['0', E_USER_DEPRECATED],
        '/deprecated-html' => ['1', E_USER_DEPRECATED],
    ];
    if (isset($diagnostics[$path])) {
        ini_set('html_errors', $diagnostics[$path][0]);
        trigger_error('Benchmark test diagnostic', $diagnostics[$path][1]);
    } elseif ($path === '/fatal-html') {
        echo '<b>Fatal error</b>: Benchmark test diagnostic';
    } elseif ($path === '/parse-html') {
        echo '<b>Parse error</b>: Benchmark test diagnostic';
    } elseif ($path === '/server-error') {
        http_response_code(500);
        echo 'Server failed';
    } else {
        echo 'Healthy response';
    }
    return;
}

$socket = stream_socket_server('tcp://127.0.0.1:0', $error_number, $error_message);
if ($socket === false) {
    throw new RuntimeException('Cannot reserve fixture port: ' . $error_message);
}
$address = stream_socket_get_name($socket, false);
fclose($socket);
$root = dirname(__DIR__, 2);
$reference = tempnam(sys_get_temp_dir(), 'benchmark-reference-');
$output = tempnam(sys_get_temp_dir(), 'benchmark-output-');
$log = tempnam(sys_get_temp_dir(), 'benchmark-server-');
if ($reference === false || $output === false || $log === false) {
    throw new RuntimeException('Cannot create benchmark test files.');
}
$server = null;
try {
    $server = proc_open(
        [PHP_BINARY, '-d', 'display_errors=1', '-d', 'error_reporting=-1',
            '-S', $address, '-t', __DIR__, __FILE__],
        [0 => ['pipe', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']],
        $pipes,
        $root
    );
    if (!is_resource($server)) {
        throw new RuntimeException('Cannot start benchmark fixture server.');
    }
    fclose($pipes[0]);
    $ready = false;
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $connection = @stream_socket_client('tcp://' . $address, $error_number, $error_message, 0.1);
        if ($connection !== false) {
            fclose($connection);
            $ready = true;
            break;
        }
        usleep(50000);
    }
    if (!$ready) {
        throw new RuntimeException('Fixture server did not start: ' . file_get_contents($log));
    }

    foreach ([
        '/warning-plain', '/warning-html', '/notice-html',
        '/deprecated-plain', '/deprecated-html', '/fatal-html', '/parse-html',
        '/server-error', '/healthy',
    ] as $path) {
        $json = json_encode(['pages' => [$path => [
            'status' => 200, 'hash' => hash('sha256', 'Healthy response'),
        ]]], JSON_THROW_ON_ERROR);
        if (file_put_contents($reference, $json) !== strlen($json)) {
            throw new RuntimeException('Cannot write benchmark reference.');
        }
        $process = proc_open(
            [PHP_BINARY, $root . '/.github/tools/benchmark.php', $output, $reference, 'http://' . $address],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $root
        );
        if (!is_resource($process)) {
            throw new RuntimeException('Cannot run benchmark: ' . $path);
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $status = proc_close($process);
        if ($path === '/healthy') {
            if ($status !== 0 || !str_contains($stdout, '1 page responses match the reference.')) {
                throw new RuntimeException('Healthy page rejected: ' . $stdout . $stderr);
            }
        } elseif ($status === 0 || !str_contains($stdout . $stderr, 'Page failed validation: ' . $path)) {
            throw new RuntimeException('Diagnostic not rejected: ' . $path . "\n" . $stdout . $stderr);
        }
        echo 'PASS: ' . $path . "\n";
    }
} finally {
    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
    }
    unlink($reference);
    unlink($output);
    unlink($log);
}

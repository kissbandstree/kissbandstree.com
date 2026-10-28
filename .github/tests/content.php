<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/content.php';

function check_content($condition, $message)
{
  if (!$condition) {
    throw new RuntimeException($message);
  }
}

$root = dirname(__DIR__, 2);
chdir($root);
$snapshot = isset($argv[1])
  ? json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR)
  : null;
$fixture = tempnam(sys_get_temp_dir(), 'content-test-');
if ($fixture === false) {
  throw new RuntimeException('Cannot create test fixture.');
}

$count = 0;
try {
  foreach (content_collections() as $directory => $name) {
    $records = content_records($directory);
    $prefix = $directory === '.' ? '' : $directory . '/';
    $expected = array_map(function ($slug) use ($prefix) {
      return $prefix . $slug . '.txt';
    }, array_keys($records));
    check_content(content_glob($prefix . '*.txt', GLOB_NOSORT) === $expected, 'Unsorted order changed: ' . $directory);
    sort($expected, SORT_STRING);
    check_content(content_glob($prefix . '*.txt') === $expected, 'Sorted order changed: ' . $directory);
    $absolute = array_map(function ($path) use ($root) { return $root . '/' . $path; }, $expected);
    check_content(content_glob($root . '/' . $prefix . '*.txt') === $absolute, 'Absolute listing changed: ' . $directory);
    if (glob($prefix . '*.txt') !== []) {
      check_content(content_glob($prefix . '*.txt') === glob($prefix . '*.txt'), 'Native listing differs: ' . $directory);
      check_content(content_glob($prefix . '*.txt', GLOB_NOSORT) === glob($prefix . '*.txt', GLOB_NOSORT), 'Native unsorted listing differs: ' . $directory);
    }

    foreach ($records as $slug => $record) {
      $key = $prefix . $slug . '.txt';
      $text = implode('', $record['lines']);
      check_content(content_exists($key), 'Record missing: ' . $key);
      check_content(content_text($key) === $text, 'Text changed: ' . $key);
      check_content(content_size($key) === strlen($text), 'Size changed: ' . $key);
      check_content(content_mtime($key) === $record['modified'], 'Timestamp changed: ' . $key);
      check_content(content_file($root . '/' . $key) === $record['lines'], 'Absolute read differs: ' . $key);
      if ($snapshot !== null) {
        check_content(isset($snapshot[$key]), 'Unexpected migrated record: ' . $key);
        check_content($snapshot[$key]['text'] === $text, 'Original bytes changed: ' . $key);
        check_content($snapshot[$key]['modified'] === $record['modified'], 'Original timestamp changed: ' . $key);
      }
      file_put_contents($fixture, $text);
      foreach ([0, FILE_IGNORE_NEW_LINES, FILE_SKIP_EMPTY_LINES, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES] as $flags) {
        check_content(content_file($key, $flags) === file($fixture, $flags), 'Line semantics changed: ' . $key . ', flags ' . $flags);
      }
      $count++;
    }
    check_content(!content_exists($prefix . 'missing_content_test.txt'), 'Missing record exists.');
  }
  if ($snapshot !== null) {
    check_content($count === count($snapshot), 'Record count changed.');
  }
  check_content(content_text('img/logo.svg') === file_get_contents('img/logo.svg'), 'Static SVG read changed.');
  check_content(content_mtime('style.css') === filemtime('style.css'), 'Static timestamp changed.');
  check_content(content_exists('style.css') === file_exists('style.css'), 'Static existence changed.');
  check_content(content_glob('photos_artists/*.png') === glob('photos_artists/*.png'), 'Static image listing changed.');
  check_content(content_glob('artists/missing_*.txt') === [], 'Missing pattern should be empty.');
  foreach ([
    '{',
    '{"schema_version":2,"records":{}}',
    '{"schema_version":1,"records":{"bad":{"modified":1,"lines":[false]}}}',
    '{"schema_version":1,"records":{"bad":{"modified":1,"lines":["two\\nlines\\n"]}}}',
    '{"schema_version":1,"records":{"bad":{"modified":1,"lines":["missing newline","next"]}}}',
    '{"schema_version":1,"records":{"../bad":{"modified":1,"lines":[]}}}',
    '{"schema_version":1,"records":{"bad":{"modified":"today","lines":[]}}}',
  ] as $invalid) {
    file_put_contents($fixture, $invalid);
    $rejected = false;
    try {
      read_content_collection($fixture);
    } catch (RuntimeException $error) {
      $rejected = true;
    }
    check_content($rejected, 'Invalid JSON/schema was accepted: ' . $invalid);
  }
  $rejected = false;
  try {
    content_file('artists/missing_content_test.txt');
  } catch (RuntimeException $error) {
    $rejected = true;
  }
  check_content($rejected, 'Missing required data was silently accepted.');
  echo "PASS: $count records; bytes, timestamps, line flags, ordering, static assets and invalid data.\n";
} finally {
  unlink($fixture);
}

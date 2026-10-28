<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/content.php';

if (PHP_SAPI !== 'cli') {
  http_response_code(404);
  exit;
}

$root = dirname(__DIR__, 2);
$remove = ($argv[1] ?? '') === '--remove-text';
if (isset($argv[1]) && !$remove) {
  throw new InvalidArgumentException('Use no arguments to convert, or --remove-text after verification.');
}
if (!$remove && !is_dir($root . '/data') && !mkdir($root . '/data')) {
  throw new RuntimeException('Cannot create data directory.');
}

$total = 0;
foreach (content_collections() as $directory => $name) {
  $files = glob($root . '/' . ($directory === '.' ? '' : $directory . '/') . '*.txt', GLOB_NOSORT);
  $records = [];
  foreach ($files as $file) {
    $lines = file($file);
    if ($lines === false) {
      throw new RuntimeException('Cannot read ' . $file);
    }
    $records[basename($file, '.txt')] = ['modified' => filemtime($file), 'lines' => $lines];
  }
  $path = $root . '/data/' . $name . '.json';
  if ($remove) {
    $saved = read_content_collection($path);
    foreach ($records as $slug => $record) {
      if (($saved[$slug] ?? null) !== $record) {
        throw new RuntimeException('Refusing to delete mismatched source: ' . $directory . '/' . $slug);
      }
    }
  } else {
    if (file_exists($path)) {
      throw new RuntimeException('Refusing to overwrite existing collection: ' . $path);
    }
    $json = json_encode(
      ['schema_version' => 1, 'records' => (object) $records],
      JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    ) . "\n";
    if (file_put_contents($path, $json) !== strlen($json)) {
      throw new RuntimeException('Cannot save ' . $path);
    }
    if (read_content_collection($path) !== $records) {
      throw new RuntimeException('Round-trip failed: ' . $path);
    }
  }
  $total += count($files);
  echo $name . ': ' . count($files) . " records verified\n";
}

if ($remove) {
  // All collections are verified before any source is removed.
  foreach (content_collections() as $directory => $name) {
    foreach (glob($root . '/' . ($directory === '.' ? '' : $directory . '/') . '*.txt') as $file) {
      if (!unlink($file)) {
        throw new RuntimeException('Cannot remove verified source: ' . $file);
      }
    }
  }
}
echo $total . ($remove ? " text files removed.\n" : " records converted without changing source files.\n");

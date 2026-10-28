<?php

function content_collections()
{
  return [
    '.' => 'pages',
    'artists' => 'artists',
    'bands' => 'bands',
    'bands_list' => 'artist-bands',
    'artists_credits' => 'artist-credits',
    'bands_credits' => 'band-credits',
    'bands_comments' => 'band-comments',
    'releases/band' => 'band-releases',
    'releases/related' => 'related-releases',
    'releases_comments/band' => 'band-release-comments',
    'releases_comments/related' => 'related-release-comments',
    'releases_tracklists/band' => 'band-release-tracklists',
    'releases_tracklists/related' => 'related-release-tracklists',
  ];
}

function content_key($path)
{
  $path = str_replace('\\', '/', $path);
  $root = str_replace('\\', '/', __DIR__) . '/';
  if (strncasecmp($path, $root, strlen($root)) === 0) {
    $path = substr($path, strlen($root));
  }
  return $path;
}

function read_content_collection($path)
{
  $text = file_get_contents($path);
  if ($text === false) {
    throw new RuntimeException('Cannot read content collection: ' . $path);
  }
  $data = json_decode($text, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    throw new RuntimeException('Invalid JSON in ' . $path . ': ' . json_last_error_msg());
  }
  if (!is_array($data) || ($data['schema_version'] ?? null) !== 1 || !isset($data['records']) || !is_array($data['records'])) {
    throw new RuntimeException('Invalid content collection schema: ' . $path);
  }
  foreach ($data['records'] as $slug => $record) {
    if (!is_string($slug) || !preg_match('/^[a-z0-9_]+$/D', $slug)
      || !is_array($record) || !isset($record['modified'], $record['lines'])
      || !is_int($record['modified']) || $record['modified'] < 0
      || !is_array($record['lines']) || array_values($record['lines']) !== $record['lines']) {
      throw new RuntimeException('Invalid record in ' . $path . ': ' . $slug);
    }
    foreach ($record['lines'] as $index => $line) {
      if (!is_string($line) || $line === ''
        || (strpos($line, "\n") !== false && strpos($line, "\n") !== strlen($line) - 1)
        || ($index < count($record['lines']) - 1 && substr($line, -1) !== "\n")) {
        throw new RuntimeException('Invalid text line in ' . $path . ': ' . $slug);
      }
    }
  }
  return $data['records'];
}

function content_records($directory)
{
  static $collections = [];
  $names = content_collections();
  if (!isset($names[$directory])) {
    throw new InvalidArgumentException('Unknown content collection: ' . $directory);
  }
  if (!isset($collections[$directory])) {
    $path = __DIR__ . '/data/' . $names[$directory] . '.json';
    try {
      $collections[$directory] = read_content_collection($path);
    } catch (RuntimeException $error) {
      http_response_code(500);
      error_log($error->getMessage());
      throw $error;
    }
  }
  return $collections[$directory];
}

function content_record($path)
{
  $key = content_key($path);
  $directory = dirname($key);
  $slug = basename($key, '.txt');
  $records = content_records($directory);
  if (!isset($records[$slug])) {
    throw new RuntimeException('Content record not found: ' . $key);
  }
  return $records[$slug];
}

function content_file($path, $flags = 0)
{
  if (($flags & ~(FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) !== 0) {
    throw new InvalidArgumentException('Unsupported content read flags.');
  }
  $lines = content_record($path)['lines'];
  $result = [];
  foreach ($lines as $line) {
    if ($flags & FILE_IGNORE_NEW_LINES) {
      $line = preg_replace('/\r?\n$/D', '', $line);
    }
    if (($flags & FILE_SKIP_EMPTY_LINES) && $line === '') {
      continue;
    }
    $result[] = $line;
  }
  return $result;
}

function content_text($path)
{
  if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'txt') {
    return file_get_contents($path);
  }
  return implode('', content_record($path)['lines']);
}

function content_exists($path)
{
  if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'txt') {
    return file_exists($path);
  }
  $key = content_key($path);
  $directory = dirname($key);
  if (!isset(content_collections()[$directory])) {
    return false;
  }
  return isset(content_records($directory)[basename($key, '.txt')]);
}

function content_mtime($path)
{
  if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'txt') {
    return filemtime($path);
  }
  return content_record($path)['modified'];
}

function content_size($path)
{
  if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'txt') {
    return filesize($path);
  }
  return strlen(content_text($path));
}

function content_glob($pattern, $flags = 0)
{
  if (strtolower(pathinfo($pattern, PATHINFO_EXTENSION)) !== 'txt') {
    return glob($pattern, $flags);
  }
  if (($flags & ~GLOB_NOSORT) !== 0) {
    throw new InvalidArgumentException('Unsupported content listing flags.');
  }
  $key = content_key($pattern);
  $directory = dirname($key);
  $prefix = substr($pattern, 0, strlen($pattern) - strlen(basename($key)));
  $files = [];
  foreach (content_records($directory) as $slug => $record) {
    $name = $slug . '.txt';
    if (fnmatch(basename($key), $name)) {
      $files[] = $prefix . $name;
    }
  }
  if (!($flags & GLOB_NOSORT)) {
    sort($files, SORT_STRING);
  }
  return $files;
}

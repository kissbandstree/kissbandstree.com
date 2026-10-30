<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

function check_updated($condition, $message)
{
  if (!$condition) {
    throw new RuntimeException($message);
  }
}

if (($argv[1] ?? '') === '--fixture') {
  chdir($argv[2]);
  require getcwd() . '/functions.php';
  check_updated(get_band_mtime('sample') === (int) $argv[3], 'Band fixture maximum is incorrect.');
  check_updated(get_artist_mtime('sample') === (int) $argv[4], 'Artist fixture maximum is incorrect.');
  exit;
}

if (($argv[1] ?? '') === '--render') {
  chdir($root);
  $_SERVER['DOCUMENT_ROOT'] = $root;
  $_SERVER['HTTP_HOST'] = 'localhost:8003';
  $_SERVER['SCRIPT_FILENAME'] = $root . '/' . $argv[2];
  $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/' . $argv[2];
  $_GET['a'] = $argv[3] ?? '';
  $_SERVER['REQUEST_URI'] = '/' . $argv[2] . '?a=' . rawurlencode($_GET['a']);
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
  });
  require $root . '/' . $argv[2];
  exit;
}

function run_updated_test(array $arguments): string
{
  $process = proc_open(array_merge([PHP_BINARY, __FILE__], $arguments),
    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
  check_updated(is_resource($process), 'Cannot start update-date test process.');
  $output = stream_get_contents($pipes[1]);
  $errors = stream_get_contents($pipes[2]);
  fclose($pipes[1]);
  fclose($pipes[2]);
  $status = proc_close($process);
  check_updated($status === 0 && $errors === '', 'Update-date test failed: ' . $errors . $output);
  return $output;
}

$fixture = sys_get_temp_dir() . '/updated-test-' . bin2hex(random_bytes(8));
$fixtureFiles = [];
$fixtureDirectories = [];
try {
  check_updated(mkdir($fixture), 'Cannot create update-date fixture.');
  foreach (['data', 'photos_bands', 'photos_bands_large', 'photos_artists', 'photos_artists_large'] as $directory) {
    $path = $fixture . '/' . $directory;
    check_updated(mkdir($path), 'Cannot create fixture directory.');
    $fixtureDirectories[] = $path;
  }
  foreach (['functions.php', 'content.php'] as $file) {
    $path = $fixture . '/' . $file;
    check_updated(copy($root . '/' . $file, $path), 'Cannot copy fixture runtime.');
    $fixtureFiles[] = $path;
  }
  $collections = ['bands', 'band-credits', 'band-comments', 'artists', 'artist-credits'];
  $photos = ['photos_bands/sample.png', 'photos_bands_large/sample.png',
    'photos_artists/sample.png', 'photos_artists_large/sample.png'];
  $components = array_merge($collections, $photos);
  foreach ($components as $winner) {
    foreach ($collections as $collection) {
      $path = $fixture . '/data/' . $collection . '.json';
      $fixtureFiles[$path] = $path;
      check_updated(file_put_contents($path, json_encode(['schema_version' => 1,
        'records' => ['sample' => ['modified' => $winner === $collection ? 1800000000 : 1600000000,
          'lines' => ["Sample\n"]]]], JSON_THROW_ON_ERROR)) !== false, 'Cannot write fixture collection.');
    }
    foreach ($photos as $photo) {
      $path = $fixture . '/' . $photo;
      $fixtureFiles[$path] = $path;
      check_updated(file_put_contents($path, 'photo fixture') !== false
        && touch($path, $winner === $photo ? 1800000000 : 1600000000), 'Cannot write photo fixture.');
    }
    $bandWins = in_array($winner, ['bands', 'band-credits', 'band-comments', $photos[0], $photos[1]], true);
    run_updated_test(['--fixture', $fixture, $bandWins ? '1800000000' : '1600000000',
      $bandWins ? '1600000000' : '1800000000']);
  }
  foreach ($photos as $photo) {
    check_updated(unlink($fixture . '/' . $photo), 'Cannot remove optional fixture photo.');
    unset($fixtureFiles[$fixture . '/' . $photo]);
  }
  foreach (['band-credits', 'band-comments', 'artist-credits'] as $collection) {
    check_updated(file_put_contents($fixture . '/data/' . $collection . '.json',
      '{"schema_version":1,"records":{}}') !== false, 'Cannot clear optional fixture records.');
  }
  run_updated_test(['--fixture', $fixture, '1600000000', '1600000000']);
} finally {
  foreach (array_unique($fixtureFiles) as $path) {
    check_updated(unlink($path), 'Cannot clean fixture file: ' . $path);
  }
  foreach (array_reverse($fixtureDirectories) as $path) {
    check_updated(rmdir($path), 'Cannot clean fixture directory: ' . $path);
  }
  check_updated(rmdir($fixture), 'Cannot clean fixture root.');
}

chdir($root);
require_once $root . '/functions.php';
$rowsChecked = 0;
foreach (['bands_table.php' => 'bands', 'artists_table.php' => 'artists', 'artists_table_detailed.php' => 'artists'] as $page => $collection) {
  $document = new DOMDocument();
  $document->loadHTML(run_updated_test(['--render', $page]), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
  $xpath = new DOMXPath($document);
  $rows = $xpath->query('//*[@id="filter_table"]/tbody/tr');
  check_updated($rows->length === count(content_records($collection)), 'Missing table rows: ' . $page);
  foreach ($rows as $row) {
    $link = $xpath->query('./td[3]/a', $row)->item(0);
    parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $parameters);
    $slug = $parameters['a'];
    $expected = $collection === 'bands' ? get_band_mtime($slug) : get_artist_mtime($slug);
    $date = $xpath->query('./td[last()]', $row)->item(0);
    check_updated($date->textContent === date('d-m-Y', $expected)
      && $date->getAttribute('data-sort-value') === (string) $expected, 'Incorrect table date/sort value: ' . $page . '/' . $slug);
    $rowsChecked++;
  }
}
foreach (['band.php' => 'kiss_1', 'artist.php' => 'paul_stanley'] as $page => $slug) {
  $expected = $page === 'band.php' ? get_band_mtime($slug) : get_artist_mtime($slug);
  check_updated(str_contains(run_updated_test(['--render', $page, $slug]),
    'Entry updated: ' . date('d-m-Y.', $expected)), 'Detail-page update date is incorrect: ' . $page);
}
echo "PASS: every timestamp source can set the update date, optional missing assets, $rowsChecked table dates/sort values and band/artist detail dates.\n";

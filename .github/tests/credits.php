<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
chdir($root);
require_once $root . '/content.php';

function check_credits($condition, $message)
{
  if (!$condition) {
    throw new RuntimeException($message);
  }
}

if (($argv[1] ?? '') === '--render') {
  $page = '/' . $argv[2] . '.php';
  $_GET['a'] = $argv[3];
  $_SERVER['DOCUMENT_ROOT'] = $root;
  $_SERVER['SCRIPT_FILENAME'] = $root . $page;
  $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $page;
  $_SERVER['REQUEST_URI'] = $page . '?a=' . rawurlencode($_GET['a']);
  $_SERVER['HTTP_HOST'] = 'localhost:8003';
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
  });
  require $root . $page;
  exit;
}

check_credits(!content_exists('artists_credits/rock_redline.txt'), 'Rock Redline credit still exists.');
$nautilus = content_file('bands_credits/nautilus.txt', FILE_IGNORE_NEW_LINES);
check_credits(count($nautilus) === 4 && trim($nautilus[2]) === 'Backstage Auctions, Lydia Criss',
  'Nautilus contains an extra separator instead of four positional fields.');

$count = 0;
foreach (['artist' => 'artists', 'band' => 'bands'] as $type => $directory) {
  $records = content_records($directory . '_credits');
  $slugs = array_keys($records);
  $slugs[] = $type === 'artist' ? 'alan_graf' : 'kkb';
  foreach ($slugs as $slug) {
    check_credits(content_exists("$directory/$slug.txt"), "Credit has no matching entry: $slug");
    $lines = array_map('trim', $records[$slug]['lines'] ?? []);
    $expected = array_map(function ($index) use ($lines) {
      return ($lines[$index] ?? '') !== '' ? $lines[$index] : 'n/a';
    }, [0, 1, 2]);
    $url = $lines[3] ?? '';
    if ($type === 'artist') {
      if (filter_var($expected[2], FILTER_VALIDATE_URL) !== false) {
        $url = $expected[2];
        $expected[2] = $lines[3] ?? 'n/a';
        check_credits(count($lines) === 4, "Linked artist credits must include a source name: $slug");
      } else {
        $url = '';
      }
    }
    if ($url !== '' && $url !== 'n/a') {
      check_credits($expected[2] !== 'n/a' && filter_var($expected[2], FILTER_VALIDATE_URL) === false,
        "Source must have a name rather than a raw URL: $type/$slug");
    }
    $process = proc_open([PHP_BINARY, __FILE__, '--render', $type, $slug],
      [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
    check_credits(is_resource($process), "Cannot render $type/$slug");
    $html = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    check_credits($status === 0 && $errors === '', "Rendering failed for $type/$slug: $errors");
    check_credits(preg_match('~DESCRIPTION:\s*(.*?)<br>\s*PHOTOGRAPHER:\s*(.*?)<br>\s*SOURCE:\s*(.*?)</div>~s',
      $html, $matches) === 1, "Credit block missing for $type/$slug");
    foreach ($expected as $index => $value) {
      $actual = trim(html_entity_decode(strip_tags($matches[$index + 1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
      check_credits($actual === $value, "Credit field $index shifted for $type/$slug: $actual");
    }
    $linked = preg_match('~<a href=[\'"]([^\'"]*)[\'"]>~', $matches[3], $anchor) === 1;
    check_credits($linked === ($url !== '' && $url !== 'n/a'), "Wrong source link type for $type/$slug");
    if ($linked) {
      check_credits(html_entity_decode($anchor[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') === $url,
        "Wrong source destination for $type/$slug");
    }
    if ($type === 'artist') {
      check_credits(preg_match('~<div id="bandsContainer"[^>]*>(.*?)</div>~s', $html, $band_block) === 1,
        "Band list missing for artist/$slug");
      $entries = preg_split('~(?=<h2\b)~', $band_block[1]);
      $prefix = array_shift($entries);
      if ($entries !== []) {
        check_credits(trim($prefix) === '', "Extra content before the first band for artist/$slug");
        foreach ($entries as $index => $entry) {
          preg_match('~(?:<br>\s*)+$~', $entry, $spacing);
          $is_band_entry = str_starts_with($entry, "<h2 class='small-photo-heading'>");
          $expected_breaks = $is_band_entry ? ($index === array_key_last($entries) ? 1 : 3) : 0;
          check_credits(substr_count($spacing[0] ?? '', '<br>') === $expected_breaks,
            "Wrong spacing after band $index for artist/$slug");
        }
      }
    }
    $count++;
  }
}
echo "PASS: $count credited and missing-credit pages; positional fields, named links, plain-text sources, artist band spacing and Rock Redline removal.\n";

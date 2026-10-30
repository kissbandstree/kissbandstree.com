<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
chdir($root);
require_once $root . '/functions.php';

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

foreach ([
  [[], ['A - vocals'], []],
  [['A - vocals'], ['B - vocals'], [false]],
  [['A - vocals', 'B - guitar'], ['B - bass', 'A - guitar'], [true, true]],
  [['? - vocals', 'A - guitar', 'B - bass'], ['? - vocals', 'B - guitar'], [false, false, true]],
  [['A "Quoted" & B - vocals'], ['A "Quoted" & B - bass'], [true]],
] as [$previous, $current, $expected]) {
  check_credits(getLineupArrowSlots($previous, $current) === $expected, 'Incorrect continuing-member arrow positions.');
  $arrows = render_lineup_arrows($previous, $current);
  check_credits(substr_count($arrows, 'class="arrow"') === count(array_filter($expected)),
    'Incorrect continuing-member arrow count.');
  check_credits(substr_count($arrows, 'class="arrow-slot"') === (in_array(true, $expected, true) ? count($expected) : 0),
    'Departing-member arrow positions were not preserved.');
  check_credits(in_array(true, $expected, true) || $arrows === '', 'Unexpected arrows without continuing members.');
  foreach ($expected as $index => $hasArrow) {
    if ($hasArrow) {
      $name = trim(explode(' - ', $previous[$index])[0]);
      check_credits(str_contains($arrows, 'data-artist-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"'),
        'Arrow artist identity is missing or incorrectly escaped.');
    }
  }
}

check_credits(!content_exists('artists_credits/rock_redline.txt'), 'Rock Redline credit still exists.');
$nautilus = content_file('bands_credits/nautilus.txt', FILE_IGNORE_NEW_LINES);
check_credits(count($nautilus) === 4 && trim($nautilus[2]) === 'Backstage Auctions, Lydia Criss',
  'Nautilus contains an extra separator instead of four positional fields.');

$count = 0;
foreach (['artist' => 'artists', 'band' => 'bands'] as $type => $directory) {
  $records = content_records($directory . '_credits');
  $slugs = $type === 'artist' ? array_keys(content_records('artists')) : array_keys($records);
  if ($type === 'band') {
    $slugs[] = 'kkb';
  }
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
      $document = new DOMDocument();
      $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
      $xpath = new DOMXPath($document);
      $container = $document->getElementById('bandsContainer');
      check_credits($container !== null, "Band list missing for artist/$slug");
      $entries = $xpath->query('./div[@class="artist-lineup"]', $container);
      $expectedBands = [];
      $expectedPreviousLineup = [];
      $hasMissingBands = false;
      $artistName = trim(content_file("artists/$slug.txt")[0]);
      if (content_exists("bands_list/$slug.txt")) {
        foreach (content_file("bands_list/$slug.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $bandName) {
          $bandSlug = sanitize_band_slug($bandName);
          if (!content_exists("bands/$bandSlug.txt")) {
            check_credits(str_contains($html, 'Missing file: ' . htmlspecialchars(trim($bandName))),
              "Missing band was not reported for artist/$slug");
            $hasMissingBands = true;
            $expectedPreviousLineup = [];
            continue;
          }
          $lineup = array_slice(content_file("bands/$bandSlug.txt"), 6);
          foreach ($lineup as $member) {
            if (strcasecmp(trim(explode(' - ', $member)[0]), $artistName) === 0) {
              $expectedBands[] = [$bandSlug, $lineup, $expectedPreviousLineup];
              $expectedPreviousLineup = $lineup;
              break;
            }
          }
        }
      }
      check_credits($entries->length === count($expectedBands), "Artist lineup selection changed: $slug");
      $expectedArrowGroups = 0;
      foreach ($entries as $index => $entry) {
        [$bandSlug, $lineup, $previousLineup] = $expectedBands[$index];
        $headingLink = $xpath->query('./h2/a', $entry)->item(0);
        check_credits($headingLink->getAttribute('href') === 'band.php?a=' . urlencode($bandSlug),
          "Artist lineup order changed: $slug");
        $breaks = 0;
        for ($node = $entry->lastChild; $node !== null; $node = $node->previousSibling) {
          if ($node->nodeType === XML_TEXT_NODE && trim($node->textContent) === '') continue;
          if ($node->nodeName !== 'br') break;
          $breaks++;
        }
        check_credits($breaks === 1, "Extra spacing after band $index for artist/$slug");
        $separator = $entry->previousSibling;
        while ($separator !== null && $separator->nodeType === XML_TEXT_NODE && trim($separator->textContent) === '') {
          $separator = $separator->previousSibling;
        }
        $slots = getLineupArrowSlots($previousLineup, $lineup);
        if (!in_array(true, $slots, true)) {
          check_credits(!($separator instanceof DOMElement) || $separator->getAttribute('class') !== 'arrow-outer-container',
            "Unexpected arrow before the first band or after missing data for artist/$slug");
          if ($index === 0 && !$hasMissingBands) {
            check_credits($separator === null, "Extra content before the first band for artist/$slug");
          }
        } else {
          $expectedArrowGroups++;
          check_credits($separator instanceof DOMElement && $separator->getAttribute('class') === 'arrow-outer-container',
            "Missing arrow separator for artist/$slug");
          check_credits($xpath->query('.//span[@class="arrow"]', $separator)->length === count(array_filter($slots))
            && $xpath->query('.//div[@class="arrow-slot"]', $separator)->length === count($slots),
            "Incorrect arrow positions for artist/$slug");
          foreach ($xpath->query('.//div[@class="arrow-slot"]', $separator) as $slotIndex => $slot) {
            $arrow = $xpath->query('./span[@class="arrow"]', $slot)->item(0);
            check_credits(($arrow !== null) === $slots[$slotIndex], "Incorrect arrow slot for artist/$slug");
            if ($arrow !== null) {
              $name = trim(explode(' - ', $previousLineup[$slotIndex])[0]);
              check_credits($arrow->getAttribute('data-artist-name') === $name,
                "Arrow identifies the wrong artist for artist/$slug");
            }
          }
        }
      }
      check_credits($xpath->query('./div[@class="arrow-outer-container"]', $container)->length === $expectedArrowGroups,
        "Unexpected extra arrow groups for artist/$slug");
      if ($entries->length > 0 && !$hasMissingBands) {
        $last = $entries->item($entries->length - 1)->nextSibling;
        while ($last !== null && $last->nodeType === XML_TEXT_NODE && trim($last->textContent) === '') {
          $last = $last->nextSibling;
        }
        check_credits($last === null, "Extra content after the last band for artist/$slug");
      }
    }
    $count++;
  }
}
echo "PASS: $count pages including every artist; credit fields, source links, lineup selection/order, spacing, continuing-member arrows and Rock Redline removal.\n";

<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
chdir($root);
require_once $root . '/functions.php';

function check_home($condition, $message)
{
  if (!$condition) {
    throw new RuntimeException($message);
  }
}

function render_home_test_page($root, $file)
{
  $_SERVER['DOCUMENT_ROOT'] = $root;
  $_SERVER['SCRIPT_FILENAME'] = $root . '/' . $file;
  $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'] = '/' . $file;
  $_SERVER['HTTP_HOST'] = 'localhost:8003';
  $_GET = [];
  ob_start();
  try {
    require $root . '/' . $file;
    return ob_get_contents();
  } finally {
    ob_end_clean();
  }
}

set_error_handler(function ($severity, $message, $file, $line) {
  throw new ErrorException($message, 0, $severity, $file, $line);
});

$html = render_home_test_page($root, 'index.php');
$document = new DOMDocument();
$document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
$xpath = new DOMXPath($document);
$headings = array_map(function ($heading) {
  return trim($heading->textContent);
}, iterator_to_array($xpath->query('//h2')));
check_home(array_search('LINEUPS', $headings, true) + 1 === array_search('MEMBERS', $headings, true),
  'LINEUPS must immediately precede MEMBERS.');

$expectedLineups = [
  'kiss_1', 'kiss_2', 'kiss_3', 'kiss_4', 'kiss_5', 'kiss_6',
  'kiss_1_reunion', 'kiss_7', 'kiss_8', 'kiss_9', 'kiss_8_reunion',
];
$expectedActive = [];
$allLineups = [];
foreach (content_records('bands') as $slug => $record) {
  if (preg_match('/^kiss_\d+(?:_reunion)?$/', $slug) === 1) {
    $allLineups[] = $slug;
  }
  if (is_band_active(trim($record['lines'][1]))) {
    $expectedActive[] = $slug;
  }
}
$allExpectedLineups = $expectedLineups;
sort($allLineups);
sort($allExpectedLineups);
check_home($allLineups === $allExpectedLineups, 'The lineup expectations must cover every Kiss record.');

$treeTargets = [];
foreach (['lineup-container', 'bandContainer', 'activeBandContainer'] as $containerId) {
  $container = $document->getElementById($containerId);
  check_home($container !== null, 'Missing homepage section: ' . $containerId);
  $cards = $xpath->query('./div[@class="band-item"]', $container);
  $slugs = [];
  foreach ($cards as $card) {
    $bandLink = $xpath->query('./div[2]/a', $card)->item(0);
    parse_str(parse_url($bandLink->getAttribute('href'), PHP_URL_QUERY), $parameters);
    $slug = $parameters['a'];
    $slugs[] = $slug;
    $contents = content_file('bands/' . $slug . '.txt', FILE_IGNORE_NEW_LINES);
    check_home($bandLink->textContent === trim($contents[0]), 'Incorrect lineup name: ' . $slug);
    $photo = $xpath->query('./div[1]/a/img', $card)->item(0);
    $photoPath = 'photos_bands/' . $slug . '.png';
    check_home($photo->getAttribute('src') === (content_exists($photoPath) ? $photoPath : 'img/band_small.png'),
      'Incorrect lineup photo: ' . $slug);
    $period = trim($contents[1]);
    if ($containerId === 'activeBandContainer') {
      $period = preg_replace('/\bpresent\b/i', '[present]', $period, 1);
    }
    check_home($xpath->query('./div[3]', $card)->item(0)->textContent === $period,
      'Incorrect lineup period: ' . $slug);
    $members = array_values(array_filter(array_map('trim', explode(',', $contents[5]))));
    $memberLinks = $xpath->query('./div[4]/a', $card);
    check_home($memberLinks->length === count($members), 'Missing lineup members: ' . $slug);
    foreach ($memberLinks as $index => $link) {
      check_home($link->textContent === $members[$index]
        && $link->getAttribute('href') === '/artist.php?a=' . sanitize_artist_slug($members[$index]),
        'Incorrect member link: ' . $slug);
    }
    $treeLinks = $xpath->query('./div/a[text()="SHOW ON TREE"]', $card);
    check_home($treeLinks->length === 1
      && $treeLinks->item(0)->getAttribute('href') === '/tree.php?focus=' . rawurlencode($slug),
      'Missing or incorrect SHOW ON TREE link: ' . $slug);
    $treeTargets[$slug] = true;
    $updated = $xpath->query('./div[@title="Entry updated"]', $card);
    check_home($updated->length === ($containerId === 'lineup-container' ? 0 : 1),
      'Unexpected update-date visibility: ' . $slug);
    if ($updated->length > 0) {
      check_home($updated->item(0)->textContent === date('d-m-Y', get_band_mtime($slug)),
        'Incorrect update date: ' . $slug);
    }
  }
  if ($containerId === 'lineup-container') {
    check_home($slugs === $expectedLineups, 'Kiss lineups and reunions must follow chronological Tree order.');
  } elseif ($containerId === 'bandContainer') {
    $files = content_glob('bands/*.txt', GLOB_NOSORT);
    $modified = array_map(function ($file) {
      return get_band_mtime(basename($file, '.txt'));
    }, $files);
    array_multisort($modified, SORT_DESC, $files);
    check_home($slugs === array_map(function ($file) {
      return basename($file, '.txt');
    }, array_slice($files, 0, 4)), 'Last Modified Bands selection or order changed.');
  } else {
    $activeFiles = array_map(function ($slug) {
      return 'bands/' . $slug . '.txt';
    }, $expectedActive);
    array_multisort(array_map('content_mtime', $activeFiles), SORT_DESC, $activeFiles);
    check_home($slugs === array_map(function ($file) {
      return basename($file, '.txt');
    }, $activeFiles), 'Active Bands selection or order changed.');
  }
}

$treeHtml = render_home_test_page($root, 'tree.php');
foreach (array_keys($treeTargets) as $slug) {
  check_home(str_contains($treeHtml, 'id="band-' . $slug . '"')
    || str_contains($treeHtml, "id='band-" . $slug . "'"),
    'SHOW ON TREE target does not exist: ' . $slug);
}
restore_error_handler();
echo 'PASS: homepage section order, all 11 Kiss lineups, card fields, unchanged existing selections and '
  . count($treeTargets) . " working Tree targets.\n";

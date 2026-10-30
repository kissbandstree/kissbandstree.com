<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

function check_preview($condition, $message)
{
  if (!$condition) {
    throw new RuntimeException($message);
  }
}

function render_preview_head($root, $page, $host = 'localhost:8003', $thumb = null, $pagetitle = null)
{
  $_SERVER['DOCUMENT_ROOT'] = $root;
  $_SERVER['SCRIPT_NAME'] = '/' . $page;
  $_SERVER['REQUEST_URI'] = '/' . $page . '?test=preserved';
  $_SERVER['HTTP_HOST'] = $host;
  ob_start();
  try {
    require $root . '/head.php';
    $html = ob_get_contents();
  } finally {
    ob_end_clean();
  }
  $document = new DOMDocument();
  $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
  $meta = [];
  foreach ($document->getElementsByTagName('meta') as $element) {
    $meta[$element->getAttribute('property')] = $element->getAttribute('content');
  }
  return $meta;
}

set_error_handler(function ($severity, $message, $file, $line) {
  throw new ErrorException($message, 0, $severity, $file, $line);
});

$sections = [
  'index' => ['index.php'],
  'bands' => ['bands_grid.php', 'bands_table.php', 'bands_list.php', 'bands_details.php', 'bands_no_details.php'],
  'artists' => ['artists_grid.php', 'artists_table.php', 'artists_list.php', 'artists_table_detailed.php'],
  'releases_band' => ['releases_band_grid.php', 'releases_band_table.php', 'releases_band_list.php'],
  'releases_related' => ['releases_related_grid.php', 'releases_related_table.php', 'releases_related_list.php'],
  'calendar' => ['calendar.php'],
  'about' => ['about.php'],
  'thanks' => ['thanks.php'],
  'stats' => ['stats.php'],
  'tree' => ['tree.php'],
];
$pageCount = 0;
foreach ($sections as $slug => $pages) {
  foreach (['png' => [512, 512], 'png/og' => [1200, 630]] as $folder => $dimensions) {
    $path = $root . '/' . $folder . '/' . $slug . '.png';
    check_preview(is_file($path), 'Missing preview: ' . $path);
    $size = getimagesize($path);
    check_preview($size !== false && $size[0] === $dimensions[0] && $size[1] === $dimensions[1]
      && $size[2] === IMAGETYPE_PNG, 'Incorrect PNG dimensions or format: ' . $path);
  }
  foreach ($pages as $page) {
    foreach (['localhost:8003' => 'http://localhost:8003', 'kissbandstree.com' => 'https://kissbandstree.com'] as $host => $origin) {
      $meta = render_preview_head($root, $page, $host);
      check_preview($meta['og:image'] === $origin . '/png/og/' . $slug . '.png', 'Wrong section preview: ' . $page);
      check_preview($meta['og:image:type'] === 'image/png' && $meta['og:image:width'] === '1200'
        && $meta['og:image:height'] === '630', 'Missing preview dimensions: ' . $page);
      $publicSlug = ['index' => '', 'releases_band' => 'releases', 'releases_related' => 'related'][$slug] ?? $slug;
      $isRouted = !in_array($page, ['bands_details.php', 'bands_no_details.php', 'artists_table_detailed.php'], true);
      $expectedPath = $isRouted ? '/' . $publicSlug : '/' . $page;
      $viewQuery = preg_match('/_(table|list)\.php$/', $page, $view) === 1 ? '&view=' . $view[1] : '';
      check_preview($meta['og:url'] === $origin . $expectedPath . '?test=preserved' . $viewQuery, 'Wrong clean sharing URL: ' . $page);
    }
    $pageCount++;
  }
}

foreach (['', 'search.php', 'timeline.php'] as $page) {
  $meta = render_preview_head($root, $page);
  check_preview($meta['og:image'] === 'http://localhost:8003/png/og/index.png', 'Missing site fallback: ' . $page);
}
foreach ([
  ['band.php', 'https://kissbandstree.com/photos_bands_large/kiss_1.png'],
  ['artist.php', 'photos_artists_large/paul_stanley.png'],
  ['release_band.php', '/photos_releases/band/kiss.png'],
  ['release_related.php', 'https://example.com/album.jpg'],
] as $example) {
  list($page, $thumb) = $example;
  $meta = render_preview_head($root, $page, 'localhost:8003', $thumb, 'Detail & title');
  $expected = strpos($thumb, 'https://') === 0 ? $thumb : 'http://localhost:8003/' . ltrim($thumb, '/');
  check_preview($meta['og:image'] === $expected, 'Detail image replaced: ' . $page);
  check_preview($meta['og:title'] === 'Detail & title', 'Detail title replaced: ' . $page);
  check_preview(!isset($meta['og:image:width']), 'Generic dimensions applied to a detail photo: ' . $page);
}

echo 'Passed: 20 PNG assets, ' . $pageCount . " section pages, local/production URLs and detail-image precedence.\n";

<?php
require_once __DIR__ . '/content.php';

// Shared helper functions

function sanitize_band_slug($band_name)
{
  $slug = preg_replace('/\s*\[.*?\]|\s*\/.*?$/', '', $band_name); // Remove brackets and slashes
  $slug = str_replace(['#', '?', "'", '-', '.', '&'], ['', '', '', '_', '', 'and'], $slug); // Remove symbols and replace & with 'and'
  $slug = strtolower(trim($slug)); // Lowercase
  $slug = preg_replace('/\s+/', '_', $slug); // Convert spaces to underscores
  $slug = preg_replace('/_+/', '_', $slug);  // Collapse repeats
  return rtrim($slug, '_'); // Remove trailing underscores
}

function sanitize_artist_slug($artist_name)
{
  $slug = strtolower(trim($artist_name));
  $slug = str_replace([" ", ".", "'", "-", "[", "]"], ["_", "", "", "_", "", ""], $slug);
  return preg_replace('/_+/', '_', $slug);
}

function render_kiss_member_links($members_line)
{
  $members = array_filter(array_map('trim', explode(',', $members_line)), function ($member) {
    return $member !== '';
  });

  $links = [];
  foreach ($members as $member) {
    $slug = sanitize_artist_slug($member);
    $links[] = '<a href="/artist.php?a=' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($member, ENT_QUOTES, 'UTF-8') . '</a>';
  }

  return implode(', ', $links);
}

function render_band_card(string $file, ?int $modified = null, ?string $period = null): string
{
  $contents = content_file($file, FILE_IGNORE_NEW_LINES);
  $slug = basename($file, '.txt');
  $name = htmlspecialchars(trim($contents[0]), ENT_QUOTES, 'UTF-8');
  $bandUrl = '/band.php?a=' . rawurlencode($slug);
  $photo = 'photos_bands/' . $slug . '.png';
  if (!content_exists($photo)) {
    $photo = 'img/band_small.png';
  }

  $html = "<div class='band-item'>";
  $html .= '<div><a href="' . $bandUrl . '"><img src="' . $photo . '" alt="' . $name . '" class="bitmap"></a></div>';
  $html .= '<div><a href="' . $bandUrl . '">' . $name . '</a></div>';
  $html .= '<div>' . htmlspecialchars($period ?? trim($contents[1]), ENT_QUOTES, 'UTF-8') . '</div>';
  $html .= '<div>' . render_kiss_member_links($contents[5]) . '</div>';
  if ($modified !== null) {
    $html .= '<div title="Entry updated">' . date('d-m-Y', $modified) . '</div>';
  }
  $html .= '<div><a href="/tree.php?focus=' . rawurlencode($slug) . '">SHOW ON TREE</a></div>';
  return $html . '</div>';
}

function getLineupArrowSlots($previousLineup, $currentLineup)
{
  $currentArtists = array_map(function ($member) {
    return trim(explode(' - ', $member)[0]);
  }, $currentLineup);

  return array_map(function ($member) use ($currentArtists) {
    $previousArtist = trim(explode(' - ', $member)[0]);
    return $previousArtist !== '?' && in_array($previousArtist, $currentArtists, true);
  }, $previousLineup);
}

function render_lineup_arrows(array $previousLineup, array $currentLineup): string
{
  $arrowSlots = getLineupArrowSlots($previousLineup, $currentLineup);
  if (!in_array(true, $arrowSlots, true)) {
    return '';
  }

  $html = '<div class="arrow-outer-container" aria-hidden="true"><div class="arrow-container">';
  foreach ($arrowSlots as $index => $hasArrow) {
    $arrow = '&nbsp;';
    if ($hasArrow) {
      $name = trim(explode(' - ', $previousLineup[$index])[0]);
      $arrow = '<span class="arrow" data-artist-name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">&darr;</span>';
    }
    $html .= '<div class="arrow-slot">' . $arrow . '</div>';
  }
  return $html . '</div></div>';
}

function is_band_active(string $dates): bool
{
  return preg_match('/\bpresent\b/i', $dates) === 1;
}

function get_band_mtime($slug)
{
  $candidates = [
    'bands/' . $slug . '.txt',
    'photos_bands/' . $slug . '.png',
    'photos_bands_large/' . $slug . '.png',
    'bands_credits/' . $slug . '.txt',
    'bands_comments/' . $slug . '.txt',
  ];
  $mtime = 0;
  foreach ($candidates as $path) {
    if (content_exists($path)) {
      $mtime = max($mtime, content_mtime($path));
    }
  }
  return $mtime;
}

function get_artist_mtime($slug)
{
  $candidates = [
    'artists/' . $slug . '.txt',
    'photos_artists/' . $slug . '.png',
    'photos_artists_large/' . $slug . '.png',
    'artists_credits/' . $slug . '.txt',
  ];
  $mtime = 0;
  foreach ($candidates as $path) {
    if (content_exists($path)) {
      $mtime = max($mtime, content_mtime($path));
    }
  }
  return $mtime;
}

function render_numbered_tracklist($tracklistFile)
{
  if (!content_exists($tracklistFile) || content_size($tracklistFile) === 0) {
    return 'No tracklist available.<br><br>';
  }

  $lines = content_file($tracklistFile, FILE_IGNORE_NEW_LINES);
  $tracks = [];

  foreach ($lines as $line) {
    $line = trim($line);
    if ($line !== '') {
      $tracks[] = $line;
    }
  }

  if (count($tracks) === 0) {
    return 'No tracklist available.<br><br>';
  }

  $output = [];
  foreach ($tracks as $index => $track) {
    $output[] = ($index + 1) . '. ' . htmlspecialchars($track, ENT_QUOTES, 'UTF-8');
  }

  return implode('<br>', $output) . '<br><br>';
}

function release_date_sort_value($dateText)
{
  $dateText = trim($dateText);
  if (!preg_match('/^(\d{2}|XX)-(\d{2}|XX)-(\d{4}|XXXX)$/i', $dateText, $matches)) {
    return PHP_INT_MAX;
  }

  if (strtoupper($matches[3]) === 'XXXX') {
    return PHP_INT_MAX;
  }

  $day = strtoupper($matches[1]) === 'XX' ? 1 : (int) $matches[1];
  $month = strtoupper($matches[2]) === 'XX' ? 1 : (int) $matches[2];
  $year = (int) $matches[3];

  if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
    return PHP_INT_MAX;
  }

  return ($year * 10000) + ($month * 100) + $day;
}

function compare_release_files_by_date($a, $b)
{
  $aLines = content_file($a, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  $bLines = content_file($b, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  $aSort = release_date_sort_value($aLines[2] ?? '');
  $bSort = release_date_sort_value($bLines[2] ?? '');

  if ($aSort === $bSort) {
    return strcasecmp($a, $b);
  }

  return $aSort <=> $bSort;
}

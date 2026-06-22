<?php

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
    if (file_exists($path)) {
      $mtime = max($mtime, filemtime($path));
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
    if (file_exists($path)) {
      $mtime = max($mtime, filemtime($path));
    }
  }
  return $mtime;
}

function render_numbered_tracklist($tracklistFile)
{
  if (!file_exists($tracklistFile) || filesize($tracklistFile) === 0) {
    return 'No tracklist available.<br><br>';
  }

  $lines = file($tracklistFile, FILE_IGNORE_NEW_LINES);
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


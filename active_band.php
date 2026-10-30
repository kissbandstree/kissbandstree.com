<?php
require_once __DIR__ . '/content.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/functions.php');
// Shows all band lineups where line 2 ($contents[1]) contains the word "present".

$bands = content_glob('bands/*.txt', GLOB_NOSORT);

$activeBands = [];

foreach ($bands as $file) {
  $contents = @content_file($file, FILE_IGNORE_NEW_LINES);
  if (!$contents || !isset($contents[1])) continue;

  $line2 = trim($contents[1]);

  // Only match the word "present" (case-insensitive)
  if (!is_band_active($line2)) continue;

  $activeBands[] = $file;
}

// Sort by file modification time (DESC)
array_multisort(array_map('content_mtime', $activeBands), SORT_DESC, $activeBands);
?>

<div class="top-line">
  <svg class="icons" aria-hidden="true">
    <title>Bands</title>
    <use href="icons.svg#band"></use>
  </svg>
  <h2>
    ACTIVE BANDS
  </h2>
  <svg class="icons show-pointer no-margin"
    onclick="toggleDiv('activeBandContainer')"
    tabindex="0" aria-label="Toggle active lineups">
    <title>Collapse</title>
    <use href="icons.svg#triangle_down" />
  </svg>
</div>

<div class="last-container colored" id="activeBandContainer">
  <?php
  foreach ($activeBands as $file) {
    $contents = content_file($file, FILE_IGNORE_NEW_LINES);
    $basename = basename($file, ".txt");

    $yearLine = trim($contents[1] ?? '');
    $yearLineUi = preg_replace('/\bpresent\b/i', '[present]', $yearLine, 1);

    echo render_band_card($file, get_band_mtime($basename), $yearLineUi);
  }
  ?>
</div>
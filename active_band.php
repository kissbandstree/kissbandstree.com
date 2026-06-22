<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/functions.php');
// Shows all band lineups where line 2 ($contents[1]) contains the word "present".

$bands = glob('bands/*.txt', GLOB_NOSORT);

$activeBands = [];

foreach ($bands as $file) {
  $contents = @file($file, FILE_IGNORE_NEW_LINES);
  if (!$contents || !isset($contents[1])) continue;

  $line2 = trim($contents[1]);

  // Only match the word "present" (case-insensitive)
  if (!preg_match('/\bpresent\b/i', $line2)) continue;

  $activeBands[] = $file;
}

// Sort by file modification time (DESC)
array_multisort(array_map('filemtime', $activeBands), SORT_DESC, $activeBands);
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
    $contents = file($file, FILE_IGNORE_NEW_LINES);
    $basename = basename($file, ".txt");

    $yearLine = trim($contents[1] ?? '');
    $yearLineUi = preg_replace('/\bpresent\b/i', '[present]', $yearLine, 1);

    // Members
    $list = explode(',', $contents[5] ?? '');
    $list = array_map('trim', $list);

    $links = [];
    foreach ($list as $name) {
      if ($name === '') continue;

      $slug = strtolower(str_replace(
        [" ", ".", "'", "-", "[", "]"],
        ["_", "", "", "_", "", ""],
        $name
      ));

      $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
      $links[] = "<a href='/artist.php?a=$slug'>$safeName</a>";
    }

    echo "<div class='band-item'>";

    if (file_exists('photos_bands/' . $basename . '.png')) {
      echo "<div><a href='/band.php?a=$basename'><img src='photos_bands/$basename.png' alt='band' class='bitmap'></a></div>";
    } else {
      echo "<div><a href='/band.php?a=$basename'><img src='img/band_small.png' alt='band' class='bitmap'></a></div>";
    }

    echo "<div><a href='/band.php?a=$basename'>" . htmlspecialchars(trim($contents[0] ?? ''), ENT_QUOTES, 'UTF-8') . "</a></div>"; // band
    echo "<div>" . htmlspecialchars($yearLineUi, ENT_QUOTES, 'UTF-8') . "</div>"; // year + [present]
    echo "<div>" . implode(', ', $links) . "</div>"; // members
    echo "<div title='Entry updated'>" . date('d-m-Y', get_band_mtime($basename)) . "</div>"; // modified date

    echo "</div>";
  }
  ?>
</div>
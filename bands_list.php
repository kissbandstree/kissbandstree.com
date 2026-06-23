<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/functions.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_bands.php"); ?>

  <div class="standard colored">
    <?php
    $bandFiles = glob('bands/*.txt');
    sort($bandFiles, SORT_NATURAL | SORT_FLAG_CASE);
    echo "
        <span id='totalEntries'>Entries: " . count($bandFiles) . ".&nbsp;</span>
        <span>Showing: <span id='visibleEntries'></span>.&nbsp;</span>
        <span>Sorting is alphabetical.</span>
    ";
    ?>
  </div>

  <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/filter.php"); render_member_filter(); ?>

  <div class="standard">
    <?php
    foreach ($bandFiles as $band_file) {
      $contents = file($band_file, FILE_IGNORE_NEW_LINES); // keep blanks to keep positions
      if (!$contents) continue;

      $band_name = trim($contents[0] ?? '');
      $year_line = trim($contents[1] ?? '');
      $band_slug = basename($band_file, '.txt');

      $members = [];
      for ($i = 4; $i < count($contents); $i++) {
        if (strpos($contents[$i], ' - ') === false) continue;
        list($name,) = array_map('trim', explode(' - ', $contents[$i], 2));
        if ($name !== '') {
          $members[] = $name;
        }
      }
      $members_attr = htmlspecialchars(implode(', ', array_values(array_unique($members))), ENT_QUOTES, 'UTF-8');

      // Photo (correct folder)
      $imgUrl = "photos_bands/$band_slug.png";
      if (!file_exists($imgUrl)) {
        $imgUrl = "img/band.png";
      }

      echo "<div class='band-list-item' data-members='$members_attr'>";
      echo "<h2 class='small-photo-heading'>";
      echo "<a href='band.php?a=$band_slug' class='thumb-link'>";
      echo "<span class='band-photo-wrap'><img src='$imgUrl' alt='" . htmlspecialchars($band_name) . "' class='band-photo bitmap'></span>";
      echo "</a>";
      echo "<a href='band.php?a=$band_slug' class='artist-name'>" . htmlspecialchars($band_name) . "</a>";
      if ($year_line !== '') {
        // Safe text, then insert break opportunities after common separators
        $safe_years = htmlspecialchars($year_line, ENT_QUOTES, 'UTF-8');
        $safe_years = preg_replace('/([–—\-\/;,])\s*/u', '$1<wbr> ', $safe_years);
        echo " <span class=\"year-brackets\">[" . $safe_years . "]</span>";
      }
      echo "</h2>";

      // Find the first actual lineup line (first line that contains " - ")
      $start = null;
      for ($i = 4; $i < count($contents); $i++) {
        if (strpos($contents[$i], ' - ') !== false) {
          $start = $i;
          break;
        }
      }

      if ($start !== null) {
        echo "<div class='band-lineup'>";
        for ($j = $start; $j < count($contents); $j++) {
          $line = trim($contents[$j]);
          if ($line === '' || strpos($line, ' - ') === false) continue;

          list($name, $instrument) = array_map('trim', explode(' - ', $line, 2));
          $artist_slug = sanitize_artist_slug($name);
          $artist_path = "artists/$artist_slug.txt";

          if (file_exists($artist_path)) {
            echo "<a href='artist.php?a=$artist_slug' class='artist-name'>" . htmlspecialchars($name) . "</a>";
          } else {
            echo htmlspecialchars($name);
          }
          echo " - " . htmlspecialchars($instrument) . "<br>";
        }
        echo "</div>";
      }
      echo "</div>";
    }
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
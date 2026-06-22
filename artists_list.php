<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/functions.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>

  <style>
    h2 {
      margin-bottom: 0.3em;
    }
  </style>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_artists.php"); ?>

  <div class="standard colored">
    <?php
    $artistFiles = glob('artists/*.txt'); // Get text files from folder
    ?>
    <?php echo
    "
    <span id='totalEntries'>Entries: " . count($artistFiles) . ".&nbsp;</span>
    <span>Showing: <span id='visibleEntries'></span>.&nbsp;</span>
    <span>Sorting is alphabetical.</span>
    ";
    ?>
  </div>

  <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/filter.php"); render_member_filter(); ?>

  <div class="standard">
    <?php
    $artists = glob('artists/*.txt');
    sort($artists, SORT_NATURAL | SORT_FLAG_CASE);

    foreach ($artists as $artist_file) {
      $contents = file($artist_file, FILE_IGNORE_NEW_LINES);
      $artist_name = trim($contents[0]);
      $artist_id = preg_replace('/[^a-z0-9]/i', '_', strtolower($artist_name));
      $artist_url = 'artist.php?a=' . urlencode(basename($artist_file, '.txt'));

      // Photo path (fallback if missing)
      $imgUrl = 'photos_artists/' . $artist_id . '.png';
      if (!file_exists($imgUrl)) {
        $imgUrl = 'img/artist_small.png';
      }

      $artist_slug = basename($artist_file, '.txt');
      $collab_members = get_artist_collaborator_members($artist_slug);
      $members_attr = htmlspecialchars(implode(', ', $collab_members), ENT_QUOTES, 'UTF-8');

      echo "<div class='artist-list-item' data-members='$members_attr'>";

      echo "<h2>";
      echo "<span class='artist-photo-wrap rounded'><a href='$artist_url'><img src='$imgUrl' alt='" . htmlspecialchars($artist_name) . "' class='artist-photo bitmap rounded'></a></span>";
      echo "<a href='$artist_url'>" . htmlspecialchars($artist_name) . "</a>";
      echo "</h2><br>";
      echo "<div id='bands_$artist_id'>";

      // Chronological bands list
      $bands_list_file = 'bands_list/' . basename($artist_file);
      if (file_exists($bands_list_file)) {
        $bands = array_filter(array_map('trim', file($bands_list_file)));

        foreach ($bands as $band_name) {
          $slug = sanitize_band_slug($band_name);
          $band_file = 'bands/' . $slug . '.txt';

          if (!file_exists($band_file)) {
            echo "<div>Missing file: " . htmlspecialchars($band_name) . "</div>";
            continue;
          }

          $band_contents = file($band_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
          $band_display_name = trim($band_contents[0]);
          $year_line = trim($band_contents[1]);

          echo "<div class='band-entry'><a href='band.php?a=" . urlencode($slug) . "' class='band-name'>" . htmlspecialchars($band_display_name) . "</a>"
            . "<span> [" . htmlspecialchars($year_line) . "]</span></div>";
        }
      } else {
        echo "No band list found!";
      }

      echo "</div><br>";
      echo "</div>";
    }
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
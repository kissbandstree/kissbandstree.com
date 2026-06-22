<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
  <link rel="stylesheet" href="tree.css">
</head>

<body>

  <?php
  require_once($_SERVER['DOCUMENT_ROOT'] . "/functions.php");
  $bandFiles = glob('bands/*.txt');
  $artists = glob('artists/*.txt');
  ?>

  <div class="sidebar">

    <div class="full">
      <a href="/" class="logo-link">
        <?php echo file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/img/logo.svg'); ?>
      </a>
    </div>

    <div class="single-line-justify">
      <span>Version: <?= date("d-m-Y", getlastmod()); ?>.</span>
      <span>Lineups: <?= count($bandFiles); ?>.</span>
      <span>Artists: <?= count($artists); ?>.</span>
    </div>

    <div class="center">
      <svg class="icons show-pointer" onclick="toggleMode()" tabindex="0" aria-label="Toggle dark mode">
        <title>Toggle dark mode</title>
        <use href="icons.svg#moon"></use>
      </svg>
    </div>

    <div class="text">
      <?php echo nl2br(file_get_contents("about_poster.txt")); ?>
    </div>

    <div class="center">
      <a href="/">
        <img src="img/icon.svg" alt="icon" class="icons">
      </a>
    </div>

    <div class="text">
      <?php echo nl2br(file_get_contents("intro.txt")); ?>
    </div>

    <div class="center">
      <a href="/">
        <img src="img/icon.svg" alt="icon" class="icons">
      </a>
    </div>

    <div class="text">
      <?php
      function generateSimpleBandLinks($file_path)
      {
        $bands_array = array_filter(array_map('trim', file($file_path)));
        $links = [];

        foreach ($bands_array as $band_name) {
          $slug = sanitize_band_slug($band_name);
          $band_url = 'band.php?a=' . urlencode($slug);
          $links[] = "<a href=\"$band_url\">" . htmlspecialchars($band_name) . "</a>";
        }

        if (count($links) > 1) {
          $last = array_pop($links);
          return implode(', ', $links) . ' and ' . $last;
        }
        return implode('', $links);
      }

      $order = [
        'Gene Simmons',
        'Paul Stanley',
        'Peter Criss',
        'Ace Frehley',
        'Eric Carr',
        'Vinnie Vincent',
        'Mark St. John',
        'Bruce Kulick',
        'Eric Singer',
        'Tommy Thayer'
      ];

      $artists = [];
      foreach ($order as $name) {
        $slug = sanitize_artist_slug($name);
        $artists[$name] = "bands_list/{$slug}.txt";
      }

      foreach ($artists as $artist_name => $file_path) {
        $artist_url = 'artist.php?a=' . urlencode(sanitize_artist_slug($artist_name));
        echo "<p><strong><a href=\"$artist_url\">" . htmlspecialchars($artist_name) . "</a></strong> played in: ";
        echo generateSimpleBandLinks($file_path);
        echo ". </p>";
      }

      function listAllArtists()
      {
        $artist_files = glob('artists/*.txt');
        $artists_info = [];

        foreach ($artist_files as $file_path) {
          $lines = file($file_path, FILE_IGNORE_NEW_LINES);
          if (count($lines) >= 2) {
            $name = trim($lines[0]);
            $instruments = trim($lines[1]);
            $artists_info[$name] = $instruments;
          }
        }

        ksort($artists_info, SORT_NATURAL | SORT_FLAG_CASE);

        $output = [];
        foreach ($artists_info as $name => $instruments) {
          $slug = sanitize_artist_slug($name);
          $link = '<a href="artist.php?a=' . urlencode($slug) . '">' . htmlspecialchars($name) . '</a> (' . htmlspecialchars($instruments) . ')';
          $output[] = $link;
        }

        echo implode(', ', $output) . '.';
      }
      ?>
    </div>

    <div class="center">
      <a href="/">
        <img src="img/icon.svg" alt="icon" class="icons">
      </a>
    </div>

    <div class="text">
      <?php
      $releaseFiles = glob('releases/band/*.txt');

      // Sort chronologically
      usort($releaseFiles, function ($a, $b) {
        $aLines = file($a, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $bLines = file($b, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $aDate = isset($aLines[2]) ? DateTime::createFromFormat('d-m-Y', trim($aLines[2])) : null;
        $bDate = isset($bLines[2]) ? DateTime::createFromFormat('d-m-Y', trim($bLines[2])) : null;
        return $aDate <=> $bDate;
      });

      $releaseText = [];
      foreach ($releaseFiles as $txtFile) {
        $lines  = file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $artist = $lines[0] ?? '';
        $title  = $lines[1] ?? '';
        $date   = $lines[2] ?? '';
        $releaseText[] = htmlspecialchars($artist) . ' - "' . htmlspecialchars($title) . '" [' . htmlspecialchars($date) . ']';
      }

      echo 'Select Kiss discography: ' . implode(', ', $releaseText) . '.';
      ?>
    </div>

    <div class="center">
      <a href="/">
        <img src="img/icon.svg" alt="icon" class="icons">
      </a>
    </div>

    <br>

    <div class="grid-container-releases" id="grid-container-releases">
      <?php
      foreach ($releaseFiles as $txtFile) {
        $slug   = basename($txtFile, '.txt');
        $lines  = file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $artist = $lines[0] ?? '';
        $title  = $lines[1] ?? '';
        $date   = $lines[2] ?? '';
        $tooltip = htmlspecialchars("$artist\n$title\n$date");

        $imgUrl = "photos_releases/band/$slug.png";
        if (!file_exists($imgUrl)) $imgUrl = 'img/album.png';

        echo '<div class="grid-item">';
        echo '<a href="release_band.php?a=' . urlencode($slug) . '">';
        echo '<img src="' . $imgUrl . '" alt="' . htmlspecialchars($title) . '" title="' . $tooltip . '" class="bitmap">';
        echo '</a>';
        echo '</div>';
      }
      ?>
    </div>

    <div class="center">
      <a href="/">
        <img src="img/icon.svg" alt="icon" class="icons">
      </a>
    </div>

    <div class="text">
      <?php
      $relatedFiles = glob('releases/related/*.txt');

      // Sort chronologically
      usort($relatedFiles, function ($a, $b) {
        $aLines = file($a, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $bLines = file($b, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $aDate = isset($aLines[2]) ? DateTime::createFromFormat('d-m-Y', trim($aLines[2])) : null;
        $bDate = isset($bLines[2]) ? DateTime::createFromFormat('d-m-Y', trim($bLines[2])) : null;
        return $aDate <=> $bDate;
      });

      $relatedText = [];
      foreach ($relatedFiles as $txtFile) {
        $lines  = file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $artist = $lines[0] ?? '';
        $title  = $lines[1] ?? '';
        $date   = $lines[2] ?? '';
        $relatedText[] = htmlspecialchars($artist) . ' - "' . htmlspecialchars($title) . '" [' . htmlspecialchars($date) . ']';
      }

      echo 'Select related discography: ' . implode(', ', $relatedText) . '.';
      ?>
    </div>

    <div class="center">
      <a href="/">
        <img src="img/icon.svg" alt="icon" class="icons">
      </a>
    </div>

    <br>

    <div class="grid-container-releases" id="grid-container-solo">
      <?php
      foreach ($relatedFiles as $txtFile) {
        $fileName = basename($txtFile);
        $slug   = basename($txtFile, '.txt');
        $lines  = file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $artist = $lines[0] ?? '';
        $title  = $lines[1] ?? '';
        $date   = $lines[2] ?? '';
        $tooltip = htmlspecialchars("$artist\n$title\n$date");

        $imgUrl = "photos_releases/related/$slug.png";
        if (!file_exists($imgUrl)) {
          $imgUrl = "photos_releases/related/$slug.webp";
        }
        if (!file_exists($imgUrl)) {
          $imgUrl = "photos_releases/related/$slug.jpg";
        }
        if (!file_exists($imgUrl)) {
          $imgUrl = "photos_releases/related/$slug.jpeg";
        }
        if (!file_exists($imgUrl)) {
          $imgUrl = 'img/album.png';
        }

        echo '<div class="grid-item">';
        echo '<a href="release_related.php?a=' . urlencode($fileName) . '">';
        echo '<img src="' . $imgUrl . '" alt="' . htmlspecialchars($title) . '" title="' . $tooltip . '" class="bitmap">';
        echo '</a>';
        echo '</div>';
      }
      ?>
    </div>

    <div class="center">
      <a href="/">
        <img src="img/icon.svg" alt="icon" class="icons">
      </a>
    </div>

    <div class="text">
      <?php echo (file_get_contents("thanks.txt")); ?>
    </div>

    <div class="center">
      <a href="/">
        <img src="img/icon.svg" alt="icon" class="icons">
      </a>
    </div>

    <div class="footer">
      <?php echo nl2br(file_get_contents("footer.txt")); ?><br><br>
      <a href="https://focusstudios.no" class="focus" aria-label="Focus Studios">
        <?php echo file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/img/focus_studios.svg'); ?>
      </a>
    </div>
  </div>

  <div class="poster-container">
    <?php
    function generateBandLink($filePath, &$previousLineup, &$firstBand)
    {
      $bandsList = file_get_contents($filePath);
      $bandsArray = explode("\n", $bandsList);

      foreach ($bandsArray as $bandName) {
        $bandName = trim($bandName);
        if ($bandName === '') continue;

        $slug = sanitize_band_slug($bandName);
        $bandUrl = 'band.php?a=' . urlencode($slug);
        $bandFilePath = 'bands/' . $slug . '.txt';

        if (!file_exists($bandFilePath)) {
          echo '<i>No lineup information available.</i>';
          continue;
        }

        $contents = file($bandFilePath, FILE_IGNORE_NEW_LINES);
        $year = htmlspecialchars(trim($contents[1] ?? ''), ENT_QUOTES, 'UTF-8');
        $photoFilePath = 'photos_bands/' . $slug . '.png';
        $lineup = array_slice($contents, 6);

        // Arrows (keep previous lineup positions with blanks where artists stop)
        if (!$firstBand) {
          $arrowSlots = getLineupArrowSlots($previousLineup, $lineup);
          if (in_array(true, $arrowSlots, true)) {
            echo '<div class="arrow-outer-container"><div class="arrow-container">';
            foreach ($arrowSlots as $hasArrow) {
              echo '<div class="arrow-slot">' . ($hasArrow ? '<span class="arrow">&darr;</span>' : '&nbsp;') . '</div>';
            }
            echo '</div></div>';
          }
        } else {
          $firstBand = false;
        }

        echo '<div class="band-box" id="band-' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '">';

        // Left column: photo + text box
        echo '<div class="band-left">';
        if (file_exists($photoFilePath)) {
          echo '<div class="band-photo"><a href="' . $bandUrl . '"><img src="' . $photoFilePath . '" alt="' . htmlspecialchars($bandName) . '" title="' . htmlspecialchars($bandName) . '" class="bitmap"></a></div>';
        } else {
          echo '<div class="band-photo"><a href="' . $bandUrl . '"><img src="img/band.png" alt="' . htmlspecialchars($bandName) . '" title="' . htmlspecialchars($bandName) . '" class="bitmap"></a></div>';
        }
        echo '<div class="band-text-box">&nbsp;</div>';
        echo '</div>';

        // Middle column: header + lineup
        echo '<div class="band-right">';
        echo '<div class="band-header"><a href="' . $bandUrl . '">' . htmlspecialchars($bandName) . '</a><br><span class="band-year">[' . $year . ']</span></div>';
        echo '<div class="band-lineup">';
        foreach ($lineup as $member) {
          $parts = explode(' - ', $member, 2);
          $name = trim($parts[0] ?? '');
          $instrument = trim($parts[1] ?? '');
          $memberNameLink = sanitize_artist_slug($name);

          if ($name !== '?') {
            if ($name !== '' && file_exists('artists/' . $memberNameLink . '.txt')) {
              $artistLink = "<a href=\"/artist.php?a={$memberNameLink}\" class=\"artist-name\">" . htmlspecialchars($name) . "</a>";
            } else {
              $artistLink = htmlspecialchars($name);
            }
            echo "<div class='lineup-item'>{$artistLink} - " . htmlspecialchars($instrument) . "</div>";
          } else {
            echo "<div class='lineup-item'>? - " . htmlspecialchars($instrument) . "</div>";
          }
        }
        echo '</div>'; // .band-lineup
        echo '</div>'; // .band-right

        // Right column: comments (matches full band-box height as a sibling column)
        $comment_file = "bands_comments/{$slug}.txt";
        $comment_html = '';
        if (file_exists($comment_file)) {
          $raw = trim(file_get_contents($comment_file));
          if ($raw !== '') $comment_html = nl2br(htmlspecialchars($raw));
        }
        echo '<div class="band-comment">' . ($comment_html !== '' ? $comment_html : '&nbsp;') . '</div>';

        echo '</div>'; // .band-box

        // Prepare for next iteration
        $previousLineup = array_map('trim', $lineup);
      }
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

    $previousLineup = [];
    $firstBand = true;

    foreach ($artists as $artistName => $filePath) {
      $artistPhotoPath = 'photos_artists_large/' . sanitize_artist_slug($artistName) . '.png';
      echo '<div class="artist">';
      echo '<div class="artist-header">';
      $artistUrl = 'artist.php?a=' . urlencode(sanitize_artist_slug($artistName));
      echo '<div class="artist-header-name"><h1><a href="' . $artistUrl . '">' . htmlspecialchars($artistName) . '</a></h1></div>';
      if (file_exists($artistPhotoPath)) {
        echo '<div class="artist-header-photo"><img src="' . $artistPhotoPath . '" alt="' . htmlspecialchars($artistName) . '" class="bitmap"></div>';
      } else {
        echo '<div class="artist-header-photo"><img src="img/artist_small.png" alt="' . htmlspecialchars($artistName) . '"></div>';
      }
      echo '</div>';
      generateBandLink($filePath, $previousLineup, $firstBand);
      echo '</div>';
    }
    ?>
  </div>

  <div class="sidebar">

    <div class="justify">
      <?php echo "Artists: ";
      listAllArtists(); ?>
    </div>

    <div class="center">
      <a href="/"><img src="img/icon.svg" alt="icon" class="icons"></a>
    </div>

    <br>

    <div class="grid-container-artist" id="grid-container">
      <?php
      $artistFiles = glob('artists/*.txt');

      foreach ($artistFiles as $txtFile) {
        $artistName = basename($txtFile, '.txt');
        $artistUrl  = 'artist.php?a=' . urlencode($artistName);
        $imgUrl     = 'photos_artists/' . $artistName . '.png';
        if (!file_exists($imgUrl)) $imgUrl = 'img/artist_small.png';

        // first line as title
        $firstLine = '';
        $h = fopen($txtFile, 'r');
        if ($h) {
          $firstLine = trim(fgets($h));
          fclose($h);
        }

        echo sprintf(
          '<div class="grid-item">
           <a href="%s">
             <img src="%s" alt="%s" title="%s" class="bitmap">
           </a>
         </div>',
          $artistUrl,
          $imgUrl,
          htmlspecialchars($artistName),
          htmlspecialchars($firstLine)
        );
      }
      ?>
    </div>

    <div class="center">
      <a href="/"><img src="img/icon.svg" alt="icon" class="icons"></a>
    </div>

    <div class="footer">
      Research and coding all night, and party once in a while!
    </div>

  </div>

  <script src="index.js"></script>
</body>

</html>
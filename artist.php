<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/functions.php"); ?>

<!DOCTYPE html>
<html lang="en">

<?php
// Get artist slug from URL
$artist = isset($_GET['a']) ? $_GET['a'] : '';
$file_path = 'artists/' . $artist . '.txt';

// Fail fast if file is missing
if (!is_file($file_path)) {
  http_response_code(404);
  die("<h2>Artist file not found: " . htmlspecialchars($artist) . "</h2>");
}

// Load contents
$contents = file($file_path, FILE_IGNORE_NEW_LINES);

// Create thumbnail URL
if (file_exists('photos_artists_large/' . $artist . '.png')) {
  $thumb = 'photos_artists_large/' . $artist . '.png';
} else {
  $thumb = 'img/artist.png';
}

// Create page title
$pagetitle = trim($contents[0]);
// Get name of artist
$artist_name = trim($contents[0]);
?>

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_artists.php"); ?>

  <?php
  // Get the list of artists from the directory
  $artists = glob('artists/*.txt');
  $current_artist = isset($_GET['a']) ? $_GET['a'] : '';
  $current_index = array_search('artists/' . $current_artist . '.txt', $artists);
  $next_index = ($current_index + 1) % count($artists);
  $prev_index = ($current_index - 1 + count($artists)) % count($artists);
  $next_artist = basename($artists[$next_index], '.txt');
  $prev_artist = basename($artists[$prev_index], '.txt');
  ?>

  <div class="standard colored">
    <?php echo "<h2>" . htmlspecialchars($artist_name) . "</h2>"; ?>
  </div>

  <div class="skip-buttons colored">
    <span id="skip-left-btn" class="skip-button">
      <a href="<?php echo 'artist.php?a=' . $prev_artist; ?>" title="Previous">
        <svg class="icons">
          <use href="icons.svg#skip_left"></use>
        </svg>
      </a>
    </span>

    <span id="play-btn" class="playButton">
      <a href="#" id="playPauseBtn" title="Play">
        <svg class="icons">
          <use href="icons.svg#play"></use>
        </svg>
      </a>
    </span>

    <span id="skip-right-btn" class="skip-button">
      <a href="<?php echo 'artist.php?a=' . $next_artist; ?>" title="Next">
        <svg class="icons">
          <use href="icons.svg#skip_right"></use>
        </svg>
      </a>
    </span>
  </div>

  <script>
    let isPlaying = localStorage.getItem('isPlaying') === 'true';
    let playInterval;
    let shouldResumeOnNextPage = false;
    const playButton = document.getElementById('playPauseBtn');

    function navigateToNextArtist() {
      const currentArtist = '<?php echo $current_artist; ?>';
      const artists = <?php echo json_encode(array_map(function ($artist) {
                        return basename($artist, '.txt');
                      }, $artists)); ?>;
      const currentIndex = artists.indexOf(currentArtist);
      const nextIndex = (currentIndex + 1) % artists.length;
      const nextArtist = artists[nextIndex];

      shouldResumeOnNextPage = true;
      localStorage.setItem('currentArtist', nextArtist);
      window.location.href = 'artist.php?a=' + nextArtist;
    }

    function setPlayIcon(state) {
      const use = playButton.querySelector('use');
      if (!use) return;
      if (state === 'play') {
        use.setAttribute('href', 'icons.svg#play');
        playButton.setAttribute('title', 'Play');
      } else {
        use.setAttribute('href', 'icons.svg#pause');
        playButton.setAttribute('title', 'Pause');
      }
    }

    function togglePlay() {
      if (isPlaying) {
        clearInterval(playInterval);
        setPlayIcon('play');
        isPlaying = false;
        shouldResumeOnNextPage = false;
        localStorage.setItem('isPlaying', 'false');
      } else {
        playInterval = setInterval(navigateToNextArtist, 5000);
        setPlayIcon('pause');
        isPlaying = true;
        shouldResumeOnNextPage = false;
        localStorage.setItem('isPlaying', 'true');
      }
    }

    playButton.addEventListener('click', function(e) {
      e.preventDefault();
      togglePlay();
    });

    function stopPlayback() {
      if (isPlaying) {
        clearInterval(playInterval);
        setPlayIcon('play');
        isPlaying = false;
        localStorage.setItem('isPlaying', 'false');
      }

      shouldResumeOnNextPage = false;
    }

    document.querySelector('#skip-left-btn a').addEventListener('click', stopPlayback);
    document.querySelector('#skip-right-btn a').addEventListener('click', stopPlayback);

    window.addEventListener('pagehide', function() {
      if (!shouldResumeOnNextPage) {
        stopPlayback();
      }
    });

    // Restore play state
    if (isPlaying) {
      setPlayIcon('pause');
      playInterval = setInterval(navigateToNextArtist, 5000);
    } else {
      setPlayIcon('play');
    }
  </script>

  <?php
  // Process the list of names
  $list = explode(',', $contents[5]);
  $list = array_map('trim', $list);
  $links = [];
  foreach ($list as $name) {
    $slug = strtolower(str_replace([" ", ".", "'", "-", "[", "]"], ["_", "", "", "_", "", ""], $name));
    $links[] = "<a href='/artist.php?a=$slug'>$name</a>";
  }
  ?>
  </div>

  <div class="top-line">
    <svg class="icons" aria-hidden="true">
      <title>Photo</title>
      <use href="icons.svg#photo"></use>
    </svg>

    <h2>ARTIST PHOTO</h2>

    <svg class="icons show-pointer no-margin"
      onclick="toggleDiv('artistPhotoContainer')"
      tabindex="0" aria-label="Toggle artist photo">
      <title>Collapse</title>
      <use href="icons.svg#triangle_down"></use>
    </svg>
  </div>

  <div class="image-container" id="artistPhotoContainer">
    <?php
    if (file_exists('photos_artists_large/' . $artist . '.png')) {
      echo "<img src='photos_artists_large/$artist.png' title='" . trim($contents[0]) . "' class='bitmap full'>";
    } else {
      echo "<img src='img/artist.png' title='No photo for this artist' class='bitmap full'>";
    }
    ?>
  </div>

  <div class="standard colored">
    <?php
    // Instrument
    $instrument = trim($contents[1]);
    if ($instrument !== "") {
      echo "<span title=\"Main instrument(s)\">INSTRUMENT: </span>$instrument<br>";
    }

    // AKA
    $aka = trim($contents[6]);
    if ($aka !== "") {
      echo "<span title=\"Also known as\">AKA: </span>$aka<br>";
    }

    // Date of birth
    $dob = trim($contents[8] ?? '');
    if ($dob !== '') {
      echo "<span title=\"Date of birth\">DATE OF BIRTH: </span>$dob<br>";
    }

    // Date of passing
    $dop = trim($contents[9] ?? '');
    if ($dop !== '') {
      echo "<span title=\"Date of passing\">DATE OF PASSING: </span>$dop<br>";
    }

    // Gender
    $gender = trim($contents[3]);
    if ($gender !== "") {
      echo "<span title=\"Gender\">GENDER: </span>$gender<br>";
    }

    // Website
    $url = trim($contents[7]);
    if ($url !== "") {
      $display_url = preg_replace("#^https?://#", "", $url); // strip protocol
      echo "<span title=\"Website\">WEBSITE: </span><a href=\"$url\">$display_url</a><br>";
    }

    // Facebook
    $facebook = trim($contents[2]);
    if ($facebook !== "") {
      $facebook_url = "https://facebook.com/$facebook";
      echo "<span title=\"Facebook page\">FACEBOOK: </span><a href=\"$facebook_url\">$facebook</a><br>";
    }

    // With Kiss member(s)
    if (!empty($links)) {
      echo "<span title=\"In lineup with Kiss member(s)\">WITH KISS MEMBER: </span>" . implode(", ", $links) . "<br><br>";
    }

    // Updated
    $file = "artists/$artist.txt";
    if (file_exists($file)) {
      echo "Entry updated: " . date("d-m-Y.", get_artist_mtime($artist)) . "<br>";
    }
    ?>
  </div>

  <div class="top-line">
      <svg class="icons" aria-hidden="true">
        <title>Bands</title>
        <use href="icons.svg#band"></use>
      </svg>

    <h2>BANDS</h2>

    <svg class="icons show-pointer no-margin"
      onclick="toggleDiv('bandsContainer')"
      tabindex="0" aria-label="Toggle bands">
      <title>Collapse</title>
      <use href="icons.svg#triangle_down"></use>
    </svg>
  </div>

  <div id="bandsContainer" class="standard colored small-top-margin">
    <?php
    $artist_name = trim($contents[0]);
    $bands_list_file = 'bands_list/' . $artist . '.txt';
    if (file_exists($bands_list_file)) {
      $bands = array_filter(array_map('trim', file($bands_list_file)));
    } else {
      $bands = [];
    }

    function artistInLineup($lineup, $artist_name)
    {
      foreach ($lineup as $lineup_item) {
        $name = trim(explode(' - ', $lineup_item)[0]);
        if (strcasecmp($name, $artist_name) === 0) {
          return true;
        }
      }
      return false;
    }

    $artist_found = false;

    foreach ($bands as $band_name) {
      $slug = sanitize_band_slug($band_name);
      $band_file = 'bands/' . $slug . '.txt';

      if (!file_exists($band_file)) {
        echo "<h2>Missing file: " . htmlspecialchars($band_name) . "</h2>";
        continue;
      }

      $band_contents = file($band_file);
      $lineup = array_map('trim', array_slice($band_contents, 6));

      if (artistInLineup($lineup, $artist_name)) {
        $artist_found = true;

        // Band title + years
        $band_title = trim($band_contents[0]);
        $years      = trim($band_contents[1]);

        // Tiny band photo (same logic as bands_list.php)
        $imgUrl = "photos_bands/$slug.png";
        if (!file_exists($imgUrl)) {
          $imgUrl = "img/band.png";
        }

        echo "<h2>";
        echo "<a href='band.php?a=" . urlencode($slug) . "' class='thumb-link'>";
        echo "<span class='band-photo-wrap'><img src='$imgUrl' alt='" . htmlspecialchars($band_title) . "' class='band-photo bitmap' loading='lazy' decoding='async'></span>";
        echo "</a> ";

        echo "<a href='band.php?a=" . urlencode($slug) . "' class='artist-name'>" . htmlspecialchars($band_title) . "</a>";

        if ($years !== '') {
          $safe_years = htmlspecialchars($years, ENT_QUOTES, 'UTF-8');
          $safe_years = preg_replace('/([–—\-\/;,])\s*/u', '$1<wbr> ', $safe_years);
          echo " <span class=\"year-brackets\">[" . $safe_years . "]</span>";
        }

        echo "</h2><br>";

        foreach ($lineup as $member) {
          $parts = explode(' - ', $member);
          if (count($parts) < 2) continue;

          $name = trim($parts[0]);
          $instrument = trim($parts[1]);
          $slug_member = sanitize_artist_slug($name);

          if (file_exists("artists/$slug_member.txt")) {
            echo "<a href=\"/artist.php?a=$slug_member\">$name</a>";
          } else {
            echo htmlspecialchars($name);
          }

          echo " - " . htmlspecialchars($instrument) . "<br>\n";
        }

        echo "<br><br>";
      }
    }

    if (!$artist_found) {
      echo "<h2>No lineup found for $artist_name</h2>";
    }
    ?>
  </div>

  <div class="standard">
    <br>
    <?php
    $file_path = 'artists_credits/' . $artist . '.txt';
    if (file_exists($file_path)) {
      $credits = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    } else {
      $credits = [];
    }

    function get_credit($credits, $index)
    {
      return isset($credits[$index]) && trim($credits[$index]) !== '' ? trim($credits[$index]) : 'n/a';
    }
    ?>

    DESCRIPTION: <?php echo get_credit($credits, 0); ?><br>
    PHOTOGRAPHER: <?php echo get_credit($credits, 1); ?><br>
    SOURCE: <?php
            $source = get_credit($credits, 2);
            echo ($source !== 'n/a') ? "<a href='" . $source . "'>" . $source . "</a>" : 'n/a';
            ?>

    <br>
    <br>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
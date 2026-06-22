<?php
require_once('functions.php');

// Get band slug from URL
$band = isset($_GET['a']) ? $_GET['a'] : '';
$file_path = 'bands/' . $band . '.txt';

// Fail fast if file is missing
if (!is_file($file_path)) {
  http_response_code(404);
  die("<h2>Band file not found: " . htmlspecialchars($band) . "</h2>");
}

// Load contents
$contents = file($file_path, FILE_IGNORE_NEW_LINES);

// Thumbnail
// Absolute (for OG in head.php)
$siteUrl = "https://kissbandstree.com/";
// Relative (for inline <img>)
$imgThumb = file_exists("photos_bands_large/$band.png")
  ? "photos_bands_large/$band.png"
  : "img/band.png";
// Keep $thumb as absolute because head.php expects it for og:image
$thumb = $siteUrl . $imgThumb;

// Title and lineup
$pagetitle = trim($contents[0]);
$lineup = array_slice($contents, 6);

// Get all bands for next/prev and make ordering stable
$bands = glob('bands/*.txt');
sort($bands, SORT_NATURAL | SORT_FLAG_CASE);

$current_index = array_search($file_path, $bands, true);
if ($current_index === false) {
  $current_index = 0;
}

$next_index = ($current_index + 1) % count($bands);
$prev_index = ($current_index - 1 + count($bands)) % count($bands);
$next_band = basename($bands[$next_index], '.txt');
$prev_band = basename($bands[$prev_index], '.txt');

// Precompute band slugs for JS without PHP 7.4 arrow functions
$band_slugs = array_map(function ($b) {
  return basename($b, '.txt');
}, $bands);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_bands.php"); ?>



  <div class="standard colored">
    <?php echo "<h2>" . htmlspecialchars($pagetitle) . " [" . htmlspecialchars(trim($contents[1])) . "]</h2>"; ?>
  </div>


  <div class="skip-buttons colored">
    <span id="skip-left-btn" class="skip-button">
      <a href="band.php?a=<?= htmlspecialchars($prev_band) ?>" title="Previous">
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
      <a href="band.php?a=<?= htmlspecialchars($next_band) ?>" title="Next">
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

    const bands = <?= json_encode($band_slugs) ?>;
    const currentIndex = bands.indexOf(<?= json_encode($band) ?>);

    function navigateToNextBand() {
      const nextBand = bands[(currentIndex + 1) % bands.length];
      shouldResumeOnNextPage = true;
      localStorage.setItem('currentBand', nextBand);
      window.location.href = 'band.php?a=' + nextBand;
    }

    function setPlayIcon(state) {
      const use = playButton.querySelector('use');
      if (!use) return;
      use.setAttribute('href', state === 'play' ? 'icons.svg#play' : 'icons.svg#pause');
      playButton.setAttribute('title', state === 'play' ? 'Play' : 'Pause');
    }

    function togglePlay() {
      if (isPlaying) {
        clearInterval(playInterval);
        setPlayIcon('play');
        isPlaying = false;
        shouldResumeOnNextPage = false;
        localStorage.setItem('isPlaying', 'false');
      } else {
        playInterval = setInterval(navigateToNextBand, 5000);
        setPlayIcon('pause');
        isPlaying = true;
        shouldResumeOnNextPage = false;
        localStorage.setItem('isPlaying', 'true');
      }
    }

    playButton.addEventListener('click', e => {
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

    if (isPlaying) {
      setPlayIcon('pause');
      playInterval = setInterval(navigateToNextBand, 5000);
    } else {
      setPlayIcon('play');
    }
  </script>

  <div class="top-line">
    <svg class="icons" aria-hidden="true">
      <title>Photo</title>
      <use href="icons.svg#photo"></use>
    </svg>

    <h2>LINEUP PHOTO</h2>

    <svg class="icons show-pointer no-margin"
      onclick="toggleDiv('lineupPhotoContainer')"
      tabindex="0" aria-label="Toggle lineup photo">
      <title>Collapse</title>
      <use href="icons.svg#triangle_down"></use>
    </svg>
  </div>

  <div class="image-container" id="lineupPhotoContainer">
    <img src="<?= htmlspecialchars($imgThumb) ?>" title="<?= htmlspecialchars($pagetitle) ?>" class="bitmap full" loading="lazy">
  </div>

  <div class="standard colored">
    <?php
    foreach ($lineup as $member) {
      if (strpos($member, ' - ') !== false) {
        [$name, $instrument] = array_map('trim', explode(' - ', $member, 2));
        $slug = sanitize_artist_slug($name);
        $path = "artists/$slug.txt";
        $safeName = htmlspecialchars($name);
        $safeInstr = htmlspecialchars($instrument);

        $link = ($name === 'unknown' || !file_exists($path))
          ? $safeName
          : "<a href=\"/artist.php?a=$slug\">$safeName</a>";

        echo "<h2>$link - $safeInstr</h2>";
      }
    }
    ?>
  </div>

  <div class="standard">
    <?php
    $url = trim($contents[2]);
    if ($url) {
      $nice = preg_replace('#^https?://#i', '', $url);
      echo "URL: <a href='" . htmlspecialchars($url) . "'>" . htmlspecialchars($nice) . "</a><br>";
    }

    $facebook = trim($contents[3]);
    if ($facebook) {
      $fbUrl = 'https://facebook.com/' . ltrim($facebook, '/');
      echo "FACEBOOK: <a href='" . htmlspecialchars($fbUrl) . "'>" . htmlspecialchars($facebook) . "</a><br>";
    }

    $with_kiss = array_map('trim', explode(',', $contents[5]));
    $links = array_map(function ($name) {
      $slug = sanitize_artist_slug($name);
      return "<a href='/artist.php?a=$slug'>" . htmlspecialchars($name) . "</a>";
    }, $with_kiss);

    echo "WITH KISS MEMBER: " . implode(', ', $links) . "<br><br>";

    echo "<a href=\"tree.php?focus=" . urlencode($band) . "\">SHOW ON TREE</a><br><br>";

    echo "Entry updated: " . date('d-m-Y.', get_band_mtime($band));
    ?>
  </div>

  <div class="top-line">
    <svg class="icons" aria-hidden="true">
      <title>Comments</title>
      <use href="icons.svg#speech"></use>
    </svg>

    <h2>COMMENTS</h2>

    <svg class="icons show-pointer no-margin"
      onclick="toggleDiv('band-comments-container')"
      tabindex="0" aria-label="Toggle comments">
      <title>Collapse</title>
      <use href="icons.svg#triangle_down"></use>
    </svg>
  </div>

  <div class="standard colored" id="band-comments-container">
    <?php
    $comments_file = "bands_comments/$band.txt";
    if (file_exists($comments_file) && filesize($comments_file) > 0) {
      $comments_content = file_get_contents($comments_file);
      echo nl2br(htmlspecialchars($comments_content)) . "<br><br>";
    } else {
      echo "No comments available.<br><br>";
    }
    ?>
  </div>

  <div class="standard">
    <h2>PHOTO CREDITS</h2><br>
    <?php
    $credit_file = "bands_credits/$band.txt";
    $credits = file_exists($credit_file) ? file($credit_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    function get_credit($array, $index)
    {
      return !empty($array[$index]) ? trim($array[$index]) : 'n/a';
    }

    echo "DESCRIPTION: " . htmlspecialchars(get_credit($credits, 0)) . "<br>";
    echo "PHOTOGRAPHER: " . htmlspecialchars(get_credit($credits, 1)) . "<br>";

    $source = get_credit($credits, 2);
    $url = get_credit($credits, 3);
    echo "SOURCE: " . ($url && $url !== 'n/a'
      ? "<a href='" . htmlspecialchars($url) . "'>" . htmlspecialchars($source) . "</a>"
      : htmlspecialchars($source));
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
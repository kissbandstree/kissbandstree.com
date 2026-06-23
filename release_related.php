<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/functions.php');

$releaseParam = isset($_GET['a']) ? trim($_GET['a']) : '';
$releaseFile = basename($releaseParam);

if ($releaseFile === '') {
  http_response_code(404);
  die('<h2>Release file not found.</h2>');
}

if (substr($releaseFile, -4) !== '.txt') {
  $releaseFile .= '.txt';
}

$releaseSlug = basename($releaseFile, '.txt');
$filePath = 'releases/related/' . $releaseFile;

if (!is_file($filePath)) {
  http_response_code(404);
  die('<h2>Release file not found: ' . htmlspecialchars($releaseFile, ENT_QUOTES, 'UTF-8') . '</h2>');
}

$contents = file($filePath, FILE_IGNORE_NEW_LINES);
$artistName = trim($contents[0] ?? '');
$albumTitle = trim($contents[1] ?? '');
$releaseDate = trim($contents[2] ?? '');
$kissMembers = trim($contents[3] ?? '');

$pagetitle = $albumTitle !== '' ? $albumTitle : $releaseSlug;

function find_related_cover($slug)
{
  $extensions = ['png', 'webp', 'jpg', 'jpeg'];

  foreach ($extensions as $ext) {
    $large = 'photos_releases_large/related/' . $slug . '.' . $ext;
    if (file_exists($large)) {
      return $large;
    }
  }

  foreach ($extensions as $ext) {
    $small = 'photos_releases/related/' . $slug . '.' . $ext;
    if (file_exists($small)) {
      return $small;
    }
  }

  return 'img/album.png';
}

$imgThumb = find_related_cover($releaseSlug);
$thumb = $imgThumb;

$releases = glob('releases/related/*.txt');
usort($releases, function ($a, $b) {
  $aLines = file($a, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  $bLines = file($b, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

  $aDate = isset($aLines[2]) ? DateTime::createFromFormat('d-m-Y', trim($aLines[2])) : false;
  $bDate = isset($bLines[2]) ? DateTime::createFromFormat('d-m-Y', trim($bLines[2])) : false;

  $aTs = $aDate ? $aDate->getTimestamp() : 0;
  $bTs = $bDate ? $bDate->getTimestamp() : 0;

  if ($aTs === $bTs) {
    return strcasecmp($a, $b);
  }

  return $aTs <=> $bTs;
});

$currentIndex = array_search($filePath, $releases, true);
if ($currentIndex === false) {
  $currentIndex = 0;
}

$nextIndex = ($currentIndex + 1) % count($releases);
$prevIndex = ($currentIndex - 1 + count($releases)) % count($releases);
$nextReleaseSlug = basename($releases[$nextIndex], '.txt');
$prevReleaseSlug = basename($releases[$prevIndex], '.txt');

$releaseFiles = array_map(function ($item) {
  return basename($item, '.txt');
}, $releases);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_releases_related.php"); ?>

  <div class="top-line">
    <h2><?= htmlspecialchars($artistName, ENT_QUOTES, 'UTF-8') ?> - "<?= htmlspecialchars($albumTitle, ENT_QUOTES, 'UTF-8') ?>" [<?= htmlspecialchars($releaseDate, ENT_QUOTES, 'UTF-8') ?>]</h2>
  </div>

  <div class="skip-buttons colored">
      <span id="skip-left-btn" class="skip-button">
        <a href="release_related.php?a=<?= htmlspecialchars($prevReleaseSlug, ENT_QUOTES, 'UTF-8') ?>" title="Previous">
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
        <a href="release_related.php?a=<?= htmlspecialchars($nextReleaseSlug, ENT_QUOTES, 'UTF-8') ?>" title="Next">
          <svg class="icons">
            <use href="icons.svg#skip_right"></use>
          </svg>
        </a>
      </span>
  </div>

  <script>
    let isPlaying = localStorage.getItem('isPlayingReleaseRelated') === 'true';
    let playInterval;
    let shouldResumeOnNextPage = false;
    const playButton = document.getElementById('playPauseBtn');

    const releases = <?= json_encode($releaseFiles) ?>;
    const currentIndex = releases.indexOf(<?= json_encode($releaseSlug) ?>);

    function navigateToNextRelease() {
      const nextRelease = releases[(currentIndex + 1) % releases.length];
      shouldResumeOnNextPage = true;
      localStorage.setItem('currentReleaseRelated', nextRelease);
      window.location.href = 'release_related.php?a=' + encodeURIComponent(nextRelease);
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
        localStorage.setItem('isPlayingReleaseRelated', 'false');
      } else {
        playInterval = setInterval(navigateToNextRelease, 5000);
        setPlayIcon('pause');
        isPlaying = true;
        shouldResumeOnNextPage = false;
        localStorage.setItem('isPlayingReleaseRelated', 'true');
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
        localStorage.setItem('isPlayingReleaseRelated', 'false');
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
      playInterval = setInterval(navigateToNextRelease, 5000);
    } else {
      setPlayIcon('play');
    }
  </script>

  <div class="top-line">
    <svg class="icons" aria-hidden="true">
      <title>Photo</title>
      <use href="icons.svg#photo"></use>
    </svg>

    <h2>COVER</h2>

    <svg class="icons show-pointer no-margin"
      onclick="toggleDiv('releaseRelatedCoverContainer')"
      tabindex="0" aria-label="Toggle cover">
      <title>Collapse</title>
      <use href="icons.svg#triangle_down"></use>
    </svg>
  </div>

  <div class="image-container" id="releaseRelatedCoverContainer">
    <img src="<?= htmlspecialchars($imgThumb, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($albumTitle, ENT_QUOTES, 'UTF-8') ?>" class="bitmap full" loading="lazy">
  </div>

  <div class="standard colored">
    <?php
    echo 'ARTIST: ' . htmlspecialchars($artistName, ENT_QUOTES, 'UTF-8') . '<br>';
    echo 'ALBUM: "' . htmlspecialchars($albumTitle, ENT_QUOTES, 'UTF-8') . '"<br>';
    echo 'RELEASE DATE: ' . htmlspecialchars($releaseDate, ENT_QUOTES, 'UTF-8') . '<br>';
    $memberLinks = render_kiss_member_links($kissMembers);
    if ($memberLinks !== '') {
      echo 'WITH KISS MEMBER: ' . $memberLinks . '<br>';
    }
    echo '<br>';
    echo 'Entry updated: ' . date('d-m-Y.', filemtime($filePath));
    ?>
  </div>

  <div class="top-line">
    <svg class="icons" aria-hidden="true">
      <title>Tracklist</title>
      <use href="icons.svg#notepad"></use>
    </svg>

    <h2>TRACKLIST</h2>

    <svg class="icons show-pointer no-margin"
      onclick="toggleDiv('release-related-tracklist-container')"
      tabindex="0" aria-label="Toggle tracklist">
      <title>Collapse</title>
      <use href="icons.svg#triangle_down"></use>
    </svg>
  </div>

  <div class="standard" id="release-related-tracklist-container">
    <?php
    $tracklistFile = 'releases_tracklists/related/' . $releaseSlug . '.txt';
    echo render_numbered_tracklist($tracklistFile);
    ?>
  </div>

  <div class="top-line">
    <svg class="icons" aria-hidden="true">
      <title>Comments</title>
      <use href="icons.svg#speech"></use>
    </svg>

    <h2>COMMENTS</h2>

    <svg class="icons show-pointer no-margin"
      onclick="toggleDiv('release-related-comments-container')"
      tabindex="0" aria-label="Toggle comments">
      <title>Collapse</title>
      <use href="icons.svg#triangle_down"></use>
    </svg>
  </div>

  <div class="standard justify center-on-medium-screen" id="release-related-comments-container">
    <?php
    $commentsFile = 'releases_comments/related/' . $releaseSlug . '.txt';
    if (file_exists($commentsFile) && filesize($commentsFile) > 0) {
      $commentsContent = file_get_contents($commentsFile);
      echo nl2br(htmlspecialchars($commentsContent, ENT_QUOTES, 'UTF-8')) . '<br><br>';
    } else {
      echo 'No comments available.<br><br>';
    }
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>

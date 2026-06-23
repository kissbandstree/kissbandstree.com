<?php
$releaseFiles = array_merge(
  glob('releases/band/*.txt', GLOB_NOSORT) ?: [],
  glob('releases/related/*.txt', GLOB_NOSORT) ?: []
);

$latestReleases = [];

foreach ($releaseFiles as $file) {
  $contents = @file($file, FILE_IGNORE_NEW_LINES);
  if (!$contents) continue;

  $type = basename(dirname($file));
  $slug = basename($file, '.txt');
  $artist = trim($contents[0] ?? '');
  $title = trim($contents[1] ?? '');
  $dateText = trim($contents[2] ?? '');
  $releaseDate = DateTime::createFromFormat('d-m-Y', $dateText);

  if (!$releaseDate) continue;

  $latestReleases[] = [
    'type' => $type,
    'slug' => $slug,
    'artist' => $artist,
    'title' => $title,
    'dateText' => $dateText,
    'timestamp' => $releaseDate->getTimestamp(),
  ];
}

usort($latestReleases, function ($a, $b) {
  if ($a['timestamp'] === $b['timestamp']) {
    return strcasecmp($a['artist'] . $a['title'], $b['artist'] . $b['title']);
  }

  return $b['timestamp'] <=> $a['timestamp'];
});

$latestReleases = array_slice($latestReleases, 0, 4);

function latest_release_thumb($type, $slug)
{
  $extensions = ['png', 'webp', 'jpg', 'jpeg'];

  foreach ($extensions as $ext) {
    $path = 'photos_releases/' . $type . '/' . $slug . '.' . $ext;
    if (file_exists($path)) {
      return $path;
    }
  }

  return 'img/album.png';
}
?>

<div class="top-line">
  <svg class="icons" aria-hidden="true">
    <title>Releases</title>
    <use href="icons.svg#lp"></use>
  </svg>
  <h2>
    LATEST RELEASES
  </h2>
  <svg class="icons show-pointer no-margin"
    onclick="toggleDiv('lastReleaseContainer')"
    tabindex="0" aria-label="Toggle latest releases">
    <title>Collapse</title>
    <use href="icons.svg#triangle_down" />
  </svg>
</div>

<div class="last-container colored" id="lastReleaseContainer">
  <?php
  foreach ($latestReleases as $release) {
    $type = $release['type'];
    $slug = $release['slug'];
    $safeArtist = htmlspecialchars($release['artist'], ENT_QUOTES, 'UTF-8');
    $safeTitle = htmlspecialchars($release['title'], ENT_QUOTES, 'UTF-8');
    $safeDate = htmlspecialchars($release['dateText'], ENT_QUOTES, 'UTF-8');
    $imgUrl = latest_release_thumb($type, $slug);
    $href = $type === 'band'
      ? '/release_band.php?a=' . urlencode($slug)
      : '/release_related.php?a=' . urlencode($slug);

    echo "<div class='band-item'>";
    echo "<div><a href='" . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . "'><img src='" . htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') . "' alt='release' class='bitmap'></a></div>";
    echo "<div><a href='" . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . "'>" . $safeArtist . " - &quot;" . $safeTitle . "&quot;</a></div>";
    echo "<div>" . $safeDate . "</div>";
    echo "</div>";
  }
  ?>
</div>
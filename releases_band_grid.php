<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_releases_band.php"); ?>

  <?php
  $releaseFiles = glob('releases/band/*.txt');

  usort($releaseFiles, function ($a, $b) {
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
  ?>

  <div class="standard colored">
    <?php echo "Entries: " . count($releaseFiles) . ". Showing: <span id='visibleEntries'>" . count($releaseFiles) . "</span>. Sorting is chronological."; ?>
  </div>

  <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/filter.php"); render_member_filter(); ?>

  <div class="grid-container-release" id="grid-container">
    <?php
    foreach ($releaseFiles as $txtFile) {
      $slug = basename($txtFile, '.txt');
      $lines = file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $artist = trim($lines[0] ?? '');
      $title = trim($lines[1] ?? '');
      $date = trim($lines[2] ?? '');
      $members = trim($lines[3] ?? '');
      $membersAttr = htmlspecialchars($members, ENT_QUOTES, 'UTF-8');
      $tooltip = htmlspecialchars($artist . "\n" . $title . "\n" . $date, ENT_QUOTES, 'UTF-8');

      $imgUrl = 'photos_releases/band/' . $slug . '.png';
      if (!file_exists($imgUrl)) {
        $imgUrl = 'img/album.png';
      }

      echo '<div class="grid-item" data-members="' . $membersAttr . '">';
      echo '<a href="release_band.php?a=' . urlencode($slug) . '">';
      echo '<img src="' . htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" title="' . $tooltip . '" class="bitmap">';
      echo '</a>';
      echo '</div>';
    }
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>

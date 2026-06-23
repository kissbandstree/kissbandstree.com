<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_releases_related.php"); ?>

  <?php
  require_once($_SERVER['DOCUMENT_ROOT'] . '/functions.php');

  function find_related_thumb($slug)
  {
    $extensions = ['png', 'webp', 'jpg', 'jpeg'];

    foreach ($extensions as $ext) {
      $path = 'photos_releases/related/' . $slug . '.' . $ext;
      if (file_exists($path)) {
        return $path;
      }
    }

    return 'img/album.png';
  }

  $releaseFiles = glob('releases/related/*.txt');
  usort($releaseFiles, 'compare_release_files_by_date');
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
      $imgUrl = find_related_thumb($slug);

      echo '<div class="grid-item" data-members="' . $membersAttr . '">';
      echo '<a href="release_related.php?a=' . urlencode($slug) . '">';
      echo '<img src="' . htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" title="' . $tooltip . '" class="bitmap">';
      echo '</a>';
      echo '</div>';
    }
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>

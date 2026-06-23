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

  <div class="standard">
    <?php
    foreach ($releaseFiles as $txtFile) {
      $slug = basename($txtFile, '.txt');
      $lines = file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $artist = trim($lines[0] ?? '');
      $title = trim($lines[1] ?? '');
      $date = trim($lines[2] ?? '');
      $members = trim($lines[3] ?? '');
      $membersAttr = htmlspecialchars($members, ENT_QUOTES, 'UTF-8');
      $imgUrl = find_related_thumb($slug);

      echo '<div class="release-list-item" data-members="' . $membersAttr . '">';
      echo '<h2 class="small-photo-heading">';
      echo '<a href="release_related.php?a=' . urlencode($slug) . '" class="thumb-link"><span class="band-photo-wrap"><img src="' . htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" class="band-photo bitmap"></span></a>';
      echo '<a href="release_related.php?a=' . urlencode($slug) . '" class="artist-name">' . htmlspecialchars($artist, ENT_QUOTES, 'UTF-8') . ' - "' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"</a>';
      echo '</h2>';
      echo '<div class="band-lineup">' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '</div><br>';
      echo '</div>';
    }
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>

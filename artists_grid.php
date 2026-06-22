<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
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

  <div class="grid-container-artist" id="grid-container">
    <?php
    $artistFiles = glob('artists/*.txt'); // Get text files from folder

    foreach ($artistFiles as $index => $txtFile) {
      $artistName = basename($txtFile, '.txt');
      $artistUrl = 'artist.php?a=' . urlencode($artistName);

      $imgUrl = 'photos_artists/' . $artistName . '.png';
      if (!file_exists($imgUrl)) {
        $imgUrl = 'img/artist_small.png'; // Use default image if no photo found
      }

      // Read the first line from the text file
      $firstLine = '';
      if (file_exists($txtFile)) {
        $firstLine = fgets(fopen($txtFile, 'r'));
        $firstLine = trim($firstLine); // Remove any leading/trailing whitespace
      }

      $collab_members = get_artist_collaborator_members($artistName);
      $members_attr = htmlspecialchars(implode(', ', $collab_members), ENT_QUOTES, 'UTF-8');

      echo sprintf(
        '<div class="grid-item" data-members="%s">
            <a href="%s">
                <img src="%s" alt="%s" title="%s" class="bitmap">
            </a>
        </div>',
        $members_attr,
        $artistUrl,
        $imgUrl,
        $artistName,
        $firstLine // Set the first line as the title
      );
    }
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
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
    <?php echo "Entries: " . count($releaseFiles) . ". Showing: <span id='visibleEntries'>" . count($releaseFiles) . "</span>. Table is sortable."; ?>
  </div>
  <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/filter.php"); render_member_filter(); ?>

<div class="table-container">
    <table class="bands-table sortable colored over" id="filter_table" data-sort-reindex-col="0">
      <thead>
        <tr style="height: 45px;">
          <th class="not-wrap" data-sort-type="number"><span title="Chronological number">#</span></th>
          <th class="not-wrap sorttable_nosort"><span title="Picture">PIC.</span></th>
          <th class="not-wrap artist" data-sort-type="text"><span title="Artist name">ARTIST</span></th>
          <th class="not-wrap album" data-sort-type="text"><span title="Album name">ALBUM</span></th>
          <th class="not-wrap date" data-sort-type="date-dmy" data-sort-default="asc"><span title="Release date">DATE</span></th>
          <th class="not-wrap with" data-sort-type="text"><span title="Kiss members on record">WITH</span></th>
          <th class="not-wrap" data-sort-type="date-dmy"><span title="Entry created or modified">ENTRY</span></th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($releaseFiles as $i => $file) {
          $slug = basename($file, '.txt');
          $contents = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

          $artist = trim($contents[0] ?? '');
          $album = trim($contents[1] ?? '');
          $date = trim($contents[2] ?? '');
          $members = trim($contents[3] ?? '');
          $membersAttr = htmlspecialchars($members, ENT_QUOTES, 'UTF-8');
          $memberLinks = [];
          foreach (array_filter(array_map('trim', explode(',', $members))) as $memberName) {
            $memberSlug = strtolower(str_replace(
              [" ", ".", "'", "-", "[", "]"],
              ["_", "", "", "_", "", ""],
              $memberName
            ));
            $memberLinks[] = '<a href="/artist.php?a=' . htmlspecialchars($memberSlug, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($memberName, ENT_QUOTES, 'UTF-8') . '</a>';
          }
          $imgUrl = find_related_thumb($slug);

          echo '<tr data-members="' . $membersAttr . '">';
          echo '<td class="not-wrap" style="text-align: right;">' . ($i + 1) . '</td>';
          echo '<td class="not-wrap less-padding"><a href="/release_related.php?a=' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '"><img src="' . htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') . '" alt="release" class="bitmap"></a></td>';
          echo '<td class="not-wrap" style="text-align: left;" title="' . htmlspecialchars($artist, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($artist, ENT_QUOTES, 'UTF-8') . '</td>';
          echo '<td class="not-wrap" style="text-align: left;" title="' . htmlspecialchars($album, ENT_QUOTES, 'UTF-8') . '"><a href="/release_related.php?a=' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '">"' . htmlspecialchars($album, ENT_QUOTES, 'UTF-8') . '"</a></td>';
          echo '<td class="not-wrap" style="text-align: left;" title="' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '</td>';
          echo '<td class="not-wrap" style="text-align: left;" title="' . $membersAttr . '">' . implode(', ', $memberLinks) . '</td>';
          echo '<td class="not-wrap">' . date('d-m-Y', filemtime($file)) . '</td>';
          echo '</tr>';
        }
        ?>
      </tbody>
    </table>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>


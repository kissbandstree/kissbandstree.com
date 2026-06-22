<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_artists.php"); ?>

  <?php
  // Fetch all filenames in the artists folder
  $artists = glob('artists/*.txt');
  ?>

  <div class="standard colored">
    <?php echo "Entries: " . count($artists) . ". Showing: <span id='visibleEntries'>" . count($artists) . "</span>. Table is sortable."; ?>
  </div>

  <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/filter.php"); render_member_filter(); ?>

  <div class="table-container">
    <table class="artists-table sortable colored over" id="filter_table" data-sort-reindex-col="0">
      <thead>
        <tr style="height: 45px;">
          <th class="not-wrap" data-sort-type="number">
            <span title="Alphabetical number">#</span>
          </th>
          <th class="not-wrap sorttable_nosort">
            <span title="Picture">PIC.</span>
          </th>
          <th class="not-wrap artist" data-sort-type="text" data-sort-default="asc">
            <span title="Artist name">ARTIST</span>
          </th>
          <th class="not-wrap instrument" data-sort-type="text">
            <span title="Main instrument(s)">INSTR.</span>
          </th>
          <th class="not-wrap" data-sort-type="text">
            <span title="Also known as">AKA</span>
          </th>
          <th class="not-wrap with" data-sort-type="text">
            <span title="Played with what Kiss member(s)">WITH</span>
          </th>
          <th class="not-wrap" data-sort-type="date-dmy">
            <span title="Entry created or modified">ENTRY</span>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Loop through all the files in the artists folder
        foreach ($artists as $i => $file) {
          $contents = file($file);
          $basename = basename($file, ".txt");

          // Process the list of names
          $list = explode(',', $contents[5]);
          $list = array_map('trim', $list);

          // Produce the link(s) by dynamically generating slugs
          $links = [];
          foreach ($list as $name) {
            $slug = strtolower(str_replace(
              [" ", ".", "'", "-", "[", "]"],
              ["_", "", "", "_", "", ""],
              $name
            ));
            $links[] = "<a href='/artist.php?a=$slug'>$name</a>";
          }

          $collab_members = get_artist_collaborator_members($basename);
          $members_attr = htmlspecialchars(implode(', ', $collab_members), ENT_QUOTES, 'UTF-8');

          // Output table rows
          echo "<tr data-members='" . $members_attr . "'>";
          echo "<td style='text-align: right;' class='not-wrap'>" . ($i + 1) . "</td>"; // index
          if (file_exists('photos_artists/' . $basename . '.png')) {
            echo "<td class='not-wrap' title='" . htmlspecialchars(trim($contents[0]), ENT_QUOTES, 'UTF-8') . "'>
        <a href='/artist.php?a=" . htmlspecialchars($basename, ENT_QUOTES, 'UTF-8') . "'>
        <img src='photos_artists/" . htmlspecialchars($basename, ENT_QUOTES, 'UTF-8') . ".png' alt='artist' class='bitmap'>
        </a>
        </td>";
          } else {
            echo "<td class='not-wrap'><img src='img/artist_small.png' alt='artist' class='bitmap'></td>"; // photo
          }
          echo "<td class='not-wrap' style='text-align: left;' title='" . htmlspecialchars(trim($contents[0]), ENT_QUOTES, 'UTF-8') . "'>
      <a href='/artist.php?a=" . htmlspecialchars($basename, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars(trim($contents[0]), ENT_QUOTES, 'UTF-8') . "</a>
      </td>"; // artist
          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[1]) . "'>" . trim($contents[1]) . "</td>"; // instrument
          echo "<td class='not-wrap' style='text-align: left;' title='" . htmlspecialchars(trim($contents[6]), ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars(trim($contents[6]), ENT_QUOTES, 'UTF-8') . "</td>";
          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[5]) . "'>" . implode(', ', $links) . "</td>"; // played with
          echo "<td class='not-wrap'>" . date('d-m-Y', filemtime('artists/' . $basename . '.txt')) . "</td>"; // entry
          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_bands.php"); ?>

  <?php
  // Fetch all filenames in the bands folder
  $bands = glob('bands/*.txt');
  ?>

  <div class="standard colored">
    <?php echo "Entries: " . count($bands) . ". Showing: <span id='visibleEntries'>" . count($bands) . "</span>. Table is sortable."; ?>
  </div>

  <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/filter.php"); render_member_filter(); ?>

  <div class="table-container">
    <table class="bands-table sortable colored over" id="filter_table" data-sort-reindex-col="0">
      <thead>
        <tr style="height: 45px;">
          <th class="not-wrap" data-sort-type="number"><span title="Alphabetical number">#</span></th>
          <th class="not-wrap sorttable_nosort"><span title="Picture">PIC.</span></th>
          <th class="not-wrap band" data-sort-type="text" data-sort-default="asc"><span title="Band name">BAND</span></th>
          <th class="not-wrap" data-sort-type="year"><span title="Year">YEAR</span></th>
          <th class="not-wrap with" data-sort-type="text"><span title="Played with what Kiss member">WITH</span></th>
          <th class="not-wrap" data-sort-type="number"><span title="Number of members in lineup">MEMBERS</span></th>
          <th class="not-wrap" data-sort-type="date-dmy"><span title="Entry created or modified">ENTRY</span></th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Loop through all the files in the bands folder
        foreach ($bands as $i => $file) {
          $contents = file($file);
          $members_attr = htmlspecialchars(trim($contents[5]), ENT_QUOTES, 'UTF-8');
          $basename = basename($file, ".txt");

          // Process the list of names
          $list = explode(',', $contents[5]);
          $list = array_map('trim', $list);
          $lineup = array_filter(array_slice($contents, 6), function ($member) {
            return strpos($member, ' - ') !== false;
          });
          $member_count = count($lineup);

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

          // Output table rows
          echo "<tr data-members='" . $members_attr . "'>";
          echo "<td class='not-wrap' style='text-align: right;'>" . ($i + 1) . "</td>";

          if (file_exists('photos_bands/' . $basename . '.png')) {
            echo "<td class='not-wrap less-padding'><a href='/band.php?a=$basename'><img src='photos_bands/$basename.png' alt='band' class='bitmap'></a></td>";
          } else {
            echo "<td class='not-wrap'><a href='/band.php?a=$basename'><img src='img/band.png' alt='band' class='bitmap'></a></td>";
          }

          echo "<td class='not-wrap' style='text-align: left;' title='" . htmlspecialchars(trim($contents[0]), ENT_QUOTES, 'UTF-8') . "'><a href='/band.php?a=" . htmlspecialchars($basename, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars(trim($contents[0]), ENT_QUOTES, 'UTF-8') . "</a></td>";

          $year_text = trim($contents[1]);
          echo "<td class='not-wrap' style='text-align: left;' title='" . htmlspecialchars($year_text, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($year_text, ENT_QUOTES, 'UTF-8') . "</td>";

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[5]) . "'>" . implode(', ', $links) . "</td>";

          echo "<td class='not-wrap' data-sort-value='" . $member_count . "'>" . $member_count . "</td>";

          $entry_ts = filemtime('bands/' . $basename . '.txt');
          echo "<td class='not-wrap' data-sort-value='" . $entry_ts . "'>" . date('d-m-Y', $entry_ts) . "</td>";

          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>

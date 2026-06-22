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
    <?php echo
    "Entries: " . count($artists) . ".
      Showing: <span id='visibleEntries'>" . count($artists) . "</span>. 
      Table is sortable.";
    ?>
  </div>

  <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/filter.php"); render_member_filter(); ?>

  <div class="table-container">
    <table class="artists-table-detailed standard sortable colored over" id="filter_table" data-sort-reindex-col="0">
      <thead>
        <tr style="height: 45px;">
          <th class="not-wrap" data-sort-type="number"><span title="Alphabetical number">#</span></th>
          <th class="not-wrap sorttable_nosort"><span title="Picture">PIC.</span></th>
          <th class="not-wrap artist" data-sort-type="text" data-sort-default="asc"><span title="Artist name">ARTIST</span></th>
          <th class="not-wrap instrument" data-sort-type="text"><span title="Main instrument(s)">INSTR.</span></th>
          <th class="not-wrap" data-sort-type="text"><span title="Web address">URL</span></th>
          <th class="not-wrap" data-sort-type="text"><span title="Facebook page">FB.COM</span></th>
          <th class="not-wrap" data-sort-type="text"><span title="Also known as">AKA</span></th>
          <th class="not-wrap sex" data-sort-type="text"><span title="Gender">GENDER</span></th>
          <th class="not-wrap birth" data-sort-type="text"><span title="Date of birth">BIRTH</span></th>
          <th class="not-wrap passing" data-sort-type="text"><span title="Date of passing">PASSING</span></th>
          <th class="not-wrap with" data-sort-type="text"><span title="Played with what Kiss member(s)">WITH</span></th>
          <th class="not-wrap" data-sort-type="date-dmy"><span title="Entry created or modified">ENTRY</span></th>
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
          echo "<td style='text-align: right;'>" . ($i + 1) . "</td>"; // index

          if (file_exists('photos_artists/' . $basename . '.png')) {
            echo "<td class='not-wrap' title='" . trim($contents[0]) . "'><a href='/artist.php?a=$basename'><img src='photos_artists/$basename.png' alt='artist' class='bitmap'></a></td>";
          } else {
            echo "<td class='not-wrap'><img src='img/artist_small.png' alt='artist' class='bitmap'></td>"; // photo
          }

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[0]) . "'>
          <a href='/artist.php?a=$basename'>" . trim($contents[0]) . "</a>
          </td>"; // artist

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[1]) . "'>" . trim($contents[1]) . "</td>"; // instrument

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[7]) . "'><a href='" . trim($contents[7]) . "'> " . str_replace('http://', '', $contents[7]) . "</a></td>"; // url

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[2]) . "'>
          <a href='https://facebook.com/" . trim($contents[2]) . "'> " . trim($contents[2]) . "</a>
          </td>"; // facebook

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[6]) . "'>" . trim($contents[6]) . "</td>"; // aka

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[3]) . "'>" . trim($contents[3]) . "</td>"; // gender

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[8]) . "'>" . trim($contents[8]) . "</td>"; // birth

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[9] ?? '') . "'>" . trim($contents[9] ?? '') . "</td>"; // passing

          echo "<td class='not-wrap' style='text-align: left;' title='" . trim($contents[5]) . "'>" . implode(', ', $links) . "</td>"; // played with

          // Fetch file modification time as a unix timestamp and convert to a human-readable date
          echo "<td class='not-wrap'>" . date('d-m-Y', filemtime('artists/' . $basename . '.txt')) . "</td>";

          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
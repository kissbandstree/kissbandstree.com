<!DOCTYPE html>
<html lang="en">
<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>
<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_bands.php"); ?>

<?php
  $bandFiles = glob('bands/*.txt'); // Assuming text files are in the "bands" folder
?>

<div class="standard colored">
      <?php echo
      "Entries: " . count($bandFiles) . ".
      Sorting is chronological."; ?>
</div>

<?php
// Function to process the band name and generate the link
function generateBandLink($file_path) {
  // Read the bands list from the provided file path
  $bands_list = file_get_contents($file_path);

  // Explode the bands list by newline to get each band name separately
  $bands_array = explode("\n", $bands_list);

  // Now you can use $bands_array variable, which contains an array of band names
  foreach ($bands_array as $band_name) {
    // Trim each band name to remove any leading/trailing whitespace
    $band_name = trim($band_name);

    // Remove specific characters from the band name
    $band_name_clean = str_replace(['#', '- ', "'", '-', '.', '?'], '', $band_name);

    // Replace '&' with 'and'
    $band_name_clean = str_replace('&', 'and', $band_name_clean);

    // Skip empty lines or lines containing only whitespace
    if (empty($band_name_clean)) {
      continue;
    }

    // Remove everything starting from " [" and " /" (including these substrings)
    $band_name_clean = preg_replace('/\s*\[.*?\]|\s*\/.*?$/i', '', $band_name_clean);

    // Replace spaces with underscores and make the band name lowercase for the URL
    $band_url = 'band.php?a=' . urlencode(strtolower(str_replace('__', '_', str_replace(' ', '_', $band_name_clean))));

    // Determine the band file path for the current band
    $band_file_path = 'bands/' . strtolower(str_replace(' ', '_', $band_name_clean)) . '.txt';

    // Check if the band file exists
    if (file_exists($band_file_path)) {
      // Read the band information from the band file
      $contents = file($band_file_path, FILE_IGNORE_NEW_LINES);

      // Extract year
      $year = trim($contents[1]);

      // Output the band name as a link
      echo '<a href="' . $band_url . '">' . $band_name . '</a> [' . $year . ']<br><br>';

      // Extract lineup
      $lineup = array_slice($contents, 6);

      // Loop through each band member
      foreach ($lineup as $member) {
        // Parse the member name and instrument
        $parts = explode(' - ', $member);
        $name = $parts[0];
        $instrument = $parts[1];

        // Generate filename-style name including [1], [2]
        $member_name_link = strtolower(
          str_replace([" ", ".", "'", "-", "[", "]"], ["_", "", "", "_", "", ""], $name)
        );

        // Check if artist file exists
        if (file_exists('artists/' . $member_name_link . '.txt')) {
          $artist_link = "<a href=\"/artist.php?a=$member_name_link\">$name</a>";
        } else {
          $artist_link = $name;
        }

        // Print the member with the artist name as a link or plain text
        echo "$artist_link - $instrument<br>";
      }

      // Calculate the number of extra <br> tags needed to ensure consistent vertical spacing
      $lineupCount = count($lineup);
      $extraBrCount = ($lineupCount >= 8) ? (20 - $lineupCount) : (8 - $lineupCount);

      // Add extra <br> tags
      for ($i = 0; $i < $extraBrCount; $i++) {
        echo "<br>";
      }

      echo '<br><br>';
    } else {
      echo 'No lineup information available.<br><br>';
    }
  }
}
?>


<table class="standard">
  <tr>
    <?php
    // Associative array with the first four artist names and file paths
    $artists_first = array(
      'Gene Simmons' => 'bands_list/gene_simmons.txt',
      'Paul Stanley' => 'bands_list/paul_stanley.txt',
      'Peter Criss' => 'bands_list/peter_criss.txt',
      'Ace Frehley' => 'bands_list/ace_frehley.txt'
    );

    // Iterate through the first four artists and generate the band links
    foreach ($artists_first as $artist_name => $file_path) {
        echo '<td class="left-top not-wrap">';
        echo '<h1><a href="artist.php?a=' . urlencode(strtolower(str_replace(' ', '_', str_replace('.', '', $artist_name)))) . '">' . $artist_name . '</a></h1><br>';
        generateBandLink($file_path);
        echo '</td>';
    }
    ?>
  </tr>
</table>

<br>

<table class="standard">
  <tr>
    <?php
    // Associative array with the next four artist names and file paths
    $artists_next = array(
      'Eric Carr' => 'bands_list/eric_carr.txt',
      'Vinnie Vincent' => 'bands_list/vinnie_vincent.txt',
      'Mark St. John' => 'bands_list/mark_st_john.txt',
      'Bruce Kulick' => 'bands_list/bruce_kulick.txt'
    );

    // Iterate through the next four artists and generate the band links
    foreach ($artists_next as $artist_name => $file_path) {
        echo '<td class="left-top not-wrap">';
        echo '<h1><a href="artist.php?a=' . urlencode(strtolower(str_replace(' ', '_', str_replace('.', '', $artist_name)))) . '">' . $artist_name . '</a></h1><br>';
        generateBandLink($file_path);
        echo '</td>';
    }
    ?>
  </tr>
</table>

<br>

<table class="standard">
  <tr>
    <?php
    // Associative array with the last two artist names and file paths
    $artists_last = array(
      'Eric Singer' => 'bands_list/eric_singer.txt',
      'Tommy Thayer' => 'bands_list/tommy_thayer.txt'
    );

    // Iterate through the last two artists and generate the band links
    foreach ($artists_last as $artist_name => $file_path) {
        echo '<td class="left-top not-wrap">';
        echo '<h1><a href="artist.php?a=' . urlencode(strtolower(str_replace(' ', '_', str_replace('.', '', $artist_name)))) . '">' . $artist_name . '</a></h1><br>';
        generateBandLink($file_path);
        echo '</td>';
    }
    ?>
    <td class="left-top not-wrap">
      <br> <!-- Empty cell for the last table -->
    </td>
    <td class="left-top not-wrap">
      <br> <!-- Empty cell for the last table -->
    </td>
  </tr>
</table>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>
</html>

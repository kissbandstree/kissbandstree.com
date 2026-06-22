<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/functions.php'); ?>
<div class="top-line">

    <svg class="icons" aria-hidden="true">
        <title>Bands</title>
        <use href="icons.svg#band"></use>
    </svg>
    <h2>
        LAST MODIFIED BANDS
    </h2>
    <svg class="icons show-pointer no-margin"
        onclick="toggleDiv('bandContainer')"
        tabindex="0" aria-label="Toggle bands">
        <title>Collapse</title>
        <use href="icons.svg#triangle_down" />
    </svg>
</div>

<?php
// Fetch all filenames in the bands folder sorted by modification time
$bands = glob('bands/*.txt', GLOB_NOSORT);
$band_mtimes = array_map(function($f) { return get_band_mtime(basename($f, '.txt')); }, $bands);
array_multisort($band_mtimes, SORT_DESC, $bands);
$latest_four_bands = array_slice($bands, 0, 4); // Get the four latest modified files
?>

<div class="last-container colored" id="bandContainer">
    <?php
    // Loop through the four latest modified files
    foreach ($latest_four_bands as $i => $file) {
        $contents = file($file);
        echo "<div class='band-item'>";

        // Fetch contents of file as an array, where each line is a 0-based numerically indexed element in the array
        $contents = file($file);

        // Fetch basename (the filename part only)
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

        // Output some stuff
        if (file_exists('photos_bands/' . $basename . '.png')) {
            echo "<div><a href='/band.php?a=$basename'><img src='photos_bands/$basename.png' alt='band' class='bitmap'></a></div>";
        } else {
            echo "<div><a href='/band.php?a=$basename'><img src='img/band_small.png' alt='band' class='bitmap'></a></div>"; // photo
        }
        echo "<div><a href='/band.php?a=$basename'>" . trim($contents[0]) . "</a></div>"; // band
        echo "<div>" . trim($contents[1]) . "</div>"; // year
        echo "<div>" . implode(', ', $links) . "</div>"; // played with
        // Fetch file modification time as a unix timestamp and convert to a human-readable date
        echo "<div title='Entry updated'>" . date('d-m-Y', get_band_mtime($basename)) . "</div>";
        echo "</div>";
    }
    ?>
</div>
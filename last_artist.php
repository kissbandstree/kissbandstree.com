<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/functions.php'); ?>
<div class="top-line">

    <svg class="icons" aria-hidden="true">
        <title>Artists</title>
        <use href="icons.svg#person"></use>
    </svg>
    <h2>
        LAST MODIFIED ARTISTS
    </h2>
    <svg class="icons show-pointer no-margin"
        onclick="toggleDiv('artist-container')"
        tabindex="0" aria-label="Toggle artists">
        <title>Collapse</title>
        <use href="icons.svg#triangle_down" />
    </svg>
</div>

<?php
// Fetch all filenames in the artists folder sorted by modification time
$artists = glob('artists/*.txt', GLOB_NOSORT);
$artist_mtimes = array_map(function($f) { return get_artist_mtime(basename($f, '.txt')); }, $artists);
array_multisort($artist_mtimes, SORT_DESC, $artists);
$latest_four_artists = array_slice($artists, 0, 4); // Get the four latest modified files
?>

<div class="last-container colored" id="artist-container">
    <?php
    // Loop through the four latest modified files
    foreach ($latest_four_artists as $i => $file) {
        $contents = file($file);
        echo "<div class='artist-item'>";

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
        if (file_exists('photos_artists/' . $basename . '.png')) {
            echo "<div><a href='/artist.php?a=$basename'><img src='photos_artists/$basename.png' alt='artist' class='bitmap rounded'></a></div>";
        } else {
            echo "<div><a href='/artist.php?a=$basename'><img src='img/artist_small.png' alt='artist' class='bitmap rounded'></a></div>"; // photo
        }
        echo "<div><a href='/artist.php?a=$basename'>" . trim($contents[0]) . "</a></div>"; // artist
        echo "<div>" . trim($contents[1]) . "</div>"; // year
        echo "<div>" . implode(', ', $links) . "</div>"; // played with
        // Fetch file modification time as a unix timestamp and convert to a human-readable date
        echo "<div title='Entry updated'>" . date('d-m-Y', get_artist_mtime($basename)) . "</div>";
        echo "</div>";
    }
    ?>
</div>
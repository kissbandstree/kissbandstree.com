<?php
require_once __DIR__ . '/content.php'; require_once($_SERVER['DOCUMENT_ROOT'] . '/functions.php'); ?>
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
$bands = content_glob('bands/*.txt', GLOB_NOSORT);
$band_mtimes = array_map(function($f) { return get_band_mtime(basename($f, '.txt')); }, $bands);
array_multisort($band_mtimes, SORT_DESC, $bands);
$latest_four_bands = array_slice($bands, 0, 4); // Get the four latest modified files
?>

<div class="last-container colored" id="bandContainer">
    <?php
    foreach ($latest_four_bands as $file) {
        echo render_band_card($file, get_band_mtime(basename($file, '.txt')));
    }
    ?>
</div>
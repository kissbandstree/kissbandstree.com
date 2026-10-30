<?php
require_once __DIR__ . '/functions.php';
?>
<div class="top-line">
  <svg class="icons" aria-hidden="true">
    <title>Lineups</title>
    <use href="icons.svg#band"></use>
  </svg>
  <h2>LINEUPS</h2>
  <svg class="icons show-pointer no-margin"
    onclick="toggleDiv('lineup-container')"
    tabindex="0" aria-label="Toggle lineups">
    <title>Collapse</title>
    <use href="icons.svg#triangle_down" />
  </svg>
</div>

<div class="last-container colored" id="lineup-container">
  <?php
  // Gene's Tree timeline includes every Kiss lineup in chronological order.
  foreach (content_file('bands_list/gene_simmons.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $bandName) {
    $slug = sanitize_band_slug($bandName);
    if (preg_match('/^kiss_\d+(?:_reunion)?$/', $slug) === 1) {
      echo render_band_card('bands/' . $slug . '.txt');
    }
  }
  ?>
</div>

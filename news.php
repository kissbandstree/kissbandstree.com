<?php require_once __DIR__ . '/functions.php'; ?>
<div class="top-line">
  <h2>
    <svg class="icons" aria-hidden="true">
      <title>News</title>
      <use href="icons.svg#news"></use>
    </svg>
    News
  </h2>
  <svg class="icons show-pointer no-margin"
    onclick="toggleDiv('news-container')"
    tabindex="0" aria-label="Toggle news">
    <title>Collapse</title>
    <use href="icons.svg#triangle_down" />
  </svg>
</div>

<div class="news-container colored" id="news-container">

  <div class="full">
    23-06-2026
    <a href="https://github.com/kissbandstree">We are now on GitHub.<?php echo external_link_icon('https://github.com/kissbandstree'); ?></a>
  </div>

  <div class="full">
    07-09-2025
    <a href="/tree">Out now: The Kiss Bands Tree!</a>
  </div>

</div>
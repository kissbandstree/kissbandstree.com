<?php require_once __DIR__ . '/content.php'; ?><footer class="footer colored">
  <?php
  echo "Document updated: " . date("d-m-Y. ", getlastmod());
  ?>
  <br>
  <br>
  <div class="footer-socials">
    <a href="https://facebook.com/kissbandstree" aria-label="Facebook" title="Facebook">
      <svg class="icons" aria-hidden="true" focusable="false">
        <use href="icons.svg#facebook"></use>
      </svg>
    </a>
    <a href="https://github.com/kissbandstree" aria-label="GitHub" title="GitHub">
      <svg class="icons" aria-hidden="true" focusable="false">
        <use href="icons.svg#github"></use>
      </svg>
    </a>
  </div>
  <?php require __DIR__ . '/footer-branding.php'; ?>
</footer>

<script src="index.js"></script>
<script src="swipe.js?v=<?php echo filemtime(__DIR__ . '/swipe.js'); ?>"></script>
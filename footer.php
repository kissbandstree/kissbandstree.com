<footer class="footer colored">
  <?php
  echo "Document updated: " . date("d-m-Y. ", getlastmod());
  ?>
  <br>
  <br>
  <?php
  echo nl2br(file_get_contents("footer.txt"));
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
</footer>

<script src="index.js"></script>
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
  <a href="https://facebook.com/kissbandstree" aria-label="Facebook">
    <svg class="icons" aria-hidden="true" focusable="false">
      <use href="icons.svg#facebook"></use>
    </svg>
  </a>
</footer>

<script src="index.js"></script>
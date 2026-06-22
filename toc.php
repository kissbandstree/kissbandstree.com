<div class="heading colored">

  <div>
    <?php require($_SERVER['DOCUMENT_ROOT'] . "/burger.php"); ?>
  </div>

  <div>
    <a href="/" class="logo-link logo-link-small">
      <?php echo file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/img/logo.svg'); ?>
    </a>
  </div>

  <div>
    <svg class="icons show-pointer" onclick="toggleMode()" tabindex="0" aria-label="Toggle dark mode">
      <title>Toggle dark mode</title>
      <use href="icons.svg#moon"></use>
    </svg>
  </div>

</div>
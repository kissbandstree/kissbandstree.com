<?php require_once __DIR__ . '/content.php'; ?><div class="heading colored">

  <div>
    <?php require($_SERVER['DOCUMENT_ROOT'] . "/burger.php"); ?>
  </div>

  <div>
    <a href="/" class="logo-link logo-link-small">
      <?php echo content_text($_SERVER['DOCUMENT_ROOT'] . '/img/logo.svg'); ?>
    </a>
  </div>

  <div>
    <?php require($_SERVER['DOCUMENT_ROOT'] . "/settings.php"); ?>
  </div>

</div>
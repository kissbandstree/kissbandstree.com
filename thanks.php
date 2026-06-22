<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>

  <div class="top-line">
    <svg class="icons" aria-hidden="true" focusable="false">
      <title>Thanks</title>
      <use href="icons.svg#star"></use>
    </svg>

    <h2>THANKS</h2>

    <svg class="icons" aria-hidden="true" focusable="false">
      <title>Thanks</title>
      <use href="icons.svg#star"></use>
    </svg>
  </div>

  <div class="standard colored justify center-on-medium-screen">
    To all the wonderful people who have contributed to this project, through interviews, sharing photos and stories, proofreading, following the Facebook page, answering my numerous questions and offering your support. This project would not have been possible without you! Only individuals, not companies, bands or organizations are mentioned here. If you want to be added or removed, please <a href="mailto:ole@peko.net?subject=Kiss%20Bands%20Tree">contact me</a>.
  </div>

  <div class="standard justify">
    <?php
    echo (file_get_contents("thanks.txt"));
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
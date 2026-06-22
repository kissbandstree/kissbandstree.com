<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>

  <div class="top-line">
    <svg class="icons" aria-hidden="true" focusable="false">
      <title>About</title>
      <use href="icons.svg#about"></use>
    </svg>

    <h2>ABOUT</h2>

    <svg class="icons" aria-hidden="true" focusable="false">
      <title>About</title>
      <use href="icons.svg#about"></use>
    </svg>
  </div>

  </div>

  <div class="standard colored">
    <h1>
      ABOUT TREE
    </h1>
  </div>

  <br>

  <div class="standard justify">
    <?php
    echo nl2br(htmlspecialchars(file_get_contents("about_poster.txt")));
    ?>
  </div>

  <br>
  <br>

  <div class="standard colored">
    <h1>
      ABOUT WEBSITE
    </h1>
  </div>

  <br>

  <div class="standard justify">
    <?php
    echo nl2br(htmlspecialchars(file_get_contents("about_website.txt")));
    ?>
  </div>

  <br>
  <br>

  <div class="standard colored">
    <h1>
      KNOWN ISSUES
    </h1>
  </div>

  <br>

  <div class="standard justify">
    <?php
    echo nl2br(htmlspecialchars(file_get_contents("issues.txt")));
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
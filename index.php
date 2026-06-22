<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
  <meta name="keywords" content="Kiss Bands Tree, Kiss, bands tree, KBT, Kiss family tree, Eric Singer, Bruce Kulick, Eric Carr, Peter Criss, Gene Simmons, Paul Stanley, Ace Frehley, Vinnie Vincent, Mark St. John, Tommy Thayer">
  <meta name="description" content="All the bands with members of the American rock band Kiss.">
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>

  <?php
  $bandFiles = glob('bands/*.txt');
  $artists = glob('artists/*.txt');
  ?>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/news.php"); ?>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/members.php"); ?>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/search.php"); ?>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/last_band.php"); ?>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/last_artist.php"); ?>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/active_band.php"); ?>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/last_release.php"); ?>

  <div class="standard justify center-on-medium-screen">
    <?php
    echo "Lineups: " . count($bandFiles) . ". Artists: " . count($artists) . ". ";
    echo nl2br(file_get_contents("intro.txt"));
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>

</html>
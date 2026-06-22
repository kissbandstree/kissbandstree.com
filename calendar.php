<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/functions.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/head.php'); ?>
</head>

<body>
  <?php require($_SERVER['DOCUMENT_ROOT'] . '/toc.php'); ?>

  <?php
  // Load artist files
  $artists = glob(__DIR__ . '/artists/*.txt');

  // Month headings + days per month (allow Feb 29)
  $months = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  $daysIn = [1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31];

  // birthdays[month][day] = array of ['name'=>..., 'html'=>...]
  $birthdays = array_fill(1, 12, []);
  $entryCount = 0;

  // Build birthdays; DOB is on line 9 (index 8) as dd-mm-YYYY (YYYY may include ? like 19??)
  foreach ($artists as $f) {
    $c   = file($f, FILE_IGNORE_NEW_LINES);
    $dob = trim($c[8] ?? '');
    // Match dd-mm-(YYYY|19?? etc.). Allow -, ., /
    if (!preg_match('/^(\d{1,2})[.\-\/](\d{1,2})[.\-\/]([0-9?]{2,4})$/', $dob, $m)) continue;

    $d  = (int)$m[1];
    $mo = (int)$m[2];
    $yr = $m[3]; // may include question marks

    if ($mo < 1 || $mo > 12 || $d < 1 || $d > 31) continue;

    $slug       = basename($f, '.txt');
    $name       = trim($c[0] ?? $slug);
    $artist_url = '/artist.php?a=' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');

    $imgRel = file_exists(__DIR__ . "/photos_artists/$slug.png")
      ? "/photos_artists/$slug.png"
      : "/img/artist_small.png";

    $imgUrl   = htmlspecialchars($imgRel, ENT_QUOTES, 'UTF-8');
    $safeName = htmlspecialchars($name,   ENT_QUOTES, 'UTF-8');

    $dateLabel = sprintf('%02d-%02d', $d, $mo) . '-' . $yr;  // keep unknown year as-is (e.g., 19??)
    $title     = $safeName . ' (' . $dateLabel . ')';

    if (!isset($birthdays[$mo][$d])) $birthdays[$mo][$d] = [];
    $birthdays[$mo][$d][] = [
      'name' => $safeName,
      'html' => "<span class='artist-photo-wrap rounded'><a href='$artist_url' title='$title'><img src='$imgUrl' alt='$safeName' class='artist-photo bitmap rounded'></a></span>"
    ];
    $entryCount++;
  }

  // Sort names alphabetically within each day (PHP 5.6+ compatible)
  for ($mo = 1; $mo <= 12; $mo++) {
    foreach ($birthdays[$mo] as $d => &$arr) {
      usort($arr, function ($a, $b) {
        return strnatcasecmp($a['name'], $b['name']);
      });
    }
    unset($arr);
  }

  $todayMonth = (int)date('n');
  $todayDay   = (int)date('j');

  ?>

  <div class="top-line">
    <svg class="icons" aria-hidden="true" focusable="false">
      <title>Calendar</title>
      <use href="icons.svg#calendar"></use>
    </svg>

    <h2>CALENDAR</h2>

    <svg class="icons" aria-hidden="true" focusable="false">
      <title>Calendar</title>
      <use href="icons.svg#calendar"></use>
    </svg>
  </div>

  <div class="standard colored">
    Entries: <?= number_format($entryCount) ?>.
  </div>

  <div class="grid-container calendar">
    <?php for ($mo = 1; $mo <= 12; $mo++): ?>
      <div class="grid-item">
        <h2><?= $months[$mo] ?></h2>
        <div class="month-grid">
          <?php
          $max = $daysIn[$mo];
          $cells = ceil($max / 7) * 7; // full rows
          for ($i = 1; $i <= $cells; $i++):
            if ($i <= $max):
              $d = $i;
              $people = isset($birthdays[$mo][$d]) ? $birthdays[$mo][$d] : [];
          ?>
              <div class="daycell<?= ($mo === $todayMonth && $d === $todayDay ? ' today' : '') ?>">
                <div class="daynum"><?= str_pad($d, 2, '0', STR_PAD_LEFT) ?></div>
                <div class="faces">
                  <?php foreach ($people as $p) echo $p['html']; ?>
                </div>
              </div>
            <?php else: ?>
              <div class="daycell empty"></div>
          <?php endif;
          endfor; ?>
        </div>
      </div>
    <?php endfor; ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . '/footer.php'); ?>
</body>

</html>
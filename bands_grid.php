<!DOCTYPE html>
<html lang="en">

<head>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
  <style>
    .grid-item {
      display: none;
      /* Hide all items initially */
    }
  </style>
</head>

<body>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>
  <?php require($_SERVER['DOCUMENT_ROOT'] . "/toc_bands.php"); ?>

  <div class="standard colored">
    <?php
    $bandFiles = glob('bands/*.txt'); // Assuming text files are in the "bands" folder
    echo "
        <span id='totalEntries'>Entries: " . count($bandFiles) . ".&nbsp;</span>
        <span>Showing: <span id='visibleEntries'></span>.&nbsp;</span>
        <span>Sorting is alphabetical.</span>
    ";
    ?>
  </div>

  <!-- Filter -->

  <div class="top-line">
    <svg class="icons" aria-hidden="true" focusable="false">
      <title>Filter</title>
      <use href="icons.svg#filter"></use>
    </svg>

    <h2>FILTER</h2>

    <a href="#" id="resetBtn" title="Reset" aria-label="Reset filters">
      <svg class="icons" aria-hidden="true" focusable="false">
        <use href="icons.svg#reset"></use>
      </svg>
    </a>
  </div>

  <div class="filter-circles colored">
    <?php
    require_once($_SERVER['DOCUMENT_ROOT'] . "/functions.php");
    $filter_members = [
      'Gene Simmons',
      'Paul Stanley',
      'Peter Criss',
      'Ace Frehley',
      'Eric Carr',
      'Vinnie Vincent',
      'Mark St. John',
      'Bruce Kulick',
      'Eric Singer',
      'Tommy Thayer'
    ];
    foreach ($filter_members as $name) {
      $slug = sanitize_artist_slug($name);
      $id   = 'm_' . $slug;
      $img  = "photos_artists/$slug.png";
      if (!file_exists($img)) {
        $img = 'img/artist_small.png';
      }

      echo '
        <input type="checkbox" name="member" value="' . htmlspecialchars($name) . '" id="' . $id . '" class="sr-only">
        <label for="' . $id . '" title="' . htmlspecialchars($name) . '" class="artist-toggle">
          <span class="artist-photo-wrap rounded">
            <img src="' . $img . '" alt="' . htmlspecialchars($name) . '" class="artist-photo bitmap rounded">
          </span>
        </label>
      ';
    }
    ?>
  </div>

  <!-- Grid container for bands -->

  <div class="grid-container band-photo-grid" id="grid-container">
    <?php
    foreach ($bandFiles as $index => $txtFile) {
      $bandName = basename($txtFile, '.txt');
      $bandUrl = 'band.php?a=' . urlencode($bandName);

      $imgUrl = 'photos_bands/' . $bandName . '.png';
      if (!file_exists($imgUrl)) {
        $imgUrl = 'img/band_small.png'; // Use default image if no photo found
      }

      // Read the first line (title) and the sixth line (members) from the text file
      $fileLines = file($txtFile, FILE_IGNORE_NEW_LINES);
      $firstLine = trim($fileLines[0]); // First line as the title
      $membersLine = trim($fileLines[5]); // Sixth line (index 5) as members

      // Convert the members line into an array, without altering them
      $membersArray = array_map('trim', explode(',', $membersLine));

      // Store the members exactly as they appear in the text file in the data attribute
      $members = implode(', ', $membersArray);

      echo sprintf(
        '<div class="grid-item" data-members="%s">
              <a href="%s">
                  <img src="%s" alt="%s" title="%s" class="bitmap">
              </a>
          </div>',
        htmlspecialchars($members, ENT_QUOTES, 'UTF-8'), // Use members directly
        $bandUrl,
        $imgUrl,
        htmlspecialchars($bandName, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($firstLine, ENT_QUOTES, 'UTF-8')
      );
    }
    ?>
  </div>

  <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

  <script>
    (() => {
      const checkboxes = document.querySelectorAll('input[name="member"]');
      const gridItems = document.querySelectorAll('.grid-item');
      const resetBtn = document.getElementById('resetBtn');
      const visibleEl = document.getElementById('visibleEntries');
      const SAVED_KEY = 'kbt_members';

      const restore = () => {
        try {
          const saved = JSON.parse(localStorage.getItem(SAVED_KEY) || '[]');
          checkboxes.forEach(c => c.checked = saved.includes(c.value));
        } catch {}
      };

      const save = () => {
        const sel = [...checkboxes].filter(c => c.checked).map(c => c.value);
        localStorage.setItem(SAVED_KEY, JSON.stringify(sel));
      };

      const filterBands = () => {
        const selected = [...checkboxes].filter(c => c.checked).map(c => c.value.trim());
        let visible = 0;
        gridItems.forEach(item => {
          const members = (item.dataset.members || '')
            .split(',')
            .map(s => s.trim())
            .filter(Boolean);
          const match = selected.length === 0 || selected.every(m => members.includes(m));
          item.style.display = match ? 'block' : 'none';
          if (match) visible++;
        });
        if (visibleEl) visibleEl.textContent = visible;
      };

      restore();
      filterBands();

      checkboxes.forEach(c => {
        c.addEventListener('change', () => {
          save();
          filterBands();
        });
      });

      if (resetBtn) {
        const doReset = (e) => {
          if (e && e.preventDefault) e.preventDefault();
          checkboxes.forEach(c => (c.checked = false));
          localStorage.removeItem(SAVED_KEY);
          filterBands();
        };
        resetBtn.addEventListener('click', doReset);
        resetBtn.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') doReset(e);
        });
      }
    })();
  </script>

</body>

</html>
<?php
/**
 * Centralized filter utilities for bands and artists
 * Supports two filter types:
 * - 'members': Filter bands by Kiss members
 * - 'bands': Filter artists by bands they played in
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/functions.php");

function get_kiss_filter_members() {
  return [
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
}

function get_artist_collaborator_members($artist_slug) {
  $filter_members = get_kiss_filter_members();
  $member_lookup = array_flip($filter_members);
  $found = [];

  $bands_list_file = 'bands_list/' . $artist_slug . '.txt';
  if (file_exists($bands_list_file)) {
    $bands = array_filter(array_map('trim', file($bands_list_file)));

    foreach ($bands as $band_name) {
      $band_slug = sanitize_band_slug($band_name);
      $band_file = 'bands/' . $band_slug . '.txt';
      if (!file_exists($band_file)) {
        continue;
      }

      $lines = file($band_file, FILE_IGNORE_NEW_LINES);
      foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, ' - ') === false) {
          continue;
        }

        list($name,) = array_map('trim', explode(' - ', $line, 2));
        if (isset($member_lookup[$name])) {
          $found[$name] = true;
        }
      }
    }
  }

  $ordered = [];
  foreach ($filter_members as $member_name) {
    if (isset($found[$member_name])) {
      $ordered[] = $member_name;
    }
  }

  return $ordered;
}

function render_member_filter() {
  $filter_members = get_kiss_filter_members();
  ?>
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

  <script>
    (() => {
      const init = () => {
        const checkboxes = document.querySelectorAll('input[name="member"]');
        const filterItems = document.querySelectorAll('[data-members]');
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
          filterItems.forEach(item => {
            const members = (item.dataset.members || '')
              .split(',')
              .map(s => s.trim())
              .filter(s => s);
            const show = selected.length === 0 || selected.every(s => members.includes(s));
            item.style.display = show ? '' : 'none';
            if (show) visible++;
          });
          if (visibleEl) visibleEl.textContent = visible;
        };

        checkboxes.forEach(c => {
          c.addEventListener('change', () => {
            save();
            filterBands();
          });
        });

        if (resetBtn) {
          resetBtn.addEventListener('click', e => {
            e.preventDefault();
            checkboxes.forEach(c => c.checked = false);
            save();
            filterBands();
          });
        }

        restore();
        filterBands();
      };

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
    })();
  </script>
  <?php
}

function render_band_filter() {
  // Collect all unique bands from all artists
  $all_bands = [];
  $artists = glob('artists/*.txt');
  
  foreach ($artists as $artist_file) {
    $band_list_file = 'bands_list/' . basename($artist_file);
    if (file_exists($band_list_file)) {
      $bands = array_filter(array_map('trim', file($band_list_file)));
      $all_bands = array_unique(array_merge($all_bands, $bands));
    }
  }
  
  sort($all_bands, SORT_NATURAL | SORT_FLAG_CASE);
  ?>
  <div class="top-line">
    <svg class="icons" aria-hidden="true" focusable="false">
      <title>Filter</title>
      <use href="icons.svg#filter"></use>
    </svg>
    <h2>FILTER</h2>
    <a href="#" id="resetBtnArtists" title="Reset" aria-label="Reset filters">
      <svg class="icons" aria-hidden="true" focusable="false">
        <use href="icons.svg#reset"></use>
      </svg>
    </a>
  </div>

  <div class="filter-circles colored">
    <?php
    foreach ($all_bands as $band_name) {
      $slug = sanitize_band_slug($band_name);
      $id = 'b_' . preg_replace('/[^a-z0-9]/i', '_', strtolower($band_name));
      
      echo '
        <input type="checkbox" name="band" value="' . htmlspecialchars($band_name) . '" id="' . $id . '" class="sr-only">
        <label for="' . $id . '" title="' . htmlspecialchars($band_name) . '" class="band-toggle">
          <span class="band-label">' . htmlspecialchars($band_name) . '</span>
        </label>
      ';
    }
    ?>
  </div>

  <script>
    (() => {
      const init = () => {
        const checkboxes = document.querySelectorAll('input[name="band"]');
        const resetBtn = document.getElementById('resetBtnArtists');
        const visibleEl = document.getElementById('visibleEntries');
        const SAVED_KEY = 'kbt_bands';

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

        const filterArtists = () => {
          const selected = [...checkboxes].filter(c => c.checked).map(c => c.value.trim());
          let visible = 0;
          const artistSections = document.querySelectorAll('[data-artist-bands]');
          
          artistSections.forEach(section => {
            const bandsList = (section.dataset.artistBands || '')
              .split('|')
              .map(s => s.trim())
              .filter(s => s);
            const show = selected.length === 0 || selected.some(s => bandsList.includes(s));
            section.style.display = show ? 'block' : 'none';
            if (show) visible++;
          });
          
          if (visibleEl) visibleEl.textContent = visible;
        };

        checkboxes.forEach(c => {
          c.addEventListener('change', () => {
            save();
            filterArtists();
          });
        });

        if (resetBtn) {
          resetBtn.addEventListener('click', e => {
            e.preventDefault();
            checkboxes.forEach(c => c.checked = false);
            save();
            filterArtists();
          });
        }

        restore();
        filterArtists();
      };

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
    })();
  </script>
  <?php
}

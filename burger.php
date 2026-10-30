<div id="burger-container">
  <button
    id="toggle-menu-button"
    class="settings-toggle burgerIcon"
    type="button"
    onclick="toggleMenu()"
    aria-controls="toc"
    aria-expanded="false"
    title="Menu"
    aria-label="Menu">
    <svg class="icons" aria-hidden="true"><use href="icons.svg#burger"></use></svg>
  </button>

  <div id="toc" class="toc-container no-swipe">
    <ul>
      <li>
        <h1>
          <a
            href="bands_grid.php"
            data-view-group="bands"
            data-view-grid="bands_grid.php"
            data-view-table="bands_table.php"
            data-view-list="bands_list.php">
            <svg class="icons">
              <use href="icons.svg#band"></use>
            </svg>
            Bands
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a
            href="artists_grid.php"
            data-view-group="artists"
            data-view-grid="artists_grid.php"
            data-view-table="artists_table.php"
            data-view-list="artists_list.php">
            <svg class="icons">
              <use href="icons.svg#person"></use>
            </svg>
            Artists
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a
            href="releases_band_grid.php"
            data-view-group="releases"
            data-view-grid="releases_band_grid.php"
            data-view-table="releases_band_table.php"
            data-view-list="releases_band_list.php">
            <svg class="icons">
              <use href="icons.svg#lp"></use>
            </svg>
            Releases
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a
            href="releases_related_grid.php"
            data-view-group="related"
            data-view-grid="releases_related_grid.php"
            data-view-table="releases_related_table.php"
            data-view-list="releases_related_list.php">
            <svg class="icons">
              <use href="icons.svg#lp"></use>
            </svg>
            Related
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="calendar.php">
            <svg class="icons">
              <use href="icons.svg#calendar"></use>
            </svg>
            Calendar
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="about.php">
            <svg class="icons">
              <use href="icons.svg#about"></use>
            </svg>
            About
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="thanks.php">
            <svg class="icons">
              <use href="icons.svg#star"></use>
            </svg>
            Thanks
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="stats.php">
            <svg class="icons">
              <use href="icons.svg#cake_diagram"></use>
            </svg>
            Stats
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="tree.php">
            <svg class="icons">
              <use href="icons.svg#tree"></use>
            </svg>
            Tree
          </a>
        </h1>
      </li>
    </ul>

    <div class="toc-close-wrap">
      <button
        id="toggle-menu-close-button"
        class="settings-toggle"
        type="button"
        onclick="closeMenu()"
        title="Close"
        aria-label="Close">
        <svg class="icons" aria-hidden="true"><use href="icons.svg#close"></use></svg>
      </button>
    </div>
  </div>
</div>
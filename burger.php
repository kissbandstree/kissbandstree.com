<div id="burger-container">
  <svg
    class="icons show-pointer burgerIcon"
    onclick="toggleMenu()"
    tabindex="0"
    aria-label="Menu">
    <title>Menu</title>
    <use href="icons.svg#burger"></use>
  </svg>

  <div id="toc" class="toc-container">
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
            BANDS
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
            ARTISTS
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
            RELEASES
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
            RELATED
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="about.php">
            <svg class="icons">
              <use href="icons.svg#about"></use>
            </svg>
            ABOUT
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="thanks.php">
            <svg class="icons">
              <use href="icons.svg#star"></use>
            </svg>
            THANKS
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="stats.php">
            <svg class="icons">
              <use href="icons.svg#cake_diagram"></use>
            </svg>
            STATS
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="tree.php">
            <svg class="icons">
              <use href="icons.svg#tree"></use>
            </svg>
            TREE
          </a>
        </h1>
      </li>
    </ul>

    <div class="toc-close-wrap">
      <svg
        class="icons show-pointer no-margin"
        onclick="closeMenu()"
        tabindex="0"
        aria-label="Close">
        <title>Close</title>
        <use href="icons.svg#close"></use>
      </svg>
    </div>
  </div>
</div>
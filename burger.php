<?php
$currentPath = rtrim(preg_replace('~\.php$~', '', parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'), '/') ?: '/';
$currentSection = [
  '/index' => '/',
  '/band' => '/bands',
  '/artist' => '/artists',
  '/release_band' => '/releases',
  '/release_related' => '/related',
][$currentPath] ?? $currentPath;
?>
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
      <li><h1><a href="/"<?php if ($currentSection === '/'): ?> aria-current="page"<?php endif; ?>><svg class="icons" aria-hidden="true"><use href="/icons.svg#home"></use></svg>Home</a></h1></li>
      <li>
        <h1>
          <a
            href="/bands"<?php if ($currentSection === '/bands'): ?> aria-current="page"<?php endif; ?>
            data-view-group="bands"
            data-view-grid="/bands"
            data-view-table="/bands?view=table"
            data-view-list="/bands?view=list">
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
            href="/artists"<?php if ($currentSection === '/artists'): ?> aria-current="page"<?php endif; ?>
            data-view-group="artists"
            data-view-grid="/artists"
            data-view-table="/artists?view=table"
            data-view-list="/artists?view=list">
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
            href="/releases"<?php if ($currentSection === '/releases'): ?> aria-current="page"<?php endif; ?>
            data-view-group="releases"
            data-view-grid="/releases"
            data-view-table="/releases?view=table"
            data-view-list="/releases?view=list">
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
            href="/related"<?php if ($currentSection === '/related'): ?> aria-current="page"<?php endif; ?>
            data-view-group="related"
            data-view-grid="/related"
            data-view-table="/related?view=table"
            data-view-list="/related?view=list">
            <svg class="icons">
              <use href="icons.svg#lp"></use>
            </svg>
            Related
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="/calendar"<?php if ($currentSection === '/calendar'): ?> aria-current="page"<?php endif; ?>>
            <svg class="icons">
              <use href="icons.svg#calendar"></use>
            </svg>
            Calendar
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="/about"<?php if ($currentSection === '/about'): ?> aria-current="page"<?php endif; ?>>
            <svg class="icons">
              <use href="icons.svg#about"></use>
            </svg>
            About
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="/thanks"<?php if ($currentSection === '/thanks'): ?> aria-current="page"<?php endif; ?>>
            <svg class="icons">
              <use href="icons.svg#star"></use>
            </svg>
            Thanks
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="/stats"<?php if ($currentSection === '/stats'): ?> aria-current="page"<?php endif; ?>>
            <svg class="icons">
              <use href="icons.svg#cake_diagram"></use>
            </svg>
            Stats
          </a>
        </h1>
      </li>
      <li>
        <h1>
          <a href="/tree"<?php if ($currentSection === '/tree'): ?> aria-current="page"<?php endif; ?>>
            <svg class="icons">
              <use href="icons.svg#tree"></use>
            </svg>
            Tree
          </a>
        </h1>
      </li>
      <li>
        <h1><a href="/updates"<?php if ($currentSection === '/updates'): ?> aria-current="page"<?php endif; ?>><svg class="icons" aria-hidden="true"><use href="/icons.svg#github"></use></svg>Updates</a></h1>
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
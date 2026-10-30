<div id="settings-menu-container" class="no-swipe">
  <button id="toggle-settings-panel-button" class="settings-toggle" type="button" title="Settings" aria-label="Settings" aria-controls="settings-panel" aria-expanded="false">
    <svg class="icons" aria-hidden="true"><use href="icons.svg#settings"></use></svg>
  </button>

  <div id="settings-panel" class="settings-panel no-swipe" role="region" aria-label="Settings" hidden>
    <div class="settings-panel-scroll">
      <?php require __DIR__ . '/settings-controls.php'; ?>
    </div>
    <div class="toc-close-wrap">
      <button id="toggle-settings-panel-close-button" class="settings-toggle" type="button" title="Close" aria-label="Close settings">
        <svg class="icons" aria-hidden="true"><use href="icons.svg#close"></use></svg>
      </button>
    </div>
  </div>
</div>

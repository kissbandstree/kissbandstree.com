<div id="settings-menu-container" class="no-swipe">
  <button id="toggle-settings-panel-button" class="settings-toggle" type="button" title="Settings" aria-label="Settings" aria-controls="settings-panel" aria-expanded="false">
    <svg class="icons" aria-hidden="true"><use href="icons.svg#settings"></use></svg>
  </button>

  <div id="settings-panel" class="settings-panel no-swipe" role="region" aria-label="Settings" hidden>
    <div class="settings-panel-scroll">
      <button id="toggle-mode-button" class="toc-button" type="button" title="Toggle dark mode" aria-pressed="false">
        <svg class="icons" aria-hidden="true"><use href="icons.svg#moon"></use></svg>
        Dark mode
      </button>
      <button id="toggle-fullscreen-button" class="toc-button" type="button" title="Toggle full screen" aria-pressed="false">
        <svg class="icons" aria-hidden="true"><use href="icons.svg#full_screen"></use></svg>
        Fullscreen
      </button>
      <button id="share-settings-button" class="toc-button" type="button" title="Share">
        <svg class="icons" aria-hidden="true"><use href="icons.svg#share"></use></svg>
        Share
      </button>
      <button id="reset-settings-button" class="toc-button" type="button" title="Reset to default">
        <svg class="icons" aria-hidden="true"><use href="icons.svg#reset"></use></svg>
        Reset
      </button>
      <p id="settings-feedback" role="status" hidden></p>
    </div>
    <div class="toc-close-wrap">
      <button id="toggle-settings-panel-close-button" class="settings-toggle" type="button" title="Close" aria-label="Close settings">
        <svg class="icons" aria-hidden="true"><use href="icons.svg#close"></use></svg>
      </button>
    </div>
  </div>
</div>

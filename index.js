// Kiss Bands Tree
// https://kissbandstree.com/index.js

function isDesktopSidebarLayout() {
  return window.matchMedia('(min-width: 800px)').matches;
}

function syncSidebarState() {
  const toc = document.getElementById('toc');
  const panel = document.getElementById('settings-panel');
  const tocOpen = toc?.classList.contains('toc-open') || false;
  const settingsOpen = panel?.classList.contains('settings-panel-open') || false;

  if (panel) panel.hidden = !settingsOpen;
  document.getElementById('toggle-menu-button')?.setAttribute('aria-expanded', String(tocOpen));
  document.getElementById('toggle-settings-panel-button')?.setAttribute('aria-expanded', String(settingsOpen));
  document.body.classList.toggle('toc-sidebar-open', tocOpen);
  document.body.classList.toggle('settings-sidebar-open', settingsOpen);
  document.body.classList.toggle('toc-lock', tocOpen && !isDesktopSidebarLayout());
  document.body.classList.toggle('settings-lock', settingsOpen && !isDesktopSidebarLayout());

  if (isDesktopSidebarLayout()) {
    if (toc) localStorage.setItem('global.toc_sidebar_open', String(tocOpen));
    if (panel) localStorage.setItem('global.settings_sidebar_open', String(settingsOpen));
  }
}

function restoreDesktopSidebarState() {
  const toc = document.getElementById('toc');
  const panel = document.getElementById('settings-panel');
  const desktop = isDesktopSidebarLayout();
  const savedTocOpen = localStorage.getItem('global.toc_sidebar_open');
  const savedSettingsOpen = localStorage.getItem('global.settings_sidebar_open');

  toc?.classList.toggle('toc-open', desktop && (savedTocOpen === null || savedTocOpen === 'true'));
  panel?.classList.toggle('settings-panel-open', desktop && (savedSettingsOpen === null || savedSettingsOpen === 'true'));
  syncSidebarState();
}

function toggleMenu() {
  const toc = document.getElementById('toc');
  if (!toc) return; // Skip if TOC is missing

  if (!isDesktopSidebarLayout()) {
    document.getElementById('settings-panel')?.classList.remove('settings-panel-open');
  }
  toc.classList.toggle('toc-open');
  syncSidebarState();
}

function closeMenu() {
  const toc = document.getElementById('toc');
  if (!toc) return;

  toc.classList.remove('toc-open');
  syncSidebarState();
}

function closeSettingsPanel() {
  const panel = document.getElementById('settings-panel');
  if (!panel) return;

  panel.classList.remove('settings-panel-open');
  syncSidebarState();
}

function toggleSettingsPanel() {
  const panel = document.getElementById('settings-panel');
  if (!panel) return;

  if (!isDesktopSidebarLayout()) {
    document.getElementById('toc')?.classList.remove('toc-open');
  }
  panel.classList.toggle('settings-panel-open');
  syncSidebarState();
}

document.addEventListener('click', function (event) {
  if (!event.isTrusted || isDesktopSidebarLayout()) return;
  const container = document.getElementById('settings-menu-container');
  if (container && !container.contains(event.target)) closeSettingsPanel();
});

document.addEventListener('keydown', function (event) {
  const panel = document.getElementById('settings-panel');
  if (event.key === 'Escape' && !isDesktopSidebarLayout() && panel && !panel.hidden) {
    closeSettingsPanel();
    document.getElementById('toggle-settings-panel-button').focus();
  }
});

// Function to close the TOC when clicked outside

document.addEventListener('click', function (event) {
  if (!event.isTrusted || isDesktopSidebarLayout()) return;
  const toc = document.getElementById('toc');
  const burger = document.getElementById('burger-container');

  // If TOC doesn't exist, skip
  if (!toc) return;

  const isClickInsideToc = toc && toc.contains(event.target);
  const isClickOnBurger = burger && burger.contains(event.target);

  if (!isClickInsideToc && !isClickOnBurger && toc.classList.contains('toc-open')) {
    closeMenu();
  }
});

// Function to close the TOC when ESC key is pressed

document.addEventListener('keydown', function (event) {
  const toc = document.getElementById('toc');
  if (!toc) return;

  if (event.key === 'Escape' && !isDesktopSidebarLayout() && toc.classList.contains('toc-open')) {
    closeMobileToc();
  }
});

function closeMobileToc() {
  if (!isDesktopSidebarLayout()) closeMenu();
}

window.addEventListener('pagehide', closeMobileToc);
window.addEventListener('pageshow', closeMobileToc);
window.addEventListener('resize', restoreDesktopSidebarState);
document.addEventListener('DOMContentLoaded', restoreDesktopSidebarState);

// Close TOC when a TOC link is activated
document.addEventListener('DOMContentLoaded', function () {
  const toc = document.getElementById('toc');
  if (!toc) return;

  toc.addEventListener('click', function (event) {
    if (event.target && event.target.closest('a')) {
      closeMobileToc();
    }
  });
});

// Function to collapse/expand DIVs and save state

function toggleDiv(containerId) {
  const contentContainer = document.getElementById(containerId);
  if (!contentContainer) return;

  contentContainer.classList.toggle('collapsed');

  // Save state to localStorage
  const isCollapsed = contentContainer.classList.contains('collapsed');
  localStorage.setItem(`divState_${containerId}`, isCollapsed ? 'collapsed' : 'expanded');
}

// Restore collapsed/expanded states on page load
document.addEventListener('DOMContentLoaded', function () {
  // Existing code ...

  // Restore toggleDiv states
  document.querySelectorAll('[id]').forEach(el => {
    const state = localStorage.getItem(`divState_${el.id}`);
    if (state === 'collapsed') {
      el.classList.add('collapsed');
    } else if (state === 'expanded') {
      el.classList.remove('collapsed');
    }
  });
});

// Function to count rows in tables

function CountRows() {
  const rows = Array.from(document.querySelectorAll('#filter_table tr')).slice(1);
  const visibleCount = rows.filter(row => row.offsetParent !== null).length;
  const target = document.getElementById('rows_shown');
  if (target) target.textContent = String(visibleCount);
}

// Function to toggle light mode/dark mode

function toggleMode() {
  const htmlElement = document.documentElement;
  htmlElement.classList.toggle('invert-colors');

  if (htmlElement.classList.contains('invert-colors')) {
    localStorage.setItem('mode', 'dark');
  } else {
    localStorage.setItem('mode', 'light');
  }
  updateModeButtonState();
}

function updateModeButtonState() {
  const button = document.getElementById('toggle-mode-button');
  if (!button) return;
  const active = !document.documentElement.classList.contains('invert-colors');
  button.classList.toggle('button-on', active);
  button.setAttribute('aria-pressed', String(active));
}

function updateFullscreenButtonState() {
  const button = document.getElementById('toggle-fullscreen-button');
  if (!button) return;
  const active = Boolean(document.fullscreenElement);
  button.classList.toggle('button-on', active);
  button.setAttribute('aria-pressed', String(active));
}

function showSettingsFeedback(message) {
  const feedback = document.getElementById('settings-feedback');
  if (!feedback) return;
  feedback.textContent = message;
  feedback.hidden = false;
}

async function toggleFullscreen() {
  try {
    if (!document.fullscreenElement) {
      await document.documentElement.requestFullscreen();
    } else {
      await document.exitFullscreen();
    }
  } catch (error) {
    console.error('Unable to change fullscreen mode', error);
    showSettingsFeedback('Unable to change fullscreen mode.');
  }
}

document.addEventListener('fullscreenchange', updateFullscreenButtonState);

async function shareCurrentPage() {
  const shareUrl = window.location.href;
  const shareTitle = document.title || 'Kiss Bands Tree';

  if (navigator.share) {
    try {
      await navigator.share({
        title: shareTitle,
        text: shareTitle,
        url: shareUrl
      });
      return;
    } catch (error) {
      if (error && error.name === 'AbortError') return;
      console.warn('Unable to share page', error);
    }
  }

  if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
    try {
      await navigator.clipboard.writeText(shareUrl);
      showSettingsFeedback('Link copied.');
      return;
    } catch (error) {
      console.warn('Unable to copy share URL', error);
    }
  }

  window.prompt('Copy this link:', shareUrl);
}

function resetSettings() {
  localStorage.clear();
  const url = new URL(window.location.href);
  url.pathname = url.pathname.replace(/_(?:table(?:_detailed)?|list)\.php$/i, '_grid.php');
  window.location.replace(url.href);
}

function updateTreeVisibility() {
  for (const option of ['photos', 'comments']) {
    const button = document.getElementById(`toggle-${option}-button`);
    if (!button) continue;

    const visible = localStorage.getItem(`tree.${option}`) !== 'false';
    document.body.classList.toggle(`tree-${option}-hidden`, !visible);
    button.classList.toggle('button-on', visible);
    button.setAttribute('aria-pressed', String(visible));
  }
}

function toggleTreeVisibility(option) {
  const key = `tree.${option}`;
  const visible = localStorage.getItem(key) !== 'false';
  localStorage.setItem(key, String(!visible));
  updateTreeVisibility();
}

document.addEventListener('DOMContentLoaded', function () {
  const actions = {
    'toggle-settings-panel-button': toggleSettingsPanel,
    'toggle-settings-panel-close-button': () => {
      closeSettingsPanel();
      document.getElementById('toggle-settings-panel-button').focus();
    },
    'toggle-mode-button': toggleMode,
    'toggle-fullscreen-button': toggleFullscreen,
    'share-settings-button': shareCurrentPage,
    'reset-settings-button': resetSettings,
    'toggle-photos-button': () => toggleTreeVisibility('photos'),
    'toggle-comments-button': () => toggleTreeVisibility('comments')
  };
  Object.entries(actions).forEach(([id, action]) => {
    const button = document.getElementById(id);
    if (button) button.addEventListener('click', action);
  });
  const fullscreenButton = document.getElementById('toggle-fullscreen-button');
  if (fullscreenButton) {
    fullscreenButton.disabled = !document.documentElement.requestFullscreen || document.fullscreenEnabled === false;
  }
  updateModeButtonState();
  updateFullscreenButtonState();
  updateTreeVisibility();
});

// Check for mode preference on page load

document.addEventListener('DOMContentLoaded', function () {

  const mode = localStorage.getItem('mode');
  if (mode === 'dark') {
    document.documentElement.classList.add('invert-colors');
  }
  updateModeButtonState();

  // Add artist-name class to all artist.php links

  document.querySelectorAll('a[href*="artist.php?a="], a[href*="band.php?a="]').forEach(a => {
    if (a.querySelector('img, svg') || a.textContent.trim() === '') {
      return;
    }

    a.classList.add('artist-name');
  });

  // Hover effect across all artist-name elements

  const artistElements = document.querySelectorAll('.artist-name');
  artistElements.forEach(el => {
    const name = el.textContent.trim();

    el.addEventListener('mouseenter', () => {
      artistElements.forEach(element => {
        if (element.textContent.trim() === name) {
          element.classList.add('artist-hover');
        }
      });
    });

    el.addEventListener('mouseleave', () => {
      artistElements.forEach(element => {
        if (element.textContent.trim() === name) {
          element.classList.remove('artist-hover');
        }
      });
    });
  });

});

// Enter key functionality for icons

document.addEventListener('keydown', function (event) {
  if (event.key === 'Enter' && document.activeElement) {
    const focusedElement = document.activeElement;
    if (focusedElement.hasAttribute('onclick') && focusedElement.tagName !== 'BUTTON') {
      focusedElement.click();
    }
  }
});

// Focus a lineup on tree.php when arriving with ?focus=<band-slug>
function focusBandFromUrl() {
  const params = new URLSearchParams(window.location.search);
  const slug = (params.get('focus') || '').trim();
  if (!slug) return;

  const el = document.getElementById('band-' + slug);
  if (!el) return;

  // Scroll the band into view (works for vertical + horizontal where applicable)
  el.scrollIntoView({
    behavior: 'smooth',
    block: 'center',
    inline: 'center'
  });

  // Brief visual highlight
  el.classList.add('focus-flash');
  window.setTimeout(() => el.classList.remove('focus-flash'), 2400);
}

document.addEventListener('DOMContentLoaded', function () {
  focusBandFromUrl();
});

// Keep grid/table/list preference consistent across sections
function getViewModeFromPage(pageName) {
  if (/_grid\.php$/i.test(pageName)) return 'grid';
  if (/_table(?:_detailed)?\.php$/i.test(pageName)) return 'table';
  if (/_list\.php$/i.test(pageName)) return 'list';
  return null;
}

function getModeUrlFromLink(link, mode) {
  if (!link || !mode) return null;
  const value = link.dataset['view' + mode.charAt(0).toUpperCase() + mode.slice(1)];
  return value || null;
}

function nextMode(mode) {
  if (mode === 'grid') return 'table';
  if (mode === 'table') return 'list';
  return 'grid';
}

function applyPreferredViews() {
  const key = 'kbt_view_mode';
  const currentPage = window.location.pathname.split('/').pop() || '';
  const currentMode = getViewModeFromPage(currentPage);
  const savedMode = localStorage.getItem(key);

  if (currentMode) {
    localStorage.setItem(key, currentMode);
  }

  const preferredMode = currentMode || savedMode || 'grid';
  const links = document.querySelectorAll('a[data-view-group]');

  links.forEach(link => {
    const gridPage = link.dataset.viewGrid;
    const tablePage = link.dataset.viewTable;
    const listPage = link.dataset.viewList;
    const isViewSwitch = link.getAttribute('title') === 'View';
    const pages = [gridPage, tablePage, listPage].filter(Boolean);
    const pageMode = pages.includes(currentPage) ? getViewModeFromPage(currentPage) : null;

    if (isViewSwitch) {
      const targetMode = pageMode ? nextMode(pageMode) : preferredMode;
      const targetUrl = getModeUrlFromLink(link, targetMode) || link.getAttribute('href');
      if (targetUrl) {
        link.setAttribute('href', targetUrl);
      }

      link.addEventListener('click', event => {
        const freshMode = pageMode ? nextMode(pageMode) : (localStorage.getItem(key) || preferredMode);
        const clickTargetUrl = getModeUrlFromLink(link, freshMode) || link.getAttribute('href');
        localStorage.setItem(key, freshMode);

        if (clickTargetUrl) {
          event.preventDefault();
          window.location.href = clickTargetUrl;
        }
      });
      return;
    }

    const targetUrl = getModeUrlFromLink(link, preferredMode) || getModeUrlFromLink(link, 'grid') || link.getAttribute('href');
    if (targetUrl) {
      link.setAttribute('href', targetUrl);
    }

    link.addEventListener('click', event => {
      const clickMode = localStorage.getItem(key) || preferredMode;
      const clickTargetUrl = getModeUrlFromLink(link, clickMode) || getModeUrlFromLink(link, 'grid') || link.getAttribute('href');
      if (clickTargetUrl) {
        event.preventDefault();
        window.location.href = clickTargetUrl;
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', function () {
  applyPreferredViews();
});

function enableTreeDragPan() {
  const page = (window.location.pathname.split('/').pop() || '').toLowerCase();
  if (page !== 'tree.php') return;

  const isTextTarget = (target) => {
    if (!(target instanceof Element)) return false;
    return target.closest('p, span, h1, h2, h3, h4, h5, h6, li, td, th, pre, code, .text, .lineup-item, .band-header, .band-comment, .artist-header-name, .footer') !== null;
  };

  let pointerId = null;
  let startX = 0;
  let startY = 0;
  let startScrollX = 0;
  let startScrollY = 0;
  let isPointerDown = false;
  let isDragging = false;
  let suppressClick = false;
  const dragThreshold = 4;

  document.addEventListener('pointerdown', function (event) {
    if (event.pointerType === 'mouse' && event.button !== 0) return;
    if (event.target instanceof Element && event.target.closest('a, button, input, select, textarea, #settings-menu-container, .tree-controls')) return;
    if (isTextTarget(event.target)) return;

    pointerId = event.pointerId;
    startX = event.clientX;
    startY = event.clientY;
    startScrollX = window.scrollX;
    startScrollY = window.scrollY;
    isPointerDown = true;
    isDragging = false;
    document.body.classList.add('drag-pan-lock');
  }, { passive: false });

  document.addEventListener('pointermove', function (event) {
    if (!isPointerDown || event.pointerId !== pointerId) return;

    const deltaX = event.clientX - startX;
    const deltaY = event.clientY - startY;

    if (!isDragging) {
      if (Math.abs(deltaX) < dragThreshold && Math.abs(deltaY) < dragThreshold) return;
      isDragging = true;
      suppressClick = true;

      const selection = window.getSelection();
      if (selection && selection.rangeCount > 0) {
        selection.removeAllRanges();
      }
    }

    window.scrollTo(startScrollX - deltaX, startScrollY - deltaY);
    event.preventDefault();
  }, { passive: false });

  const stopDragging = (event) => {
    if (!isPointerDown || event.pointerId !== pointerId) return;
    isPointerDown = false;
    isDragging = false;
    pointerId = null;
    document.body.classList.remove('drag-pan-lock');
  };

  document.addEventListener('pointerup', stopDragging);
  document.addEventListener('pointercancel', stopDragging);

  document.addEventListener('dragstart', function (event) {
    if (!isPointerDown && !isDragging) return;
    event.preventDefault();
  }, true);

  document.addEventListener('click', function (event) {
    if (!suppressClick) return;
    suppressClick = false;
    event.preventDefault();
    event.stopPropagation();
  }, true);
}

document.addEventListener('DOMContentLoaded', function () {
  enableTreeDragPan();
});

// END OF FILE

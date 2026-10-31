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
  if (['/bands', '/artists', '/releases', '/related'].includes(url.pathname)) {
    url.searchParams.delete('view');
  } else {
    url.pathname = url.pathname.replace(/_(?:table(?:_detailed)?|list)\.php$/i, '_grid.php');
  }
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

  // Hover effect across matching artist names and arrows

  const hoverElements = document.querySelectorAll('.artist-name, .arrow[data-artist-name]');
  hoverElements.forEach(el => {
    const name = el.dataset.artistName ?? el.textContent.trim();

    el.addEventListener('mouseenter', () => {
      hoverElements.forEach(element => {
        if ((element.dataset.artistName ?? element.textContent.trim()) === name) {
          element.classList.add('artist-hover');
        }
      });
    });

    el.addEventListener('mouseleave', () => {
      hoverElements.forEach(element => {
        if ((element.dataset.artistName ?? element.textContent.trim()) === name) {
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

// Focus a lineup when arriving with ?focus=<band-slug>
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
  if (['bands', 'artists', 'releases', 'related'].includes(pageName)) {
    return new URLSearchParams(window.location.search).get('view') || 'grid';
  }
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
    const pageMode = pages.some(page => new URL(page, window.location.origin).pathname === window.location.pathname)
      ? currentMode : null;

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
  if (page !== 'tree' && page !== 'tree.php') return;

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

function setupResizableTables() {
  document.querySelectorAll('table').forEach((table) => {
    if (table.dataset.resizableColumns === 'true') return;
    if (!table.tHead || !table.tHead.querySelector('th')) return;

    const getVisibleHeaders = () => [...table.querySelectorAll('thead th')]
      .filter((header) => getComputedStyle(header).display !== 'none');
    const tablePosition = [...document.querySelectorAll('table')].indexOf(table);
    const columnWidthsStorageKey = `table.column_widths:${window.location.pathname}:${tablePosition}:${table.id || table.className}`;
    let resizingHeader = null;
    let startX = 0;
    let startWidth = 0;
    let lastTouchResizeTap = null;
    let resizeMoved = false;
    let clickSuppressionHeader = null;
    let clickSuppressionTimer = null;

    const getResizeEdgeWidth = (event) => event.pointerType === 'touch' ? 24 : 8;
    const saveColumnWidths = () => {
      const widths = getVisibleHeaders().map((header) => Number(header.dataset.columnWidth));
      localStorage.setItem(columnWidthsStorageKey, JSON.stringify(widths));
    };
    const suppressHeaderClick = (header) => {
      clickSuppressionHeader = header;
      window.clearTimeout(clickSuppressionTimer);
      clickSuppressionTimer = window.setTimeout(() => {
        clickSuppressionHeader = null;
        clickSuppressionTimer = null;
      }, 750);
    };
    const applyColumnWidths = (headers) => {
      const totalWidth = headers.reduce((total, header) => total + Number(header.dataset.columnWidth), 0);
      if (totalWidth <= 0) return;

      headers.forEach((header) => {
        const percentage = 100 * Number(header.dataset.columnWidth) / totalWidth;
        if (!header.style.width.endsWith('%')
          || Math.abs(parseFloat(header.style.width) - percentage) > 0.00001) {
          header.style.width = `${percentage}%`;
        }
      });
      table.style.width = '100%';
    };

    const syncColumnWidths = () => {
      if (resizingHeader) return;

      const headers = getVisibleHeaders();
      if (!headers.length) return;

      headers.forEach((header) => {
        const width = Math.round(header.getBoundingClientRect().width);
        if (width > 0 && !header.dataset.defaultColumnWidth) {
          header.dataset.defaultColumnWidth = String(width);
        }
        if (width > 0 && !header.dataset.columnWidth) {
          header.dataset.columnWidth = String(width);
        }
      });
      applyColumnWidths(headers);
    };

    const restoreColumnWidths = () => {
      const savedWidths = localStorage.getItem(columnWidthsStorageKey);
      if (savedWidths === null) return;

      let widths;
      try {
        widths = JSON.parse(savedWidths);
      } catch (error) {
        console.warn('Unable to restore saved table column widths', error);
        return;
      }

      const headers = getVisibleHeaders();
      if (!Array.isArray(widths) || widths.length !== headers.length
        || widths.some((width) => !Number.isInteger(width) || width < 48)) {
        console.warn('Ignoring invalid saved table column widths', columnWidthsStorageKey);
        return;
      }

      headers.forEach((header, index) => {
        header.dataset.columnWidth = String(widths[index]);
      });
      applyColumnWidths(headers);
    };

    const setResizingColumn = (header, isResizing) => {
      const columnIndex = getVisibleHeaders().indexOf(header);
      if (columnIndex < 0) return;

      [...table.rows].forEach((row) => {
        if (row.parentElement?.tagName === 'TFOOT') return;
        row.cells[columnIndex]?.classList.toggle('column-resizing', isResizing);
      });
    };

    const setColumnWidth = (header, requestedWidth) => {
      const headers = getVisibleHeaders();
      const columnIndex = headers.indexOf(header);
      if (columnIndex < 0) return;

      const widths = headers.map((visibleHeader) => {
        return Math.round(visibleHeader.getBoundingClientRect().width);
      });
      const totalWidth = widths.reduce((total, width) => total + width, 0);
      const maximumWidth = totalWidth - (widths.length - 1) * 48;
      const width = Math.min(maximumWidth, Math.max(48, requestedWidth));
      let remainingChange = width - widths[columnIndex];
      widths[columnIndex] = width;

      const otherColumns = headers
        .map((_, index) => index)
        .filter((index) => index !== columnIndex)
        .sort((left, right) => {
          const distance = Math.abs(left - columnIndex) - Math.abs(right - columnIndex);
          if (distance !== 0) return distance;
          return left > columnIndex ? -1 : 1;
        });

      for (const index of otherColumns) {
        if (remainingChange > 0) {
          const change = Math.min(remainingChange, widths[index] - 48);
          widths[index] -= change;
          remainingChange -= change;
        } else if (remainingChange < 0) {
          widths[index] -= remainingChange;
          remainingChange = 0;
        }
        if (remainingChange === 0) break;
      }

      headers.forEach((visibleHeader, index) => {
        visibleHeader.dataset.columnWidth = String(Math.round(widths[index]));
      });
      applyColumnWidths(headers);
    };

    const finishResize = () => {
      if (!resizingHeader) return;
      setResizingColumn(resizingHeader, false);
      resizingHeader = null;
      if (resizeMoved) saveColumnWidths();
    };

    const resetColumnWidth = (header) => {
      const defaultWidth = Number(header.dataset.defaultColumnWidth);
      if (!Number.isFinite(defaultWidth) || defaultWidth <= 0) return;

      setColumnWidth(header, defaultWidth);
      saveColumnWidths();
    };

    table.addEventListener('pointermove', (event) => {
      if (resizingHeader) {
        if (Math.abs(event.clientX - startX) > 3) resizeMoved = true;
        if (!resizeMoved) return;
        setColumnWidth(resizingHeader, startWidth + event.clientX - startX);
        return;
      }

      const header = event.target.closest?.('th');
      table.querySelectorAll('.column-resize-target').forEach((target) => {
        target.classList.remove('column-resize-target');
      });
      if (!header || !table.tHead?.contains(header)) return;

      const bounds = header.getBoundingClientRect();
      if (event.clientX >= bounds.right - getResizeEdgeWidth(event)) {
        header.classList.add('column-resize-target');
      }
    });

    table.addEventListener('pointerleave', () => {
      if (resizingHeader) return;
      table.querySelectorAll('.column-resize-target').forEach((header) => {
        header.classList.remove('column-resize-target');
      });
    });

    table.addEventListener('pointerdown', (event) => {
      if (event.button !== 0) return;

      const header = event.target.closest?.('th');
      if (!header || !table.tHead?.contains(header)) return;

      const bounds = header.getBoundingClientRect();
      if (event.clientX < bounds.right - getResizeEdgeWidth(event)) return;

      event.preventDefault();
      suppressHeaderClick(header);
      resizingHeader = header;
      startX = event.clientX;
      startWidth = bounds.width;
      resizeMoved = false;
      setResizingColumn(header, true);
      header.setPointerCapture(event.pointerId);
    });

    table.addEventListener('pointerup', (event) => {
      const header = resizingHeader;
      finishResize();
      if (!header) return;

      suppressHeaderClick(header);
      if (event.pointerType === 'touch') {
        if (resizeMoved) {
          lastTouchResizeTap = null;
        } else {
          const now = event.timeStamp;
          const isDoubleTap = lastTouchResizeTap?.header === header && now - lastTouchResizeTap.time < 350;
          lastTouchResizeTap = { header, time: now };
          if (isDoubleTap) {
            resetColumnWidth(header);
            lastTouchResizeTap = null;
          }
        }
      }
    });
    table.addEventListener('pointercancel', () => {
      finishResize();
      clickSuppressionHeader = null;
      window.clearTimeout(clickSuppressionTimer);
      clickSuppressionTimer = null;
    });
    table.addEventListener('lostpointercapture', finishResize);
    table.addEventListener('click', (event) => {
      if (!clickSuppressionHeader || event.target.closest?.('th') !== clickSuppressionHeader) return;

      event.preventDefault();
      event.stopImmediatePropagation();
      clickSuppressionHeader = null;
      window.clearTimeout(clickSuppressionTimer);
      clickSuppressionTimer = null;
    }, true);

    table.addEventListener('dblclick', (event) => {
      const header = event.target.closest?.('th');
      if (!header || !table.tHead?.contains(header)) return;

      const bounds = header.getBoundingClientRect();
      if (event.clientX < bounds.right - getResizeEdgeWidth(event)) return;

      event.preventDefault();
      resetColumnWidth(header);
    });

    table.dataset.resizableColumns = 'true';
    table.classList.add('resizable-columns');
    syncColumnWidths();
    restoreColumnWidths();

    const observer = new MutationObserver(() => {
      window.requestAnimationFrame(syncColumnWidths);
    });
    observer.observe(table.tHead, {
      attributes: true,
      attributeFilter: ['style'],
      childList: true,
      subtree: true
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', setupResizableTables, { once: true });
} else {
  setupResizableTables();
}

// END OF FILE

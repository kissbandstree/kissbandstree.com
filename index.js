// Kiss Bands Tree
// https://kissbandstree.com/index.js

// Function to toggle the TOC visibility

function toggleMenu() {
  const toc = document.getElementById('toc');
  if (!toc) return; // Skip if TOC is missing

  const isOpen = toc.classList.toggle('toc-open');
  document.body.classList.toggle('toc-lock', isOpen);
}

function closeMenu() {
  const toc = document.getElementById('toc');
  if (!toc) return;

  toc.classList.remove('toc-open');
  document.body.classList.remove('toc-lock');
}

// Function to close the TOC when clicked outside

document.addEventListener('click', function (event) {
  const toc = document.getElementById('toc');
  const burger = document.querySelector('.burgerIcon');

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

  if (event.key === 'Escape' && toc.classList.contains('toc-open')) {
    closeMenu();
  }
});

// Close TOC when a TOC link is activated
document.addEventListener('DOMContentLoaded', function () {
  const toc = document.getElementById('toc');
  if (!toc) return;

  toc.addEventListener('click', function (event) {
    if (event.target && event.target.closest('a')) {
      closeMenu();
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
}

// Check for mode preference on page load

document.addEventListener('DOMContentLoaded', function () {

  const mode = localStorage.getItem('mode');
  if (mode === 'dark') {
    document.documentElement.classList.add('invert-colors');
  }

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
    if (focusedElement.hasAttribute('onclick') || focusedElement.tagName === 'BUTTON') {
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

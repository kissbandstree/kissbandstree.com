const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const root = path.join(__dirname, '..', '..');
const source = fs.readFileSync(path.join(root, 'swipe.js'), 'utf8');
const menu = fs.readFileSync(path.join(root, 'burger.php'), 'utf8');
const origin = 'http://localhost:8003';

function createSwipe(url, mode = 'grid', hasHeader = true) {
  const handlers = {};
  const links = [...menu.matchAll(/<a\s+([\s\S]*?)>/g)].map(match => {
    const attributes = Object.fromEntries([...match[1].matchAll(/([\w-]+)="([^"]*)"/g)]
      .map(attribute => [attribute[1], attribute[2]]));
    const destination = attributes[`data-view-${mode}`] || attributes.href;
    return {
      href: new URL(destination, origin).href,
      dataset: {
        viewGrid: attributes['data-view-grid'],
        viewTable: attributes['data-view-table'],
        viewList: attributes['data-view-list']
      }
    };
  });
  const home = { href: origin + '/', dataset: {} };
  const header = {
    style: {},
    querySelector: selector => selector === '.logo-link' ? home : null,
    addEventListener(name, handler) { handlers[name] = handler; }
  };
  const location = new URL(url, origin);
  const document = {
    querySelector: selector => selector === '.heading' && hasHeader ? header : null,
    querySelectorAll: selector => selector === '#toc a[href]' ? links : [],
    addEventListener(name, callback) {
      assert.equal(name, 'DOMContentLoaded');
      callback();
    }
  };
  vm.runInNewContext(source, { window: { location }, document, URL });

  function swipe(direction, target = { closest: () => null }) {
    const end = direction === 'forward' ? -100 : 100;
    for (const type of ['pointerdown', 'pointermove', 'pointerup']) {
      handlers[type]?.({
        pointerId: 1, pointerType: 'touch', button: 0, target,
        clientX: type === 'pointerdown' ? 0 : end, clientY: 0,
        preventDefault() {}
      });
    }
    return location.pathname + location.search;
  }
  return { location, header, handlers, swipe };
}

test('main-menu swipes wrap through Home and skip Tree in both directions', () => {
  const pages = ['/', '/bands', '/artists', '/releases',
    '/related', '/calendar', '/about', '/thanks', '/stats'];
  for (let index = 0; index < pages.length; index++) {
    assert.equal(createSwipe(pages[index]).swipe('forward'), pages[(index + 1) % pages.length]);
    assert.equal(createSwipe(pages[index]).swipe('backward'),
      pages[(index - 1 + pages.length) % pages.length]);
  }
  assert.equal(createSwipe('/index.php').swipe('forward'), '/bands');
});

test('Grid, Table and List navigation follows the existing view preference', () => {
  for (const mode of ['grid', 'table', 'list']) {
    const sections = ['bands', 'artists', 'releases', 'related'];
    const query = mode === 'grid' ? '' : `?view=${mode}`;
    for (let index = 0; index < sections.length - 1; index++) {
      const current = `/${sections[index]}${query}`;
      const next = `/${sections[index + 1]}${query}`;
      assert.equal(createSwipe(current, mode).swipe('forward'), next);
      assert.equal(createSwipe(next, mode).swipe('backward'), current);
    }
    assert.equal(createSwipe('/', mode).swipe('forward'), `/bands${query}`);
  }
});

test('detail pages and detailed artist table use their parent navigation section', () => {
  for (const mode of ['grid', 'table', 'list']) {
    const query = mode === 'grid' ? '' : `?view=${mode}`;
    for (const [page, next] of [
      ['/band.php?a=shogun_mojo_2', `/artists${query}`],
      ['/artist.php?a=tommy_thayer', `/releases${query}`],
      ['/release_band.php?a=kiss', `/related${query}`],
      ['/release_related.php?a=shogun_mojo', '/calendar'],
      ['/artists_table_detailed.php', `/releases${query}`]
    ]) {
      assert.equal(createSwipe(page, mode).swipe('forward'), next);
    }
  }
});

test('Calendar has a main-menu link with its calendar icon', () => {
  assert.match(menu, /<a href="\/calendar">\s*<svg class="icons">\s*<use href="icons\.svg#calendar"><\/use>\s*<\/svg>\s*Calendar\s*<\/a>/);
});

test('Tree and pages without a header attach no swipe handlers', () => {
  const tree = createSwipe('/tree', 'grid', false);
  assert.deepEqual(Object.keys(tree.handlers), []);
  assert.equal(tree.swipe('forward'), '/tree');
  assert.equal(createSwipe('/tree').swipe('forward'), '/tree');
  const treeSource = fs.readFileSync(path.join(root, 'tree.php'), 'utf8');
  assert.doesNotMatch(treeSource, /src="swipe\.js/);
});

test('header controls, their SVG children and the open menu never trigger navigation', () => {
  assert.match(menu, /id="toc" class="toc-container no-swipe"/);
  for (const selector of ['a:not([data-swipe-link])', '[onclick]', "[role='button']", '.no-swipe']) {
    const swipe = createSwipe('/');
    assert.equal(swipe.swipe('forward', {
      closest: selectors => selectors.split(', ').includes(selector) ? {} : null
    }), '/');
  }
});

test('header logo and its SVG children allow swipes while retaining a Home link', () => {
  const toc = fs.readFileSync(path.join(root, 'toc.php'), 'utf8');
  assert.match(toc, /<a href="\/" class="logo-link logo-link-small" data-swipe-link draggable="false">/);
  for (const direction of ['forward', 'backward']) {
    const swipe = createSwipe('/artists');
    const logo = {};
    assert.equal(swipe.swipe(direction, {
      closest: selector => selector === 'a[data-swipe-link]' ? logo : null
    }), direction === 'forward' ? '/releases' : '/bands');
  }
});

test('runtime footer loads exactly one local versioned swipe script', () => {
  const footer = fs.readFileSync(path.join(root, 'footer.php'), 'utf8');
  assert.match(footer, /src="swipe\.js\?v=<\?php echo filemtime\(/);
  assert.equal([...footer.matchAll(/src="[^"]*swipe[^"]*"/g)].length, 1);
});

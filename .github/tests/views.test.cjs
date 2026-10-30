const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'index.js'), 'utf8');
const viewSource = source.slice(source.indexOf('function getViewModeFromPage'), source.indexOf('function enableTreeDragPan'));
const origin = 'http://localhost:8003';
const sections = ['bands', 'artists', 'releases', 'related'];
const destination = (section, mode) => `/${section}${mode === 'grid' ? '' : `?view=${mode}`}`;

function createViews(current, saved = null, switchSection = null) {
  const storage = { mode: saved };
  let resolved = new URL(current, origin);
  const location = {
    get pathname() { return resolved.pathname; },
    get search() { return resolved.search; },
    get origin() { return resolved.origin; },
    get href() { return resolved.href; },
    set href(value) { resolved = new URL(value, resolved); }
  };
  function link(section, isSwitch) {
    const attributes = { href: `/${section}`, title: isSwitch ? 'View' : section };
    return {
      dataset: Object.fromEntries(['grid', 'table', 'list'].map(mode =>
        ['view' + mode[0].toUpperCase() + mode.slice(1), destination(section, mode)])),
      handlers: {},
      getAttribute: name => attributes[name],
      setAttribute(name, value) { attributes[name] = value; },
      addEventListener(name, callback) { this.handlers[name] = callback; }
    };
  }
  const links = sections.map(section => link(section, false));
  const viewSwitch = switchSection ? link(switchSection, true) : null;
  const allLinks = [...links, ...(viewSwitch ? [viewSwitch] : [])];
  vm.runInNewContext(viewSource, {
    URL, URLSearchParams, window: { location },
    localStorage: {
      getItem: () => storage.mode,
      setItem(key, value) { assert.equal(key, 'kbt_view_mode'); storage.mode = value; }
    },
    document: {
      querySelectorAll: () => allLinks,
      addEventListener(name, callback) { assert.equal(name, 'DOMContentLoaded'); callback(); }
    }
  });
  return { links, viewSwitch, storage, location };
}

test('all four clean sections remember and propagate their explicit Grid, Table and List views', () => {
  for (const section of sections) {
    for (const mode of ['grid', 'table', 'list']) {
      const setup = createViews(destination(section, mode), 'list');
      assert.equal(setup.storage.mode, mode);
      setup.links.forEach((link, index) => {
        assert.equal(link.getAttribute('href'), destination(sections[index], mode));
      });
    }
  }
});

test('clean view controls cycle Grid to Table to List to Grid', () => {
  for (const section of sections) {
    for (const [mode, next] of [['grid', 'table'], ['table', 'list'], ['list', 'grid']]) {
      const setup = createViews(destination(section, mode), null, section);
      assert.equal(setup.viewSwitch.getAttribute('href'), destination(section, next));
      let prevented = false;
      setup.viewSwitch.handlers.click({ preventDefault() { prevented = true; } });
      assert.equal(prevented, true);
      assert.equal(setup.location.pathname + setup.location.search, destination(section, next));
      assert.equal(setup.storage.mode, next);
    }
  }
});

test('Home, Calendar and detail pages preserve remembered navigation views', () => {
  for (const page of ['/', '/calendar', '/band.php?a=kiss_1', '/artist.php?a=paul_stanley']) {
    const setup = createViews(page, 'table');
    assert.equal(setup.storage.mode, 'table');
    setup.links.forEach((link, index) => {
      assert.equal(link.getAttribute('href'), destination(sections[index], 'table'));
    });
    setup.storage.mode = 'list';
    setup.links[0].handlers.click({ preventDefault() {} });
    assert.equal(setup.location.pathname + setup.location.search, '/bands?view=list');
  }
});

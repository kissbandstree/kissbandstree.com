const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'index.js'), 'utf8');
const resizeSource = source.slice(source.indexOf('function setupResizableTables()'), source.indexOf("if (document.readyState === 'loading')"));
const storageKey = 'table.column_widths:/artists:0:filter_table';

function createTable(saved, containerWidth = 1340) {
  const writes = [];
  const frames = [];
  const layout = { width: containerWidth };
  const handlers = {};
  const defaults = [50, 60, 260, 220, 220, 180, 150];
  const classList = () => ({
    add() {}, remove() {}, toggle() {}, contains() { return false; }
  });
  const table = {
    id: 'filter_table', className: 'artists-table', dataset: {}, style: {},
    classList: classList(), parentElement: { get clientWidth() { return layout.width; } },
    addEventListener(name, callback) { handlers[name] = callback; },
    getBoundingClientRect() { return { width: Math.max(1140, layout.width) }; },
    querySelectorAll(selector) { return selector === 'thead th' ? headers : []; }
  };
  const headers = defaults.map((defaultWidth, index) => ({
    dataset: {}, style: { width: '' }, classList: classList(),
    closest() { return this; },
    setPointerCapture() {},
    getBoundingClientRect() {
      const widthFor = (header, position) => header.style.width
        ? parseFloat(header.style.width) / 100 * table.getBoundingClientRect().width
        : defaults[position] / 1140 * table.getBoundingClientRect().width;
      const right = headers.slice(0, index + 1).reduce((total, header, position) => total + widthFor(header, position), 0);
      return { width: widthFor(this, index), right };
    }
  }));
  table.tHead = { querySelector: () => headers[0], contains: header => headers.includes(header) };
  table.rows = [{ cells: headers }];
  let stored = saved === undefined ? null : JSON.stringify(saved);
  let observer;
  vm.runInNewContext(`${resizeSource}\nsetupResizableTables();`, {
    document: { querySelectorAll: () => [table] },
    window: {
      location: { pathname: '/artists' }, clearTimeout() {}, setTimeout() { return 1; },
      requestAnimationFrame(callback) { frames.push(callback); }
    },
    getComputedStyle: () => ({ display: 'table-cell' }),
    localStorage: {
      getItem(key) { assert.equal(key, storageKey); return stored; },
      setItem(key, value) { assert.equal(key, storageKey); writes.push(value); stored = value; }
    },
    MutationObserver: class {
      constructor(callback) { observer = callback; }
      observe() {}
    },
    console
  });
  const event = (header, clientX, extra = {}) => ({
    target: header, clientX, pointerId: 1, pointerType: 'mouse', button: 0,
    preventDefault() {}, ...extra
  });
  return {
    table, headers, handlers, layout, writes, event,
    saved: () => JSON.parse(stored),
    widths: () => headers.map(header => header.getBoundingClientRect().width),
    sync() { observer(); while (frames.length) frames.shift()(); }
  };
}

test('restoring a narrower saved profile preserves its proportions and fills the container', () => {
  const profile = [50, 60, 78, 48, 201, 553, 150];
  const state = createTable(profile);
  state.sync();
  assert.deepEqual(state.headers.map(header => Number(header.dataset.columnWidth)), profile);
  assert.equal(state.table.style.width, '100%');
  assert.equal(state.writes.length, 0);
  profile.forEach((width, index) => {
    assert.ok(Math.abs(state.widths()[index] - width / 1140 * 1340) < 0.01);
  });
  state.layout.width = 1600;
  state.sync();
  assert.equal(state.table.getBoundingClientRect().width, 1600);
  assert.deepEqual(state.saved(), profile);
});

test('a drag writes nothing during movement and saves exactly once on release', () => {
  const state = createTable();
  const header = state.headers[2];
  const startX = header.getBoundingClientRect().right - 1;
  state.handlers.pointerdown(state.event(header, startX));
  for (const delta of [10, 20, 30, 40, 50]) {
    state.handlers.pointermove(state.event(header, startX + delta));
    assert.equal(state.writes.length, 0);
  }
  const widths = state.widths();
  state.handlers.pointerup(state.event(header, startX + 50));
  state.handlers.lostpointercapture();
  assert.equal(state.writes.length, 1);
  assert.equal(state.table.getBoundingClientRect().width, 1340);
  const reloaded = createTable(state.saved());
  reloaded.sync();
  widths.forEach((width, index) => assert.ok(Math.abs(width - reloaded.widths()[index]) < 0.01));
});

test('capture loss finalizes a drag once, while an edge tap does not save', () => {
  const state = createTable();
  const header = state.headers[2];
  const startX = header.getBoundingClientRect().right - 1;
  state.handlers.pointerdown(state.event(header, startX));
  state.handlers.pointerup(state.event(header, startX));
  assert.equal(state.writes.length, 0);
  state.handlers.pointerdown(state.event(header, startX));
  state.handlers.pointermove(state.event(header, startX + 50));
  state.handlers.lostpointercapture();
  state.handlers.pointerup(state.event(header, startX + 50));
  assert.equal(state.writes.length, 1);
});

test('double-click resets a column and persists the resulting profile', () => {
  const state = createTable([50, 60, 320, 160, 220, 180, 150], 1140);
  const header = state.headers[2];
  state.handlers.dblclick(state.event(header, header.getBoundingClientRect().right - 1));
  assert.equal(state.writes.length, 1);
  assert.equal(state.saved()[2], 260);
  assert.equal(state.table.getBoundingClientRect().width, 1140);
});

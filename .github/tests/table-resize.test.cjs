const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'index.js'), 'utf8');
const resizeSource = source.slice(source.indexOf('function setupResizableTables()'), source.indexOf("if (document.readyState === 'loading')"));
const storageKey = 'table.column_widths:/artists:0:filter_table';

test('legacy band columns expose matching headers and body cells for resizing', () => {
  for (const file of ['bands_details.php', 'bands_no_details.php']) {
    const result = spawnSync('php', ['-r', `
      $_SERVER['DOCUMENT_ROOT'] = getenv('TEST_ROOT');
      $_SERVER['SCRIPT_NAME'] = '/' . getenv('TEST_PAGE');
      $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
      $_SERVER['HTTP_HOST'] = 'localhost:8003';
      ob_start();
      require $_SERVER['DOCUMENT_ROOT'] . '/' . getenv('TEST_PAGE');
      $html = ob_get_clean();
      $document = new DOMDocument();
      $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
      $xpath = new DOMXPath($document);
      $tables = [];
      foreach ($document->getElementsByTagName('table') as $table) {
        $tables[] = [
          'headers' => $xpath->query('./thead/tr/th[@scope="col"]', $table)->length,
          'cells' => $xpath->query('./tbody/tr/td', $table)->length
        ];
      }
      echo json_encode($tables);
    `], {
      encoding: 'utf8',
      env: { ...process.env, TEST_ROOT: path.resolve(__dirname, '..', '..'), TEST_PAGE: file }
    });
    assert.equal(result.status, 0, result.stderr);
    assert.deepEqual(JSON.parse(result.stdout), Array.from({ length: 3 }, () => ({
      headers: 4, cells: 4
    })), file);
  }
});

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
    id: 'filter_table', className: 'artists-table', dataset: {}, clientLeft: 2,
    style: { setProperty(name, value) { this[name] = value; }, removeProperty(name) { delete this[name]; } },
    classList: classList(), parentElement: { get clientWidth() { return layout.width; } },
    addEventListener(name, callback) { handlers[name] = callback; },
    getBoundingClientRect() { return { left: 0, width: Math.max(1140, layout.width) }; },
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
  assert.equal(parseFloat(state.table.style['--column-resize-guide-left']), header.getBoundingClientRect().right - 2);
  for (const delta of [10, 20, 30, 40, 50]) {
    state.handlers.pointermove(state.event(header, startX + delta));
    assert.equal(state.writes.length, 0);
    assert.equal(parseFloat(state.table.style['--column-resize-guide-left']), header.getBoundingClientRect().right - 2);
  }
  const widths = state.widths();
  state.handlers.pointerup(state.event(header, startX + 50));
  assert.equal(state.table.style['--column-resize-guide-left'], undefined);
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

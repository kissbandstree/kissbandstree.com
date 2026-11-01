const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'sort_table.js'), 'utf8');
const key = 'table.sort:/artists:0:filter_table';

function createTable(saved, pathname = '/artists') {
  const writes = [];
  const warnings = [];
  const headers = ['Name', 'Year', 'Picture'].map((text, index) => ({
    textContent: text, className: index === 2 ? 'sorttable_nosort' : '',
    attributes: index === 0 ? { 'data-sort-default': 'asc', 'data-sort-type': 'text' } : { 'data-sort-type': 'number' },
    classList: { add() {} }, handlers: {}, indicator: null,
    getAttribute(name) { return this.attributes[name] ?? null; },
    addEventListener(name, callback) { this.handlers[name] = callback; },
    querySelector() { return this.indicator; },
    appendChild(indicator) {
      this.indicator = indicator;
      indicator.remove = () => { this.indicator = null; };
    }
  }));
  const rows = [['Beta', '2000'], ['Alpha', '2020'], ['Gamma', '2010']].map(values => ({
    cells: values.map(textContent => ({ textContent, getAttribute() { return null; } }))
  }));
  const body = {
    rows,
    appendChild(row) { this.rows.splice(this.rows.indexOf(row), 1); this.rows.push(row); }
  };
  const table = {
    id: 'filter_table', className: 'sortable', attributes: {},
    tHead: { rows: [{ cells: headers }] }, tBodies: [body],
    getAttribute(name) { return this.attributes[name] ?? null; },
    setAttribute(name, value) { this.attributes[name] = value; }
  };
  const storage = { [key]: saved };
  vm.runInNewContext(source, {
    Intl, window: { location: { pathname } },
    document: { readyState: 'complete', querySelectorAll: () => [table], createElement: () => ({}) },
    localStorage: {
      getItem(name) { return storage[name] ?? null; },
      setItem(name, value) { writes.push([name, value]); storage[name] = value; }
    },
    console: { warn: (...args) => warnings.push(args) }
  });
  return { table, headers, storage, writes, warnings, names: () => body.rows.map(row => row.cells[0].textContent) };
}

test('user sorting saves column and direction and restores actual row order without writes', () => {
  const state = createTable();
  assert.deepEqual(state.names(), ['Alpha', 'Beta', 'Gamma']);
  assert.equal(state.writes.length, 0);
  state.headers[1].handlers.click();
  assert.deepEqual(JSON.parse(state.storage[key]), { colIndex: 1, dir: 'asc' });
  state.headers[1].handlers.click();
  assert.deepEqual(JSON.parse(state.storage[key]), { colIndex: 1, dir: 'desc' });
  const restored = createTable(state.storage[key]);
  assert.deepEqual(restored.names(), ['Alpha', 'Gamma', 'Beta']);
  assert.equal(restored.table.attributes['data-sort-dir'], 'desc');
  assert.equal(restored.headers[1].indicator.textContent, ' \u25b4');
  assert.equal(restored.writes.length, 0);
  restored.headers[1].handlers.click();
  assert.equal(JSON.parse(restored.storage[key]).dir, 'asc');
});

test('sorting is isolated by page and clearing storage restores the authored default', () => {
  const saved = JSON.stringify({ colIndex: 1, dir: 'desc' });
  const otherPage = createTable(saved, '/bands');
  assert.deepEqual(otherPage.names(), ['Alpha', 'Beta', 'Gamma']);
  const reset = createTable();
  assert.deepEqual(reset.names(), ['Alpha', 'Beta', 'Gamma']);
});

test('invalid saved sorting warns and uses the authored default', () => {
  for (const saved of ['{', 'null', '{"colIndex":2,"dir":"asc"}', '{"colIndex":9,"dir":"asc"}',
    '{"colIndex":-1,"dir":"asc"}', '{"colIndex":1,"dir":"bad"}', '{"colIndex":"1","dir":"asc"}']) {
    const state = createTable(saved);
    assert.equal(state.warnings.length, 1);
    assert.equal(state.writes.length, 0);
    assert.deepEqual(state.names(), ['Alpha', 'Beta', 'Gamma']);
  }
});

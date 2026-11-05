const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'search.js'), 'utf8');

function createSearch(saved = {}) {
  const elements = Object.fromEntries(['bandSearchInput', 'bandSearchResults', 'artistSearchInput', 'artistSearchResults'].map(id => [
    id, { value: '', innerHTML: '', handlers: {}, addEventListener(event, callback) { this.handlers[event] = callback; } }
  ]));
  const storage = { ...saved };
  const writes = [];
  vm.runInNewContext(source, {
    document: { getElementById: id => elements[id] },
    window: { kbtSearchData: {
      bands: [{ name: 'Kiss', slug: 'kiss', years: '1973' }],
      artists: [{ name: 'Paul Stanley', slug: 'paul_stanley' }]
    } },
    localStorage: {
      getItem: key => storage[key] ?? null,
      setItem(key, value) { storage[key] = value; writes.push(key); }
    }
  });
  return { elements, storage, writes };
}

test('both search fields and their results restore without saving again', () => {
  const state = createSearch();
  state.elements.bandSearchInput.value = 'Kiss';
  state.elements.bandSearchInput.handlers.input();
  state.elements.artistSearchInput.value = 'Paul';
  state.elements.artistSearchInput.handlers.input();
  const reloaded = createSearch(state.storage);
  assert.equal(reloaded.elements.bandSearchInput.value, 'Kiss');
  assert.equal(reloaded.elements.artistSearchInput.value, 'Paul');
  assert.match(reloaded.elements.bandSearchResults.innerHTML, /band\?a=kiss/);
  assert.match(reloaded.elements.artistSearchResults.innerHTML, /artist\?a=paul_stanley/);
  assert.equal(reloaded.writes.length, 0);
});

test('clearing a search stays cleared on reload and Reset restores empty fields', () => {
  const state = createSearch({ 'search.bands': 'Kiss' });
  state.elements.bandSearchInput.value = '';
  state.elements.bandSearchInput.handlers.input();
  const reloaded = createSearch(state.storage);
  assert.equal(reloaded.elements.bandSearchResults.innerHTML, '');
  const reset = createSearch();
  assert.equal(reset.elements.bandSearchInput.value, '');
  assert.equal(reset.elements.artistSearchInput.value, '');
});

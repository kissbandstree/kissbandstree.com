const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const root = path.join(__dirname, '..', '..');
const source = fs.readFileSync(path.join(root, 'index.js'), 'utf8');

function createSettings(url = 'http://localhost:8003/release_related.php?a=shogun_mojo', saved = {}, desktop = false) {
  function element() {
    const classes = new Set();
    return {
      hidden: false, attributes: {}, handlers: {}, focused: false,
      classList: {
        contains: name => classes.has(name),
        add: name => classes.add(name),
        remove: name => classes.delete(name),
        toggle(name, force = !classes.has(name)) {
          if (force) classes.add(name);
          else classes.delete(name);
          return force;
        }
      },
      setAttribute(name, value) { this.attributes[name] = value; },
      addEventListener(name, callback) { this.handlers[name] = callback; },
      focus() { this.focused = true; },
      contains(target) { return target === this; },
      click() { return this.handlers.click?.(); }
    };
  }
  const isTree = new URL(url).pathname === '/tree.php';
  const ids = ['settings-feedback', 'toggle-mode-button', 'toggle-fullscreen-button',
    'share-settings-button', 'reset-settings-button', ...(isTree
      ? ['toggle-photos-button', 'toggle-comments-button']
      : ['settings-panel', 'settings-menu-container', 'toc', 'toggle-menu-button',
        'burger-container', 'toggle-settings-panel-button', 'toggle-settings-panel-close-button'])];
  const elements = Object.fromEntries(ids.map(id => [id, element()]));
  if (elements['settings-panel']) elements['settings-panel'].hidden = true;
  const events = {};
  const windowEvents = {};
  const layout = { desktop };
  const storage = { ...saved };
  Object.defineProperties(storage, {
    getItem: { value: key => storage[key] ?? null },
    setItem: { value: (key, value) => { storage[key] = String(value); } },
    removeItem: { value: key => { delete storage[key]; } },
    clear: { value: () => { Object.keys(storage).forEach(key => { delete storage[key]; }); } }
  });
  const document = {
    body: element(), documentElement: element(), title: 'Kiss Bands Tree',
    fullscreenEnabled: true, fullscreenElement: null,
    getElementById: id => elements[id] || null,
    querySelector: () => null, querySelectorAll: () => [],
    addEventListener(name, callback) { (events[name] ??= []).push(callback); },
    async exitFullscreen() { document.fullscreenElement = null; }
  };
  document.documentElement.requestFullscreen = async () => {
    document.fullscreenElement = document.documentElement;
  };
  const replacements = [];
  const prompts = [];
  const warnings = [];
  const errors = [];
  const location = new URL(url);
  location.replace = target => replacements.push(target);
  const navigator = {};
  const context = vm.createContext({
    document, navigator, localStorage: storage, URL, URLSearchParams,
    console: { warn: (...args) => warnings.push(args), error: (...args) => errors.push(args) },
    window: {
      location, prompt: (...args) => prompts.push(args),
      matchMedia: () => ({ matches: layout.desktop }),
      addEventListener(name, callback) { (windowEvents[name] ??= []).push(callback); }
    }
  });
  vm.runInContext(source, context);
  for (const callback of events.DOMContentLoaded) callback();
  return { context, document, elements, events, windowEvents, layout, storage, navigator, replacements, prompts, warnings, errors };
}

test('mobile menus start closed and close each other, with outside-click and Escape dismissal', () => {
  const { context, document, elements, events } = createSettings();
  context.toggleMenu();
  assert.equal(document.body.classList.contains('toc-lock'), true);
  elements['toggle-settings-panel-button'].click();
  assert.equal(elements['settings-panel'].hidden, false);
  assert.equal(elements['toggle-settings-panel-button'].attributes['aria-expanded'], 'true');
  assert.equal(elements.toc.classList.contains('toc-open'), false);
  assert.equal(document.body.classList.contains('toc-lock'), false);
  for (const callback of events.keydown) callback({ key: 'Escape' });
  assert.equal(elements['settings-panel'].hidden, true);
  assert.equal(elements['toggle-settings-panel-button'].focused, true);
  context.toggleSettingsPanel();
  context.toggleMenu();
  assert.equal(elements['settings-panel'].hidden, true);
  context.toggleSettingsPanel();
  for (const callback of events.click) callback({ target: {}, isTrusted: true });
  assert.equal(elements['settings-panel'].hidden, true);
  assert.equal(document.body.classList.contains('settings-lock'), false);
});

test('desktop sidebars default open, stay independent and survive outside clicks, Escape and navigation', () => {
  const { context, document, elements, events, storage } = createSettings(undefined, {}, true);
  assert.equal(elements.toc.classList.contains('toc-open'), true);
  assert.equal(elements['settings-panel'].hidden, false);
  assert.equal(document.body.classList.contains('toc-lock'), false);
  assert.equal(document.body.classList.contains('settings-lock'), false);
  for (const callback of events.click) callback({ target: {}, isTrusted: true });
  for (const callback of events.keydown) callback({ key: 'Escape' });
  elements.toc.handlers.click({ target: { closest: () => ({}) } });
  assert.equal(elements.toc.classList.contains('toc-open'), true);
  assert.equal(elements['settings-panel'].hidden, false);
  context.toggleMenu();
  assert.equal(elements.toc.classList.contains('toc-open'), false);
  assert.equal(elements['settings-panel'].hidden, false);
  assert.equal(storage['global.toc_sidebar_open'], 'false');
  assert.equal(storage['global.settings_sidebar_open'], 'true');
  const next = createSettings(undefined, storage, true);
  assert.equal(next.elements.toc.classList.contains('toc-open'), false);
  assert.equal(next.elements['settings-panel'].hidden, false);
});

test('mobile navigation and Back close TOC, while resize preserves desktop preferences', () => {
  const { context, elements, windowEvents, layout, storage } = createSettings(undefined, {}, true);
  layout.desktop = false;
  for (const callback of windowEvents.resize) callback();
  assert.equal(elements.toc.classList.contains('toc-open'), false);
  assert.equal(elements['settings-panel'].hidden, true);
  context.toggleMenu();
  elements.toc.handlers.click({ target: { closest: () => ({}) } });
  assert.equal(elements.toc.classList.contains('toc-open'), false);
  context.toggleMenu();
  for (const callback of windowEvents.pageshow) callback();
  assert.equal(elements.toc.classList.contains('toc-open'), false);
  assert.equal(storage['global.toc_sidebar_open'], 'true');
  layout.desktop = true;
  for (const callback of windowEvents.resize) callback();
  assert.equal(elements.toc.classList.contains('toc-open'), true);
  assert.equal(elements['settings-panel'].hidden, false);
});

test('Dark mode is active by default and follows the displayed theme without changing saved preferences', () => {
  for (const url of ['http://localhost:8003/about.php', 'http://localhost:8003/tree.php']) {
    for (const saved of [{}, { mode: 'light' }, { mode: 'dark' }]) {
      const { document, elements, storage } = createSettings(url, saved);
      const button = elements['toggle-mode-button'];
      const dark = saved.mode !== 'dark';
      assert.equal(storage.mode, saved.mode);
      assert.equal(document.documentElement.classList.contains('invert-colors'), !dark);
      assert.equal(button.classList.contains('button-on'), dark);
      assert.equal(button.attributes['aria-pressed'], String(dark));
      button.click();
      assert.equal(storage.mode, dark ? 'dark' : 'light');
      assert.equal(document.documentElement.classList.contains('invert-colors'), dark);
      assert.equal(button.classList.contains('button-on'), !dark);
      assert.equal(button.attributes['aria-pressed'], String(!dark));
      button.click();
      assert.equal(button.classList.contains('button-on'), dark);
      assert.equal(button.attributes['aria-pressed'], String(dark));
    }
  }
});

test('native button keyboard activation is not duplicated by the legacy icon handler', () => {
  const { document, events } = createSettings();
  let clicks = 0;
  document.activeElement = { tagName: 'BUTTON', hasAttribute: () => false, click: () => clicks++ };
  for (const callback of events.keydown) callback({ key: 'Enter' });
  assert.equal(clicks, 0);
  document.activeElement.tagName = 'svg';
  document.activeElement.hasAttribute = () => true;
  for (const callback of events.keydown) callback({ key: 'Enter' });
  assert.equal(clicks, 1);
});

test('Fullscreen enters and exits, synchronizes state and reports rejection', async () => {
  const { context, document, elements, errors } = createSettings();
  await context.toggleFullscreen();
  context.updateFullscreenButtonState();
  assert.equal(elements['toggle-fullscreen-button'].attributes['aria-pressed'], 'true');
  await context.toggleFullscreen();
  context.updateFullscreenButtonState();
  assert.equal(elements['toggle-fullscreen-button'].attributes['aria-pressed'], 'false');
  document.documentElement.requestFullscreen = async () => { throw new Error('Denied'); };
  await context.toggleFullscreen();
  assert.equal(errors.length, 1);
  assert.equal(elements['settings-feedback'].textContent, 'Unable to change fullscreen mode.');
});

test('Share uses the current detail URL and native sharing before clipboard fallback', async () => {
  const { context, navigator, elements } = createSettings();
  let shared;
  navigator.share = async data => { shared = data; };
  await context.shareCurrentPage();
  assert.equal(shared.url, 'http://localhost:8003/release_related.php?a=shogun_mojo');
  assert.equal(shared.title, 'Kiss Bands Tree');
  delete navigator.share;
  let copied;
  navigator.clipboard = { writeText: async value => { copied = value; } };
  await context.shareCurrentPage();
  assert.equal(copied, shared.url);
  assert.equal(elements['settings-feedback'].textContent, 'Link copied.');
});

test('Share cancellation does not copy, while failures fall back with diagnostics', async () => {
  const { context, navigator, prompts, warnings } = createSettings();
  navigator.share = async () => { throw Object.assign(new Error(), { name: 'AbortError' }); };
  await context.shareCurrentPage();
  assert.equal(prompts.length, 0);
  navigator.share = async () => { throw new Error('Share denied'); };
  navigator.clipboard = { writeText: async () => { throw new Error('Clipboard denied'); } };
  await context.shareCurrentPage();
  assert.equal(warnings.length, 2);
  assert.equal(prompts.length, 1);
  assert.equal(prompts[0][1], 'http://localhost:8003/release_related.php?a=shogun_mojo');
});

test('Reset clears all saved settings and filters, restores Grid and preserves queries and detail pages', () => {
  for (const [page, expected] of [
    ['artists_table_detailed.php?search=Tommy#top', 'artists_grid.php?search=Tommy#top'],
    ['bands_list.php', 'bands_grid.php'],
    ['release_related.php?a=shogun_mojo', 'release_related.php?a=shogun_mojo'],
    ['tree.php?focus=kiss', 'tree.php?focus=kiss']
  ]) {
    const setup = createSettings('http://localhost:8003/' + page, {
      mode: 'dark', kbt_view_mode: 'list', divState_cover: 'collapsed',
      kbt_members: '["Tommy Thayer"]', kbt_bands: '["Kiss"]',
      isPlaying: 'true', currentArtist: 'tommy_thayer',
      isPlayingReleaseBand: 'true', currentReleaseBand: 'kiss',
      isPlayingReleaseRelated: 'true', currentReleaseRelated: 'shogun_mojo',
      'tree.photos': 'false', 'tree.comments': 'false',
      futureSetting: 'custom',
      'global.toc_sidebar_open': 'false', 'global.settings_sidebar_open': 'false'
    });
    setup.elements['reset-settings-button'].click();
    assert.deepEqual(Object.keys(setup.storage), []);
    assert.deepEqual(setup.replacements, ['http://localhost:8003/' + expected]);
  }
});

test('header Settings and inline Tree controls share the four standard actions', () => {
  const markup = fs.readFileSync(path.join(root, 'settings.php'), 'utf8');
  const controls = fs.readFileSync(path.join(root, 'settings-controls.php'), 'utf8');
  for (const id of ['toggle-mode-button', 'toggle-fullscreen-button', 'share-settings-button', 'reset-settings-button']) {
    assert.equal(controls.split(`id="${id}"`).length - 1, 1);
  }
  assert.match(markup, /id="settings-menu-container" class="no-swipe"/);
  assert.match(markup, /id="settings-panel"[^>]*hidden/);
  const header = fs.readFileSync(path.join(root, 'toc.php'), 'utf8');
  assert.match(header, /require\(\$_SERVER\['DOCUMENT_ROOT'\] \. "\/settings.php"\)/);
  for (const file of ['settings.php', 'tree.php']) {
    const content = fs.readFileSync(path.join(root, file), 'utf8');
    assert.match(content, /require __DIR__ \. '\/settings-controls.php'/);
    assert.doesNotMatch(content, /onclick="toggleMode\(\)"/);
  }
  const tree = fs.readFileSync(path.join(root, 'tree.php'), 'utf8');
  assert.doesNotMatch(tree, /\/settings.php|id="toggle-settings-panel-button"/);
  assert.match(tree, /class="tree-controls no-swipe"/);
  for (const option of ['photos', 'comments']) {
    assert.match(tree, new RegExp(`id="toggle-${option}-button"[^>]*aria-pressed="true"`));
  }
});

test('Tree Photos and Comments default on, toggle independently and persist across visits', () => {
  const setup = createSettings('http://localhost:8003/tree.php', {}, true);
  assert.equal(setup.elements['settings-panel'], undefined);
  for (const option of ['photos', 'comments']) {
    const button = setup.elements[`toggle-${option}-button`];
    assert.equal(button.attributes['aria-pressed'], 'true');
    button.click();
    assert.equal(setup.storage[`tree.${option}`], 'false');
    assert.equal(button.attributes['aria-pressed'], 'false');
    assert.equal(setup.document.body.classList.contains(`tree-${option}-hidden`), true);
  }
  const restored = createSettings('http://localhost:8003/tree.php', setup.storage, true);
  assert.equal(restored.elements['toggle-photos-button'].attributes['aria-pressed'], 'false');
  assert.equal(restored.elements['toggle-comments-button'].attributes['aria-pressed'], 'false');
  restored.elements['toggle-photos-button'].click();
  assert.equal(restored.document.body.classList.contains('tree-photos-hidden'), false);
  assert.equal(restored.document.body.classList.contains('tree-comments-hidden'), true);
  restored.elements['reset-settings-button'].click();
  const reset = createSettings('http://localhost:8003/tree.php', restored.storage, true);
  assert.equal(reset.elements['toggle-mode-button'].attributes['aria-pressed'], 'true');
  assert.equal(reset.elements['toggle-photos-button'].attributes['aria-pressed'], 'true');
  assert.equal(reset.elements['toggle-comments-button'].attributes['aria-pressed'], 'true');
});

test('Tree icon buttons retain their labels without hiding Settings menu text', () => {
  const controls = fs.readFileSync(path.join(root, 'settings-controls.php'), 'utf8');
  const tree = fs.readFileSync(path.join(root, 'tree.php'), 'utf8');
  for (const label of ['Dark mode', 'Fullscreen', 'Share', 'Reset']) {
    assert.ok(controls.includes(`<span class="control-label">${label}</span>`));
  }
  for (const label of ['Photos', 'Comments']) {
    assert.ok(tree.includes(`<span class="control-label">${label}</span>`));
  }
  const styles = fs.readFileSync(path.join(root, 'style.css'), 'utf8');
  assert.match(styles, /\.sr-only,\s*\.tree-controls \.control-label\s*\{/);
  assert.doesNotMatch(styles, /(?:^|\})\s*\.control-label\s*\{/);
});

test('Tree visibility preferences do not change other pages or their Settings menus', () => {
  const setup = createSettings(undefined, { 'tree.photos': 'false', 'tree.comments': 'false' });
  assert.equal(setup.document.body.classList.contains('tree-photos-hidden'), false);
  assert.equal(setup.document.body.classList.contains('tree-comments-hidden'), false);
});

test('inline Tree controls reuse Dark mode, Fullscreen, Share and Reset without a Settings panel', async () => {
  const setup = createSettings('http://localhost:8003/tree.php?focus=kiss', {}, true);
  setup.elements['toggle-mode-button'].click();
  assert.equal(setup.storage.mode, 'dark');
  assert.equal(setup.elements['toggle-mode-button'].attributes['aria-pressed'], 'false');
  await setup.elements['toggle-fullscreen-button'].click();
  assert.equal(setup.document.fullscreenElement, setup.document.documentElement);
  await setup.elements['toggle-fullscreen-button'].click();
  assert.equal(setup.document.fullscreenElement, null);
  const copied = [];
  setup.navigator.clipboard = { writeText: async value => copied.push(value) };
  await setup.elements['share-settings-button'].click();
  assert.deepEqual(copied, ['http://localhost:8003/tree.php?focus=kiss']);
  assert.equal(setup.elements['settings-feedback'].textContent, 'Link copied.');
  setup.elements['reset-settings-button'].click();
  assert.deepEqual(Object.keys(setup.storage), []);
  assert.deepEqual(setup.replacements, ['http://localhost:8003/tree.php?focus=kiss']);
});

test('the copied sprite contains every literal site icon reference', () => {
  const sprite = fs.readFileSync(path.join(root, 'icons.svg'), 'utf8');
  const ids = new Set([...sprite.matchAll(/\bid="([^"]+)"/g)].map(match => match[1]));
  for (const file of fs.readdirSync(root).filter(name => /\.(php|js|css)$/.test(name))) {
    const content = fs.readFileSync(path.join(root, file), 'utf8');
    for (const [, id] of content.matchAll(/icons\.svg#([\w-]+)/g)) {
      assert.equal(ids.has(id), true, `${file}: missing ${id}`);
    }
  }
});
